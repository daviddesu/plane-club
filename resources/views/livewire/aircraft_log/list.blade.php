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


    #[on('aircraft_log-created')]
    public function getAircraftLogs(): void
    {
        $this->aircraftLogs = AircraftLog::with('user', 'images')->latest()->get();
    }

}


?>

<div class="grid-cols-1 gap-4 p-4 maxgrid md:grid-cols-3 xl:grid-cols-4">
    @foreach ($aircraftLogs as $aircraftLog)
        <div class='h-auto max-w-full' wire:key='{{ $aircraftLog->id }}'>
            {{-- <svg xmlns="http://www.w3.org/2000/svg" class="w-6 h-6 text-gray-600 -scale-x-100" fill="none"
                viewBox="0 0 24 24" stroke="currentColor" stroke-width="2">
                <path stroke-linecap="round" stroke-linejoin="round"
                    d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z" />
            </svg> --}}
            @foreach ($aircraftLog->images as $aircraftImage)
                <img class="object-cover h-48 w-70" src="{{ asset('storage/' . $aircraftImage->path) }}" />
            @endforeach
            <div class="items-center justify-between">
                <div>
                    <span class="text-gray-800">{{ $aircraftLog->user->name }}</span>
                    <small
                        class="ml-2 text-sm text-gray-600">{{ $aircraftLog->created_at->format('j M Y, g:i a') }}</small>
                </div>
            </div>
            <p class="mt-4 text-lg text-gray-900">{{ $aircraftLog->message }}</p>
        </div>
    @endforeach

</div>
