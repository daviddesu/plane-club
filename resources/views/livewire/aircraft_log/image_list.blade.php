<?php

namespace App\Http\Livewire;

use App\Models\Image;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;


new class extends Component
{
    public Collection $images;
    public array $imageUrls = [];

    public function mount(): void
    {
        $this->getImageIds();
    }

    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getImageIds(): void
    {
        $this->images = auth()->user()->images()
            ->with([
                'aircraftLog',
                'aircraftLog.user',
                'aircraftLog.airline',
                'aircraftLog.airport',
                'aircraftLog.aircraft',
            ])
            ->latest()
            ->get();

        // Cache the image URLs
        $this->imageUrls = $this->images->map(function ($image) {
            return [
                'id' => $image->id,
                'url' => $this->getCachedImageUrl($image->path),
            ];
        })->values()->toArray();
    }

    /**
     * Get the cached image URL for an S3 image.
     */
    public function getCachedImageUrl(string $path): string
    {
        // Cache key for this image's temporary URL
        $cacheKey = "s3_image_url_" . md5($path);

        // Check if a cached URL exists, otherwise generate a new one
        return Cache::remember($cacheKey, now()->addMinutes(60), function () use ($path) {
            return Storage::disk('s3')->temporaryUrl($path, now()->addMinutes(60));
        });
    }
};



?>

<div x-data="imageModal()" x-init="init()" class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto duration-1000 delay-300 select-none ease animate-fade-in-view">
        <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
            @foreach($images as $index => $image)
                <li wire:key='{{ $image->id }}'>
                    <livewire:aircraft_log.image_card lazy :image="$image" :index="$index" />
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Fullscreen Image Modal -->
<template x-if="isOpen">
    <div
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
        @keydown.escape.window="closeModal()"
    >
        <div class="relative max-w-full max-h-screen">
            <button @click="prevImage()" class="absolute left-0 p-4 text-3xl text-white transform -translate-y-1/2 top-1/2 focus:outline-none">&larr;</button>
            <img
                x-bind:src="images[currentIndex].url"
                alt="Image"
                class="object-contain max-w-full max-h-screen"
            >
            <button @click="nextImage()" class="absolute right-0 p-4 text-3xl text-white transform -translate-y-1/2 top-1/2 focus:outline-none">&rarr;</button>
            <button
                @click="closeModal()"
                class="absolute top-0 right-0 p-2 text-2xl text-white focus:outline-none"
            >&times;</button>
        </div>
    </div>
</template>
</div>

<!-- Alpine.js Script -->
<script>
function imageModal() {
    return {
        isOpen: false,
        currentIndex: null,
        images: [],
        init() {
            // Initialize images array
            this.images = @json($imageUrls);
            // Listen for the event from image_card component
            window.addEventListener('open-image-modal', event => {
                this.currentIndex = event.detail.index;
                this.openModal();
            });
        },
        openModal() {
            this.isOpen = true;
            document.body.classList.add('overflow-hidden');
        },
        closeModal() {
            this.isOpen = false;
            document.body.classList.remove('overflow-hidden');
        },
        nextImage() {
            if (this.currentIndex < this.images.length - 1) {
                this.currentIndex++;
            } else {
                this.currentIndex = 0; // Loop back to first image
            }
        },
        prevImage() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            } else {
                this.currentIndex = this.images.length - 1; // Loop back to last image
            }
        },
    };
}
</script>

