<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state([
    'name' => '',
    'username' => '',
    'email' => '',
    'password' => '',
    'password_confirmation' => '',
    'marketing_preferences' => false,
    'selectedPlan' => '',
    'availablePlans' => [
        [
            'name' => 'Hobby',
            'price' => '£15/month',
            'storage' => '200 GB',
            'stripe_price_id' => 'tier1',
        ],
        [
            'name' => 'Aviator',
            'price' => '£25/month',
            'storage' => '500 GB',
            'stripe_price_id' => 'tier2',
        ],
        [
            'name' => 'Pro',
            'price' => '£75/month',
            'storage' => '2 TB',
            'stripe_price_id' => 'tier3',
        ],
    ],
]);

rules([
    'name' => ['required', 'string', 'max:255'],
    'username' => ['required', 'string', 'max:255', 'unique:users,username'],
    'email' => ['required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class],
    'password' => ['required', 'string', 'confirmed', Rules\Password::defaults()],
    'marketing_preferences' => ['accepted'],
    'selectedPlan' => ['required', 'string'],
]);

$register = function () {
    $validated = $this->validate();

    $validated['password'] = Hash::make($validated['password']);

    // Cast 'marketing_preferences' to boolean
    $validated['marketing_preferences'] = !empty($validated['marketing_preferences']) ? 'true' : 'false';

    // Remove 'selectedPlan' from $validated if it's not a column in your users table
    $userData = $validated;
    $userData['used_disk'] = 0;
    unset($userData['selectedPlan']);

    $user = User::create($userData);

    Auth::login($user);


    $this->redirectRoute('checkout', ['plan' => $this->selectedPlan]);
};

?>

<div>
    <form wire:submit="register">
        <!-- Name -->
        <div>
            <x-input-label for="name" :value="__('Name')" />
            <x-text-input wire:model="name" id="name" class="block w-full mt-1" type="text" name="name" required autofocus autocomplete="name" />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-input-label for="username" :value="__('Username')" />
            <x-text-input wire:model="username" id="username" class="block w-full mt-1" type="text" name="username" required autocomplete="username" />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-input-label for="email" :value="__('Email')" />
            <x-text-input wire:model="email" id="email" class="block w-full mt-1" type="email" name="email" required autocomplete="username" />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-input-label for="password" :value="__('Password')" />

            <x-text-input wire:model="password" id="password" class="block w-full mt-1"
                            type="password"
                            name="password"
                            required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-input-label for="password_confirmation" :value="__('Confirm Password')" />

            <x-text-input wire:model="password_confirmation" id="password_confirmation" class="block w-full mt-1"
                            type="password"
                            name="password_confirmation" required autocomplete="new-password" />

            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

         <!-- Plan Selection -->
         <div class="mt-4">
            <x-input-label for="selectedPlan" :value="__('Select Plan')" />
            <select wire:model="selectedPlan" id="selectedPlan" class="block w-full mt-1" required>
                <option value="" disabled selected>Select a plan</option>
                @foreach ($availablePlans as $plan)
                    <option value="{{ $plan['stripe_price_id'] }}">
                        {{ $plan['name'] }} - {{ $plan['price'] }} - {{ $plan['storage'] }}
                    </option>
                @endforeach
            </select>
            <x-input-error :messages="$errors->get('selectedPlan')" class="mt-2" />
            <p class="mt-2 text-sm text-gray-600">You are starting a 7-day free trial.</p>
        </div>

        <!-- Marketing Preferences Checkbox -->
        <div class="mt-4">
            <label for="marketing_preferences" class="inline-flex items-center">
                <input id="marketing_preferences" type="checkbox" wire:model="marketing_preferences" class="text-indigo-600 border-gray-300 rounded shadow-sm focus:ring-indigo-500" required>
                <span class="ml-2 text-sm text-gray-600">I have read and understand the <a class="underline" href="/privacy-policy" target="_blank">privacy</a> and <a class="underline" href="/cookie-policy" target="_blank">cookies</a> policies and agree to receive marketing communications</span>
            </label>
            <x-input-error :messages="$errors->get('marketing_preferences')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <a class="text-sm text-gray-600 underline rounded-md dark:text-gray-400 hover:text-gray-900 dark:hover:text-gray-100 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-indigo-500 dark:focus:ring-offset-gray-800" href="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </a>

            <x-primary-button class="ms-4">
                {{ __('Register') }}
            </x-primary-button>
        </div>
    </form>
</div>
