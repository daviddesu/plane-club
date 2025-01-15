<?php

use App\Models\User;
use Illuminate\Auth\Events\Registered;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;

use Livewire\Volt\Component;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Validate;


new
#[Layout('layouts.guest')]
class extends Component {

    #[Validate('required', 'string', 'max:255')]
    public string $name;

    #[Validate('required', 'string', 'max:255', 'unique:users,username')]
    public string $username;

    #[Validate('required', 'string', 'lowercase', 'email', 'max:255', 'unique:'.User::class)]
    public string $email;

    #[Validate('required', 'string', 'confirmed', Rules\Password::defaults())]
    public string $password;
    public string $password_confirmation;

    #[Validate('accepted', 'string', 'confirmed')]
    public bool $marketingPreferences = false;


    public function register() {
        $validated = $this->validate();

        $validated['password'] = Hash::make($validated['password']);

        // Cast 'marketing_preferences' to boolean
        $validated['marketing_preferences'] = !empty($validated['marketing_preferences']) ? 'true' : 'false';

        $userData = $validated;
        $userData['used_disk'] = 0;

        event(new Registered($user = User::create($userData)));

        Auth::login($user);


        $this->redirectRoute('verification.notice');
    }
};

?>

<div>
    <x-mary-form wire:submit="register">
        <!-- Name -->
        <div>
            <x-mary-input
                wire:model="name"
                label="Name"
                placeholder="Your name"
                icon="o-user"
                required
                autofocus
                autocomplete="name"
            />
            <x-input-error :messages="$errors->get('name')" class="mt-2" />
        </div>

        <!-- Username -->
        <div class="mt-4">
            <x-mary-input
                wire:model="username"
                label="Username"
                placeholder="Create a unique username"
                icon="o-identification"
                required
                autofocus
            />
            <x-input-error :messages="$errors->get('username')" class="mt-2" />
        </div>

        <!-- Email Address -->
        <div class="mt-4">
            <x-mary-input
                wire:model="email"
                label="Email"
                placeholder="Your email"
                icon="o-at-symbol"
                required
                autofocus
                autocomplete="email"
            />
            <x-input-error :messages="$errors->get('email')" class="mt-2" />
        </div>

        <!-- Password -->
        <div class="mt-4">
            <x-mary-password label="Password" wire:model="password" clearable autocomplete="new-password" right />
            <x-input-error :messages="$errors->get('password')" class="mt-2" />
        </div>

        <!-- Confirm Password -->
        <div class="mt-4">
            <x-mary-password label="Confirm Password" wire:model="password_confirmation" clearable autocomplete="new-password" right />
            <x-input-error :messages="$errors->get('password_confirmation')" class="mt-2" />
        </div>

        <!-- Marketing Preferences Checkbox -->
        <div class="mt-4">
            <x-mary-checkbox wire:model="marketing_preferences" class="self-start">
                <x-slot:label>
                    <div>I have read and understand the <a class="underline" href="/privacy-policy" target="_blank">privacy</a> and <a class="underline" href="/cookie-policy" target="_blank">cookies</a> policies and agree to receive marketing communications.</div>
                </x-slot:label>
            </x-mary-checkbox>

            <x-input-error :messages="$errors->get('marketing_preferences')" class="mt-2" />
        </div>

        <div class="flex items-center justify-end mt-4">
            <x-mary-button class="underline btn-ghost" link="{{ route('login') }}" wire:navigate>
                {{ __('Already registered?') }}
            </x-mary-button>

            <x-mary-button type="submit" class="btn-primary">
                {{ __('Register') }}
            </x-mary-button>
        </div>
    </x-mary-form>
</div>
