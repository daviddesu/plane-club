<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;



new class extends Component
{
    public array $aircraftLogIds;


    public function mount(): void
    {
        $this->getAircraftLogIds();
    }


    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getAircraftLogIds(): void
    {
        $this->aircraftLogIds = AircraftLog::with('user', 'image', 'airline', 'airport', 'aircraft')->latest()->pluck("id")->toArray();
    }

}


?>


<div class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
        <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
            @foreach($aircraftLogIds as $aircraftLogId)
                <li wire:key='{{ $aircraftLogId }}'>
                    <livewire:aircraft_log.log lazy :$aircraftLogId />
                </li>
            @endforeach
        </ul>
    </div>
</div>
