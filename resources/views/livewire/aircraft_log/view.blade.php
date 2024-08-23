<?php
use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Livewire\Attributes\On;

new class extends Component
{
    public int|null $id;

    public AircraftLog|null $aircraftLog;

    #[On('load_aircraft_log')]
    public function loadAircraftLog($id): void
    {
        $this->id = $id;
        $this->aircraftLog = AircraftLog::with('user', 'image', 'airport')->where('id', $id)->first();
    }

    public function mount(int $id = null){
        if(!is_null($id)){
            $this->loadAircraftLog($id);
        }
    }

    #[On('close_aircraft_log')]
    public function closeLog()
    {
        $this->id = null;
        $this->aircraftLog = null;
    }

}
?>

<div class="grid grid-cols-1 md:grid-cols-3">

    <div class="relative top-0 bottom-0 right-0 flex-shrink-0 bg-cover md:col-span-2 md:border-r-2 overlow-scroll lg:block">
        <img
            class="object-contain object-center w-full h-full bg-opacity-100 cursor-pointer select-none"
            src="{{ asset('storage/' . $aircraftLog?->image->path) }}"
            alt=""
        />
    </div>
    <div class="relative flex flex-wrap items-center w-full h-full px-8 md:col-span-1">

        <div class="relative w-full max-w-sm mx-auto lg:mb-0">
            <div class="relative text-center">
                <div class="flex flex-col mb-6 space-y-2">
                    <h1 class="text-2xl font-semibold tracking-tight">{{ $aircraftLog?->airport->name }}</h1>
                    <p class="text-sm text-neutral-500">{{ $aircraftLog?->user->name }}</p>
                    <p class="text-sm text-neutral-500">{{ $aircraftLog?->logged_at }}</p>
                </div>
            </div>
        </div>
    </div>
</div>

