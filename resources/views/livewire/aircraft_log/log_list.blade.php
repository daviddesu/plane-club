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

    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getAircraftLogs(): void
    {
        $this->aircraftLogs = auth()->user()->aircraftLogs()->latest()->get();
    }
}


?>


<div class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
        <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
            @foreach($aircraftLogs as $aircraftLog)
                <li wire:key='{{ $aircraftLog->id }}'>
                    <livewire:aircraft_log.log_card lazy :aircraftLogId="$aircraftLog->id" />
                </li>
            @endforeach
        </ul>
    </div>
</div>
