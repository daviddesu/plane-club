<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\MediaService;
use Mary\Traits\Toast;
use App\Models\Sighting;
use App\Traits\WithMedia;
use Illuminate\Support\Facades\Auth;


new class extends Component
{
    use WithFileUploads;
    use Toast;
    use WithMedia;

    public bool $storageLimitExceeded = false;

    #[Validate('required|date')]
    public ?string $loggedAt;

    #[Validate('required')]
    public ?string $status = null;

    #[Validate('required_if:status,1,2')]
    public ?string $departureAirport = null;

    #[Validate('required_if:status,2,3')]
    public ?string $arrivalAirport = null;

    #[Validate('nullable')]
    public ?string $airline = null;

    #[Validate('nullable')]
    public ?string $aircraft = null;

    #[Validate('nullable|string')]
    public string $description = "";

    #[Validate('nullable|string')]
    public string $registration = "";

    #[Validate('nullable|string')]
    public string $flightNumber = "";

    public array $statuses;

    public function mount()
    {
        $this->checkStorageLimits();
        $this->loggedAt = now()->format('Y-m-d H:i');
    }



    /**
    * Custom validation error messages.
    */
    public function messages()
    {
        return [
            'media.max' => 'Your file may not be larger than :max KB.',
        ];
    }

    public function store()
    {
        $this->validate($this->rules());

        $sighting = auth()->user()->sightings()->create([
            'logged_at' => $this->loggedAt,
            'status' => $this->status,
            'aircraft_id' => $this->aircraft,
            'airline_id' => $this->airline,
            'departure_airport_id' => $this->departureAirport,
            'arrival_airport_id' => $this->arrivalAirport,
            'description' => $this->description,
            'registration' => $this->registration,
            'flight_number' => $this->flightNumber,
        ]);

        if (!$this->validateAndProcessMedia($sighting->id)) {
            $sighting->delete();
            return;
        }

        $this->success('Sighting created successfully');
        $this->redirect('/sightings', navigate: true);
    }

    public function removeUploadedMedia()
    {
        $this->media = null;
    }
}
?>


<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            {{ __('Create Sighting') }}
        </h2>
    </x-slot>
    @if($storageLimitExceeded)
        <div class="text-center">
            You have reached the limit of 30 sightings with images for this month. Please <a href="/profile" class="text-blue-500 underline">upgrade to Pro</a> for unlimited sightings with images and videos, or wait until next month for your limit to reset.
        </div>
    @else
        <x-mary-form wire:submit='store()'>
            <div class="grid grid-cols-1 gap-4">
                {{-- File upload for images --}}
                    <x-mary-file
                        wire:model="media"
                        label="Choose an image"
                        hint="{{ Auth::user()->isPro() ? 'Max file size upload of 500MB' : 'Larger image uploads available with Pro' }}"
                        spinner
                    />
                    @error('media')
                        <span class="error">{{ $message }}</span>
                    @enderror
                <div class="flex flex-col gap-y-6">

                    <x-mary-datetime
                        label="Date"
                        wire:model="loggedAt"
                        icon="o-calendar"
                    />

                    <livewire:sightings.components.status_select wire:model="status" lazy />
                    <livewire:sightings.components.airport_search wire:model="departureAirport" label="Departure airport" lazy />
                    <livewire:sightings.components.airport_search wire:model="arrivalAirport" label="Arrival airport" lazy />
                    <livewire:sightings.components.airline_search wire:model="airline" lazy />
                    <livewire:sightings.components.aircraft_search wire:model="aircraft" lazy />

                    <x-mary-input
                        label="Flight Number"
                        placeholder="BA1234"
                        wire:model='flightNumber'
                        style="text-transform: uppercase"
                    />

                    <x-mary-input
                        label="Registration"
                        placeholder="G-PNCB"
                        wire:model='registration'
                        style="text-transform: uppercase"
                    />
                </div>
            </div>

            <div class="pt-2 border-b-2"></div>
            <div name="footer" class="flex justify-between gap-x-4">
                @if($media)
                    <x-mary-button class="mt-4" flat negative label="Clear media" wire:click='removeUploadedMedia' />
                @endif
                <div class="flex gap-x-4">
                    <x-mary-button type="submit" label="Save" wire:loading.attr="disabled" flat class="justify-center mt-4 btn-primary" spinner />
                </div>
            </div>
        </x-mary-form>
    @endif
</div>


