<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public AircraftLog $aircraftLog;

    public function mount(int $aircraftLogId): void
    {
        $this->aircraftLog = AircraftLog::with([
                'user',
                'media',
                'airline',
                'airport',
                'aircraft'
            ])
            ->find($aircraftLogId);
    }

    // Get the S3 temporary URL with caching
    public function getCachedMediaUrl($mediaPath)
    {
        if(!$mediaPath){
            return null;
        }
        return Cache::remember("s3_media_{$mediaPath}", now()->addDays(7), function() use ($mediaPath) {
            return Storage::disk('s3')->temporaryUrl($mediaPath, now()->addDays(7));
        });
    }
}

?>

<div>
    {{-- Media Container with rectangular aspect ratio --}}
    <div
        x-on:click="$wire.dispatch('open_aircraft_log', {id: {{ $aircraftLog->id }}});"
        class="relative w-full bg-gray-200 rounded cursor-pointer overflow-hidden aspect-[4/3]"
    >
        @if($aircraftLog->media->isVideo())
            <div
                class="relative w-full h-full"
            >
                <img class="object-cover w-full h-full select-none"
                src={{ $this->getCachedMediaUrl($aircraftLog->media->thumbnail_path) }}
                />
                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-30">
                    <x-icon name="play-circle" class="w-12 h-12 text-white" />
                </div>
            </div>
        @else
            <img
                src="{{ $this->getCachedMediaUrl($aircraftLog->media->path) }}"
                alt=""
                loading="lazy"
                class="object-cover w-full h-full select-none"
            >
        @endif
    </div>

    {{-- Log Details --}}
    <div class="grid grid-cols-2 mt-2">
        <div>
            <div><span class="text-gray-800">{{ $aircraftLog->airport->name }}</span></div>
            <div><small class="text-xs text-gray-600">{{ (new DateTime($aircraftLog->logged_at))->format('d/m/Y') }}</small></div>
        </div>
        <div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->aircraft?->getFormattedName() }}</small></div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->airline?->name }}</small></div>
        </div>
    </div>
</div>
