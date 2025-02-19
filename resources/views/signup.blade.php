<x-app-layout>
    <!-- Hero Section -->
    <section
        class="relative flex items-center justify-center min-h-screen -m-5 bg-center bg-cover lg:-mx-10"
        style="background-image: url('/ryanair-737-hero.jpeg');"
    >
        <div class="absolute inset-0 bg-black opacity-50"></div>

        <div class="relative max-w-3xl px-6 py-10 text-center text-white z-6">
            <h1 class="mb-4 text-4xl font-extrabold leading-tight md:text-6xl">
                The Ultimate <span class="text-blue-400">Personal</span> Sighting Logbook
            </h1>
            <p class="mx-auto mb-8 text-lg md:max-w-xl">
                Capture every detail of your plane sightings – from tail numbers to personal notes.
                Your data, your way. <span class="font-semibold">Private by default.</span>
            </p>

            <!-- Primary CTA -->
            <div class="flex flex-col items-center justify-center gap-3 sm:flex-row">
                <x-mary-button class="px-3 font-semibold ext-lg p btn-primary" link="/register">
                    Sign Up for Free ✈️
                </x-mary-button>
                <x-mary-button class="px-6 text-lg font-semibold" link="/login">
                    Login
                </x-mary-button>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-16">
        <div class="container max-w-5xl px-6 mx-auto">
            <h2 class="mb-6 text-3xl font-extrabold text-center md:text-4xl">
                Why Plane Club?
            </h2>
            <p class="mx-auto mb-10 text-lg text-center text-gray-600 md:max-w-2xl">
                Track all your sightings in one seamless, user-friendly platform.
                Designed for aviation enthusiasts, powered by practical features.
            </p>
            <div class="grid gap-8 md:grid-cols-3">
                <div class="flex flex-col items-center text-center">
                    <x-mary-icon name="o-paper-airplane" class="w-12 h-12 mb-4 text-primary" />
                    <h3 class="mb-2 text-xl font-semibold">Simple Logging</h3>
                    <p>
                        Quickly record tail numbers, airline, location, aircraft and time
                        without any unnecessary clutter.
                    </p>
                </div>

                <div class="flex flex-col items-center text-center">
                    <x-mary-icon name="o-lock-closed" class="w-12 h-12 mb-4 text-primary" />
                    <h3 class="mb-2 text-xl font-semibold">Private by Default</h3>
                    <p>
                        You’re in control. Enjoy complete privacy over your data and gallery
                    </p>
                </div>

                <div class="flex flex-col items-center text-center">
                    <x-mary-icon name="o-magnifying-glass" class="w-12 h-12 mb-4 text-primary" />
                    <h3 class="mb-2 text-xl font-semibold">Smart Search & Stats</h3>
                    <p>
                        Instantly find past sightings
                    </p>
                </div>
            </div>
        </div>
    </section>

    <hr class="w-4/5 mx-auto border-t border-gray-300" />


    <!-- Secondary CTA Section -->
    <section class="py-16">
        <div class="container max-w-3xl px-6 mx-auto text-center">
            <h2 class="mb-6 text-2xl font-extrabold md:text-3xl">
                Ready for Your Next Spotting Adventure?
            </h2>
            <p class="mb-8">
                Join Plane Club today and start building your personal logbook.
            </p>
            <x-mary-button class="px-8 text-lg font-semibold btn-primary" link="/register">
                Sign Up for Free
            </x-mary-button>
        </div>
    </section>

    <hr class="w-4/5 mx-auto border-t" />


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
