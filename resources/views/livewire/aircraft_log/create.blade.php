<?php

use Livewire\Volt\Component;
use Livewire\Attributes\Validate;
use Livewire\WithFileUploads;


new class extends Component
{
    use WithFileUploads;

    #[Validate([
        'logs.*.image' => 'required|image|max:1024',
        'logs.*.aircraft_type' => 'string|max:10',
        'logs.*.airport' => 'string|max:10',
        'logs.*.airline' => 'string|max:10',
    ])]
    public array $logs = [];

    public array $images = [];

    public function store()
    {
        // Images are just temp, we want the log image. So these can be reset first
        try{
            $validated = $this->validate();

        }catch(\Exception $e){
            dd($e);
        }
        dd($_POST);
        dd($validated);

        // var_dump($this->logs);die;

        foreach ($this->logs as $log) {
            $newAircraftLog = auth()->user()->aircraftLogs()->create($log);
            $storedFilePath = $log['image']->storePublicly('public/aircraft');
            auth()->user()->images()->create(
                [
                    "aircraft_log_id" => $newAircraftLog->id,
                    "path" => str_replace("public/", "", $storedFilePath),
                ]
        );
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
    <x-modal-card title="Create log" name="logModal">
        <div class="grid grid-cols-1 gap-4 sm:grid-cols-2">


                @if(!$images)
                    {{-- File upload --}}
                    <div
                        x-data="{ isUploading: false, progress: 0 }"
                        x-on:livewire-upload-start="isUploading = true"
                        x-on:livewire-upload-finish="isUploading = false"
                        x-on:livewire-upload-error="isUploading = false"
                        x-on:livewire-upload-progress="progress = $event.detail.progress"
                    >
                        <input type="file" wire:model="images" multiple>

                        @error('images.*')
                            <span class="error">{{ $message }}</span>
                        @enderror

                        <!-- Progress Bar -->
                        <div x-show="isUploading">
                            <progress max="100" x-bind:value="progress"></progress>
                        </div>
                    </div>
                @endif
                <form wire:submit='store'>

                @if ($images)
                {{-- Log Preview --}}
                    @foreach ($images as $key => $image)
                        <div wire:key="{{ $key++ }}" class="grid grid-cols-2 gap-4">
                            <div>
                                <img src="{{ $image->temporaryUrl() }}">
                                <x-heroicons::outline.x-circle
                                    wire:key='{{ $image->temporaryUrl() }}'
                                    wire:click="removePreviewImage('{{ $image->temporaryUrl() }}')" class="text-red-500 size-6" />
                            </div>
                            <div>
                                <input type="hidden" wire:model.lazy='logs.*.image' name="image" value="{{ $image->temporaryUrl() }}" />
                                <select wire:model.lazy='logs.*.airport'>
                                    <option value="egph">EGPH - Edinburgh</option>
                                    <option value="egpf">EGPF - Glasgow</option>
                                </select>
                                <input wire:model='logs.*.registration' name="registration" type="text" />
                                <select wire:model='logs.*.aircraft_type'>
                                    <option value="b738">Boeing 737-800</option>
                                    <option value="a321">Airbus a321</option>
                                </select>
                                <select wire:model='logs.*.airline'>
                                    <option value="BA">British Airways</option>
                                    <option value="EZ">Easyjet</option>
                                </select>
                            </div>
                        </div>
                    @endforeach
                @endif


                <x-input-error :messages="$errors->get('message')" class="mt-2" />

                <x-primary-button class="mt-4">{{ __('Add') }}</x-primary_button>
            </form>
        </div>
    </x-model-card>
</div>
