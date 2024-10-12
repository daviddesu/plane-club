<?php

namespace App\Http\Livewire;

use App\Models\Media;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;

new class extends Component
{
    public Collection $mediaItems;
    public array $mediaUrls = [];

    public function mount(): void
    {
        $this->getMediaIds();
    }

    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getMediaIds(): void
    {
        $this->mediaItems = auth()->user()->mediaItems()
            ->with([
                'aircraftLog',
                'aircraftLog.user',
                'aircraftLog.airline',
                'aircraftLog.airport',
                'aircraftLog.aircraft',
            ])
            ->latest()
            ->get();

        // Cache the media URLs
        $this->mediaUrls = $this->mediaItems->map(function ($media) {
            return [
                'id' => $media->id,
                'url' => $this->getCachedMediaUrl($media->path),
                'is_video' => str_contains($media->mime_type, 'video'), // Check if it's a video
            ];
        })->values()->toArray();
    }

    /**
     * Get the cached media URL for an S3 media item.
     */
    public function getCachedMediaUrl(string $path): string
    {
        // Cache key for this media temporary URL
        $cacheKey = "s3_media_url_" . md5($path);

        // Check if a cached URL exists, otherwise generate a new one
        return Cache::remember($cacheKey, now()->addDays(7), function () use ($path) {
            return Storage::disk('s3')->temporaryUrl($path, now()->addDays(7));
        });
    }
};

?>

<div x-data="mediaModal()" x-init="init()" class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto duration-1000 delay-300 select-none ease animate-fade-in-view">
        <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
            @foreach($mediaItems as $index => $media)
                <li wire:key='{{ $media->id }}'>
                    <livewire:aircraft_log.media_card lazy :media="$media" :index="$index" />
                </li>
            @endforeach
        </ul>
    </div>

    <!-- Fullscreen Media Modal -->
    <template x-if="isOpen">
        <div
            x-transition
            class="fixed inset-0 z-50 flex items-center justify-center bg-black bg-opacity-75"
            @keydown.escape.window="closeModal()"
        >
            <div class="relative max-w-full max-h-screen">
                <button @click="prevItem()" class="absolute left-0 p-4 text-3xl text-white transform -translate-y-1/2 top-1/2 focus:outline-none">&larr;</button>

                <!-- Display video or image based on media type -->
                <template x-if="mediaItems[currentIndex].is_video">
                    <video
                        controls
                        x-bind:src="mediaItems[currentIndex].url"
                        class="object-contain max-w-full max-h-screen"
                    ></video>
                </template>
                <template x-if="!mediaItems[currentIndex].is_video">
                    <img
                        x-bind:src="mediaItems[currentIndex].url"
                        alt="Media"
                        loading="lazy"
                        class="object-contain max-w-full max-h-screen"
                    >
                </template>

                <button @click="nextItem()" class="absolute right-0 p-4 text-3xl text-white transform -translate-y-1/2 top-1/2 focus:outline-none">&rarr;</button>
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
function mediaModal() {
    return {
        isOpen: false,
        currentIndex: null,
        mediaItems: [],
        init() {
            // Initialize media array
            this.mediaItems = @json($mediaUrls);
            // Listen for the event from media_card component
            window.addEventListener('open-media-modal', event => {
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
        nextItem() {
            if (this.currentIndex < this.mediaItems.length - 1) {
                this.currentIndex++;
            } else {
                this.currentIndex = 0; // Loop back to first item
            }
        },
        prevItem() {
            if (this.currentIndex > 0) {
                this.currentIndex--;
            } else {
                this.currentIndex = this.mediaItems.length - 1; // Loop back to last item
            }
        },
    };
}
</script>
