@vite(['resources/css/app.css', 'resources/js/app.js'])
<!-- Ensure Alpine.js is included, for example by adding in app.js or a separate script -->
<!-- <script src="//unpkg.com/alpinejs" defer></script> if not using Vite -->

<x-app-layout>
    <section class="relative h-screen pb-20 bg-gray-200 bg-center bg-cover" style="background-image: url('{{ asset('desktop-image.png') }}');">
        <div class="absolute inset-0 bg-gray-900 opacity-80"></div>
        <div class="relative flex items-center justify-center h-full">
            <div class="pt-20 text-center">
                <h1 class="m-4 text-5xl font-bold text-white md:text-5xl">
                    Capture, organize, and showcase your aircraft images and videos
                </h1>
                <br>
                <a href="/register" class="px-8 py-4 text-lg text-white rounded-full bg-cyan-800">Get your first 15 days for free</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-cyan-800">
        <div class="container px-6 mx-auto text-center">
            <h2 class="mb-12 text-3xl font-bold text-white">Why Choose Plane Club?</h2>
            <div class="flex flex-wrap items-center justify-center">
                <!-- Feature 1 -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="p-6">
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="photo" class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white">Personalized Gallery</h3>
                        <p class="text-xl text-center text-white">Upload photos and videos to create your own gallery and database.</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="p-6">
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="tag" class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white">Detailed Tagging</h3>
                        <p class="text-xl text-center text-white">Tag aircraft, airlines, and locations with ease.</p>
                    </div>
                </div>
                <!-- Feature 3 -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="p-6">
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="cloud-arrow-up" class="w-12 h-12 text-white" />
                        </div>
                        <h3 class="mb-2 text-2xl font-bold text-white">Cloud storage</h3>
                        <p class="text-xl text-center text-white">Your images and videos available everywhere with our cloud storage solution.</p>
                    </div>
                </div>
            </div>
        </div>
    </section>

    <!-- Subscription Tiers Section -->
    <!-- Initialize Alpine here -->
    <section class="py-20 bg-white" x-data="{ showGBP: true }">
        <div class="container px-6 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800">Plans</h2>
                <p class="text-gray-600">Choose the plan that best fits your needs.</p>
            </div>

            <!-- Toggle Button -->
            <div class="mb-8 text-center">
                <button
                    @click="showGBP = !showGBP"
                    class="px-4 py-2 bg-gray-300 rounded hover:bg-gray-400">
                    <span x-show="showGBP">Show Euros (€)</span>
                    <span x-show="!showGBP">Show Pounds (£)</span>
                </button>
            </div>

            <div class="flex flex-wrap items-start justify-center">
                <!-- Hobby Plan -->
                <div class="w-full p-4 mb-8 md:w-1/3">
                    <div class="p-6 text-center bg-gray-100 rounded-lg shadow-md">
                        <h3 class="mb-4 text-2xl font-bold text-gray-800">Hobby</h3>
                        <!-- Prices -->
                        <p class="mb-4 text-xl font-bold text-gray-800">
                            <!-- Show GBP if showGBP is true, otherwise show EUR -->
                            <span x-show="showGBP">£4.99/month</span>
                            <span x-show="!showGBP" style="display:none;">€5.99/month</span>
                        </p>
                        <p class="mb-6 text-gray-600">Unlimited sightings</p>
                        <p class="mb-6 text-gray-600">Upload images to sightings</p>
                        <p class="mb-6 text-gray-600">500GB of image uploads</p>
                        <p class="mb-6 text-gray-600">Personalised logbook</p>
                    </div>
                </div>

                <!-- Aviator Plan -->
                <div class="w-full p-4 mb-8 md:w-1/3">
                    <div class="p-6 text-center bg-gray-100 rounded-lg shadow-md">
                        <h3 class="mb-4 text-2xl font-bold text-gray-800">Aviator</h3>
                        <p class="mb-4 text-xl font-bold text-gray-800">
                            <span x-show="showGBP">£19.99/month</span>
                            <span x-show="!showGBP" style="display:none;">€24.99/month</span>
                        </p>
                        <p class="mb-6 text-gray-600">Unlimited sightings</p>
                        <p class="mb-6 text-gray-600">Upload images to sightings</p>
                        <p class="mb-6 text-gray-600">Upload videos to sightings - 500MB limit per video</p>
                        <p class="mb-6 text-gray-600">2TB of image and video uploads</p>
                        <p class="mb-6 text-gray-600">Personalised logbook</p>
                    </div>
                </div>

                <!-- Pro Plan -->
                <div class="w-full p-4 mb-8 md:w-1/3">
                    <div class="p-6 text-center bg-gray-100 rounded-lg shadow-md">
                        <h3 class="mb-4 text-2xl font-bold text-gray-800">Pro</h3>
                        <p class="mb-4 text-xl font-bold text-gray-800">
                            <span x-show="showGBP">£49.99/month</span>
                            <span x-show="!showGBP" style="display:none;">€59.99/month</span>
                        </p>
                        <p class="mb-6 text-gray-600">Unlimited sightings</p>
                        <p class="mb-6 text-gray-600">Upload images to sightings</p>
                        <p class="mb-6 text-gray-600">Upload videos to sightings - 1GB limit per video</p>
                        <p class="mb-6 text-gray-600">5TB of image and video uploads</p>
                        <p class="mb-6 text-gray-600">Personalised logbook</p>
                        <p class="mb-6 text-gray-600">No image or video compression</p>
                    </div>
                </div>
            </div>

            <!-- Call to Action Button -->
            <div class="mt-8 text-center">
                <a href="/register" class="px-8 py-3 text-lg text-white rounded-full bg-cyan-800 hover:bg-cyan-700">Get your first 15 days for free</a>
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
                <p class="text-xs text-white ">Contact: <a href="mailto:support@planeclub.app">support@planeclub.app</a></p><br>
            </div>
        </div>
        <div class="container px-6 pt-4 mx-auto text-center">
            <p class="text-white">&copy; {{ date('Y') }} Plane Club LTD All rights reserved.</p>
        </div>
    </footer>
</x-app-layout>

