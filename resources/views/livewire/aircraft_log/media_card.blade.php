<?php

namespace App\Http\Livewire\AircraftLog;

use App\Models\AircraftLog;
use App\Models\Media;
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Storage;

new class extends Component
{
    public Media $media;
    public int $index;
    public AircraftLog $aircraftLog;

    public function mount(Media $media, int $index): void
    {
        $this->media = $media;
        $this->index = $index;

        $this->aircraftLog = $media->aircraftLog;

        if (!$this->aircraftLog) {
            dd('AircraftLog not found for media', $media->id);
        }
    }

    // Check if the media is a video
    public function isVideo(): bool
    {
        return str_contains($this->media->mime_type, 'video');
    }
};

?>

@php
use Illuminate\Support\Facades\Storage;
@endphp

<div x-data="{
    index: {{ $index }},
    openModal() {
        window.dispatchEvent(new CustomEvent('open-media-modal', { detail: { index: this.index } }));
    }
}">
    <div class="relative w-full h-auto bg-gray-200 rounded cursor-pointer aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]" x-on:click="openModal">
        @if($this->isVideo())
            <!-- Video Thumbnail with Play Icon -->
            <video
                src="{{ Storage::disk('s3')->temporaryUrl($media->path, now()->addDays(7)) }}"
                class="object-cover w-full h-full"
                muted
            ></video>
            <div class="absolute inset-0 flex items-center justify-center bg-black bg-opacity-50">
                <x-icon name="play-circle" class="w-12 h-12 text-white" />
            </div>
        @else
            <!-- Image Thumbnail -->
            <img
                src="{{ Storage::disk('s3')->temporaryUrl($media->path, now()->addDays(7)) }}"
                alt="Media"
                loading="lazy"
                class="object-cover w-full h-full"
            >
        @endif
    </div>

    <div class="grid grid-cols-2 mt-2">
        <div>
            <div><span class="text-gray-800">{{ $aircraftLog->airport->name }}</span></div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->logged_at->format('d/m/Y') }}</small></div>
        </div>
        <div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->aircraft?->getFormattedName() }}</small></div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->airline?->name }}</small></div>
        </div>
    </div>
</div>
