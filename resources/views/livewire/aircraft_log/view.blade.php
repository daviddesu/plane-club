<?php
use App\Models\AircraftLog;
use App\Models\Aircraft;
use App\Models\Airline;
use App\Models\Airport;
use Livewire\Volt\Component;
use Livewire\Attributes\On;
use Livewire\Attributes\Validate;
use Illuminate\Database\Eloquent\Collection;



new class extends Component
{
    public Collection $airports;
    public Collection $airlines;
    public Collection $aircraft;

    public ?int $id;

    public ?AircraftLog $aircraftLog;

    #[Validate('required')]
    public string|null $loggedAt;

    #[Validate('required')]
    public ?string $airport_id;

    #[Validate]
    public ?string $airline_id = null;

    #[Validate]
    public ?string $aircraft_id = null;

    #[Validate]
    public ?string $description = "";

    #[Validate]
    public ?string $registration = "";


    public bool $editing = false;


    #[On('load_aircraft_log')]
    public function loadAircraftLog($id): void
    {
        $this->id = $id;
        $this->aircraftLog = AircraftLog::with('user', 'image', 'airport', 'airline', 'aircraft')->where('id', $id)->first();
        $this->loggedAt = $this->aircraftLog->logged_at;
        $this->airport_id = $this->aircraftLog->airport?->id;
        $this->description = $this->aircraftLog->description;
        $this->airline_id = $this->aircraftLog->airline?->id;
        $this->aircraft_id = $this->aircraftLog->aircraft?->id;
        $this->registration = $this->aircraftLog->registration;
    }

    public function mount(int $id = null){
        if(!is_null($id)){
            $this->loadAircraftLog($id);
        }
        $this->airports = Airport::all();
        $this->aircraft = Aircraft::all();
        $this->airlines = Airline::all();
    }

    #[On('close_aircraft_log')]
    public function closeLog()
    {
        $this->id = null;
        $this->aircraftLog = null;
        $this->editing = false;
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

        $this->aircraftLog->update([
            "airport_id" => $this->airport_id,
            "logged_at" => $this->loggedAt,
            "description" => $this->description,
            "airline_id" => $this->airline_id,
            "registration" => strtoupper($this->registration),
            "aircraft_id" => $this->aircraft_id,
        ]);
        $this->dispatch('aircraft_log-updated');
        $this->dispatch('close_aircraft_log_modal');
    }

    public function delete()
    {
        $this->aircraftLog->delete();
        $this->dispatch('aircraft_log-deleted');
    }

}
?>

<div class="grid grid-cols-1 md:grid-cols-3">

    @if($id)
    <div class="relative top-0 bottom-0 right-0 flex-shrink-0 bg-cover md:col-span-2 md:border-r-2 overlow-scroll lg:block">
        <img
            class="object-contain object-center w-full h-full bg-opacity-100 cursor-pointer select-none"
            src="{{ Storage::disk('s3')->url($aircraftLog?->image->path) }}"
            alt=""
        />
    </div>
    <div class="relative flex flex-wrap w-full h-full px-8 pt-2 md:col-span-1">
        <div class="relative float-left w-full max-w-sm mx-auto space-x-1 lg:mb-0">
            @if ($aircraftLog?->user->is(auth()->user()) && !$editing)
                <x-mini-button rounded outline wire:click='startEdit' icon="pencil" />
                {{-- <x-mini-button rounded wire:click='copyLink' icon="link" />
                <x-mini-button rounded wire:click='publishToFacebook' icon="facebook" />
                <x-mini-button rounded wire:click='publishToTwitter' icon="twitter" /> --}}
                <x-mini-button rounded outline negative wire:click='delete' icon="trash" />
            @endif
        </div>

        <div class="relative w-full max-w-sm mx-auto lg:mb-0">
            <div class="relative text-left">
                <div class="flex flex-col mb-6 space-y-2">
                    <div class="pb-4">
                        <p class="text-md text-neutral-500">{{ (new DateTime($aircraftLog?->logged_at))->format("d/m/Y") }}</p>
                    </div>
                    @if($editing)
                        <form class="flex flex-wrap" wire:submit='update'>
                            <x-datetime-picker
                            class="pd-2"
                            wire:model="loggedAt"
                            label="Date"
                            placeholder="Date"
                            without-time
                        />

                        <x-select
                            class="pd-2"
                            label="Airport"
                            placeholder="Please select"
                            wire:model='airport_id'
                            searchable="true"
                            min-items-for-search="2"
                        >
                            @foreach ($airports as $airport)
                                <x-select.option value="{{ $airport->id }}" label="{{ $airport->name }} ({{ $airport->code }})" />
                            @endforeach
                        </x-select>

                        <x-select
                            class="pd-2"
                            label="Airline"
                            placeholder="Please select"
                            wire:model='airline_id'
                            searchable="true"
                            min-items-for-search="2"
                        >
                            @foreach ($airlines as $airline)
                                <x-select.option value="{{ $airline->id }}" label="{{ $airline->name }}" />
                            @endforeach
                        </x-select>

                        <x-select
                            class="pd-2"
                            label="Aircraft"
                            placeholder="Please select"
                            wire:model='aircraft_id'
                            searchable="true"
                            min-items-for-search="2"
                        >
                            @foreach ($aircraft as $aircraftType)
                                <x-select.option value="{{ $aircraftType->id }}" label="{{ $aircraftType->getFormattedName() }}" />
                            @endforeach
                        </x-select>

                        <x-input
                            label="Registration"
                            placeholder="G-PNCB"
                            wire:model='registration'
                            style="text-transform: uppercase"
                        />
                    @else
                        <p class="text-md text-neutral-500">Aircraft: {{ $aircraftLog?->aircraft?->manufacturer }} {{ $aircraftLog?->aircraft?->model }}-{{ $aircraftLog?->aircraft?->varient }}</p>
                        <p class="text-md text-neutral-500">Registration: {{ $aircraftLog?->registration }}</p>
                        <p class="text-md text-neutral-500">Airline: {{ $aircraftLog?->airline?->name }}</p>
                        <p class="text-md text-neutral-500">Airport: {{ $aircraftLog?->airport->name }} ({{ $aircraftLog?->airport->code }})</p>
                        <p class="text-md text-neutral-500">{{ $aircraftLog?->description }}</p>
                    @endif


                    @if($editing)
                            <x-button flat class="justify-center mt-4" label="Cancel" wire:click='stopEdit' />
                            <x-primary-button  flat class="justify-center mt-4">{{ __('Save') }}</x-primary-button>
                        </form>
                    @endif
                </div>
            </div>
        </div>
    </div>
    @endif
</div>

