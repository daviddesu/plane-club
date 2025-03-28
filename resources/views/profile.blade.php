<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight">
            {{ __('Profile') }}
        </h2>
    </x-slot>

    @if (session()->has('message'))
        <div class="p-4 mt-4rounded-md">
            {{ session('message') }}
        </div>
    @endif

    <div class="py-12">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">


            <!-- Update Profile Information -->
            <div class="p-4 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-profile-information-form />
                </div>
            </div>

            <!-- Update Password -->
            <div class="p-4 shadow sm:p-8 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.update-password-form />
                </div>
            </div>

            <!-- Subscription Status -->
            <div class="p-4 shadow sm:p-8 sm:rounded-lg">
                <livewire:profile.subscription-status />
            </div>

            <!-- Marketing preferences -->
            <div class="p-4 shadow sm:p-8 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.marketing-preferences />
                </div>
            </div>

            <!-- Update Payment Method -->
            @if (auth()->user()->subscribed())
                <div class="p-4 shadow sm:p-8 sm:rounded-lg">
                    <livewire:profile.update-payment-method />
                </div>
            @endif

            <!-- Delete User -->
            <div class="p-4 shadow sm:p-8 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>

            <div class="p-4 shadow sm:p-8 sm:rounded-lg">
                <div class="max-w-xl">
                    <p>Need help? Let us know by contacting us at <a href="mailto:support@planeclub.app">support@planeclub.app</a></p><br>
                    <a href="/privacy-policy">Privacy Policy</a><br>
                    <a href="/terms-conditions">Terms of Service</a><br>
                    <a href="/cookie-policy">Cookie Policy</a><br>
                    <a href="https://app.termly.io/notify/f18e572b-567a-4704-b52c-0b6fdd7d9ab6">Data Subject Access Request (DSAR) Form</a><br>
                    <a href="https://app.termly.io/notify/f18e572b-567a-4704-b52c-0b6fdd7d9ab6">Do Not Sell or Share My Personal information</a><br>
                    <a href="https://app.termly.io/notify/f18e572b-567a-4704-b52c-0b6fdd7d9ab6">Limit the Use Of My Sensitive Personal Information</a><br>
                 </div>
            </div>
        </div>
    </div>
</x-app-layout>
