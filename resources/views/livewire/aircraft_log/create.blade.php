<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Database\Eloquent\Collection;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Google\Cloud\Vision\V1\ImageAnnotatorClient;
use Masmerise\Toaster\Toaster;
use Illuminate\Support\Facades\Cache;

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

    #[Validate(['media.*' => 'file|max:51200|mimes:jpeg,jpg,png,gif,mp4,mov,avi,wmv'])]
    public array $media = []; // Accept both images and videos

    public function mount()
    {
        $this->airports = Airport::all();
        $this->aircraftCollection = Aircraft::all();
        $this->airlines = Airline::all();
    }

    /**
     * Analyze an image or video for sensitive content.
     */
    public function analyzeMedia($mediaPath, $type): bool
    {
        $imageAnnotator = new ImageAnnotatorClient();

        try {
            if ($type === 'image') {
                $imageData = file_get_contents($mediaPath);
                $response = $imageAnnotator->safeSearchDetection($imageData);
                $safeSearch = $response->getSafeSearchAnnotation();

                if ($safeSearch) {
                    $adult = $safeSearch->getAdult();
                    $violence = $safeSearch->getViolence();
                    $medical = $safeSearch->getMedical();

                    $unacceptableLikelihoods = [
                        \Google\Cloud\Vision\V1\Likelihood::LIKELY,
                        \Google\Cloud\Vision\V1\Likelihood::VERY_LIKELY,
                    ];

                    if (
                        in_array($adult, $unacceptableLikelihoods) ||
                        in_array($violence, $unacceptableLikelihoods) ||
                        in_array($medical, $unacceptableLikelihoods)
                    ) {
                        $imageAnnotator->close();
                        return false;
                    }
                }
            } else if ($type === 'video') {
                // Use a video moderation API, or return true as a placeholder
                return true; // Placeholder, replace with actual moderation
            }

            $imageAnnotator->close();
            return true;
        } catch (\Exception $e) {
            return false;
        }
    }

    public function store()
    {
        $validated = $this->validate();

        foreach ($this->media as $file) {
            $fileType = $file->getMimeType();
            $isSafe = false;

            // Check file type and perform content analysis accordingly
            if (str_contains($fileType, 'image')) {
                $isSafe = $this->analyzeMedia($file->getRealPath(), 'image');
            } else if (str_contains($fileType, 'video')) {
                $isSafe = $this->analyzeMedia($file->getRealPath(), 'video');
            }

            if (!$isSafe) {
                Toaster::warning('The uploaded file contains inappropriate content and cannot be uploaded.');
                throw new \RuntimeException("The uploaded file contains inappropriate content and cannot be uploaded.");
            }
        }

        $newAircraftLog = auth()->user()->aircraftLogs()->create([
            "airport_id" => $this->airport,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline,
            "registration" => strtoupper($this->registration),
            "aircraft_id" => $this->aircraft,
        ]);

        foreach ($this->media as $file) {
            $fileType = $file->getMimeType();

            // Store in S3 based on file type
            if (str_contains($fileType, 'image')) {
                $storedFilePath = $this->storeFileInS3($file, 'images');
            } else if (str_contains($fileType, 'video')) {
                $storedFilePath = $this->storeFileInS3($file, 'videos');
            }

            // Save media record in the database
            auth()->user()->media()->create([
                "path" => $storedFilePath,
                "aircraft_log_id" => $newAircraftLog->id,
            ]);

            // Cache the media URL
            $this->cacheMediaUrl($storedFilePath, $fileType);
        }

        Toaster::info('Log created successfully.');
        $this->reset();
        $this->dispatch('aircraft_log-created');
        $this->mount();
    }

    /**
     * Store files in S3 with proper caching headers.
     */
    public function storeFileInS3($file, $type): string
    {
        return $file->store($type, 's3', [
            'CacheControl' => 'public, max-age=31536000, immutable',
        ]);
    }

    /**
     * Cache the media URL (image/video).
     */
    public function cacheMediaUrl(string $path, string $fileType): void
    {
        $cacheKey = "s3_media_url_" . md5($path);
        $expiration = $fileType === 'image' ? now()->addDays(7) : now()->addHours(6);  // Shorter expiration for videos
        $temporaryUrl = Storage::disk('s3')->temporaryUrl($path, $expiration);

        Cache::put($cacheKey, $temporaryUrl, $expiration);
    }

    public function removeUploadedFiles()
    {
        $this->media = [];
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
        $this->media = [];
        $this->description = "";
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

            {{-- Media Preview --}}
            @if ($media)
                <div class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view">
                    <ul x-ref="gallery" id="gallery" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ($media as $key => $file)
                            <li wire:key="{{ $key }}">
                                @if (str_contains($file->getMimeType(), 'video'))
                                    <video src="{{ $file->temporaryUrl() }}" controls class="object-cover select-none w-full h-auto bg-gray-200 rounded aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]"></video>
                                @else
                                    <img src="{{ $file->temporaryUrl() }}" class="object-cover select-none w-full h-auto bg-gray-200 rounded aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]" />
                                @endif
                            </li>
                        @endforeach
                    </ul>
                </div>
            @endif
        </div>

        <div class="pt-2 border-b-2"></div>
        <div name="footer" class="flex justify-between gap-x-4">
            @if($media)
                <x-button class="mt-4" flat negative label="Clear media" wire:click='removeUploadedFiles' />
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


