<?php

use Livewire\Volt\Component;
use Livewire\WithFileUploads;
use Livewire\Attributes\Validate;
use App\Models\Sighting;
use App\Models\Media;
use App\Services\MediaService;
use Masmerise\Toaster\Toaster;
use App\Traits\WithMedia;
use Mary\Traits\Toast;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

new class extends Component {
    use WithFileUploads;
    use WithMedia;
    use Toast;

    public int $id;

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

    public bool $showFullscreen = false;
    public ?string $mediaPath = null;
    public ?string $thumbnailPath = null;

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
        return Sighting::find($this->id)?->media;
    }

    public function generateFileName()
    {
        $name = "Plane-club-";
        $name .= (new \DateTime($this->loggedAt))->format("Y-m-d");

        $sighting = Sighting::with(['departureAirport', 'arrivalAirport', 'aircraft'])
            ->find($this->id);

        if($sighting->departureAirport){
            $name .= "-{$sighting->departureAirport->code}";
        }

        if($sighting->arrivalAirport){
            $name .= "-{$sighting->arrivalAirport->code}";
        }

        if($sighting->aircraft){
            $name .= "-{$sighting->aircraft->getFormattedName()}";
        }

        // Add file extension based on media type
        $media = $this->getExistingMedia();
        if ($media) {
            $name .= '.jpg';
        }

        return $name;
    }

    public function mount($id)
    {
        $sighting = Sighting::with(['media', 'aircraft', 'airline', 'departureAirport', 'arrivalAirport'])
            ->findOrFail($id);

        $this->authorize('update', $sighting);

        $this->aircraft = $sighting->aircraft?->id;
        $this->airline = $sighting->airline?->id;
        $this->departureAirport = $sighting->departureAirport?->id;
        $this->arrivalAirport = $sighting->arrivalAirport?->id;
        $this->status = $sighting->status;
        $this->loggedAt = $sighting->logged_at?->format('Y-m-d');
        $this->description = $sighting->description ?? '';
        $this->registration = $sighting->registration ?? '';
        $this->flightNumber = $sighting->flight_number ?? '';

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
        $this->authorize('update', Sighting::find($this->id));

        $validated = $this->validate();

        $sighting = Sighting::find($this->id);

        $sighting->update([
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
                    'Content-Type' => 'image/jpeg',
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
                        <img
                            src="{{ $this->getCachedMediaUrl($this->getExistingMedia()->path) }}"
                            class="object-contain w-full max-h-80"
                            alt="Current media"
                        >
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
                    label="Choose new image"
                    spinner
                />
                @error('media')
                    <span class="error">{{ $message }}</span>
                @enderror

                <div class="flex flex-col gap-y-6">
                    <x-mary-datetime
                        label="Date"
                        wire:model="loggedAt"
                        icon="o-calendar-days"
                    />
                    <livewire:sightings.components.status_select wire:model="status" />
                    <livewire:sightings.components.airport_search wire:model="departureAirport" label="Departure Airport" />
                    <livewire:sightings.components.airport_search wire:model="arrivalAirport" label="Arrival Airport" />
                    <livewire:sightings.components.airline_search wire:model="airline" />
                    <livewire:sightings.components.aircraft_search wire:model="aircraft" />

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

                {{-- Image Preview --}}
                @if($mediaPath)
                    <div class="relative">
                        {{-- Fullscreen Modal --}}
                        <x-mary-modal
                            wire:model="showFullscreen"
                            class="w-screen h-screen p-0 bg-black"
                            blur
                            separator
                        >
                            <div class="relative flex items-center justify-center w-full h-full">
                                <img
                                    class="max-h-screen"
                                    src="{{ Storage::url($mediaPath) }}"
                                    alt="Full size image"
                                />
                                <button
                                    wire:click="$toggle('showFullscreen')"
                                    class="absolute p-2 text-white transition-colors duration-200 rounded-full top-4 right-4 hover:bg-white/20"
                                >
                                    <x-mary-icon name="o-x-mark" class="w-6 h-6" />
                                </button>
                            </div>
                        </x-mary-modal>

                        {{-- Thumbnail --}}
                        <div class="overflow-hidden rounded-lg aspect-video">
                            <img
                                class="object-cover w-full h-full cursor-pointer"
                                wire:click="$toggle('showFullscreen')"
                                src="{{ Storage::url($thumbnailPath) }}"
                                alt="Thumbnail"
                            />
                        </div>
                    </div>
                @endif

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
