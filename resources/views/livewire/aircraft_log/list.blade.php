<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;



new class extends Component
{
    public Collection $aircraftLogs;


    public function mount(): void
    {
        $this->getAircraftLogs();
    }


    #[on('aircraft_log-created')]
    public function getAircraftLogs(): void
    {
        $this->aircraftLogs = AircraftLog::with('user', 'image')->latest()->get();
    }

}


?>


<div x-data="{
    imageGalleryOpened: false,
    imageGalleryActiveUrl: null,
    imageGalleryImageIndex: null,
    imageGallery: [
    @foreach ($aircraftLogs as $aircraftLog)
        {
            'photo': '{{ asset('storage/' .  $aircraftLog->image->path) }}',
            'user': '{{ $aircraftLog->user->name }}',
            'date': '{{ $aircraftLog->logged_at }}',
            'airport': '{{ $aircraftLog->airport->name }}'
        },
    @endforeach
    ],
    imageGalleryOpen(event) {
        this.imageGalleryImageIndex = event.target.dataset.index;
        this.imageGalleryActiveUrl = event.target.src;
        this.imageGalleryOpened = true;
    },
    imageGalleryClose() {
        this.imageGalleryOpened = false;
        setTimeout(() => this.imageGalleryActiveUrl = null, 300);
    },
    imageGalleryNext(){
        this.imageGalleryImageIndex = (this.imageGalleryImageIndex == this.imageGallery.length) ? 1 : (parseInt(this.imageGalleryImageIndex) + 1);
        this.imageGalleryActiveUrl = this.$refs.gallery.querySelector('[data-index=\'' + this.imageGalleryImageIndex + '\']').src;
    },
    imageGalleryPrev() {
        this.imageGalleryImageIndex = (this.imageGalleryImageIndex == 1) ? this.imageGallery.length : (parseInt(this.imageGalleryImageIndex) - 1);
        this.imageGalleryActiveUrl = this.$refs.gallery.querySelector('[data-index=\'' + this.imageGalleryImageIndex + '\']').src;

    }
}"
@image-gallery-next.window="imageGalleryNext()"
@image-gallery-prev.window="imageGalleryPrev()"
@keyup.right.window="imageGalleryNext();"
@keyup.left.window="imageGalleryPrev();"
class="w-full h-full select-none">
<div class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
    <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-4">
        <template x-for="(image, index) in imageGallery">
            <li>
                <img x-on:click="imageGalleryOpen" :src="image.photo" :alt="image.alt" :data-index="index+1" class="object-cover select-none w-full h-auto bg-gray-200 rounded cursor-zoom-in aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]">
                <div>
                    <span class="text-gray-800" x-text="image.airport"></span>
                    <small class="ml-2 text-xs text-gray-600" x-text="image.user"></small>
                    <small
                        class="ml-2 text-xs text-gray-600" x-text="image.date"></small>
                </div>
            </li>
        </template>
    </ul>
