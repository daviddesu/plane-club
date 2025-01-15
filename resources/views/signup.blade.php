
<x-app-layout>
    <!-- Hero Section -->
    <section class="relative h-screen bg-cover pb-20bg-center">
        <div class="absolute inset-0"></div>
        <div class="relative flex items-center justify-center h-full">
            <div class="max-w-2xl pt-10 mx-auto text-center">
                <!-- Headline focusing on Personal Logbook USP -->
                <h1 class="m-4 text-5xl font-bold md:text-5xl">
                    Your Personal Aircraft Sighting Logbook
                </h1>
                <br>

                <br>

                <!-- Trial and Guarantee -->
                <br>
                <!-- Strong CTA -->
                <x-mary-button class="btn-primary" link="/register">
                    Join now ✈️
                </x-mary-button>
            </div>
        </div>
    </section>


    <!-- Footer -->
    <footer class="py-6">
        <div class="container grid grid-cols-4 px-10 text-center">
            <div>
                <a class="text-xs text-white" href="/privacy-policy">Privacy Policy</a><br>
            </div>
            <div>
                <a class="text-xs text-white" href="/terms-conditions">Terms of Service</a><br>
            </div>
            <div>
                <a class="text-xs text-white" href="/cookie-policy">Cookie Policy</a><br>
            </div>
            <div>
                <a class="text-xs text-white" href="#" class="termly-display-preferences">Consent Preferences</a>
            </div>
        </div>
        <div class="container px-6 pt-4 mx-auto text-center">
            <p class="text-xs text-white">&copy; {{ date('Y') }} Wayfinder Technologies LTD. All rights reserved.</p>
        </div>
    </footer>
</x-app-layout>
