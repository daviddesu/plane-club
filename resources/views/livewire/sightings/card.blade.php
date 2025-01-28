<?php

use App\Models\Sighting;
use App\Enums\FlyingStatus;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;


new class extends Component
{
    public int $id;
    public string $loggedAt;

    public ?string $arrivalAirportName = null;
    public ?string $departureAirportName = null;
    public ?string $status = null;
    public ?string $aircraftType = null;
    public ?string $airlineName = null;
    public ?string $registration = null;
    public ?string $flightNumber = null;

    public bool $isVideo = false;
    public bool $isProcessing = false;
    public ?string $thumbnailPath = null;
    public ?string $mediaPath = null;

    protected function loadSighting(int $id): void
    {
        $sighting = Sighting::with([
                'user',
                'media',
                'airline',
                'arrivalAirport',
                'departureAirport',
                'aircraft'
            ])
            ->find($id);

            $this->id = $sighting->id;
            $this->isVideo = $sighting->media?->isVideo() ?? false;
            $this->isProcessing = $sighting->media?->isProcessing() ?? false;
            $this->thumbnailPath = $this->getCachedMediaUrl($sighting->media?->thumbnail_path);
            $this->mediaPath = $this->getCachedMediaUrl($sighting->media?->path);
            $this->arrivalAirportName = $sighting->arrivalAirport?->name ?? '';
            $this->departureAirportName = $sighting->departureAirport?->name ?? '';
            $this->status = $sighting->status ? (string)$sighting->status : '';
            $this->loggedAt = $sighting->logged_at ? $sighting->logged_at->format('d/m/Y') : '';
            $this->aircraftType = $sighting->aircraft?->getFormattedName() ?? '';
            $this->airlineName = $sighting->airline?->name ?? '';
            $this->registration = $sighting->aircraft?->registration ?? '';
            $this->flightNumber = $sighting->aircraft?->flight_number ?? '';
    }


    public function mount(int $id): void
    {
        $this->loadSighting($id);
    }

    public function refreshIfNeeded($id)
    {
        if ($this->id == $id) {
            $this->loadSighting($id);
        }
    }

    public function getCachedMediaUrl($mediaPath): ?string
    {
        if (!$mediaPath) {
            return null;
        }

        $driverName = getenv('FILESYSTEM_DISK');

        if ($driverName === 'b2') {
            // Construct the URL directly
            return rtrim(env('CDN_HOST'), '/') . '/' . ltrim($mediaPath, '/');
        } else {
            // For local, generate a URL using asset() or url()
            return asset('storage/' . $mediaPath);
        }
    }

}

?>

<x-mary-card class="flex flex-col w-full p-0 overflow-hidden rounded shadow">
    @if($id)
        <x-slot:figure>
            @if($mediaPath)
            <!-- Media Container with a 4:3 aspect ratio -->
            <div
                x-data="{
                    showModal: false,
                    stopVideo() {
                        const video = this.$refs.videoPlayer;
                        if (video) {
                            video.pause();
                            video.currentTime = 0;
                        }
                    }
                }"
                x-on:keydown.escape.window="showModal = false"
                class="relative w-full h-0 pb-[75%] overflow-hidden cursor-pointer"
            >
                @if($isVideo && $isProcessing)
                    <p class="absolute inset-0 flex items-center justify-center">
                        Processing
                    </p>
                @elseif ($isVideo)
                    <video
                        x-ref="videoPlayer"
                        src="{{ $mediaPath }}"
                        controls
                        class="absolute top-0 left-0 object-cover w-full h-full"
                    ></video>
                @else
                    <div x-on:click="showModal = true" class="absolute inset-0">
                        <img
                            src="{{ $mediaPath }}"
                            alt="Sighting Image"
                            loading="lazy"
                            class="absolute top-0 left-0 object-cover w-full h-full"
                        />
                    </div>

                    <!-- Image Modal -->
                    <div
                        x-show="showModal"
                        x-cloak
                        class="fixed inset-0 z-50 flex items-center justify-center p-4 bg-black/80"
                        @click.self="showModal = false"
                    >
                        <!-- Close Button -->
                        <button
                            @click="showModal = false"
                            class="absolute text-3xl font-bold text-white top-5 right-5"
                        >
                            &times;
                        </button>

                        <img
                            src="{{ $mediaPath }}"
                            alt="Fullscreen Sighting Image"
                            class="max-w-full max-h-full rounded"
                        />
                    </div>
                @endif
            </div>
            @endif
        </x-slot:figure>

        <!-- Clickable Content Area -->
        <a
            href="/sighting/{{ $id }}/edit"
            wire:navigate.hover
            class="flex-1 transition-colors duration-200 hover:cursor-pointer"
        >
            <!-- Log Details -->
            <div class="flex flex-col flex-1 gap-3 p-4">
                <!-- Date and Status -->
                <div class="flex items-center justify-between text-sm">
                    <div class="flex items-center gap-1">
                        <x-mary-icon name="o-calendar" class="w-4 h-4" />
                        {{ $loggedAt }}
                    </div>
                    <div class="flex items-center gap-1">
                        <x-mary-icon name="o-signal" class="w-4 h-4" />
                        {{ \App\Enums\FlyingStatus::getNameByStatus($status) }}
                    </div>
                </div>

                <!-- Airports -->
                <div class="flex flex-col space-y-4">
                    @if($departureAirportName)
                        <div class="flex items-center gap-3">
                            <div class="shrink-0 w-14">
                                <x-mary-badge flat slate value="DEP" />
                            </div>
                            <span class="flex-1">{{ $departureAirportName }}</span>
                        </div>
                    @endif

                    @if($arrivalAirportName)
                        <div class="flex items-center gap-3">
                            <div class="shrink-0 w-14">
                                <x-mary-badge flat slate value="ARV" />
                            </div>
                            <span class="flex-1">{{ $arrivalAirportName }}</span>
                        </div>
                    @endif
                </div>

                <!-- Aircraft and Airline Info -->
                <div class="grid grid-cols-1 pt-3 mt-2 text-sm border-t gap-y-2">
                    @if($aircraftType)
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-paper-airplane" class="w-4 h-4 text-gray-600 shrink-0" />
                            <span>{{ $aircraftType }}</span>
                        </div>
                    @endif

                    @if($airlineName)
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-building-office" class="w-4 h-4 text-gray-600 shrink-0" />
                            <span>{{ $airlineName }}</span>
                            @if($flightNumber)
                                <span class="text-gray-600">{{ $flightNumber }}</span>
                            @endif
                        </div>
                    @endif

                    @if($registration)
                        <div class="flex items-center gap-2">
                            <x-mary-icon name="o-identification" class="w-4 h-4 text-gray-600 shrink-0" />
                            <span>{{ $registration }}</span>
                        </div>
                    @endif
                </div>
            </div>
        </a>
    @endif

</x-mary-card>

