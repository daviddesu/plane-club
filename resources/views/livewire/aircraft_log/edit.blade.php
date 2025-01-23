<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Models\AircraftLog;
use App\Models\Media;
use App\Services\MediaService;
use Masmerise\Toaster\Toaster;
use App\Traits\WithMedia;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;

new class extends Component {
    use WithFileUploads;
    use WithMedia;
    use Toast;

    public int $id;
    public bool $storageLimitExceeded = false;

    #[Validate('required')]
    public ?string $loggedAt = null;

    #[Validate('required')]
    public ?string $status = null;

    #[Validate('required_if:status,1,2')]
    public ?string $departureAirport = null;

    #[Validate('required_if:status,2,3')]
    public ?string $arrivalAirport = null;

    #[Validate]
    public ?string $airline = null;

    #[Validate('required')]
    public ?string $aircraft = null;

    #[Validate]
    public string $description = '';

    #[Validate]
    public string $registration = '';

    #[Validate]
    public string $flightNumber = '';

    public string $fileName = '';

    protected function getCachedMediaUrl($mediaPath): ?string
    {
        if (!$mediaPath) {
            return null;
        }

        $driverName = getenv('FILESYSTEM_DISK');

        if ($driverName === 'b2') {
            // Construct the URL directly
            return rtrim(env('CDN_HOST'), '/') . '/' . ltrim($mediaPath, '/');
        } else {
            // For local, generate a URL using asset() or url()
            return asset('storage/' . $mediaPath);
        }
    }

    protected function getExistingMedia(): ?Media
    {
        return AircraftLog::find($this->id)?->media;
    }

    public function generateFileName()
    {
        $name = "Plane-club-";
        $name .= (new \DateTime($this->loggedAt))->format("Y-m-d");

        $aircraftLog = AircraftLog::with(['departureAirport', 'arrivalAirport', 'aircraft'])
            ->find($this->id);

        if($aircraftLog->departureAirport){
            $name .= "-{$aircraftLog->departureAirport->code}";
        }

        if($aircraftLog->arrivalAirport){
            $name .= "-{$aircraftLog->arrivalAirport->code}";
        }

        if($aircraftLog->aircraft){
            $name .= "-{$aircraftLog->aircraft->getFormattedName()}";
        }

        // Add file extension based on media type
        $media = $this->getExistingMedia();
        if ($media) {
            $extension = $media->isVideo() ? '.mp4' : '.jpg';
            $name .= $extension;
        }

        return $name;
    }

    public function mount($id)
    {
        $aircraftLog = AircraftLog::with(['media', 'aircraft', 'airline', 'departureAirport', 'arrivalAirport'])
            ->findOrFail($id);

        $this->authorize('update', $aircraftLog);

        $this->aircraft = $aircraftLog->aircraft?->id;
        $this->airline = $aircraftLog->airline?->id;
        $this->departureAirport = $aircraftLog->departureAirport?->id;
        $this->arrivalAirport = $aircraftLog->arrivalAirport?->id;
        $this->status = $aircraftLog->status;
        $this->loggedAt = $aircraftLog->logged_at?->format('Y-m-d H:i');
        $this->description = $aircraftLog->description ?? '';
        $this->registration = $aircraftLog->registration ?? '';
        $this->flightNumber = $aircraftLog->flight_number ?? '';

        $this->checkStorageLimits();
        $this->fileName = $this->generateFileName();
    }

    public function removeMedia()
    {
        $existingMedia = $this->getExistingMedia();
        if ($existingMedia) {
            $user = auth()->user();
            $user->used_disk = max(0, $user->used_disk - $existingMedia->size);
            $user->save();

            $existingMedia->delete();
            $this->success('Media removed successfully');
        }
    }

    public function update()
    {
        $this->authorize('update', AircraftLog::find($this->id));

        $validated = $this->validate();

        $aircraftLog = AircraftLog::find($this->id);

        $aircraftLog->update([
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

        if ($this->media) {
            $existingMedia = $this->getExistingMedia();
            if ($existingMedia) {
                $this->removeMedia();
            }

            if (!$this->validateAndProcessMedia($this->id)) {
                return;
            }
        }

        $this->success('Sighting updated successfully');
    }

    public function downloadMedia()
    {
        $media = $this->getExistingMedia();
        if ($media) {
            return response()->streamDownload(
                function() use ($media) {
                    $url = $this->getCachedMediaUrl($media->path);
                    echo file_get_contents($url);
                },
                $this->fileName,
                [
                    'Content-Type' => $media->isVideo() ? 'video/mp4' : 'image/jpeg',
                    'Content-Disposition' => 'attachment'
                ]
            );
        }
    }
}

?>

<div>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            {{ __('Edit Sighting') }}
        </h2>
    </x-slot>

    @if($storageLimitExceeded)
        <div class="text-center">
            You have reached your storage limit. Please <a href="/profile" class="text-blue-500 underline">upgrade your subscription</a>
        </div>
    @else
        <x-mary-form wire:submit='update()'>
            <div class="grid grid-cols-1 gap-4">
                {{-- Current Media Display --}}
                @if($this->getExistingMedia())
                    <div class="relative">
                        @if($this->getExistingMedia()->isVideo())
                            <video controls class="object-contain w-full max-h-80">
                                <source src="{{ $this->getCachedMediaUrl($this->getExistingMedia()->path) }}" type="video/mp4">
                                Your browser does not support the video tag.
                            </video>
                        @else
                            <img
                                src="{{ $this->getCachedMediaUrl($this->getExistingMedia()->path) }}"
                                class="object-contain w-full max-h-80"
                                alt="Current media"
                            >
                        @endif
                        <div class="absolute flex gap-2 top-2 right-2">
                            <x-mary-button
                                wire:click="downloadMedia"
                                icon="o-arrow-down-tray"
                                size="sm"
                            />
                            <x-mary-button
                                wire:click="removeMedia"
                                class="btn-error"
                                icon="o-trash"
                                size="sm"
                            />
                        </div>
                    </div>
                @endif

                {{-- File upload for new media --}}
                <x-mary-file
                    wire:model="media"
                    label="Choose new image or video"
                    hint="Video uploads available on the Pro plan"
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
                    <livewire:aircraft_log.components.status_select wire:model="status" />
                    <livewire:aircraft_log.components.airport_search wire:model="departureAirport" label="Departure Airport" />
                    <livewire:aircraft_log.components.airport_search wire:model="arrivalAirport" label="Arrival Airport" />
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

                <div class="flex justify-end gap-x-4">
                    <x-mary-button label="Cancel" link="/sightings" wire:navigate flat />
                    <x-mary-button label="Update" class="btn-primary" type="submit" spinner="update" />
                </div>
            </div>
        </x-mary-form>
    @endif
</div>

<script>
    document.addEventListener('livewire:initialized', () => {
        Livewire.on('download-file', (data) => {
            const link = document.createElement('a');
            link.href = data[0].url;
            link.download = data[0].filename;
            document.body.appendChild(link);
            link.click();
            document.body.removeChild(link);
        });
    });
</script>
