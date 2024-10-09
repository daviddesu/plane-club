<?php

namespace App\Http\Livewire\AircraftLog;

use App\Models\AircraftLog;
use App\Models\Image;
use Livewire\Volt\Component;

new class extends Component
{
    public Image $image;
    public int $index;
    public AircraftLog $aircraftLog;

    public function mount(Image $image, int $index): void
    {
        $this->image = $image;
        $this->index = $index;

        $this->aircraftLog = $image->aircraftLog;

        if (!$this->aircraftLog) {
            dd('AircraftLog not found for image', $image->id);
        }
    }
};




?>

@php
use Illuminate\Support\Facades\Storage;
@endphp

<div x-data="{
    index: {{ $index }},
    openModal() {
            console.log('Image clicked, index:', this.index);

        window.dispatchEvent(new CustomEvent('open-image-modal', { detail: { index: this.index } }));
    }
}">
    <img
        src="{{ Storage::disk('s3')->temporaryUrl($image->path, now()->addMinutes(60)) }}"
        alt=""
        class="object-cover select-none w-full h-auto bg-gray-200 rounded cursor-pointer aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]"
        x-on:click="openModal"
    >
    <div class="grid grid-cols-2">
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



