<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;


new class extends Component
{
    use WithFileUploads;

    #[Validate('required')]
    public string $loggedAt;
    #[Validate('required')]
    public int $airport;

    public array $images = [];

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
                "image_id" => $image.id,
                "airport" => $this->airport,
                "logged_at" => $this->loggedAt,
            ]);

        }

        $this->logs = [];
        $this->images = [];

        $this->dispatch('aircraft_log-created');
    }

    public function removePreviewImage(string $temporaryUrl)
    {
        $images = [];
        foreach ($this->images as $image) {
            if($image->temporaryUrl() != $temporaryUrl){
                $images[] = $image;
                continue;
            }
            $image->delete();
        }
        $this->images = $images;
    }
}


?>

<div>
    <x-modal-card title="Add photos" name="logModal">
        <form wire:submit='store'>
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
                    wire.model='airport'
                    label="Airport"
                    placeholder="Airport"
                    {{-- :async-data="route('api.users.index')" --}}
                    option-label="name"
                    option-value="id"
                    :options="[
                        ['name' => 'EGPH - Edinburgh', 'id' => 1],
                        ['name' => 'EGPF - Glasgow', 'id' => 2],
                    ]"
                />
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
                                    <p class="text-blue-600 dark:text-teal-600">
                                        <x-icon name="cloud-arrow-up" class="w-8 h-8 text-blue-600 dark:text-teal-600" /> Add photos
                                    </p>
                                </div>
                            </div>
                        </label>
                        <input type="file" id="images" wire:model="images" multiple hidden>

                        @error('images.*')
                            <span class="error">{{ $message }}</span>
                        @enderror

                        <!-- Progress Bar -->
                        <div x-show="isUploading">
                            <progress max="100" x-bind:value="progress"></progress>
                        </div>
                    </div>
                @endif

                @if ($images)
                {{-- Log Preview --}}
                    @foreach ($images as $key => $image)
                        <div wire:key="{{ $key++ }}" class="grid grid-cols-2 gap-4">
                            <img src="{{ $image->temporaryUrl() }}">
                            <x-heroicons::outline.x-circle
                                wire:key='{{ $image->temporaryUrl() }}'
                                wire:click="removePreviewImage('{{ $image->temporaryUrl() }}')" class="text-red-500 size-6" />
                        </div>
                    @endforeach
                @endif
                <x-input-error :messages="$errors->get('message')" class="mt-2" />
        </div>
        <x-slot name="footer" class="flex justify-between gap-x-4">
            <div class="flex gap-x-4">

                </div>
        </x-slot>
        <x-slot name="footer" class="flex justify-between gap-x-4">
            <x-button flat negative label="Delete" x-on:click="close" />

            <div class="flex gap-x-4">
                <x-button flat label="Cancel" x-on:click="close" />

                <x-primary-button class="mt-4">{{ __('Add') }}</x-primary_button>
            </div>
        </x-slot>
    </form>
    </x-model-card>
</div>
