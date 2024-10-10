<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;

new class extends Component
{
    public AircraftLog $aircraftLog;

    public function mount(int $aircraftLogId): void
    {
        $this->aircraftLog = AircraftLog::with([
                'user',
                'images',
                'airline',
                'airport',
                'aircraft'
            ])
            ->find($aircraftLogId);
    }

    // Get the S3 temporary URL with caching
    public function getCachedImageUrl($imagePath)
    {
        return Cache::remember("s3_image_{$imagePath}", now()->addDays(7), function() use ($imagePath) {
            return Storage::disk('s3')->temporaryUrl($imagePath, now()->addDays(7));
        });
    }
}


?>


<div>
    @php
        $images = $aircraftLog->images;
        $imageCount = $images->count();
    @endphp

    {{-- Image Container with rectangular aspect ratio --}}
    <div
        x-on:click="$wire.dispatch('open_aircraft_log', {id: {{ $aircraftLog->id }}});"
        class="relative w-full bg-gray-200 rounded cursor-pointer overflow-hidden aspect-[4/3]"
    >
        @if($imageCount == 1)
            {{-- Case 1: One image --}}
            <img
                src="{{ $this->getCachedImageUrl($images->first()->path) }}"
                alt=""
                loading="lazy"
                class="object-cover w-full h-full select-none"
            >
        @elseif($imageCount == 2)
            {{-- Case 2: Two images side by side --}}
            <div class="absolute inset-0 flex">
                @foreach($images->take(2) as $image)
                    <div class="w-1/2 h-full">
                        <img
                            src="{{ $this->getCachedImageUrl($image->path) }}"
                            alt=""
                            loading="lazy"
                            class="object-cover w-full h-full select-none"
                        >
                    </div>
                @endforeach
            </div>
        @elseif($imageCount == 3)
            {{-- Case 3: One large image on the left, two smaller images stacked on the right --}}
            <div class="absolute inset-0 flex">
                {{-- Left large image --}}
                <div class="w-2/3 h-full">
                    <img
                        src="{{ $this->getCachedImageUrl($images[0]->path) }}"
                        alt=""
                        loading="lazy"
                        class="object-cover w-full h-full select-none"
                    >
                </div>
                {{-- Right two images --}}
                <div class="flex flex-col w-1/3 h-full">
                    @foreach($images->slice(1, 2) as $image)
                        <div class="h-1/2">
                            <img
                                src="{{ $this->getCachedImageUrl($image->path) }}"
                                alt=""
                                loading="lazy"
                                class="object-cover w-full h-full select-none"
                            >
                        </div>
                    @endforeach
                </div>
            </div>
        @elseif($imageCount > 3)
            {{-- Case 4: Same layout as Case 3, but overlay "+ X" on the last image --}}
            <div class="absolute inset-0 flex">
                {{-- Left large image --}}
                <div class="w-2/3 h-full">
                    <img
                        src="{{ $this->getCachedImageUrl($images[0]->path) }}"
                        alt=""
                        loading="lazy"
                        class="object-cover w-full h-full select-none"
                    >
                </div>
                {{-- Right two images --}}
                <div class="flex flex-col w-1/3 h-full">
                    <div class="h-1/2">
                        <img
                            src="{{ $this->getCachedImageUrl($images[1]->path) }}"
                            alt=""
                            loading="lazy"
                            class="object-cover w-full h-full select-none"
                        >
                    </div>
                    <div class="relative h-1/2">
                        <img
                            src="{{ $this->getCachedImageUrl($images[2]->path) }}"
                            alt=""
                            loading="lazy"
                            class="object-cover w-full h-full select-none"
                        >
                        <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                            <span class="text-xl font-semibold text-white">
                                +{{ $imageCount - 3 }}
                            </span>
                        </div>
                    </div>
                </div>
            </div>
        @endif
    </div>

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



