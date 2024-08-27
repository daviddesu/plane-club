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
    public function getAircraftLogs(): void
    {
        $this->aircraftLogs = AircraftLog::with('user', 'image', 'airline', 'airport', 'aircraft')->latest()->get();
    }

}


?>


<div class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
        <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
            @foreach($aircraftLogs as $aircraftLog)
                <li wire:key='{{ $aircraftLog->id }}'>
                    <img
                        x-on:click="$wire.dispatch('open_aircraft_log', { id: {{ $aircraftLog->id }},});"
                        src="{{ asset('storage/' .  $aircraftLog->image->path) }}"
                        alt=""
                        class="object-cover select-none w-full h-auto bg-gray-200 rounded cursor-pointer aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]"
                    >
                    <div>
                        <span class="text-gray-800">{{ $aircraftLog->airport->name }}</span>
                        <small class="ml-2 text-xs text-gray-600">{{ $aircraftLog->user->name }}</small>
                        <small
                            class="ml-2 text-xs text-gray-600">{{ $aircraftLog->logged_at }}</small>
                    </div>
                </li>
            @endforeach
        </ul>
    </div>
</div>
