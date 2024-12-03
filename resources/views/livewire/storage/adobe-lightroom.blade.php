<?php

use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;


new class extends Component
 {
    public bool $active = false;

    public function mount()
    {
        $user = Auth::user();
        if($user->hasAdobeAccessToken())
        {
            $this->active = true;
        }
    }

    public function redirectToAdobeOauth()
    {
        $this->redirect('auth.adobe');
    }
 }
 ?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Adobe Lightroom</h2>
    </header>
    @if($active)
        <p>Adobe Lightroom connection is <x-badge positive label="active" />.</p>
    @elseif
        <div>
            <x-primary-button wire:click='redirectToAdobeOauth'>{{ __('Activate Adobe Lightroom') }}</x-primary-button>
        </div>
    @endif
</section>
