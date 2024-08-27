<?php
use App\Models\AircraftLog;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;


new class extends Component
{
    public ?int $id;

    public ?AircraftLog $aircraftLog;

    #[Validate('required')]
    public string|null $loggedAt;
    #[Validate('required')]
    public string|null $airport;

    public string $description = "";

    public bool $editing = false;


    #[On('load_aircraft_log')]
    public function loadAircraftLog($id): void
    {
        $this->id = $id;
        $this->aircraftLog = AircraftLog::with('user', 'image', 'airport')->where('id', $id)->first();
        $this->loggedAt = $this->aircraftLog->logged_at;
        $this->airport = $this->aircraftLog->airport;
        $this->description = $this->aircraftLog->description;
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

    public function startEdit()
    {
        $this->editing = true;
    }

    public function stopEdit()
    {
        $this->editing = false;
    }

    public function update()
    {
        $this->authorize('update', $this->aircraftLog);
        $validated = $this->validate();
        $this->editing = false;
        $this->aircraftLog->update($validated);
        $this->dispatch('aircraft_log-updated');
        $this->dispatch('close_aircraft_log_modal');
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
    <div class="relative flex flex-wrap items-center w-full h-full px-8 pt-2 md:col-span-1">
        <div class="relative w-full max-w-sm mx-auto lg:mb-0">

        @if ($aircraftLog?->user->is(auth()->user()) && !$editing)
            <x-button class="float-right" wire:click='startEdit' icon="pencil" label="Edit" />
        @endif
        </div>

        <div class="relative w-full max-w-sm mx-auto lg:mb-0">
            <div class="relative text-left">
                <div class="flex flex-col mb-6 space-y-2">
                    @if($editing)
                        <form wire:submit='update'>
                        <x-select
                            class="pd-2"
                            label="Airport"
                            placeholder="Please select"
                            :async-data="route('airports')"
                            option-label="name"
                            option-value="id"
                            wire:model='airport'
                        />
                    @else
                        <p class="text-sm text-neutral-500">Aircraft: {{ $aircraftLog?->aircraft }}</h1>
                        <p class="text-sm text-neutral-500">Registration: {{ $aircraftLog?->registration }}</h1>
                        <p class="text-sm text-neutral-500">Airline: {{ $aircraftLog?->airline?->name }}</h1>
                        <p class="text-sm text-neutral-500">Airport: {{ $aircraftLog?->airport->name }}</h1>
                        <p class="text-sm text-neutral-500">{{ $aircraftLog?->description }}</h1>
                    @endif

                    <p class="text-sm text-neutral-500">by {{ $aircraftLog?->user->name }}</p>
                    <p class="text-sm text-neutral-500">{{ (new DateTime($aircraftLog?->logged_at))->format("d/m/Y") }}</p>

                    @if($editing)
                            <x-button flat class="justify-center mt-4" label="Cancel" wire:click='stopEdit' />
                            <x-primary-button  flat class="justify-center mt-4">{{ __('Save') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

