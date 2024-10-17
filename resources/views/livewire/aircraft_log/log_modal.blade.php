<?php

namespace App\Http\Livewire;

use App\Models\AircraftLog;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Media;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Support\Collection;
use Illuminate\Support\Facades\Storage;
use Illuminate\Support\Facades\Cache;
use Masmerise\Toaster\Toaster;

new class extends Component
{
    public Collection $airports;
    public Collection $airlines;
    public Collection $aircraft;

    public ?int $id = null;
    public ?AircraftLog $aircraftLog = null;

    #[Validate('required')]
    public ?string $loggedAt = null;

    #[Validate('required')]
    public ?string $airport_id = null;

    #[Validate]
    public ?string $airline_id = null;

    #[Validate]
    public ?string $aircraft_id = null;

    #[Validate]
    public ?string $description = "";

    #[Validate]
    public ?string $registration = "";

    public ?Media $media = null;

    public ?string $mediaUrl = "";

    public bool $editing = false;
    public bool $modalOpened = false;

    #[On('open_aircraft_log')]
    public function getAircraftLog($id): void
    {
        $this->id = $id;
        $this->modalOpened = true;
        $this->loadAircraftLog($id);
    }

    public function loadAircraftLog($id): void
    {
        $this->aircraftLog = AircraftLog::with('user', 'media', 'airport', 'airline', 'aircraft')->find($id);

        if ($this->aircraftLog) {
            $this->loggedAt = $this->aircraftLog->logged_at;
            $this->airport_id = $this->aircraftLog->airport?->id;
            $this->description = $this->aircraftLog->description;
            $this->airline_id = $this->aircraftLog->airline?->id;
            $this->aircraft_id = $this->aircraftLog->aircraft?->id;
            $this->registration = $this->aircraftLog->registration;
            $this->media = $this->aircraftLog->media;
            $this->mediaUrl = $this->getCachedMediaUrl($this->media->path);
        }
    }

    public function getCachedMediaUrl(string $path): string
    {
        // Cache the media URL for 7 days
        $cacheKey = "s3_media_url_" . md5($path);

        return Cache::remember($cacheKey, now()->addDays(7), function () use ($path) {
            return Storage::disk('s3')->temporaryUrl($path, now()->addDays(7));
        });
    }

    #[On('close_aircraft_log')]
    #[On('aircraft_log-deleted')]
    public function closeLog()
    {
        $this->id = null;
        $this->aircraftLog = null;
        $this->editing = false;
        $this->modalOpened = false;
    }

    public function mount()
    {
        $this->airports = Airport::all();
        $this->aircraft = Aircraft::all();
        $this->airlines = Airline::all();
    }

    public function startEdit()
    {
        $this->editing = true;
    }

    public function stopEdit()
    {
        $this->editing = false;
    }

    public function update()
    {
        $validated = $this->validate();
        $this->aircraftLog->update([
            "airport_id" => $this->airport_id,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline_id,
            "registration" => strtoupper($this->registration),
            "aircraft_id" => $this->aircraft_id,
        ]);

        Toaster::info("Log updated");
        $this->dispatch('aircraft_log-updated');
        $this->closeLog();
    }
};

?>

