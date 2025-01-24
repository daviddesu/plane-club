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

    public ?int $selectedAircraftType = null;
    public ?int $selectedAirline = null;
    public ?int $selectedAirport = null;

    // Pagination
    public int $perPage = 20;
    public int $page = 1;
    public bool $hasMorePages = true;
    public bool $isLoading = false;

    public function mount(): void
    {
        $this->aircraftLogIds = collect();
        $this->getAircraftLogs(true);
    }

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
            $query->where('arrival_airport_id', $this->selectedAirport)
            ->orWhere('departure_airport_id', $this->selectedAirport);
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

    #[On('aircraft_log-created')]
    public function aircraftLogCreated()
    {
        $this->getAircraftLogs(true);
    }

    #[On('aircraft_log-deleted')]
    public function aircraftLogDeleted($id): void
    {
        $this->aircraftLogIds = $this->aircraftLogIds->diff([$id]);
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



<div class="items-center w-full h-full select-none">
    <div class="mx-auto md:w-4/5" xl:w-3/4>
        <!-- Filters -->
        <x-mary-collapse class="mb-5">
            <x-slot:heading>
                Filters
            </x-slot:heading>
            <x-slot:content class="flex flex-col gap-4">
                <livewire:aircraft_log.components.airport_search wire:model="selectedAirport" label="Airport" />
                <livewire:aircraft_log.components.airline_search wire:model="selectedAirline" />
                <livewire:aircraft_log.components.aircraft_search wire:model="selectedAircraftType" />
            </x-slot:content>
        </x-mary-collapse>

        <!-- Logs -->
        <div class="duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="opacity: 1;">
            <ul
                x-ref="gallery"
                id="gallery"
                class="flex flex-col gap-4"
            >


                @foreach($this->aircraftLogIds as $aircraftLogId)
                    <li wire:key="aircraftLog-item-{{ $aircraftLogId }}">
                        <livewire:aircraft_log.log_card
                            lazy
                            wire:key="aircraftLog-{{ $aircraftLogId }}"
                            :aircraftLogId="$aircraftLogId"
                        />
                    </li>
                @endforeach
            </ul>


            @if ($this->aircraftLogIds && $hasMorePages)
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

