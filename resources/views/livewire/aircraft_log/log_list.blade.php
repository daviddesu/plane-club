<?php

use App\Models\AircraftLog;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;


new class extends Component
{
    public Collection $aircraftLogIds;
    public Collection $aircraftTypes;
    public Collection $airlines;
    public Collection $airports;

    public array $aircraftOptions = [];
    public array $airlineOptions = [];
    public array $airportOptions = [];

    public $selectedAircraftType = null;
    public $selectedAirline = null;
    public $selectedAirport = null;

    // Pagination
    public int $perPage = 20;
    public int $page = 1;
    public bool $hasMorePages = true;
    public bool $isLoading = false;

    public function mount(): void
    {
        $this->aircraftLogIds = collect();

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

        $this->getAircraftLogs(true);
    }

    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getAircraftLogs(bool $reset = false): void
    {
        if ($reset) {
            $this->page = 1;
            $this->aircraftLogIds = collect();
            $this->hasMorePages = true;
        }

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

        $logs = $query->skip($this->perPage * ($this->page - 1))
                    ->take($this->perPage + 1)
                    ->pluck('id');

        if ($logs->count() > $this->perPage) {
            $this->hasMorePages = true;
            $logs = $logs->slice(0, $this->perPage);
        } else {
            $this->hasMorePages = false;
        }

        $this->aircraftLogIds = $this->aircraftLogIds->concat($logs);
        $this->page++;
    }

    public function updatedSelectedAircraftType($value): void
    {
        $this->selectedAircraftType = $value ?: null;
        $this->getAircraftLogs(true);
    }

    public function updatedSelectedAirline($value): void
    {
        $this->selectedAirline = $value ?: null;
        $this->getAircraftLogs(true);
    }

    public function updatedSelectedAirport($value): void
    {
        $this->selectedAirport = $value ?: null;
        $this->getAircraftLogs(true);
    }

    public function loadMore(): void
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->isLoading = true;
            $this->getAircraftLogs();
            $this->isLoading = false;
        }
    }
}
?>



<div class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto">
        <!-- Filters Dropdown -->
        <div class="flex justify-end mb-4">
            <x-dropdown height="5xl" width="2xl" position="bottom-end" persistent="true">
                <x-slot name="trigger">
                    <x-button class="bg-cyan-800" label="Filters" primary />
                </x-slot>

                <!-- Dropdown Content -->
                <div class="p-4 space-y-4">
                    <!-- Aircraft Type Filter -->
                    <div>
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
                    <div>
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
                    <div>
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
            </x-dropdown>
        </div>

        <!-- Logs -->
        <div class="duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="opacity: 1;">
            <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
                @foreach($this->aircraftLogIds as $aircraftLogId)
                    <li>
                        <livewire:aircraft_log.log_card
                            wire:key="aircraftLog-{{ $aircraftLogId }}"
                            :aircraftLogId="$aircraftLogId"
                        />
                    </li>
                @endforeach
            </ul>

            @if ($hasMorePages)
                <div
                    x-data
                    x-intersect:enter="$wire.loadMore()"
                    class="py-4 text-center"
                >
                    <span wire:loading.delay>Loading...</span>
                </div>
            @endif
        </div>
    </div>
</div>