</div>
<template x-teleport="body">
    {{-- <div
        x-show="imageGalleryOpened"
        x-transition:enter="transition ease-in-out duration-300"
        x-transition:enter-start="opacity-0"
        x-transition:leave="transition ease-in-in duration-300"
        x-transition:leave-end="opacity-0"
        @click="imageGalleryClose"
        @keydown.window.escape="imageGalleryClose"
        x-trap.inert.noscroll="imageGalleryOpened"
        class="fixed inset-0 z-[99] flex items-center justify-center bg-black bg-opacity-50 select-none cursor-zoom-out" x-cloak>
        <div class="relative flex items-center justify-center w-11/12 xl:w-4/5 h-11/12">
            <div @click="$event.stopPropagation(); $dispatch('image-gallery-prev')" class="absolute left-0 flex items-center justify-center text-white translate-x-10 rounded-full cursor-pointer xl:-translate-x-24 2xl:-translate-x-32 bg-white/10 w-14 h-14 hover:bg-white/20">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </div>
            <img
                x-show="imageGalleryOpened"
                x-transition:enter="transition ease-in-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-50"
                x-transition:leave="transition ease-in-in duration-300"
                x-transition:leave-end="opacity-0 transform scale-50"
                class="object-contain object-center w-full h-full select-none cursor-zoom-out" :src="imageGalleryActiveUrl" alt="" style="display: none;">
            <div @click="$event.stopPropagation(); $dispatch('image-gallery-next');" class="absolute right-0 flex items-center justify-center text-white -translate-x-10 rounded-full cursor-pointer xl:translate-x-24 2xl:translate-x-32 bg-white/10 w-14 h-14 hover:bg-white/20">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
            </div>
        </div>
    </div> --}}
    <div
            x-show="imageGalleryOpened"
            x-transition:enter="transition ease-out duration-100"
            x-transition:enter-start="opacity-0"
            x-transition:enter-end="opacity-80"
            x-transition:leave="transition ease-in duration-100"
            x-transition:leave-start="opacity-80"
            x-transition:leave-end="opacity-0"
            class="flex fixed inset-0 z-[99] w-screen h-screen bg-white opacity-96"
            @keydown.window.escape="imageGalleryClose"
            >
            <button @click="imageGalleryOpened=false" class="absolute top-0 right-0 z-30 flex items-center justify-center px-3 py-2 mt-3 mr-3 space-x-1 text-xs font-medium uppercase border rounded-md border-neutral-200 text-neutral-600 hover:bg-neutral-100">
                <svg xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor" class="w-4 h-4"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12" /></svg>
                <span>Close</span>
            </button>
            <div @click="$event.stopPropagation(); $dispatch('image-gallery-prev')" class="absolute left-0 flex items-center justify-center text-white translate-x-10 rounded-full cursor-pointer xl:-translate-x-24 2xl:-translate-x-32 bg-white/10 w-14 h-14 hover:bg-white/20">
                <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M15.75 19.5L8.25 12l7.5-7.5" /></svg>
            </div>
            <div class="relative top-0 bottom-0 right-0 flex-shrink-0 hidden w-3/4 bg-cover border-r-2 overlow-hidden lg:block">

                <div class="absolute inset-0 z-20 w-full h-full opacity-70"></div>
                <img
                x-show="imageGalleryOpened"
                x-transition:enter="transition ease-in-out duration-300"
                x-transition:enter-start="opacity-0 transform scale-50"
                x-transition:leave="transition ease-in-in duration-300"
                x-transition:leave-end="opacity-0 transform scale-50"
                class="object-cover object-center w-full h-full bg-opacity-100 select-none cursor-zoom-out" :src="imageGalleryActiveUrl" alt="" style="display: none;">

            </div>
            <div class="relative flex flex-wrap items-center w-full h-full px-8">

                <div class="relative w-full max-w-sm mx-auto lg:mb-0">
                    <div class="relative text-center">

                        <div class="flex flex-col mb-6 space-y-2">
                            <h1 class="text-2xl font-semibold tracking-tight">Tiitle</h1>
                            <p class="text-sm text-neutral-500">Hi</p>
                        </div>
                    </div>
                </div>
                <div @click="$event.stopPropagation(); $dispatch('image-gallery-next');" class="absolute right-0 flex items-center justify-center text-white -translate-x-10 rounded-full cursor-pointer xl:translate-x-24 2xl:translate-x-32 bg-white/10 w-14 h-14 hover:bg-white/20">
                    <svg class="w-6 h-6" xmlns="http://www.w3.org/2000/svg" fill="none" viewBox="0 0 24 24" stroke-width="1.5" stroke="currentColor"><path stroke-linecap="round" stroke-linejoin="round" d="M8.25 4.5l7.5 7.5-7.5 7.5" /></svg>
                </div>
            </div>
        </div>
</template>
</div>
