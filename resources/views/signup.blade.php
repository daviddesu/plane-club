@vite(['resources/css/app.css', 'resources/js/app.js'])

<x-app-layout>
    <!-- Hero Section -->
    <section class="relative h-screen pb-20 bg-gray-300 bg-center bg-cover">
        <div class="absolute inset-0 bg-white opacity-80"></div>
        <div class="relative flex items-center justify-center h-full">
            <div class="max-w-2xl pt-10 mx-auto text-center">
                <!-- Headline focusing on Personal Logbook USP -->
                <h1 class="m-4 text-5xl font-bold text-gray-800 md:text-5xl">
                    Your Personal Aircraft Sighting Logbook
                </h1>
                <br>

                <br>

                <!-- Trial and Guarantee -->
                {{-- <p class="mb-2 text-lg text-gray-600">Start a 15-day free trial.</p> --}}
                <br>
                <!-- Strong CTA -->
                <a href="/register" class="inline-block px-8 py-4 text-lg font-semibold text-white rounded-full bg-cyan-800 hover:bg-cyan-700">
                    Start Your 15-Day Free Trial Now
                </a>
            </div>
        </div>
    </section>

    <!-- Why Choose Plane Club Section -->
    <section class="py-16 bg-cyan-800">
        <div class="container max-w-4xl px-6 mx-auto text-center">
            <h2 class="mb-8 text-3xl font-bold text-white">Why Plane Club?</h2>
            <p class="mb-12 text-xl text-white">
                Unlike public-only aviation photo databases, Plane Club puts <b>you</b> in control. Keep your entire aircraft spotting collection private, perfectly tagged, and searchable. {{--Share on your terms and post directly to Plane Club’s community or Facebook with just a click. --}}
            </p>
            <div class="flex flex-wrap items-start justify-center">
                <!-- Feature 1: Personalized Gallery -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="photo" class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white">Personalized & Private Gallery</h3>
                        <p class="text-xl text-center text-white">Store images & videos of all your sightings in a cloud-based gallery that’s private by default. Filter by aircraft type, airline, or location to find what you need instantly.</p>
                    </div>
                </div>
                <!-- Feature 2: Detailed Tagging -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="tag" class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white">Detailed Tagging & Search</h3>
                        <p class="text-xl text-center text-white">Tag each sighting with registration, airline, or airports visited. Unlike general storage solutions, Plane Club is built for aviation — ensuring every sighting is easy to retrieve.</p>
                    </div>
                </div>
                <!-- Feature 3: Cloud Storage -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="p-6 text-white">
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="cloud-arrow-up" class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white">Accessible anywhere</h3>
                        <p class="text-xl text-center text-white">Plane Club is cloud based, meaning you can access your logbook from anywhere.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Plans Section -->
    <section class="py-20 bg-white" x-data="{ showGBP: true }">
        <div class="container max-w-4xl px-6 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800">Plans</h2>
            </div>

            <!-- Currency Toggle -->
            <div class="mb-8 text-center">
                <button
                    @click="showGBP = !showGBP"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    <template x-if="showGBP"><span>Show Prices in Euros (€)</span></template>
                    <template x-if="!showGBP"><span>Show Prices in Pounds (£)</span></template>
                </button>
            </div>

            <div class="flex flex-wrap items-start justify-center">
                <!-- Hobby Plan -->
                <div class="w-full p-4 mb-8 md:w-1/3">
                    <div class="p-6 text-center bg-gray-100 rounded-lg shadow-md">
                        <h3 class="mb-4 text-2xl font-bold text-gray-800">Hobby</h3>
                        <p class="mb-4 text-xl font-bold text-gray-800">
                            <span x-show="showGBP"> <span class="line-through">£9.99</span> £4.99/month</span>
                            <span x-show="!showGBP" style="display:none;"><span class="line-through">€11.99</span> €5.99/month</span>
                        </p>
                        <p class="mb-6 text-gray-600">Unlimited sightings</p>
                        <p class="mb-6 text-gray-600">Image uploads</p>
                        {{-- <p class="mb-6 text-gray-600">500GB storage</p> --}}
                        <p class="mb-6 text-gray-600">Private logbook</p>
                        {{-- <p class="mb-6 font-semibold text-gray-600">Future: Selective social sharing</p> --}}
                        <a href="/register" class="inline-block px-6 py-2 mx-2 font-semibold text-white rounded-full text-md bg-cyan-800 hover:bg-cyan-700">
                            Start Free Trial
                        </a>
                    </div>
                </div>

                <!-- Aviator Plan -->
                <div class="w-full p-4 mb-8 md:w-1/3">
                    <div class="p-6 text-center bg-gray-100 border-2 rounded-lg shadow-md border-cyan-800">
                        <h3 class="mb-4 text-2xl font-bold text-gray-800">Aviator</h3>
                        <p class="mb-4 text-xl font-bold text-gray-800">
                            <span x-show="showGBP">£19.99/month</span>
                            <span x-show="!showGBP" style="display:none;">€24.99/month</span>
                        </p>
                        <p class="mb-6 text-gray-600">Unlimited sightings</p>
                        <p class="mb-6 text-gray-600">Image uploads</p>
                        <p class="mb-6 text-gray-600">Video uploads</p>
                        {{-- <p class="mb-6 text-gray-600">2TB storage</p> --}}
                        <p class="mb-6 text-gray-600">Private logbook</p>
                        {{-- <p class="mb-6 font-semibold text-gray-600">Coming Soon: Social Sharing to Plane Club & Facebook</p>
                        <p class="mb-6 font-semibold text-gray-600">Coming Soon: Sighting autocomplete</p> --}}
                                                <a href="/register" class="inline-block px-6 py-2 mx-2 font-semibold text-white rounded-full text-md bg-cyan-800 hover:bg-cyan-700">
                            Start Free Trial
                        </a>
                    </div>
                </div>

                {{-- <!-- Pro Plan -->
                <div class="w-full p-4 mb-8 md:w-1/3">
                    <div class="p-6 text-center bg-gray-100 rounded-lg shadow-md">
                        <h3 class="mb-4 text-2xl font-bold text-gray-800">Pro</h3>
                        <p class="mb-4 text-xl font-bold text-gray-800">
                            <span x-show="showGBP">£49.99/month</span>
                            <span x-show="!showGBP" style="display:none;">€59.99/month</span>
                        </p>
                        <p class="mb-6 text-gray-600">Unlimited sightings</p>
                        <p class="mb-6 text-gray-600">Images & videos (1GB/video)</p>
                        <p class="mb-6 text-gray-600">5TB storage</p>
                        <p class="mb-6 text-gray-600">Private logbook</p>
                        <p class="mb-6 font-semibold text-gray-600">Coming Soon: Social Sharing to Plane Club & Facebook</p>
                        <p class="mb-6 font-semibold text-gray-600">Coming Soon: Sighting autocomplete</p>
                                                <a href="/register" class="inline-block px-6 py-2 mx-2 font-semibold text-white rounded-full text-md bg-cyan-800 hover:bg-cyan-700">
                            Start Free Trial
                        </a>
                    </div>
                </div> --}}
            </div>

            <!-- Another Call to Action -->
            <div class="mt-8 text-center">
                <p class="mb-3 text-lg text-cyan-800">Start now and explore all features for free</p>
                <a href="/register" class="px-8 py-3 text-lg text-white rounded-full bg-cyan-800 hover:bg-cyan-700">
                    Begin Your 15-Day Free Trial
                </a>
            </div>
        </div>
    </section>

    <!-- Footer -->
    <footer class="py-6 bg-gray-800">
        <div class="container grid grid-cols-2 px-10 text-center">
            <div>
                <a class="text-xs text-white" href="/privacy-policy">Privacy Policy</a><br>
                <a class="text-xs text-white" href="/terms-conditions">Terms of Service</a><br>
                <a class="text-xs text-white" href="/cookie-policy">Cookie Policy</a><br>
                <a class="text-xs text-white" href="#" class="termly-display-preferences">Consent Preferences</a>
            </div>
            <div>
                <p class="text-xs text-white">Contact: <a href="mailto:support@planeclub.app" class="underline">support@planeclub.app</a></p><br>
            </div>
        </div>
        <div class="container px-6 pt-4 mx-auto text-center">
            <p class="text-xs text-white">&copy; {{ date('Y') }} Plane Club LTD. All rights reserved.</p>
        </div>
    </footer>
</x-app-layout>
