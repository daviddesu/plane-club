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
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Marketing Preferences</h2>
    </header>
    <div>
        <input id="marketing_preferences" type="checkbox" wire:model="preference" wire:click='updatePreference' class="border-gray-300 rounded shadow-sm text-cyan-800 focus:ring-cyan-500" required>
        <span class="ml-2 text-sm text-gray-600">Recieve marketing emails</span>

        {{-- <x-toggle  wire:model='preference' color="bg-cyan-800" name="toggle" label="Recieve marketing emails" xl /> --}}
    </div>
</section>

