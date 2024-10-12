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
                'mediaItems', // Assuming mediaItems includes both images and videos
                'airline',
                'airport',
                'aircraft'
            ])
            ->find($aircraftLogId);
    }

    // Get the S3 temporary URL with caching
    public function getCachedMediaUrl($mediaPath)
    {
        return Cache::remember("s3_media_{$mediaPath}", now()->addDays(7), function() use ($mediaPath) {
            return Storage::disk('s3')->temporaryUrl($mediaPath, now()->addDays(7));
        });
    }

    // Determine if the media is a video based on its MIME type
    public function isVideo($media)
    {
        return str_contains($media->mime_type, 'video');
    }
}

?>

<div>
    @php
        $mediaItems = $aircraftLog->mediaItems;
        $mediaCount = $mediaItems->count();
    @endphp

    {{-- Media Container with rectangular aspect ratio --}}
    <div
        x-on:click="$wire.dispatch('open_aircraft_log', {id: {{ $aircraftLog->id }}});"
        class="relative w-full bg-gray-200 rounded cursor-pointer overflow-hidden aspect-[4/3]"
    >
        @if($mediaCount == 1)
            {{-- Case 1: One media item (image or video) --}}
            @php
                $firstMedia = $mediaItems->first();
            @endphp

            @if($this->isVideo($firstMedia))
                <div class="relative w-full h-full">
                    <video class="object-cover w-full h-full select-none"></video>
                    <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                        <x-icon name="play-circle" class="w-12 h-12 text-white" />
                    </div>
                </div>
            @else
                <img
                    src="{{ $this->getCachedMediaUrl($firstMedia->path) }}"
                    alt=""
                    loading="lazy"
                    class="object-cover w-full h-full select-none"
                >
            @endif
        @elseif($mediaCount == 2)
            {{-- Case 2: Two media items side by side --}}
            <div class="absolute inset-0 flex">
                @foreach($mediaItems->take(2) as $media)
                    <div class="relative w-1/2 h-full">
                        @if($this->isVideo($media))
                            <video class="object-cover w-full h-full select-none"></video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                                <x-icon name="play-circle" class="w-12 h-12 text-white" />
                            </div>
                        @else
                            <img
                                src="{{ $this->getCachedMediaUrl($media->path) }}"
                                alt=""
                                loading="lazy"
                                class="object-cover w-full h-full select-none"
                            >
                        @endif
                    </div>
                @endforeach
            </div>
        @elseif($mediaCount == 3)
            {{-- Case 3: One large media item on the left, two smaller items stacked on the right --}}
            <div class="absolute inset-0 flex">
                {{-- Left large media --}}
                <div class="relative w-2/3 h-full">
                    @php
                        $firstMedia = $mediaItems[0];
                    @endphp
                    @if($this->isVideo($firstMedia))
                        <video class="object-cover w-full h-full select-none"></video>
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                            <x-icon name="play-circle" class="w-12 h-12 text-white" />
                        </div>
                    @else
                        <img
                            src="{{ $this->getCachedMediaUrl($firstMedia->path) }}"
                            alt=""
                            loading="lazy"
                            class="object-cover w-full h-full select-none"
                        >
                    @endif
                </div>
                {{-- Right two smaller media items --}}
                <div class="flex flex-col w-1/3 h-full">
                    @foreach($mediaItems->slice(1, 2) as $media)
                        <div class="relative h-1/2">
                            @if($this->isVideo($media))
                                <video class="object-cover w-full h-full select-none"></video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                                    <x-icon name="play-circle" class="w-12 h-12 text-white" />
                                </div>
                            @else
                                <img
                                    src="{{ $this->getCachedMediaUrl($media->path) }}"
                                    alt=""
                                    loading="lazy"
                                    class="object-cover w-full h-full select-none"
                                >
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($mediaCount > 3)
            {{-- Case 4: Same layout as Case 3, but overlay "+ X" on the last media item --}}
            <div class="absolute inset-0 flex">
                {{-- Left large media --}}
                <div class="relative w-2/3 h-full">
                    @php
                        $firstMedia = $mediaItems[0];
                    @endphp
                    @if($this->isVideo($firstMedia))
                        <video class="object-cover w-full h-full select-none"></video>
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                            <x-icon name="play-circle" class="w-12 h-12 text-white" />
                        </div>
                    @else
                        <img
                            src="{{ $this->getCachedMediaUrl($firstMedia->path) }}"
                            alt=""
                            loading="lazy"
                            class="object-cover w-full h-full select-none"
                        >
                    @endif
                </div>
                {{-- Right two smaller media items --}}
                <div class="flex flex-col w-1/3 h-full">
                    @foreach($mediaItems->slice(1, 2) as $media)
                        <div class="relative h-1/2">
                            @if($this->isVideo($media))
                                <video class="object-cover w-full h-full select-none"></video>
                                <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                                    <x-icon name="play-circle" class="w-12 h-12 text-white" />
                                </div>
                            @else
                                <img
                                    src="{{ $this->getCachedMediaUrl($media->path) }}"
                                    alt=""
                                    loading="lazy"
                                    class="object-cover w-full h-full select-none"
                                >
                            @endif
                        </div>
                    @endforeach
                    <div class="relative h-1/2">
                        @php
                            $thirdMedia = $mediaItems[2];
                        @endphp
                        @if($this->isVideo($thirdMedia))
                            <video class="object-cover w-full h-full select-none"></video>
                            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                                <x-icon name="play-circle" class="w-12 h-12 text-white" />
                            </div>
                        @else
                            <img
                                src="{{ $this->getCachedMediaUrl($thirdMedia->path) }}"
                                alt=""
                                loading="lazy"
                                class="object-cover w-full h-full select-none"
                            >
                        @endif
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                            <span class="text-xl font-semibold text-white">
                                +{{ $mediaCount - 3 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
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
