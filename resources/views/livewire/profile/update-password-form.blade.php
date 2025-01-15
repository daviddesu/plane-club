<?php

use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use Illuminate\Validation\Rules\Password;
use Illuminate\Validation\ValidationException;
use Livewire\Attributes\Validate;
use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {

    #[Validate('required|string|current_password')]
    public string $currentPassword;

    #[Validate('required|string|Password::defaults()|confirmed')]
    public string $password;

    public string $passwordConfirmation;

    public function updatePassword() {
        try {
            $validated = $this->validate();
        } catch (ValidationException $e) {
            $this->reset('currentPassword', 'password', 'passwordConfirmation');

            throw $e;
        }

        Auth::user()->update([
            'password' => Hash::make($validated['password']),
        ]);

        $this->reset('currentPassword', 'password', 'passwordConfirmation');

        $this->dispatch('password-updated');
    }
}

?>

<section>
    <header>
        <h2 class="text-lg font-medium">
            {{ __('Update Password') }}
        </h2>

        <p class="mt-1 text-sm">
            {{ __('Ensure your account is using a long, random password to stay secure.') }}
        </p>
    </header>

    <x-mary-form wire:submit="updatePassword" class="mt-6 space-y-6">
        <div>
            <x-mary-password
                label="Current Password"
                wire:model="currentPassword"
                autocomplete="current-password"
                right
            />
            <x-input-error :messages="$errors->get('current_password')" class="mt-2" />
        </div>

        <div>
            <x-mary-password
                label="New Password"
                wire:model="password"
                autocomplete="new-password"
                right
            />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <div>
            <x-mary-password
                label="Confirm Password"
                wire:model="passwordConfirmation"
                autocomplete="new-password"
                right
            />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <div class="flex items-center gap-4">
            <x-mary-button class="btn-primary" label="Save" spinner type="submit" />
        </div>
    </x-mary-form>
</section>
