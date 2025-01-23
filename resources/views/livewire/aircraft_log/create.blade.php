<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;
use Illuminate\Support\Facades\Storage;
use App\Services\MediaService;
use Mary\Traits\Toast;
use App\Models\AircraftLog;
use App\Traits\WithMedia;
use Illuminate\Support\Facades\Auth;


new class extends Component
{
    use WithFileUploads;
    use Toast;
    use WithMedia;

    public bool $storageLimitExceeded = false;

    #[Validate('required')]
    public ?string $loggedAt;

    #[Validate('required')]
    public ?string $status = null;

    #[Validate('required_if:status,1,2')]
    public ?string $departureAirport = null;

    #[Validate('required_if:status,2,3')]
    public ?string $arrivalAirport = null;

    #[Validate]
    public ?string $airline = null;

    #[Validate]
    public ?string $aircraft = null;

    #[Validate]
    public string $description = "";

    #[Validate]
    public string $registration = "";

    #[Validate]
    public string $flightNumber = "";

    public array $statuses;

    public function mount()
    {
        $this->checkStorageLimits();
        $this->loggedAt = now()->format('Y-m-d H:i');
    }

    public function rules()
    {
        $user = Auth::user();

        // Default plan: up to 4 MB
        $maxSizeKB = 4096;
        // Default plan: images only
        $mimeRule = 'mimetypes:image/*';

        if ($user->isPro()) {
            // Pro plan: up to 500 MB
            $maxSizeKB = 512000;
            // Pro plan: images or videos
            $mimeRule = 'mimetypes:image/*,video/*';
        }

        return [
            'media' => ['nullable', 'file', 'max:'.$maxSizeKB, $mimeRule],
        ];
    }

    /**
    * Custom validation error messages.
    */
    public function messages()
    {
        return [
            'media.max' => 'Your file may not be larger than :max KB.',
            'media.mimetypes' => 'Free users can only upload images. Pro users can upload images and videos.',
        ];
    }

    public function store()
    {
        $validated = $this->validate(array_merge(
            $this->rules(),
            [
                'loggedAt' => 'required|date',
                'status' => 'required',
                'aircraft' => 'required',
                'airline' => 'nullable',
                'departureAirport' => 'nullable',
                'arrivalAirport' => 'nullable',
            ]
        ));

        $aircraftLog = AircraftLog::create([
            'user_id' => auth()->id(),
            'logged_at' => $this->loggedAt,
            'status' => $this->status,
            'aircraft_id' => $this->aircraft,
            'airline_id' => $this->airline,
            'departure_airport_id' => $this->departureAirport,
            'arrival_airport_id' => $this->arrivalAirport,
        ]);

        if (!$this->validateAndProcessMedia($aircraftLog->id)) {
            $aircraftLog->delete();
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
            You have reached your storage limit. Please <a href="/profile" class="text-blue-500 underline">upgrade your subscription</a>
        </div>
    @else
        <x-mary-form wire:submit='store()'>
            <div class="grid grid-cols-1 gap-4">
                {{-- File upload for images and videos --}}
                    <x-mary-file wire:model="media" label="Choose an image or video" hint="Video uploads available on the Pro plan" spinner />
                    @error('media')
                        <span class="error">{{ $message }}</span>
                    @enderror
                <div class="flex flex-col gap-y-6">

                    <x-mary-datetime
                        label="Date"
                        wire:model="loggedAt"
                        icon="o-calendar"
                    />

                    <livewire:aircraft_log.components.status_select wire:model="status" />
                    <livewire:aircraft_log.components.airport_search wire:model="departureAirport" label="Departure airport" />
                    <livewire:aircraft_log.components.airport_search wire:model="arrivalAirport" label="Arrival airport" />
                    <livewire:aircraft_log.components.airline_search wire:model="airline" />
                    <livewire:aircraft_log.components.aircraft_search wire:model="aircraft" />

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


