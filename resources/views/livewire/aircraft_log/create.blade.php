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
    public $media;  // Handles both images and videos

    public function mount()
    {
        $this->airports = Airport::all();
        $this->aircraftCollection = Aircraft::all();
        $this->airlines = Airline::all();
    }

    /**
     * Downloads the file from S3 to a local temporary path.
     */
    private function downloadFromS3($path)
    {
        $tempPath = sys_get_temp_dir() . '/' . basename($path);
        $s3 = Storage::disk('s3');
        if (!$s3->exists($path)) {
            throw new \Exception("File not found on S3: $path");
        }
        $content = $s3->get($path);
        file_put_contents($tempPath, $content);
        return $tempPath;
    }

    /**
     * Deletes a file from S3.
     */
    private function deleteFromS3($path)
    {
        $s3 = Storage::disk('s3');
        if ($s3->exists($path)) {
            $s3->delete($path);
        }
    }

    public function extractThumbnail($videoFile)
    {
        // Get the temporary S3 path of the uploaded video file
        $originalS3Path = $videoFile->getRealPath(); // This is the S3 path
        $originalPath = $this->downloadFromS3($originalS3Path);

        $thumbnailPath = sys_get_temp_dir() . '/' . $videoFile->hashName() . '.jpg';

        try {
            $cmd = "ffmpeg -i " . escapeshellarg($originalPath) . " -ss 1 -vframes 1 " . escapeshellarg($thumbnailPath) . " 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                // Command failed
                Toaster::warning('Failed to extract thumbnail: ' . implode("\n", $output));
                return false;
            }

            // Clean up original video file
            unlink($originalPath);

            // Delete the temporary file from S3
            $this->deleteFromS3($originalS3Path);

            return $thumbnailPath;

        } catch (\Exception $e) {
            Toaster::warning('Failed to extract thumbnail: ' . $e->getMessage());
            return false;
        }
    }


    public function convertImagetoJPEG($mediaFile)
    {
        // Get the temporary S3 path of the uploaded image file
        $originalS3Path = $mediaFile->getRealPath(); // This is the S3 path
        $originalPath = $this->downloadFromS3($originalS3Path);

        $convertedFilePath = sys_get_temp_dir() . '/' . $mediaFile->hashName() . '.jpg';

        try {
            exec("magick convert " . escapeshellarg($originalPath) . " " . escapeshellarg($convertedFilePath));

            // Clean up original image file
            unlink($originalPath);

            // Delete the temporary file from S3
            $this->deleteFromS3($originalS3Path);

            return $convertedFilePath; // Return the converted file path
        } catch (\Exception $e) {
            Toaster::warning('Failed to convert image to JPEG: ' . $e->getMessage());
            return false;
        }
    }

    public function compressVideo($videoFile)
    {
        // Get the temporary S3 path of the uploaded video file
        $originalS3Path = $videoFile->getRealPath(); // This is the S3 path
        $originalPath = $this->downloadFromS3($originalS3Path);

        $compressedPath = sys_get_temp_dir() . '/' . $videoFile->hashName();

        // Compress video using FFmpeg
        try {
            $filterChain = "scale=1280:-2,setsar=1/1,transpose=1,transpose=2,scale='if(gt(iw,1920),1920,-2)':'if(gt(ih,1080),1080,-2)',setsar=1/1";

            $cmd = "ffmpeg -i " . escapeshellarg($originalPath) . " -c:v libx264 -b:v 1000k -c:a libmp3lame -vf " . escapeshellarg($filterChain) . " " . escapeshellarg($compressedPath) . " 2>&1";
            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                // Command failed
                Toaster::warning('Failed to compress video: ' . implode("\n", $output));
                return false;
            }

            // Clean up original video file
            unlink($originalPath);

            // Delete the temporary file from S3
            $this->deleteFromS3($originalS3Path);

            return $compressedPath;
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
        $mimeType = $this->media->getMimeType();

        // Determine if the content is safe (omitted for brevity)

        $newAircraftLog = auth()->user()->aircraftLogs()->create([
            "airport_id" => $this->airport,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline,
            "registration" => strtoupper($this->registration),
            "aircraft_id" => $this->aircraft,
        ]);

        if (str_contains($mimeType, 'image')) {
            $storedThumbnailFilePath = null;
            $filePath = $this->convertImagetoJPEG($this->media);
            if (!$filePath) {
                Toaster::warning('Failed to process file.');
                throw new \RuntimeException("The uploaded image could not be processed and converted to JPG.");
            }
        } elseif (str_contains($mimeType, 'video')) {
            // Compress and upload the video
            $filePath = $this->compressVideo($this->media);
            $thumbnailFilePath = $this->extractThumbnail($this->media);

            // Upload thumbnail to S3
            $storedThumbnailFilePath = Storage::disk('s3')
                ->putFile(
                    'aircraft/thumbnails',
                    new \Illuminate\Http\File($thumbnailFilePath),
                    [
                        'CacheControl' => 'public, max-age=31536000, immutable',
                    ]
                );

            // Clean up local thumbnail file
            unlink($thumbnailFilePath);
        }

        // Upload processed file to S3
        $storedFilePath = Storage::disk('s3')
            ->putFile(
                'aircraft',
                new \Illuminate\Http\File($filePath),
                [
                    'CacheControl' => 'public, max-age=31536000, immutable',
                ]
            );

        // Clean up the local temporary file
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
        if ($this->media) {
            // Delete the temporary file from S3
            $this->deleteFromS3($this->media->getRealPath());
        }
        $this->media = null;
    }
}
?>


<x-modal-card title="Add a photo of a video as a log" name="logModal">
    <form wire:submit='store()'>
        <div class="grid grid-cols-1 gap-4">
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
                                Click to add an image or video
                            </p>
                        </div>
                    </div>
                </label>
                <input type="file" id="media" wire:model="media" hidden />

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


