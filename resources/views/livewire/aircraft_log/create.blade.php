<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Google\Cloud\VideoIntelligence\V1\VideoIntelligenceServiceClient;
use Google\Cloud\Vision\V1\Likelihood;
use Google\Cloud\VideoIntelligence\V1\Feature as VideoFeature;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;
use App\Enums\Media;

new class extends Component
{
    use WithFileUploads;

    public Collection $airports;
    public Collection $airlines;
    public Collection $aircraftCollection;

    #[Validate('required')]
    public ?string $loggedAt;

    #[Validate('required')]
    public ?string $airport = null;

    #[Validate]
    public ?string $airline = null;

    #[Validate]
    public ?string $aircraft = null;

    #[Validate]
    public string $description = "";

    #[Validate]
    public string $registration = "";

    #[Validate('file|max:512000')]
    public array $media = [];  // Handles both images and videos


    public function mount()
    {
        $this->airports = Airport::all();
        $this->aircraftCollection = Aircraft::all();
        $this->airlines = Airline::all();
    }

    public function extractThumbnail($videoFile)
    {
        $originalPath = "/livewire-tmp/" . $videoFile->getFilename();
        $thumbnailPath = "/temp/video_thumbnails/" . $videoFile->hashName() . ".jpg";

        try {
            FFMpeg::fromDisk('local')
                ->open($originalPath)
                ->getFrameFromSeconds(1)
                ->export()
                ->toDisk('local')
                ->save($thumbnailPath);

            return $thumbnailPath;

        } catch (\Exception $e) {
            Toaster::warning('Failed to extract thumbnail: ' . $e->getMessage());
            return false;
        }
    }

    public function analyzeImage($imagePath)
    {
        $imageAnnotator = new ImageAnnotatorClient();

        try {
            $imageData = file_get_contents($imagePath);
            $response = $imageAnnotator->safeSearchDetection($imageData);
            $safeSearch = $response->getSafeSearchAnnotation();

            if ($safeSearch) {
                $adult = $safeSearch->getAdult();
                $violence = $safeSearch->getViolence();
                $medical = $safeSearch->getMedical();

                $unacceptableLikelihoods = [Likelihood::LIKELY, Likelihood::VERY_LIKELY];

                if (in_array($adult, $unacceptableLikelihoods) || in_array($violence, $unacceptableLikelihoods) || in_array($medical, $unacceptableLikelihoods)) {
                    $imageAnnotator->close();
                    return false; // Inappropriate content found
                }
            }
            $imageAnnotator->close();
            return true; // Safe content
        } catch (\Exception $e) {
            $imageAnnotator->close();
            return false; // Error in processing
        }
    }

    public function analyzeVideo($videoPath)
    {
        $videoClient = new VideoIntelligenceServiceClient();

        try {
            $inputUri = "gs://{$videoPath}";
            $features = [VideoFeature::EXPLICIT_CONTENT_DETECTION];
            $operation = $videoClient->annotateVideo([
                'inputUri' => $inputUri,
                'features' => $features,
            ]);

            $operation->pollUntilComplete();

            if ($operation->operationSucceeded()) {
                $results = $operation->getResult()->getAnnotationResults()[0];
                $explicitAnnotations = $results->getExplicitAnnotation();

                foreach ($explicitAnnotations->getFrames() as $frame) {
                    $pornographyLikelihood = $frame->getPornographyLikelihood();
                    if ($pornographyLikelihood >= Likelihood::LIKELY) {
                        $videoClient->close();
                        return false; // Inappropriate content found in video
                    }
                }
            }
            $videoClient->close();
            return true; // Safe video content
        } catch (\Exception $e) {
            $videoClient->close();
            return false; // Error in processing
        }
    }

    /**
     * Convert HEIC image to JPEG using ImageMagick.
     */
    public function convertHEICtoJPEG($mediaFile)
    {
        // Get original HEIC file path
        $heicFilePath = $mediaFile->getRealPath();

        // Set the path to store the converted JPEG file
        $convertedFilePath = public_path('storage/temp/' . $mediaFile->hashName() . '.jpg');

        // Convert HEIC to JPEG using ImageMagick
        try {
            exec("magick convert {$heicFilePath} {$convertedFilePath}");
            return $convertedFilePath; // Return the converted file path
        } catch (\Exception $e) {
            Toaster::warning('Failed to convert HEIC to JPEG: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Compress and upload video using FFmpeg.
     */
    public function compressVideo($videoFile)
    {
        // Temporary file paths
        $originalPath = "/livewire-tmp/" . $videoFile->getFilename();
        $compressedPath = "/temp/compressed_videos/" . $videoFile->hashName();

        // Compress video using FFmpeg
        try {
            FFMpeg::fromDisk('local')
                ->open($originalPath)
                ->export()
                ->toDisk('local')
                ->inFormat((new \FFMpeg\Format\Video\X264('libmp3lame'))->setKiloBitrate(1000))
                ->addFilter('-vf', 'scale=1280:-2,setsar=1/1,transpose=1')  // Added transpose for orientation correction
                ->addFilter('-vf', 'transpose=2') // Add transpose for further orientation correction if needed
                ->addFilter('-vf', 'scale=\'if(gt(iw,1920),1920,-2)\':\'if(gt(ih,1080),1080,-2)\',setsar=1/1')
                ->save($compressedPath);

            return storage_path('app'.$compressedPath);
        } catch (\Exception $e) {
            Toaster::warning('Failed to compress video: ' . $e->getMessage());
            return false;
        }
    }

    /**
     * Store function for handling uploads and probabilistic content analysis.
     */
    public function store()
    {
        $validated = $this->validate();

        // Check if the file is an image or a video
        $mimeType = $media->getMimeType();

        $isSafe = true; // Default to safe content

        if (str_contains($mimeType, 'image')) {
            // 1 in 5 chance to analyze image
            if (rand(1, 5) === 1) {
                $isSafe = $this->analyzeImage($media->getRealPath());
            }
        } elseif (str_contains($mimeType, 'video')) {
            // 1 in 10 chance to analyze video
            if (rand(1, 10) === 1) {
                $isSafe = $this->analyzeVideo($media->getRealPath());//$mediaFile->store('video_temp', 'gcs'));
            }
        }

        if (!$isSafe) {
            Toaster::warning('The uploaded media contains inappropriate content and cannot be uploaded.');
            throw new \RuntimeException("The uploaded media contains inappropriate content and cannot be uploaded.");
        }


        $newAircraftLog = auth()->user()->aircraftLogs()->create([
            "airport_id" => $this->airport,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline,
            "registration" => strtoupper($this->registration),
            "aircraft_id" => $this->aircraft,
        ]);

        // Check if the file is an image or a video
        $mimeType = $media->getMimeType();

        if (str_contains($mimeType, 'image')) {
            // Check if it's an HEIC file and convert it to JPEG
            if ($mimeType === 'image/heic') {
                $convertedFilePath = $this->convertHEICtoJPEG($media);
                if (!$convertedFilePath) {
                    Toaster::warning('Failed to process HEIC file.');
                    throw new \RuntimeException("The uploaded HEIC image could not be processed.");
                }
            } else {
                $convertedFilePath = $media->getRealPath();
            }

            $filePath = $convertedFilePath;
            $storedThumbnailFilePath = null;
        } elseif (str_contains($mimeType, 'video')) {
            // Compress and upload the video
            $filePath = $this->compressVideo($media);
            $thumbnailFilePath = $this->extractThumbnail($media);

            $storedThumbnailFilePath = Storage::disk('s3')
                ->putFile(
                    'aircraft/thumbnails',
                    new \Illuminate\Http\File(storage_path('app'. $thumbnailFilePath)),
                    [
                        'CacheControl' => 'public, max-age=31536000, immutable',  // Cache for 1 year
            ]);
        }

        $storedFilePath = Storage::disk('s3')
            ->putFile(
                'aircraft',
                new \Illuminate\Http\File($filePath),
                [
                    'CacheControl' => 'public, max-age=31536000, immutable',  // Cache for 1 year
        ]);


            // Clean up the temporary file
            unlink($filePath);

        // Save media record
        auth()->user()->mediaItems()->create([
            "path" => $storedFilePath,
            "aircraft_log_id" => $newAircraftLog->id,
            "type" => str_contains($mimeType, 'video') ? Media::VIDEO->value : Media::IMAGE->value,
            "thumbnail_path" => $storedThumbnailFilePath,
        ]);

        // Cache the media URL
        $this->cacheMediaUrl($storedFilePath);


        Toaster::info('Log created successfully.');
        $this->reset();
        $this->dispatch('aircraft_log-created');
        $this->mount();

    }

    /**
     * Cache the S3 media URL immediately after uploading.
     */
    public function cacheMediaUrl(string $path): void
    {
        $cacheKey = "s3_media_url_" . md5($path);
        Cache::put($cacheKey, Storage::disk('s3')->temporaryUrl($path, now()->addDays(7)), now()->addDays(7));
    }

    public function close()
    {
        $this->resetProperties();
    }

    private function resetProperties()
    {
        $this->loggedAt = null;
        $this->airport = null;
        $this->airline = null;
        $this->aircraft = null;
        $this->description = "";
        $this->removeUploadedMedia();
    }

    public function removeUploadedMedia()
    {
        $this->media = [];
    }
}



?>


<x-modal-card title="Add Photos/Videos" name="logModal">
    <form wire:submit='store()'>
        <div class="grid grid-cols-1 gap-4">
            <!-- Existing Form Inputs -->
            <div class="grid-cols-1 gap-4 sm:grid-cols-2">
                <x-datetime-picker
                    class="pd-2"
                    wire:model="loggedAt"
                    label="Date"
                    placeholder="Date"
                    without-time
                />

                <x-select
                    class="pd-2"
                    label="Airport"
                    placeholder="Please select"
                    wire:model='airport'
                    searchable="true"
                    min-items-for-search="2"
                >
                    @foreach ($airports as $airport)
                        <x-select.option value="{{ $airport->id }}" label="{{ $airport->name }} ({{ $airport->code }})" />
                    @endforeach
                </x-select>

                <x-select
                    class="pd-2"
                    label="Airline"
                    placeholder="Please select"
                    wire:model='airline'
                    searchable="true"
                    min-items-for-search="2"
                >
                    @foreach ($airlines as $airline)
                        <x-select.option value="{{ $airline->id }}" label="{{ $airline->name }}" />
                    @endforeach
                </x-select>

                <x-select
                    class="pd-2"
                    label="Aircraft"
                    placeholder="Please select"
                    wire:model='aircraft'
                    searchable="true"
                    min-items-for-search="2"
                >
                    @foreach ($aircraftCollection as $aircraftType)
                        <x-select.option value="{{ $aircraftType->id }}" label="{{ $aircraftType->manufacturer}} {{ $aircraftType->model }}-{{ $aircraftType->varient }}" />
                    @endforeach
                </x-select>

                <x-input
                    label="Registration"
                    placeholder="G-PNCB"
                    wire:model='registration'
                    style="text-transform: uppercase"
                />
            </div>

            {{-- File upload for images and videos --}}
            @if (!$media)
            <div
                x-data="{ isUploading: false, progress: 0 }"
                x-on:livewire-upload-start="isUploading = true"
                x-on:livewire-upload-finish="isUploading = false"
                x-on:livewire-upload-error="isUploading = false"
                x-on:livewire-upload-progress="progress = $event.detail.progress"
            >
                <label for="media">
                    <div class="flex items-center justify-center h-20 col-span-1 bg-gray-100 shadow-md cursor-pointer sm:col-span-2 dark:bg-secondary-700 rounded-xl">
                        <div class="flex flex-col items-center justify-center">
                            <x-icon name="cloud-arrow-up" class="w-8 h-8 text-blue-600 dark:text-teal-600" />
                            <p class="text-blue-600 dark:text-teal-600">
                                Click to add media (images or videos)
                            </p>
                        </div>
                    </div>
                </label>
                <input type="file" id="media" wire:model="media" multiple hidden />

                @error('media.*')
                    <span class="error">{{ $message }}</span>
                @enderror

                <!-- Progress Bar -->
                <div x-show="isUploading" class="flex items-center justify-center h-20 col-span-1 shadow-md">
                    <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24">
                        <path fill="currentColor" d="M20.27,4.74a4.93,4.93,0,0,1,1.52,4.61a5.32,5.32,0,0,1-4.1,4.51a5.12,5.12,0,0,1-5.2-1.5a5.53,5.53,0,0,0,6.13-1.48A5.66,5.66,0,0,0,20.27,4.74ZM12.32,11.53a5.49,5.49,0,0,0-1.47-6.2A5.57,5.57,0,0,0,4.71,3.72a5.17,5.17,0,0,1,4.82-1.52a5.52,5.52,0,0,1,4.37,4.25a5.28,5.28,0,0,1-1.58,5.1Z"/>
                    </svg>
                </div>
            </div>
            @endif

            {{-- Media Preview --}}
            @if ($media)
            <div class="max-w-6xl mx-auto duration-1000 delay-300 select-none ease animate-fade-in-view">
                <p>File: {{ $media->getClientOriginalName() }}</p>
            </div>
            @endif
        </div>

        <div class="pt-2 border-b-2"></div>
        <div name="footer" class="flex justify-between gap-x-4">
            @if($media)
                <x-button class="mt-4" flat negative label="Clear media" wire:click='removeUploadedMedia' />
            @endif
            <div class="flex gap-x-4">
                <x-button flat class="justify-center mt-4" label="Cancel" x-on:click="close" wire:click='close' />
                <x-primary-button flat class="justify-center mt-4">{{ __('Save') }}</x-primary-button>
            </div>
        </div>
    </form>
</x-model-card>

@script
    <script>
        $wire.on('aircraft_log-created', ({ id }) => {
            $closeModal('logModal');
        });
    </script>
@endscript


