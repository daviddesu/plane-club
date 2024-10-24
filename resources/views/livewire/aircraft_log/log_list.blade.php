<?php

use App\Models\AircraftLog;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;


new class extends Component
{
    public Collection $aircraftLogs;
    public Collection $aircraftTypes;
    public Collection $airlines;
    public Collection $airports;

    public array $aircraftOptions = [];
    public array $airlineOptions = [];
    public array $airportOptions = [];

    public $selectedAircraftType = null;
    public $selectedAirline = null;
    public $selectedAirport = null;

    public function mount(): void
    {
        // Load filter options
        $this->aircraftTypes = Aircraft::all();
        $this->airlines = Airline::all();
        $this->airports = Airport::all();

        $this->aircraftOptions = $this->aircraftTypes->map(function ($aircraft) {
            return [
                'id' => $aircraft->id,
                'name' => $aircraft->manufacturer . ' ' . $aircraft->model . ($aircraft->variant ? '-' . $aircraft->variant : ''),
            ];
        })->toArray();

        $this->airlineOptions = $this->airlines->map(function ($airline) {
            return [
                'id' => $airline->id,
                'name' => $airline->name,
            ];
        })->toArray();

        // Prepare airport options
        $this->airportOptions = $this->airports->map(function ($airport) {
            return [
                'id' => $airport->id,
                'name' => $airport->name . ' (' . $airport->code . ')',
            ];
        })->toArray();

        $this->getAircraftLogs();
    }

    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getAircraftLogs(): void
    {
        $query = auth()->user()->aircraftLogs()->latest();

        if ($this->selectedAircraftType) {
            $query->where('aircraft_id', $this->selectedAircraftType);
        }

        if ($this->selectedAirline) {
            $query->where('airline_id', $this->selectedAirline);
        }

        if ($this->selectedAirport) {
            $query->where('airport_id', $this->selectedAirport);
        }

        $this->aircraftLogs = $query->get();
    }

    public function updatedSelectedAircraftType(): void
    {
        $this->getAircraftLogs();
    }

    public function updatedSelectedAirline(): void
    {
        $this->getAircraftLogs();
    }

    public function updatedSelectedAirport(): void
    {
        $this->getAircraftLogs();
    }
}
?>



<div class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto">
        <!-- Filters -->
        <div class="flex flex-col gap-4 mb-4 sm:flex-row">
            <!-- Aircraft Type Filter -->
            <div class="flex-1">
                <x-select
                    wire:model.live="selectedAircraftType"
                    label="Aircraft Type"
                    placeholder="All Aircraft Types"
                    :options="$aircraftOptions"
                    option-label="name"
                    option-value="id"
                    searchable
                />
            </div>

            <!-- Airline Filter -->
            <div class="flex-1">
                <x-select
                    wire:model.live="selectedAirline"
                    label="Airline"
                    placeholder="All Airlines"
                    :options="$airlineOptions"
                    option-label="name"
                    option-value="id"
                    searchable
                />
            </div>

            <!-- Airport Filter -->
            <div class="flex-1">
                <x-select
                    wire:model.live="selectedAirport"
                    label="Airport"
                    placeholder="All Airports"
                    :options="$airportOptions"
                    option-label="name"
                    option-value="id"
                    searchable
                />
            </div>
        </div>

        <!-- Logs -->
        <div class="duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="opacity: 1;">
            <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
                @foreach($this->aircraftLogs as $aircraftLog)
                    <li>
                        <livewire:aircraft_log.log_card
                            wire:key="aircraftLog-{{ $aircraftLog->id }}"
                            :aircraftLogId="$aircraftLog->id"
                        />
                    </li>
                @endforeach
            </ul>
        </div>
    </div>
</div>

