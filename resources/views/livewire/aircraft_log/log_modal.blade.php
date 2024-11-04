<?php

namespace App\Http\Livewire;

use App\Models\AircraftLog;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use App\Models\Media;
use App\Enums\FlyingStatus;
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
    public ?string $status = null;

    #[Validate('required_if:status,1,2')]
    public ?string $departure_airport_id = null;

    #[Validate('required_if:status,2,3')]
    public ?string $arrival_airport_id = null;

    #[Validate]
    public ?string $airline_id = null;

    #[Validate]
    public ?string $aircraft_id = null;

    #[Validate]
    public ?string $description = "";

    #[Validate]
    public ?string $registration = "";

    #[Validate]
    public ?string $flightNumber = "";

    public ?Media $media = null;

    public ?string $mediaUrl = "";

    public string $fileName = "";

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
        $this->aircraftLog = AircraftLog::with('user', 'media', 'departureAirport', 'arrivalAirport', 'airline', 'aircraft')->find($id);

        if ($this->aircraftLog) {
            $this->loggedAt = $this->aircraftLog->logged_at;
            $this->arrival_airport_id = $this->aircraftLog->arrivalAirport?->id;
            $this->departure_airport_id = $this->aircraftLog->departureAirport?->id;
            $this->status = $this->aircraftLog->status;
            $this->description = $this->aircraftLog->description;
            $this->airline_id = $this->aircraftLog->airline?->id;
            $this->aircraft_id = $this->aircraftLog->aircraft?->id;
            $this->registration = $this->aircraftLog->registration;
            $this->flightNumber = $this->aircraftLog->flight_number;
            $this->media = $this->aircraftLog->media;
            $this->mediaUrl = $this->getCachedMediaUrl($this->media->path);
            $this->fileName = $this->generateFileName();
        }
    }

    public function generateFileName()
    {
        $name = "Plane-club-";
        $name .= (new \DateTime($this->loggedAt))->format("Y-m-d");

        if($this->aircraftLog->departureAirport){
            $name .= "-{$this->aircraftLog->departureAirport->code}";
        }

        if($this->aircraftLog->arrivalAirport){
            $name .= "-{$this->aircraftLog->arrivalAirport->code}";
        }

        if($this->aircraftLog->aircraft){
            $name .= "-{$this->aircraftLog->aircraft->model}-{$this->aircraftLog->aircraft->varient}";
        }
        return $name;
    }

    public function getCachedMediaUrl($mediaPath)
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
            "arrival_airport_id" => $this->arrival_airport_id,
            "departure_airport_id" => $this->departure_airport_id,
            "status" => $this->status,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline_id,
            "registration" => strtoupper($this->registration),
            "flight_number" => strtoupper($this->flightNumber),
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
            <div class="grid grid-cols-1 p-4 md:grid-cols-2">
                <div class="pt-4 ">
                    <div class="p-4">
                        <div x-data='mediaGallery(@json($mediaUrl))' x-bind:key="$id" class="relative">
                            <div>
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
                <div class="ml-2">
                    <!-- Display edit buttons if the user owns the log -->
                    @if ($aircraftLog?->user->is(auth()->user()) && !$editing)
                        <div class="flex mb-4 first-letter:space-x-2">
                            <x-mini-button wire:click='startEdit' rounded icon="pencil" flat class="text-cyan-800 hover:cyan-200 hover:bg-cyan-800" interaction:solid />
                            <a href="{{ $mediaUrl }}" download="{{ $fileName }}"><x-mini-button rounded icon="arrow-down-tray" flat class="text-cyan-800 hover:cyan-200 hover:bg-cyan-800" interaction:solid /></a>
                            <livewire:aircraft_log.delete lazy :aircraftLog="$aircraftLog">
                        </div>
                    @endif

                    @if($editing)
                        <!-- Edit Form -->
                        <form wire:submit.prevent='update'>
                            <!-- Date Field -->
                            <div class="mb-2">
                                <x-datetime-picker
                                    class="pd-2"
                                    wire:model="loggedAt"
                                    label="Date"
                                    placeholder="Date"
                                    without-time
                                />
                            </div>
                            <!-- Status Field -->
                            <div class="mb-2">
                                <x-select
                                    class="pd-2"
                                    label="Status"
                                    placeholder="Please select"
                                    wire:model='status'
                                >
                                    <x-select.option value="{{ FlyingStatus::DEPARTING->value }}" label="{{ strtolower(ucfirst(FlyingStatus::DEPARTING->name)) }}" />
                                    <x-select.option value="{{ FlyingStatus::ARRIVING->value }}" label="{{ strtolower(ucfirst(FlyingStatus::ARRIVING->name)) }}" />
                                    <x-select.option value="{{ FlyingStatus::IN_FLIGHT->value }}" label="{{ strtolower(ucfirst(FlyingStatus::IN_FLIGHT->name)) }}" />
                                </x-select>
                            </div>
                            <!-- Departure Airport Field -->
                            <x-select
                                class="pd-2"
                                label="Airport"
                                placeholder="Please select"
                                wire:model='departure_airport_id'
                                searchable="true"
                                min-items-for-search="2"
                            >
                                @foreach ($airports as $airport)
                                    <x-select.option value="{{ $airport->id }}" label="{{ $airport->name }} ({{ $airport->code }})" />
                                @endforeach
                            </x-select>
                            <!-- Arrival Airport Field -->
                            <x-select
                                class="pd-2"
                                label="Airport"
                                placeholder="Please select"
                                wire:model='arrival_airport_id'
                                searchable="true"
                                min-items-for-search="2"
                            >
                                @foreach ($airports as $airport)
                                    <x-select.option value="{{ $airport->id }}" label="{{ $airport->name }} ({{ $airport->code }})" />
                                @endforeach
                            </x-select>
                            <!-- Airline Field -->
                            <x-select
                                class="pd-2"
                                label="Airline"
                                placeholder="Please select"
                                wire:model='airline_id'
                                searchable="true"
                                min-items-for-search="2"
                            >
                                @foreach ($airlines as $airline)
                                    <x-select.option value="{{ $airline->id }}" label="{{ $airline->name }}" />
                                @endforeach
                            </x-select>
                            <!-- Aircraft Field -->
                            <x-select
                                class="pd-2"
                                label="Aircraft"
                                placeholder="Please select"
                                wire:model='aircraft_id'
                                searchable="true"
                                min-items-for-search="2"
                            >
                                @foreach ($aircraft as $aircraftType)
                                    <x-select.option value="{{ $aircraftType->id }}" label="{{ $aircraftType->manufacturer}} {{ $aircraftType->model }}-{{ $aircraftType->varient }}" />
                                @endforeach
                            </x-select>
                            <!-- Flight number field -->
                            <x-input
                                label="Flight Number"
                                placeholder="BA1234"
                                wire:model='flightNumber'
                                style="text-transform: uppercase"
                            />
                            <!-- Registration Field -->
                            <x-input
                                label="Registration"
                                placeholder="G-PNCB"
                                wire:model='registration'
                                style="text-transform: uppercase"
                            />
                            <div class="flex mt-4 space-x-2">
                                <button type="button" wire:click='stopEdit' class="px-2 py-1 text-white bg-gray-500 rounded">Cancel</button>
                                <button type="submit" class="px-2 py-1 text-white bg-green-500 rounded">Save</button>
                            </div>
                        </form>
                    @else
                        <div>
                            <div><span class="text-gray-800">
                                <x-badge flat slate label="DEP" />
                                {{ $aircraftLog?->departureAirport?->name }} ({{ $aircraftLog?->departureAirport?->code }})
                                <x-icon name="arrow-right" class="inline-block w-5 h-3" />
                                <x-badge flat slate label="ARV" />
                                {{ $aircraftLog?->arrivalAirport?->name }} ({{ $aircraftLog?->arrivalAirport?->code }})
                                </span></div>
                        </div>
                        <div class="grid grid-cols-3 mt-2">
                            <div class="col-span-1">
                                <div><small class="text-xs text-gray-600">{{ (new DateTime($aircraftLog?->logged_at))->format("d/m/Y") }}</small></div>
                            </div>
                            <div class="col-span-2">
                                <div><small class="text-xs text-gray-600">{{ FlyingStatus::getNameByStatus($status) }}</small></div>
                            </div>
                            <div></div>
                        </div>
                        <!-- Display Log Details -->
                        <p class="mb-2 text-gray-700 text-md">Aircraft: {{ $aircraftLog?->aircraft?->manufacturer }} {{ $aircraftLog?->aircraft?->model }}-{{ $aircraftLog?->aircraft?->varient }}</p>
                        <p class="mb-2 text-gray-700 text-md">Registration: {{ $aircraftLog?->registration }}</p>
                        <p class="mb-2 text-gray-700 text-md">Airline: {{ $aircraftLog?->airline?->name }} {{ $aircraftLog->flight_number }}</p>
                        <p class="text-gray-700 text-md">{{ $aircraftLog?->description }}</p>
                    @endif
                </div>
            </div>

            <!-- Modal Content -->

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
