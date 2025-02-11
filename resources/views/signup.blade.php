<x-app-layout>
    <!-- Hero Section -->
    <section
        class="relative flex items-center justify-center min-h-screen bg-center bg-cover"
    >
        <!-- Overlay -->
        <div class="absolute inset-0"></div>

        <div class="relative z-10 max-w-3xl px-6 text-center">
            <h1 class="mb-4 text-4xl font-extrabold md:text-6xl">
                Your Personal Sighting Logbook
            </h1>
            <ul class="max-w-lg pt-10 pl-20 mx-auto mb-8 space-y-4">
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-paper-airplane" class="w-5 h-5 shrink-0" />
                    <span class="text-lg">Keep track of your sightings in one place</span>
                </li>
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-user-group" class="w-5 h-5 shrink-0" />
                    <span class="text-lg">Share with others</span>
                </li>
                <li class="flex items-center gap-3">
                    <x-mary-icon name="o-lock-closed" class="w-5 h-5 shrink-0" />
                    <span class="text-lg">Private by default</span>
                </li>
            </ul>

            <!-- Primary CTA -->
            <x-mary-button class="font-semibold ext-lg btn-primary" link="/register">
                Sign Up for Free ✈️
            </x-mary-button>
            <x-mary-button class="font-semibold ext-lg btn-outline" link="/login">
                Login
            </x-mary-button>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-6">
        <div class="container grid grid-cols-2 gap-4 px-6 mx-auto text-xs text-center md:grid-cols-43">
            <div>
                <a class="hover:underline" href="/privacy-policy">Privacy Policy</a>
            </div>
            <div>
                <a class="hover:underline" href="/terms-conditions">Terms of Service</a>
            </div>
            <div>
                <a class="hover:underline" href="/cookie-policy">Cookie Policy</a>
            </div>
            <div>
                <a class="hover:underline termly-display-preferences" href="#">
                    Consent Preferences
                </a>
            </div>
            <div>
                <a class="hover:underline" href="https://app.termly.io/notify/f18e572b-567a-4704-b52c-0b6fdd7d9ab6">Data Subject Access Request (DSAR) Form</a>
            </div>
            <div>
                <a class="hover:underline" href="https://app.termly.io/notify/f18e572b-567a-4704-b52c-0b6fdd7d9ab6">Do Not Sell or Share My Personal information</a>
            </div>
            <div>
                <a class="hover:underline" href="https://app.termly.io/notify/f18e572b-567a-4704-b52c-0b6fdd7d9ab6">Limit the Use Of My Sensitive Personal Information</a>
            </div>
        </div>
        <div class="container px-6 pt-4 mx-auto mt-4 text-center">
            <p class="text-xs">&copy; {{ date('Y') }} Wayfinder Technologies LTD. All rights reserved.</p>
        </div>
    </footer>
</x-app-layout>
