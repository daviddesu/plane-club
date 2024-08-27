<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;


new class extends Component
{
    use WithFileUploads;

    #[Validate('required')]
    public ?string $loggedAt;

    #[Validate('required')]
    public ?string $airport;

    #[Validate]
    public ?string $airline;

    #[Validate]
    public ?string $aircraft;

    #[Validate]
    public string $description = "";

    #[Validate]
    public string $registration = "";

    public array $images = [];

    public bool $moreDetails = false;

    public function store()
    {
        $validated = $this->validate();

        foreach ($this->images as $image) {
            $storedFilePath = $image->storePublicly('public/aircraft');
            $image = auth()->user()->images()->create(
                [
                    "path" => str_replace("public/", "", $storedFilePath),
                ]
            );
            $newAircraftLog = auth()->user()->aircraftLogs()->create([
                "image_id" => $image->id,
                "airport_id" => $this->airport,
                "logged_at" => $this->loggedAt,
                "description" => $this->description,
                "airline_id" => $this->airline,
                "registration" => strtoupper($this->registration),
                "aircraft_id" => $this->aircraft,
            ]);

        }

        $this->reset();

        $this->dispatch('aircraft_log-created');
    }

    public function removeUploadedImages()
    {
        $this->images = [];
    }

    public function close()
    {
        $this->resetProperties();
    }

    private function resetProperties()
    {
        $this->loggedAt = null;
        $this->airport = null;
        $this->images = [];
        $this->description = "";
    }

    public function toggleMoreDetails()
    {
        if($this->moreDetails){
            $this->moreDetails = true;
        }else{
            $this->moreDetails = false;
        }
    }
}


