<?php

use App\Livewire\Actions\Logout;

$logout = function (Logout $logout) {
    $logout();

    $this->redirect('/', navigate: true);
};

?>

<nav x-data="{ open: false }" class="bg-white border-b border-gray-100 dark:bg-gray-800 dark:border-gray-700">
    <!-- Primary Navigation Menu -->
    <div class="px-4 mx-auto max-w-7xl sm:px-6 lg:px-8">
        <div class="flex justify-between h-16">
            <div class="flex">
                <!-- Logo -->
                <div class="flex items-center shrink-0">
                    <a href="{{ route('aircraft_logs') }}" wire:navigate>
                        <img src="/logo.png" class="block w-auto h-16 text-gray-800 fill-current dark:text-gray-200" />
                    </a>
                </div>
            </div>

            <!-- Settings Dropdown -->
            <div class="hidden sm:flex sm:items-center sm:ms-6">
                @if(Auth::check())
                    <x-icon name="arrow-up-tray" class="w-5 h-5 text-gray-800 cursor-pointer dark:text-gray-200" x-on:click="$openModal('logModal')" />
                @endif
                <x-dropdown align="right" width="48">
                    <x-slot name="trigger">
                        <button
                            class="inline-flex items-center px-3 py-2 text-sm font-medium leading-4 text-gray-500 transition duration-150 ease-in-out bg-white border border-transparent rounded-md dark:text-gray-400 dark:bg-gray-800 hover:text-gray-700 dark:hover:text-gray-300 focus:outline-none">
                            @if(Auth::check())
                                <div x-data="{{ json_encode(['name' => auth()->user()->name]) }}" x-text="name"
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
                        </button>
                    </x-slot>

                    @if(Auth::check())
                        <x-dropdown.item label="Images & Videos" class="text-sm" :href="route('aircraft_logs')" :active="request()->routeIs('aircraft_logs')" wire:navigate />
                        <x-dropdown.item label="Aircraft" class="text-sm" :href="route('aircraft_logs')" :active="request()->routeIs('aircraft_logs')" wire:navigate />
                        <x-dropdown.item label="Airlines" class="text-sm" :href="route('aircraft_logs')" :active="request()->routeIs('aircraft_logs')" wire:navigate />
                        <x-dropdown.item label="Airports" class="text-sm" :href="route('aircraft_logs')" :active="request()->routeIs('aircraft_logs')" wire:navigate />
                        <x-dropdown.item separator label="My Profile" class="text-sm" :href="route('profile')" wire:navigate />
                        <x-dropdown.item label="Logout" wire:click="logout" class="w-full text-start" />
                    @else
                        <x-dropdown.item label="Log in" class="text-sm" :href="route('login')" wire:navigate />
                        <x-dropdown.item label="Register" class="text-sm" :href="route('register')" wire:navigate />
                    @endif


                </x-dropdown>
            </div>

            {{-- <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                {{ __('Images & Videos') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                {{ __('Aircraft') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                {{ __('Airlines') }}
            </x-responsive-nav-link>
            <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                {{ __('Airports') }}
            </x-responsive-nav-link> --}}
        </div>

            <!-- Hamburger -->
            <div class="flex items-center -me-2 sm:hidden">
                <div class="p-4">
                @if(Auth::check())
                    <x-icon name="arrow-up-tray" class="w-5 h-5 text-gray-800 cursor-pointer dark:text-gray-200" x-on:click="$openModal('logModal')" />
                @endif
                </div>
                <button @click="open = ! open"
                    class="inline-flex items-center justify-center p-2 text-gray-400 transition duration-150 ease-in-out rounded-md dark:text-gray-500 hover:text-gray-500 dark:hover:text-gray-400 hover:bg-gray-100 dark:hover:bg-gray-900 focus:outline-none focus:bg-gray-100 dark:focus:bg-gray-900 focus:text-gray-500 dark:focus:text-gray-400">
                    <svg class="w-6 h-6" stroke="currentColor" fill="none" viewBox="0 0 24 24">
                        <path :class="{ 'hidden': open, 'inline-flex': !open }" class="inline-flex"
                            stroke-linecap="round" stroke-linejoin="round" stroke-width="2"
                            d="M4 6h16M4 12h16M4 18h16" />
                        <path :class="{ 'hidden': !open, 'inline-flex': open }" class="hidden" stroke-linecap="round"
                            stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12" />
                    </svg>
                </button>
            </div>
        </div>
    </div>

    <!-- Responsive Navigation Menu -->
    <div :class="{ 'block': open, 'hidden': !open }" class="hidden sm:hidden">
        <div class="px-4">
            @if(Auth::check())
                <div class="text-base font-medium text-gray-800 dark:text-gray-200" x-data="{{ json_encode(['name' => auth()->user()->name]) }}"
                    x-text="name" x-on:profile-updated.window="name = $event.detail.name"></div>
                <div class="text-sm font-medium text-gray-500">{{ auth()->user()->email }}</div>
            @endif
        </div>
        <div class="pt-2 pb-3 space-y-1">


        <!-- Responsive Settings Options -->
        <div class="pt-4 pb-1 border-t border-gray-200 dark:border-gray-600">



            @if(Auth::check())
                <div class="mt-3 space-y-1">
                    <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                        {{ __('Images & Videos') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                        {{ __('Aircraft') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                        {{ __('Airlines') }}
                    </x-responsive-nav-link>
                    <x-responsive-nav-link :href="route('aircraft_logs')" wire:navigate>
                        {{ __('Airports') }}
                    </x-responsive-nav-link>

                    <x-responsive-nav-link :href="route('profile')" wire:navigate>
                        {{ __('Profile') }}
                    </x-responsive-nav-link>

                    <!-- Authentication -->
                    <button wire:click="logout" class="w-full text-start">
                        <x-responsive-nav-link>
                            {{ __('Log Out') }}
                        </x-responsive-nav-link>
                    </button>
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
        </div>
    </div>
</nav>
