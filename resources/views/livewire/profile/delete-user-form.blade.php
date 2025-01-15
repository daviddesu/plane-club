<?php

use App\Livewire\Actions\Logout;
use Illuminate\Support\Facades\Auth;
use Livewire\Volt\Component;
use Livewire\Attributes\Validate;

new class extends Component {

    #[Validate('required|string|current_password')]
    public string $password;

    public bool $modalOpen = false;

    public function deleteUser()
    {
        $this->validate();
        Auth::user()->subscription(env('STRIPE_PRODUCT_ID'))->cancelNow();

        tap(Auth::user(), $logout(...))->delete();

        $this->redirect('/', navigate: true);
    }
}

?>

<section class="space-y-6">
    <header>
        <h2 class="text-lg font-medium">
            {{ __('Delete Account') }}
        </h2>

        <p class="mt-1 text-sm">
            {{ __('Once your account is deleted, all of its resources and data will be permanently deleted. Before deleting your account, please download any data or information that you wish to retain.') }}
        </p>
    </header>

    <x-mary-button
        label="Delete Account"
        class="btn-error"
        @click="$wire.modalOpen = true"
    />

    <x-mary-modal wire:model='modalOpen' title="Are you sure you want to delete your account?">
        <x-mary-form wire:submit="deleteUser" class="p-6">

            <div>
                Once your account is deleted, all of its resources and data will be permanently deleted. Please enter your password to confirm you would like to permanently delete your account.
            </div>

            <div class="mt-6">
                <x-mary-password label="Password" wire:model="password" clearable right />
                <x-input-error :messages="$errors->get('password')" class="mt-2" />
            </div>

            <x-slot:actions>
                <x-mary-button label="Cancel" @click="$wire.modalOpen = false" />
                <x-mary-button label="Delete" class="btn-error" type="submit" />
            </x-slot:actions>
        </x-mary-form>
    </x-mary-modal>
</section>
