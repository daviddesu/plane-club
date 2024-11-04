<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Collection;
use App\Jobs\ProcessVideoUpload;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Enums\FlyingStatus;
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
    public ?string $status = null;

    #[Validate('required_if:status,1,2')]
    public ?string $departureAirport = null;

    #[Validate('required_if:status,2,3')]
    public ?string $arrivalAirport = null;

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

    /**
     * Prepares the media file for local processing.
     */
    private function prepareMediaFile()
    {
        if (env('FILESYSTEM_DISK') === 's3') {
            // Livewire temporary uploads are stored on S3
            $originalS3Path = $this->media->getRealPath(); // S3 path
            $localPath = $this->downloadFromS3($originalS3Path);

            // Delete the temporary file from S3 after downloading
            $this->deleteFromS3($originalS3Path);

            return $localPath;
        } else {
            // Livewire temporary uploads are stored locally
            return $this->media->getRealPath();
        }
    }

    public function convertImagetoJPEG($imagePath)
    {
        // Check if the image is already a JPEG
        $mimeType = $this->getMimeType($imagePath);

        if ($mimeType === 'image/jpeg' || $mimeType === 'image/jpg') {
            // Image is already JPEG, return the original path
            return $imagePath;
        }

        $convertedFilePath = sys_get_temp_dir() . '/' . uniqid('image_', true) . '.jpg';

        // Initialize variables to capture output and status
        $output = [];
        $returnVar = 0;

        // Use 'convert' instead of 'magick convert'
        exec("convert " . escapeshellarg($imagePath) . " " . escapeshellarg($convertedFilePath) . " 2>&1", $output, $returnVar);

        if ($returnVar !== 0) {
            // Conversion failed
            $errorMessage = implode("\n", $output);
            Toaster::warning('Failed to convert image to JPEG: ' . $errorMessage);
            return false;
        }

        // Check if the output file exists
        if (!file_exists($convertedFilePath)) {
            Toaster::warning('Conversion failed: Output file not found.');
            return false;
        }

        return $convertedFilePath; // Return the converted file path
    }


    private function getMimeType($filePath)
    {
        if (function_exists('finfo_open')) {
            $finfo = finfo_open(FILEINFO_MIME_TYPE);
            $mimeType = finfo_file($finfo, $filePath);
            finfo_close($finfo);
            return $mimeType;
        } else {
            // Fallback to getimagesize for images
            $imageInfo = getimagesize($filePath);
            if ($imageInfo && isset($imageInfo['mime'])) {
                return $imageInfo['mime'];
            }
            // Unable to determine MIME type
            return null;
        }
    }

    public function store()
    {
        $validated = $this->validate();

        // Prepare the media file for local processing
        $mediaFilePath = $this->prepareMediaFile();

        // Get the MIME type from the local file
        $mimeType = $this->getMimeType($mediaFilePath);

        if (!$mimeType) {
            Toaster::warning('Unable to determine the MIME type of the uploaded file.');
            throw new \RuntimeException("Unable to determine the MIME type of the uploaded file.");
        }

        $newAircraftLog = auth()->user()->aircraftLogs()->create([
            "arrival_airport_id" => $this->arrivalAirport,
            "departure_airport_id" => $this->departureAirport,
            "status" => $this->status,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline,
            "registration" => strtoupper($this->registration),
            "aircraft_id" => $this->aircraft,
        ]);

        if (str_contains($mimeType, 'image')) {
            // Process image synchronously (since it's quick)
            $filePath = $this->convertImagetoJPEG($mediaFilePath);
            if (!$filePath) {
                Toaster::warning('Failed to process image.');
                throw new \RuntimeException("The uploaded image could not be processed and converted to JPG.");
            }

            // Upload processed file to S3
            $storedFilePath = Storage::disk(getenv('FILESYSTEM_DISK'))
                ->putFile(
                    'aircraft',
                    new \Illuminate\Http\File($filePath),
                    [
                        'CacheControl' => 'public, max-age=31536000, immutable',
                    ]
                );

            // Clean up the local temporary files
            if (file_exists($filePath)) {
                unlink($filePath);
            }
            if ($filePath !== $mediaFilePath && file_exists($mediaFilePath)) {
                unlink($mediaFilePath);
            }

            // Save media record
            $mediaItem = auth()->user()->mediaItems()->create([
                "path" => $storedFilePath,
                "aircraft_log_id" => $newAircraftLog->id,
                "type" => Media::IMAGE->value,
                "thumbnail_path" => null,
                'status' => 'processed',
                'raw_video_path' => null,
            ]);

            // Cache the media URL
            $this->cacheMediaUrl($storedFilePath);

            Toaster::info('Log created successfully.');
        } elseif (str_contains($mimeType, 'video')) {
            // Upload raw video file to S3
            $rawVideoPath = Storage::disk(getenv('FILESYSTEM_DISK'))
                ->putFile(
                    'aircraft/raw_videos',
                    new \Illuminate\Http\File($mediaFilePath),
                    [
                        'CacheControl' => 'public, max-age=31536000, immutable',
                    ]
                );

            // Clean up the local temporary file
            if (file_exists($mediaFilePath)) {
                unlink($mediaFilePath);
            }

            // Save media record with status 'processing'
            $mediaItem = auth()->user()->mediaItems()->create([
                "path" => "",
                "aircraft_log_id" => $newAircraftLog->id,
                "type" => Media::VIDEO->value,
                "thumbnail_path" => null,
                'status' => 'processing',
                'raw_video_path' => $rawVideoPath, // Store the path to the raw video
            ]);

            // Dispatch job to process video
            try {
                if(env('FLY_QUEUE_MACHINE_ID')){
                    Artisan::call('machine:start', ['id' => env('FLY_QUEUE_MACHINE_ID')]);
                }
                ProcessVideoUpload::dispatch($mediaItem->id);
                Log::info('Dispatched ProcessVideoUpload job for Media Item ID: ' . $mediaItem->id);
                Toaster::info('Your video is being processed. It will appear once processing is complete.');
            } catch (\Exception $e) {
                Log::error('Failed to dispatch ProcessVideoUpload job: ' . $e->getMessage());
                Toaster::warning('Failed to dispatch video processing job.');
                throw $e;
            }
        } else {
            // Unsupported media type
            Toaster::warning('Unsupported media type uploaded.');
            throw new \RuntimeException("Unsupported media type uploaded.");
        }

        $this->reset();
        $this->dispatch('aircraft_log-created');
        $this->mount();
    }

    public function cacheMediaUrl(string $path): void
{
    $storageDisk = Storage::disk(getenv('FILESYSTEM_DISK'));
    $cacheKey = "media_url_" . md5($path);

    $driverName = getenv('FILESYSTEM_DISK');

    if ($driverName === 's3') {
        // For S3, generate a temporary URL
        $url = $storageDisk->temporaryUrl($path, now()->addDays(7));
    } else {
        // For local, generate a URL using asset() or url()
        $url = asset('storage/' . $path);
    }

    Cache::put($cacheKey, $url, now()->addDays(7));
}

    public function close()
    {
        $this->resetProperties();
    }

    private function resetProperties()
    {
        $this->loggedAt = null;
        $this->departureAirport = null;
        $this->arrivalAirport = null;
        $this->status = null;
        $this->airline = null;
        $this->aircraft = null;
        $this->description = "";
        $this->removeUploadedMedia();
    }

    public function removeUploadedMedia()
    {
        // Livewire handles local temporary file cleanup automatically
        if ($this->media) {
            if (env('FILESYSTEM_DISK') == 's3') {
                // Delete the temporary file from S3
                $this->deleteFromS3($this->media->getRealPath());
            }
        }
        $this->media = null;
    }
}
?>


