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

    public $selectedDepartureAirport = null;
    public $selectedArrivalAirport = null;
    public $selectedAirline = null;
    public $selectedAircraft = null;


    public ?int $id = null;

    #[Validate('required')]
    public ?string $loggedAt = null;

    #[Validate('required')]
    public ?string $status = null;

    #[Validate('required_if:status,1,2')]
    public ?string $departure_airport_id = null;
    public ?string $arrivalAirportName = "";
    public ?string $arrivalAirportCode = "";
    public ?string $departureAirportName = "";
    public ?string $departureAirportCode = "";

    #[Validate('required_if:status,2,3')]
    public ?string $arrival_airport_id = null;

    #[Validate]
    public ?string $airline_id = null;

    #[Validate]
    public ?string $aircraft_id = null;

    public ?string $aircraftName = "";

    #[Validate]
    public ?string $description = "";

    #[Validate]
    public ?string $registration = "";

    #[Validate]
    public ?string $flightNumber = "";

    public ?string $airlineName = "";

    public ?Media $media = null;

    public ?string $mediaUrl = "";
    public ?string $realMediaUrl = "";

    public string $fileName = "";

    public bool $editing = false;
    public bool $editingAllowed = false;
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
        $aircraftLog = AircraftLog::with('user', 'media', 'departureAirport', 'arrivalAirport', 'airline', 'aircraft')->find($id);

        if ($aircraftLog) {
            $this->id = $id;
            $this->loggedAt = $aircraftLog->logged_at;
            $this->arrival_airport_id = $aircraftLog->arrivalAirport?->id;
            $this->arrivalAirportName = $aircraftLog->arrivalAirport?->name;
            $this->arrivalAirportCode = $aircraftLog->arrivalAirport?->code;
            $this->departure_airport_id = $aircraftLog->departureAirport?->id;
            $this->departureAirportName = $aircraftLog->departureAirport?->name;
            $this->departureAirportCode = $aircraftLog->departureAirport?->code;
            $this->status = $aircraftLog->status;
            $this->description = $aircraftLog->description;
            $this->airline_id = $aircraftLog->airline?->id;
            $this->aircraft_id = $aircraftLog->aircraft?->id;
            $this->registration = $aircraftLog->registration;
            $this->flightNumber = $aircraftLog->flight_number;
            $this->media = $aircraftLog->media;
            $this->mediaUrl = $this->getCachedMediaUrl($this->media->path);
            $this->realMediaUrl = $aircraftLog->media->path;
            $this->fileName = $this->generateFileName();
            $this->editingAllowed = $aircraftLog->user->is(auth()->user());
            $this->aircraftName = $aircraftLog->aircraft?->getFormattedName();
            $this->airlineName = $aircraftLog->airline?->name;

            // Populate selected options for x-select components
            if ($aircraftLog->departureAirport) {
                $this->selectedDepartureAirport = [
                    'id' => $aircraftLog->departureAirport->id,
                    'name' => $aircraftLog->departureAirport->name . ' (' . $aircraftLog->departureAirport->iata_code . ')',
                ];
            }

            if ($aircraftLog->arrivalAirport) {
                $this->selectedArrivalAirport = [
                    'id' => $aircraftLog->arrivalAirport->id,
                    'name' => $aircraftLog->arrivalAirport->name . ' (' . $aircraftLog->arrivalAirport->iata_code . ')',
                ];
            }

            if ($aircraftLog->airline) {
                $this->selectedAirline = [
                    'id' => $aircraftLog->airline->id,
                    'name' => $aircraftLog->airline->name,
                ];
            }

            if ($aircraftLog->aircraft) {
                $this->selectedAircraft = [
                    'id' => $aircraftLog->aircraft->id,
                    'name' => $aircraftLog->aircraft->getFormattedName(),
                ];
        }


        }
    }

    public function generateFileName()
    {
        $name = "Plane-club-";
        $name .= (new \DateTime($this->loggedAt))->format("Y-m-d");

        if($this->departureAirportName){
            $name .= "-{$this->departureAirportCode}";
        }

        if($this->arrivalAirportName){
            $name .= "-{$this->arrivalAirportCode}";
        }

        if($this->aircraftName){
            $name .= "-{$this->aaircraftName}";
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
    public function closeLog()
    {
        $this->editing = false;
        $this->modalOpened = false;
    }

    public function delete(): void
    {
        // Delete the media and the log first
        Storage::disk(getenv('FILESYSTEM_DISK'))->delete($this->realMediaUrl);
        AircraftLog::destroy($this->id);

        // Dispatch events after deletion
        $this->dispatch('aircraft_log-deleted', $this->id);
        Toaster::info("Log deleted");
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
        $aircraftLog = AircraftLog::find($this->id);

        $aircraftLog->update([
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
        $this->dispatch('aircraft_log-updated', $this->id);
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
    <!-- Confirmation Dialog -->
    <div
        x-show="confirmDelete"
        x-transition
        class="fixed inset-0 flex items-center justify-center bg-black bg-opacity-75 z-60"
        @keydown.escape.window="confirmDelete = false"
    >
            <div class="max-w-sm p-6 mx-auto bg-white rounded-lg">
                <h2 class="mb-4 text-lg font-semibold">Delete Log</h2>
                <p class="mb-6">Are you sure you want to delete this log? This action cannot be undone.</p>
                <div class="flex justify-end space-x-2">
                    <button
                        @click="confirmDelete = false"
                        type="button"
                        class="px-4 py-2 text-white bg-gray-500 rounded"
                    >
                        Cancel
                    </button>
                    <button
                        @click="confirmDelete = false; modalClose(); @this.delete()"
                        type="button"
                        class="px-4 py-2 text-white bg-red-600 rounded"
                    >
                        Delete
                    </button>
                </div>
            </div>
        </div>
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
                    @if ($editingAllowed && !$editing)
                        <div class="flex mb-4 first-letter:space-x-2">
                            <x-mini-button wire:click='startEdit' rounded icon="pencil" flat class="text-cyan-800 hover:cyan-200 hover:bg-cyan-800" interaction:solid />
                            <a href="{{ $mediaUrl }}" download="{{ $fileName }}"><x-mini-button rounded icon="arrow-down-tray" flat class="text-cyan-800 hover:cyan-200 hover:bg-cyan-800" interaction:solid /></a>
                            <x-mini-button @click="confirmDelete = true" rounded icon="trash" flat red interaction="negative" />
                            {{-- Delete dialog --}}


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
                                    <x-select.option value="{{ FlyingStatus::ON_STAND->value }}" label="{{ strtolower(ucfirst(FlyingStatus::ON_STAND->name)) }}" />
                                </x-select>
                            </div>
                            <!-- Departure Airport Field -->
                            <x-select
                                label="Departure airport"
                                placeholder="Search airport or IATA code"
                                :async-data="route('airports')"
                                option-label="name"
                                option-value="id"
                                wire:model='departure_airport_id'
                                :selected="$departure_airport_id"
                                searchable
                                min-items-for-search="2"
                            />

                            <x-select
                                label="Arrival airport"
                                placeholder="Search airport or IATA code"
                                :async-data="route('airports')"
                                option-label="name"
                                option-value="id"
                                wire:model='arrival_airport_id'
                                :selected="$arrival_airport_id"
                                searchable
                                min-items-for-search="2"
                            />
                            <x-select
                                label="Airline"
                                placeholder="Search airline"
                                :async-data="route('airlines')"
                                option-label="name"
                                option-value="id"
                                wire:model='airline_id'
                                :selected="$airline_id"
                                searchable
                                min-items-for-search="2"
                            />

                            <!-- Aircraft Field -->
                            <x-select
                                label="Aircraft"
                                placeholder="Search aircraft"
                                :async-data="route('aircraft')"
                                option-label="name"
                                option-value="id"
                                wire:model='aircraft_id'
                                :selected="$aircraft_id"
                                searchable
                                min-items-for-search="2"
                            />

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
                                <button type="submit" class="px-2 py-1 text-white rounded bg-cyan-800">Save</button>
                            </div>
                        </form>
                    @else
                        <div>
                            <div><span class="text-gray-800">
                                <x-badge flat slate label="DEP" />
                                {{ $departureAirportName }} ({{ $departureAirportCode }})
                                <x-icon name="arrow-right" class="inline-block w-5 h-3" />
                                <x-badge flat slate label="ARV" />
                                {{ $arrivalAirportName }} ({{ $arrivalAirportCode }})
                                </span></div>
                        </div>
                        <div class="grid grid-cols-3 mt-2">
                            <div class="col-span-1">
                                <div><small class="text-xs text-gray-600">{{ (new DateTime($loggedAt))->format("d/m/Y") }}</small></div>
                            </div>
                            <div class="col-span-2">
                                <div><small class="text-xs text-gray-600">{{ FlyingStatus::getNameByStatus($status) }}</small></div>
                            </div>
                            <div></div>
                        </div>
                        <!-- Display Log Details -->
                        <p class="mb-2 text-gray-700 text-md">Aircraft: {{ $aircraftName }}</p>
                        <p class="mb-2 text-gray-700 text-md">Registration: {{ $registration }}</p>
                        <p class="mb-2 text-gray-700 text-md">Airline: {{ $airlineName }} {{ $flightNumber }}</p>
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
            confirmDelete: false,
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
