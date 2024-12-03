<x-app-layout>
    <x-slot name="header">
        <h2 class="text-xl font-semibold leading-tight text-gray-800 dark:text-gray-200">
            {{ __('Storage') }}
        </h2>
        <h4>Connect to the storage service(s) that hosts your images and videos.</h4>
    </x-slot>

    @if (session()->has('message'))
        <div class="p-4 mt-4 bg-green-100 rounded-md">
            {{ session('message') }}
        </div>
    @endif

    <div class="py-12">
        <div class="mx-auto space-y-6 max-w-7xl sm:px-6 lg:px-8">



            <!-- Adobe Lightroom -->
            <div class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:storage.adobe-lightroom/>
                </div>
            </div>

            <!-- Subscription Status -->
            <div class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg">
                <livewire:profile.subscription-status />
            </div>

            <!-- Marketing preferences -->
            <div class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.marketing-preferences />
                </div>
            </div>

            <!-- Update Payment Method -->
            @if (auth()->user()->subscribed())
                <div class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg">
                    <livewire:profile.update-payment-method />
                </div>
            @endif

            <!-- Delete User -->
            <div class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg">
                <div class="max-w-xl">
                    <livewire:profile.delete-user-form />
                </div>
            </div>

            <div class="p-4 bg-white shadow sm:p-8 dark:bg-gray-800 sm:rounded-lg">
                <div class="max-w-xl">
                    <p>Need help? Let us know by contacting us at <a href="mailto:support@planeclub.app">support@planeclub.app</a></p><br>
                    <a href="/privacy-policy">Privacy Policy</a><br>
                    <a href="/terms-conditions">Terms of Service</a><br>
                    <a href="/cookie-policy">Cookie Policy</a><br>
                </div>
            </div>
        </div>
    </div>
</x-app-layout>
