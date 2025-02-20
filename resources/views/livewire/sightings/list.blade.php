<?php

use App\Models\Sighting;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Livewire\Volt\Component;
use Illuminate\Support\Collection;
use Livewire\Attributes\On;


new class extends Component
{
    public Collection $ids;

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
        $this->ids = collect();
        $this->getSightings(true);
    }

    public function getSightings(bool $reset = false): void
    {
        if ($reset) {
            $this->page = 1;
            $this->ids = collect();
            $this->hasMorePages = true;
        }

        $query = auth()->user()->sightings()->latest();

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

        $sightings = $query->skip($this->perPage * ($this->page - 1))
                    ->take($this->perPage + 1)
                    ->pluck('id');

        if ($sightings->count() > $this->perPage) {
            $this->hasMorePages = true;
            $sightings = $sightings->slice(0, $this->perPage);
        } else {
            $this->hasMorePages = false;
        }

        $this->ids = $this->ids->concat($sightings);
        $this->page++;
    }

    public function updatedSelectedAircraftType($value): void
    {
        $this->selectedAircraftType = $value ?: null;
        $this->getSightings(true);
    }

    public function updatedSelectedAirline($value): void
    {
        $this->selectedAirline = $value ?: null;
        $this->getSightings(true);
    }

    public function updatedSelectedAirport($value): void
    {
        $this->selectedAirport = $value ?: null;
        $this->getSightings(true);
    }

    public function loadMore(): void
    {
        if ($this->hasMorePages && !$this->isLoading) {
            $this->isLoading = true;
            $this->getSightings();
            $this->isLoading = false;
        }
    }
}
?>



<div class="items-center w-full h-full select-none">
    <div class="mx-auto md:w-4/6" xl:w-3/4>
        <!-- Filters -->
        <x-mary-collapse class="mb-5 ">
            <x-slot:heading>
                Filters
            </x-slot:heading>
            <x-slot:content class="flex flex-col gap-4">
                <livewire:sightings.components.airport_search wire:model.live="selectedAirport" label="Airport"  lazy/>
                <livewire:sightings.components.airline_search wire:model.live="selectedAirline" lazy />
                <livewire:sightings.components.aircraft_search wire:model.live="selectedAircraftType" lazy />
            </x-slot:content>
        </x-mary-collapse>

        <!-- Logs -->
        <div class="duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="opacity: 1;">
            <ul
                x-ref="gallery"
                id="gallery"
                class="flex flex-col gap-4"
            >


                @foreach($this->ids as $index => $id)
                    @if($id % 5 == 0 && !Auth::user()->isPro()){
                        <li wire:key="sighting-ad-{{ $index }}">
                            <livewire:ads.card
                                wire:key="ad-card-{{ $index }}"
                                lazy
                        </li>
                    @endif
                    <li wire:key="sighting-item-{{ $id }}">
                        <livewire:sightings.card
                            wire:key="sighting-card-{{ $id }}"
                            :id="$id"
                            lazy
                        />
                    </li>
                @endforeach
            </ul>


            @if ($this->ids && $hasMorePages)
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