<div>
<x-modal-card title="Add a log" name="logModal">
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
                            <x-icon name="cloud-arrow-up" class="w-8 h-8 text-cyan-800 dark:text-cyan-200" />
                            <p class="text-cyan-800 dark:text-cyan-200">
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
                <div x-show="isUploading" class="mt-4">
                    <progress max="100" x-bind:value="progress" class="w-full h-4 progress-bar"></progress>
                    <p class="text-center">Uploading: <span x-text="progress"></span>%</p>
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
                    label="Status"
                    placeholder="Please select"
                    wire:model='status'
                >
                    <x-select.option value="{{ FlyingStatus::DEPARTING->value }}" label="{{ FlyingStatus::getNameByStatus(FlyingStatus::DEPARTING->value) }}" />
                    <x-select.option value="{{ FlyingStatus::ARRIVING->value }}" label="{{ FlyingStatus::getNameByStatus(FlyingStatus::ARRIVING->value) }}" />
                    <x-select.option value="{{ FlyingStatus::IN_FLIGHT->value }}" label="{{ FlyingStatus::getNameByStatus(FlyingStatus::IN_FLIGHT->value) }}" />
                </x-select>

                <x-select
                    class="pd-2"
                    label="Departure airport"
                    placeholder="Please select"
                    wire:model='departureAirport'
                    searchable="true"
                    min-items-for-search="2"
                >
                    @foreach ($airports as $airport)
                        <x-select.option value="{{ $airport->id }}" label="{{ $airport->name }} ({{ $airport->code }})" />
                    @endforeach
                </x-select>

                <x-select
                    class="pd-2"
                    label="Arrival airport"
                    placeholder="Please select"
                    wire:model='arrivalAirport'
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
                <x-button flat class="justify-center mt-4 text-cyan-800" label="Cancel" x-on:click="close" wire:click='close' />
                <x-primary-button flat class="justify-center mt-4">{{ __('Save') }}</x-primary-button>
            </div>
        </div>
    </form>
</x-model-card>

<style>
    /* Remove default appearance */
    progress.progress-bar {
        -webkit-appearance: none;
        appearance: none;
        width: 100%;
        height: 1rem; /* Adjust the height as needed */
    }

    /* Style the progress bar background */
    progress.progress-bar::-webkit-progress-bar {
        background-color: #6b7280; /* grey-500 */
        border-radius: 0.5rem; /* Rounded corners */
    }

    /* Style the progress value */
    progress.progress-bar::-webkit-progress-value {
        background-color: #155e75; /* cyan-800 */
        border-radius: 0.5rem; /* Match the parent border radius */
    }

    /* For Firefox */
    progress.progress-bar::-moz-progress-bar {
        background-color: #155e75; /* cyan-800 */
        border-radius: 0.5rem;
    }
</style>
</div>


@script
    <script>
        $wire.on('aircraft_log-created', ({ id }) => {
            $closeModal('logModal');
        });
    </script>
@endscript


