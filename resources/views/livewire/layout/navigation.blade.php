<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false }" class="border-b">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('sightings') }}" wire:navigate>
                        <img alt="Light Mode Logo" class="block w-auto h-16 fill-current [[data-theme=dark]_&]:hidden" src="/logo.png" />
                        <img alt="Dark Mode Logo" class="block w-auto h-16 fill-current dark:block [[data-theme=light]_&]:hidden" src="/logo-white.png" />
                    </a>
                </div>
            </div>

            <!-- Navigation Links -->
            <div class="hidden space-x-8 sm:-my-px sm:ms-10 sm:flex">
                @if(!Auth::check())
                    <x-nav-link :href="route('register')" wire:navigate>
                        {{ __('Register') }}
                    </x-nav-link>
                    <x-nav-link :href="route('login')" wire:navigate>
                        {{ __('Log In') }}
                    </x-nav-link>
                @endif
                <x-mary-theme-toggle class="pt-6" darkTheme="dark" lightTheme="light" />
            </div>


            <!-- Settings Dropdown -->
            @if(Auth::check())
                <div class="hidden sm:flex sm:items-center sm:ms-6">
                    @if(Auth::user()->subscribedStripe())
                        <x-mary-button
                            x-on:click="$openModal('logModal')"
                            class="w-5 h-5 cursor-pointer text-cyan-800 hover:text-white hover:bg-cyan-800 dark:text-gray-200"
                            rounded
                            icon="o-plus"
                            flat
                            interaction:solid
                        />
                    @endif
                    <x-mary-dropdown align="right" width="48">
                        <x-slot name="trigger">
                            <x-mary-button
                                class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                                @if(Auth::check())
                                    <div x-data="{{ json_encode(['name' => auth()->user()->username]) }}" x-text="name"
                                        x-on:profile-updated.window="name = $event.detail.name"></div>
                                @endif
                                <div class="ms-1">
                                    <svg class="w-4 h-4 fill-current" xmlns="http://www.w3.org/2000/svg"
                                        viewBox="0 0 20 20">
                                        <path fill-rule="evenodd"
                                            d="M5.293 7.293a1 1 0 011.414 0L10 10.586l3.293-3.293a1 1 0 111.414 1.414l-4 4a1 1 0 01-1.414 0l-4-4a1 1 0 010-1.414z"
                                            clip-rule="evenodd" />
                                    </svg>
                                </div>
                            </x-mary-button>
                        </x-slot>
                        <x-mary-menu-item label="My Profile" class="text-sm" :href="route('profile')" wire:navigate />
                        <x-mary-menu-item label="Logout" wire:click="logout" class="w-full text-start" />
                    </x-mary-dropdown>
                </div>
            @endif


            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <div class="p-4">
                @if(Auth::check())
                        <x-mary-button
                            x-on:click="$openModal('logModal')"
                            class="w-5 h-5 cursor-pointer text-cyan-800 hover:text-white hover:bg-cyan-800 dark:text-gray-200"
                            rounded
                            icon="o-plus"
                            flat
                            interaction:solid
                        />
                @endif
                </div>
                <x-mary-button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </x-mary-button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="px-4">
            @if(Auth::check())
                <div class="text-base font-medium text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->username]) }}"
                    x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
            @endif
        </div>
        <div class="pt-2 pb-3 space-y-1"></div>


        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">



            @if(Auth::check())
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <x-mary-button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </x-mary-button>
                </div>
            @else
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('login')" wire:navigate>
                        {{ __('Log in') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('register')" wire:navigate>
                        {{ __('Register') }}
                    </x-responsive-nav-link>
                </div>
            @endif
            <x-mary-theme-toggle class="pt-2 pl-4" darkTheme="dark" lightTheme="light" />
        </div>
    </div>
</nav>
