<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Validation\ValidationException;

use function Livewire\Volt\layout;
use function Livewire\Volt\rules;
use function Livewire\Volt\state;

layout('layouts.guest');

state(['password' => '']);

rules(['password' => ['required', 'string']]);

$confirmPassword = function () {
    $this->validate();

    if (! Auth::guard('web')->validate([
        'email' => Auth::user()->email,
        'password' => $this->password,
    ])) {
        throw ValidationException::withMessages([
            'password' => __('auth.password'),
        ]);
    }

    session(['auth.password_confirmed_at' => time()]);

    $this->redirectIntended(default: route('aircraft_logs', absolute: false), navigate: true);
};

?>

<div>
    <div class="mb-4 text-sm">
        {{ __('This is a secure area of the application. Please confirm your password before continuing.') }}
    </div>

    <x-mary-form wire:submit="confirmPassword">
        <!-- Password -->
        <div>

            <x-mary-password
                label="Password"
                wire:model="password"
                id="password"
                class="block w-full mt-1"
                type="password"
                name="password"
                required autocomplete="current-password"
                right
            />

            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div class="flex justify-end mt-4">
            <x-mary-button label="Confirm" class="btn-primary" />
        </div>
    </x-mary-form>
</div>
