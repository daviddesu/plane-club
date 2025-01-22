<?php

use App\Models\AircraftLog;
use App\Enums\FlyingStatus;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;
use Livewire\Attributes\On;


new class extends Component
{
    public int $aircraftLogId;
    public string $loggedAt;

    public ?string $arrivalAirportName = null;
    public ?string $departureAirportName = null;
    public ?string $status = null;
    public ?string $aircraftType = null;
    public ?string $airlineName = null;

    public bool $isVideo = false;
    public bool $isProcessing = false;
    public ?string $thumbnailPath = null;
    public ?string $mediaPath = null;

    protected function loadAircraftLog(int $aircraftLogId): void
    {
        $aircraftLog = AircraftLog::with([
                'user',
                'media',
                'airline',
                'arrivalAirport',
                'departureAirport',
                'aircraft'
            ])
            ->find($aircraftLogId);

            $this->aircraftLogId = $aircraftLog->id;
            $this->isVideo = $aircraftLog->media?->isVideo() ?? false;
            $this->isProcessing = $aircraftLog->media?->isProcessing() ?? false;
            $this->thumbnailPath = $this->getCachedMediaUrl($aircraftLog->media?->thumbnail_path);
            $this->mediaPath = $this->getCachedMediaUrl($aircraftLog->media?->path);
            $this->arrivalAirportName = $aircraftLog->arrivalAirport?->name ?? '';
            $this->departureAirportName = $aircraftLog->departureAirport?->name ?? '';
            $this->status = $aircraftLog->status ? (string)$aircraftLog->status : '';
            $this->loggedAt = $aircraftLog->logged_at ? $aircraftLog->logged_at->format('d/m/Y') : '';
            $this->aircraftType = $aircraftLog->aircraft?->getFormattedName() ?? '';
            $this->airlineName = $aircraftLog->airline?->name ?? '';
    }


    public function mount(int $aircraftLogId): void
    {
        $this->loadAircraftLog($aircraftLogId);
    }

    #[On('aircraft_log-updated')]
    public function refreshIfNeeded($aircraftLogId)
    {
        if ($this->aircraftLogId == $aircraftLogId) {
            $this->loadAircraftLog($aircraftLogId);
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

<x-mary-card class="flex flex-col w-full p-0 overflow-hidden bg-white rounded shadow-md">
    @if($aircraftLogId)
        <!-- Log Details -->
        <div class="flex flex-col flex-1 gap-2 p-3">
            <!-- Airports Row -->
            <div class="space-x-2 text-sm">
                @if($departureAirportName)
                    <x-mary-badge flat slate value="DEP" />
                    <span>{{ $departureAirportName }}</span>
                @endif

                @if($departureAirportName && $arrivalAirportName)
                    <x-mary-icon name="o-arrow-long-right" class="inline-block w-5 h-5 text-gray-600" />
                @endif

                @if($arrivalAirportName)
                    <x-mary-badge flat slate value="ARV" />
                    <span>{{ $arrivalAirportName }}</span>
                @endif
            </div>

            <!-- Additional Info -->
            <div class="flex justify-between text-sm">
                <div class="space-y-1">
                    <div>{{ $loggedAt }}</div>
                    <div>{{ $airlineName }}</div>
                </div>
                <div class="space-y-1 text-right">
                    <div>{{ \App\Enums\FlyingStatus::getNameByStatus($status) }}</div>
                    <div>{{ $aircraftType }}</div>
                </div>
            </div>
        </div>
        <x-slot:figure>
            @if($mediaPath)
            <!-- Media Container with a 4:3 aspect ratio -->
            <div
                x-on:click="$wire.dispatch('open_aircraft_log', { id: {{ $aircraftLogId }} })"
                class="relative w-full h-0 pb-[75%] cursor-pointer overflow-hidden bg-gray-200"
            >
                @if($isVideo && $isProcessing)
                    <p class="absolute inset-0 flex items-center justify-center text-cyan-800">
                        Processing
                    </p>
                @elseif ($isVideo)
                    <img
                        class="absolute top-0 left-0 object-cover w-full h-full"
                        src="{{ $thumbnailPath }}"
                        alt="Video Thumbnail"
                    />
                    <div class="absolute inset-0 flex items-center justify-center bg-black/30">
                        <!-- Using MaryUI icon -->
                        <x-mary-icon name="o-play-circle" class="w-12 h-12 text-white" />
                    </div>
                @else
                    <img
                        src="{{ $mediaPath }}"
                        alt="Sighting Image"
                        loading="lazy"
                        class="absolute top-0 left-0 object-cover w-full h-full"
                    />
                @endif
            </div>
        @endif
        </x-slot:figure>
    @endif
</x-mary-card>

