<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public int $aircraftLogId;
    public string $loggedAt;

    public ?string $airportName = null;
    public ?string $aircraftType = null;
    public ?string $airlineName = null;

    public bool $isVideo = false;
    public bool $isProcessing = false;
    public ?string $thumbnailPath = null;
    public ?string $mediaPath = null;



    public function mount(int $aircraftLogId): void
    {
        $aircraftLog = AircraftLog::with([
                'user',
                'media',
                'airline',
                'airport',
                'aircraft'
            ])
            ->find($aircraftLogId);

            $this->aircraftLogId = $aircraftLog->id;
            $this->isVideo = $aircraftLog->media?->isVideo() ?? false;
            $this->isProcessing = $aircraftLog->media?->isProcessing() ?? false;
            $this->thumbnailPath = $this->getCachedMediaUrl($aircraftLog->media?->thumbnail_path);
            $this->mediaPath = $this->getCachedMediaUrl($aircraftLog->media?->path);
            $this->airportName = $aircraftLog->airport->name ?? '';
            $this->loggedAt = $aircraftLog->logged_at->format('d/m/Y');
            $this->aircraftType = $aircraftLog->aircraft?->getFormattedName() ?? '';
            $this->airlineName = $aircraftLog->airline?->name ?? '';
    }

    public function getCachedMediaUrl($mediaPath): ?string
    {
        if (!$mediaPath) {
            return null;
        }

        $storageDisk = Storage::disk(getenv('FILESYSTEM_DISK'));
        $cacheKey = "media_url_" . md5($mediaPath);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($mediaPath, $storageDisk) {
            $driverName = getenv('FILESYSTEM_DISK');

            if ($driverName === 's3') {
                // For S3, generate a temporary URL
                return $storageDisk->temporaryUrl($mediaPath, now()->addDays(7));
            } else {
                // For local, generate a URL using asset() or url()
                return asset('storage/' . $mediaPath);
            }
        });
    }

}

?>

<div>
    {{-- Media Container with rectangular aspect ratio --}}
    <div
        x-on:click="$wire.dispatch('open_aircraft_log', {id: {{ $aircraftLogId }}});"
        class="relative w-full bg-gray-200 rounded cursor-pointer overflow-hidden aspect-[4/3]"
    >
        @if($isVideo && $isProcessing)
            <p>Processing...</p>
        @elseif ($isVideo)
            <div
                class="relative w-full h-full"
            >
                <img class="object-cover w-full h-full select-none"
                src={{ $thumbnailPath }}
                />
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30">
                    <x-icon name="play-circle" class="w-12 h-12 text-white" />
                </div>
            </div>
        @else
            <img
                src="{{ $mediaPath }}"
                alt=""
                loading="lazy"
                class="object-cover w-full h-full select-none"
            >
        @endif
    </div>

    {{-- Log Details --}}
    <div class="grid grid-cols-2 mt-2">
        <div>
            <div><span class="text-gray-800">{{ $airportName }}</span></div>
            <div><small class="text-xs text-gray-600">{{ $loggedAt }}</small></div>
        </div>
        <div>
            <div><small class="text-xs text-gray-600">{{ $aircraftType }}</small></div>
            <div><small class="text-xs text-gray-600">{{ $airlineName }}</small></div>
        </div>
    </div>
</div>
