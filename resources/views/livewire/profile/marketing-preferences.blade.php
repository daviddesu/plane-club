<?php

use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;


new class extends Component {

    public $preference;

    public function mount()
    {
        $this->preference = auth()->user()->marketing_preferences;
    }

    public function updatePreference()
    {
        Auth::user()->update(['marketing_preferences' => $this->preference]);
    }
};

?>

<section>
    <header>
        <h2 class="text-lg font-medium">Marketing Preferences</h2>
    </header>
    <div>
        <div class="mt-4">
            <x-mary-checkbox wire:model="preference" class="self-start" wire:click='updatePreference'>
                <x-slot:label>
                    Recieve marketing emails
                </x-slot:label>
            </x-mary-checkbox>
        </div>
    </div>
</section>

