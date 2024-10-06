<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Attributes\On;



new class extends Component
{
    public Collection $images;


    public function mount(): void
    {
        $this->getImageIds();
    }

    #[On('aircraft_log-created')]
    #[On('aircraft_log-updated')]
    #[On('aircraft_log-deleted')]
    public function getImageIds(): void
    {
        $this->images = auth()->user()->images()->with('aircraftLog:id')->latest()->get();
    }

}


?>


<div class="w-full h-full select-none">
    <div class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view" style="translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
        <ul x-ref="gallery" id="gallery" class="grid grid-cols-2 gap-5 lg:grid-cols-3">
            @foreach($images as $image)
                <li wire:key='{{ $image->id }}'>
                    <livewire:aircraft_log.log lazy :aircraftLogId="$image->aircraftLog->id" :imageId="$image->id" />
                </li>
            @endforeach
        </ul>
    </div>
</div>