?>
<x-modal-card title="Add photos" name="logModal">
    <form wire:submit='store()'>
        <div>
        <div class="grid grid-cols-1 gap-4">
            <div class="grid-cols-1 gap-4 sm:grid-cols-2">
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
                    :async-data="route('airports')"
                    option-label="name"
                    option-value="id"
                    wire:model='airport'
                />

                @if(count($images) != 1)
                    <x-toggle id="label" label="More details..." name="toggle" wire:click='toggleMoreDetails' wire:model='moreDetails' />
                @endif

                @if(count($images) == 1 || $moreDetails)
                <x-select
                    class="pd-2"
                    label="Airline"
                    placeholder="Please select"
                    :async-data="route('airlines')"
                    option-label="name"
                    option-value="id"
                    wire:model='airline'

                />

                <x-select
                    class="pd-2"
                    label="Aircraft"
                    placeholder="Please select"
                    :async-data="route('aircraft')"
                    option-label="name"
                    option-value="id"
                    wire:model='aircraft'

                />

                <x-input
                    label="Registration"
                    placeholder="G-PNCB"
                    wire:model='registration'
                    style="text-transform: uppercase"
                />
                @endif
            </div>

            @if(!$images)
                {{-- File upload --}}
                <div
                    x-data="{ isUploading: false, progress: 0 }"
                    x-on:livewire-upload-start="isUploading = true"
                    x-on:livewire-upload-finish="isUploading = false"
                    x-on:livewire-upload-error="isUploading = false"
                    x-on:livewire-upload-progress="progress = $event.detail.progress"
                >
                    <label for="images">
                        <div
                            class="flex items-center justify-center h-20 col-span-1 bg-gray-100 shadow-md cursor-pointer sm:col-span-2 dark:bg-secondary-700 rounded-xl">
                            <div class="flex flex-col items-center justify-center">

                                <x-icon name="cloud-arrow-up" class="w-8 h-8 text-blue-600 dark:text-teal-600" />
                                <p class="text-blue-600 dark:text-teal-600">
                                    Click to add photos
                                </p>
                            </div>
                        </div>
                    </label>
                    <input type="file" id="images" wire:model="images" multiple hidden />

                    @error('images.*')
                        <span class="error">{{ $message }}</span>
                    @enderror

                    <!-- Progress Bar -->
                    <div
                        x-show="isUploading"
                        class="flex items-center justify-center h-20 col-span-1 shadow-md">
                        <svg xmlns="http://www.w3.org/2000/svg" width="1em" height="1em" viewBox="0 0 24 24"><path fill="currentColor" d="M20.27,4.74a4.93,4.93,0,0,1,1.52,4.61,5.32,5.32,0,0,1-4.1,4.51,5.12,5.12,0,0,1-5.2-1.5,5.53,5.53,0,0,0,6.13-1.48A5.66,5.66,0,0,0,20.27,4.74ZM12.32,11.53a5.49,5.49,0,0,0-1.47-6.2A5.57,5.57,0,0,0,4.71,3.72,5.17,5.17,0,0,1,9.53,2.2,5.52,5.52,0,0,1,13.9,6.45,5.28,5.28,0,0,1,12.32,11.53ZM19.2,20.29a4.92,4.92,0,0,1-4.72,1.49,5.32,5.32,0,0,1-4.34-4.05A5.2,5.2,0,0,1,11.6,12.5a5.6,5.6,0,0,0,1.51,6.13A5.63,5.63,0,0,0,19.2,20.29ZM3.79,19.38A5.18,5.18,0,0,1,2.32,14a5.3,5.3,0,0,1,4.59-4,5,5,0,0,1,4.58,1.61,5.55,5.55,0,0,0-6.32,1.69A5.46,5.46,0,0,0,3.79,19.38ZM12.23,12a5.11,5.11,0,0,0,3.66-5,5.75,5.75,0,0,0-3.18-6,5,5,0,0,1,4.42,2.3,5.21,5.21,0,0,1,.24,5.92A5.4,5.4,0,0,1,12.23,12ZM11.76,12a5.18,5.18,0,0,0-3.68,5.09,5.58,5.58,0,0,0,3.19,5.79c-1,.35-2.9-.46-4-1.68A5.51,5.51,0,0,1,11.76,12ZM23,12.63a5.07,5.07,0,0,1-2.35,4.52,5.23,5.23,0,0,1-5.91.2,5.24,5.24,0,0,1-2.67-4.77,5.51,5.51,0,0,0,5.45,3.33A5.52,5.52,0,0,0,23,12.63ZM1,11.23a5,5,0,0,1,2.49-4.5,5.23,5.23,0,0,1,5.81-.06,5.3,5.3,0,0,1,2.61,4.74A5.56,5.56,0,0,0,6.56,8.06,5.71,5.71,0,0,0,1,11.23Z"><animateTransform attributeName="transform" dur="1.5s" repeatCount="indefinite" type="rotate" values="0 12 12;360 12 12"/></path></svg>
                    </div>
                </div>
            @endif

            @if ($images)
                {{-- Log Preview --}}
                <div
                class="max-w-6xl mx-auto duration-1000 delay-300 opacity-0 select-none ease animate-fade-in-view"
                style="translate: none; rotate: none; scale: none; opacity: 1; transform: translate(0px, 0px);">
                    <ul x-ref="gallery" id="gallery" class="grid grid-cols-1 gap-5 sm:grid-cols-2 lg:grid-cols-5">
                        @foreach ($images as $key => $image)
                                <li><img
                                    wire:key="{{ $key++ }}"
                                    src="{{ $image->temporaryUrl() }}"
                                    class="object-cover select-none w-full h-auto bg-gray-200 rounded aspect-[6/5] lg:aspect-[3/2] xl:aspect-[4/3]" />
                                </li>
                        @endforeach
                    </ul>
                </div>
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
            @endif

        </div>
        <div class="pt-2 border-b-2"></div>
        <div name="footer" class="flex justify-between gap-x-4">
            <x-button class="mt-4" flat negative label="Clear images" wire:click='removeUploadedImages' />
            <div class="flex gap-x-4">
                <x-button flat class="justify-center mt-4" label="Cancel" x-on:click="close" wire:click='close' />
                <x-primary-button  flat class="justify-center mt-4">{{ __('Save') }}</x-primary-button>
            </div>
        </div>
    </div>
    </form>
</x-model-card>