<div x-data="modalComponent" class="w-full h-full select-none">
    @if($id)

    <!-- Modal -->
    <div
        x-show="modalOpened"
        x-transition
        class="fixed inset-0 z-50 flex items-center justify-center overflow-auto bg-black bg-opacity-75"
        @keydown.window.escape="modalClose"
    >
        <div
            class="relative w-full max-w-5xl mx-auto bg-white rounded-lg"
            style="max-height: 100vh; overflow-y: auto;"
        >
            <!-- Close Button -->
            <button @click="modalClose" class="absolute text-gray-600 top-2 right-2 hover:text-gray-800">
                <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6" fill="none" viewBox="0 0 24 24" stroke="currentColor">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                </svg>
            </button>

            <!-- Log Details -->
            <div class="p-4 md:col-span-1">
                <!-- Display edit buttons if the user owns the log -->
                @if ($aircraftLog?->user->is(auth()->user()) && !$editing)
                    <div class="flex mb-4 space-x-2">
                        <button wire:click='startEdit' class="px-2 py-1 text-white bg-blue-500 rounded">Edit</button>
                        <livewire:aircraft_log.delete lazy :aircraftLog="$aircraftLog">
                    </div>
                @endif

                @if($editing)
                    <!-- Edit Form -->
                    <form wire:submit.prevent='update'>
                        <!-- Date Field -->
                        <div class="mb-2">
                            <label class="block text-gray-700">Date</label>
                            <input type="date" wire:model="loggedAt" class="w-full px-2 py-1 border rounded">
                        </div>
                        <!-- Airport Field -->
                        <div class="mb-2">
                            <label class="block text-gray-700">Airport</label>
                            <select wire:model="airport_id" class="w-full px-2 py-1 border rounded">
                                <option value="">Select Airport</option>
                                @foreach ($airports as $airport)
                                    <option value="{{ $airport->id }}">{{ $airport->name }} ({{ $airport->code }})</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Airline Field -->
                        <div class="mb-2">
                            <label class="block text-gray-700">Airline</label>
                            <select wire:model="airline_id" class="w-full px-2 py-1 border rounded">
                                <option value="">Select Airline</option>
                                @foreach ($airlines as $airline)
                                    <option value="{{ $airline->id }}">{{ $airline->name }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Aircraft Field -->
                        <div class="mb-2">
                            <label class="block text-gray-700">Aircraft</label>
                            <select wire:model="aircraft_id" class="w-full px-2 py-1 border rounded">
                                <option value="">Select Aircraft</option>
                                @foreach ($aircraft as $aircraftType)
                                    <option value="{{ $aircraftType->id }}">{{ $aircraftType->manufacturer }} {{ $aircraftType->model }}-{{ $aircraftType->varient }}</option>
                                @endforeach
                            </select>
                        </div>
                        <!-- Registration Field -->
                        <div class="mb-2">
                            <label class="block text-gray-700">Registration</label>
                            <input type="text" wire:model="registration" class="w-full px-2 py-1 uppercase border rounded">
                        </div>
                        <!-- Description Field -->
                        <div class="mb-2">
                            <label class="block text-gray-700">Description</label>
                            <textarea wire:model="description" class="w-full px-2 py-1 border rounded"></textarea>
                        </div>

                        <div class="flex mt-4 space-x-2">
                            <button type="button" wire:click='stopEdit' class="px-2 py-1 text-white bg-gray-500 rounded">Cancel</button>
                            <button type="submit" class="px-2 py-1 text-white bg-green-500 rounded">Save</button>
                        </div>
                    </form>
                @else
                    <!-- Display Log Details -->
                    <p class="mb-2 text-gray-700 text-md">Date: {{ (new DateTime($aircraftLog?->logged_at))->format("d/m/Y") }}</p>
                    <p class="mb-2 text-gray-700 text-md">Aircraft: {{ $aircraftLog?->aircraft?->manufacturer }} {{ $aircraftLog?->aircraft?->model }}-{{ $aircraftLog?->aircraft?->varient }}</p>
                    <p class="mb-2 text-gray-700 text-md">Registration: {{ $aircraftLog?->registration }}</p>
                    <p class="mb-2 text-gray-700 text-md">Airline: {{ $aircraftLog?->airline?->name }}</p>
                    <p class="mb-2 text-gray-700 text-md">Airport: {{ $aircraftLog?->airport->name }} ({{ $aircraftLog?->airport->code }})</p>
                    <p class="text-gray-700 text-md">{{ $aircraftLog?->description }}</p>
                @endif
            </div>

            <!-- Modal Content -->
            <div class="pt-4 ">
                <div class="p-4">
                    <div x-data='mediaGallery(@json($mediaUrl))' x-bind:key="$id" class="relative">
                        <div class="grid grid-cols-1">
                            <div class="w-full h-80">
                                @if($media->isVideo())
                                    <div class="relative">
                                        <video controls
                                            autoplay
                                            muted
                                            src="{{ $mediaUrl }}"
                                            class="object-contain w-full h-80"
                                            ></video>
                                    </div>
                                @else
                                    <img src="{{ $mediaUrl }}"
                                            alt="Media"
                                            loading="lazy"
                                            class="object-contain w-full cursor-pointer h-80"
                                            @click="openModal()">
                                @endif
                            </div>
                        </div>

                        <!-- Fullscreen Modal for Media -->
                        <div
                            x-show="isOpen"
                            x-transition
                            class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75 z-60"
                            @keydown.escape.window="closeModal()"
                        >
                        <div class="relative max-w-full max-h-screen">
                            <img
                                x-bind:src="mediaUrl"
                                loading="lazy"
                                class="object-contain max-w-full max-h-screen"
                            >

                            <!-- Close Button -->
                            <button
                                @click="closeModal()"
                                class="absolute top-0 right-0 p-2 text-2xl text-white focus:outline-none"
                            >
                                &times;
                            </button>
                        </div>
                        </div>
                    </div>
                </div>


            </div>
        </div>
    </div>
    @endif
</div>

<script>
    document.addEventListener('alpine:init', () => {
        Alpine.data('modalComponent', () => ({
            modalOpened: @entangle('modalOpened'),
            init() {},
            modalClose() {
                this.modalOpened = false;
                @this.call('closeLog');
            },
        }));

        Alpine.data('mediaGallery', (mediaUrl) => ({
            isOpen: false,
            mediaUrl: mediaUrl || "",
            openModal() {
                this.isOpen = true;
                document.body.classList.add('overflow-hidden');
            },
            closeModal() {
                this.isOpen = false;
                document.body.classList.remove('overflow-hidden');
            },
        }));
    });
</script>
