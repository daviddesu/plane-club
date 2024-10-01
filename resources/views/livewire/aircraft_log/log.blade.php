<?php

use App\Models\AircraftLog;
use Livewire\Volt\Component;

new class extends Component
{
    public AircraftLog $aircraftLog;

    public function mount(int $aircraftLogId): void
    {
        $this->aircraftLog = AircraftLog::with('user', 'image', 'airline', 'airport', 'aircraft')->find($aircraftLogId);
    }
}


?>


<div>
    <img
        x-on:click="$wire.dispatch('open_aircraft_log', { id: {{ $aircraftLog->id }},});"
        src="{{ Storage::disk('s3')->temporaryUrl($aircraftLog->image->path, now()->addMinutes(60)) }}"
        alt=""
        class="object-cover select-none w-full h-auto bg-gray-200 rounded cursor-pointer aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]"
    >
    <div class="grid grid-cols-2">
        <div>
            <div><span class="text-gray-800">{{ $aircraftLog->airport->name }}</span></div>
            <div><small class="text-xs text-gray-600">{{ (new DateTime($aircraftLog->logged_at))->format('d/m/Y') }}</small></div>
        </div>
        <div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->aircraft?->getFormattedName() }}</small></div>
            <div><small class="text-xs text-gray-600">{{ $aircraftLog->airline?->name }}</small></div>

        </div>
    </div>
</div>
