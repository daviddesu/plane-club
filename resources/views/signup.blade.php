<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{


}

?>

<x-app-layout>
    <!-- Hero Section -->
    <section class="relative h-screen pb-20 bg-gray-200 bg-center bg-cover"  style="background-image: url('{{ asset('main-image.png') }}');">
        <!-- Overlay -->
        <div class="absolute inset-0 bg-gray-900 opacity-80"></div>

        <!-- Content Container -->
        <div class="relative flex items-center justify-center h-full">
            <div class="pt-20 text-center">
                <h1 class="m-4 text-5xl font-bold text-white md:text-5xl">Capture, Organize, and Showcase your images and videos</h1>

                <p class="mb-8 text-xl text-white">From £15/month</p>
                <a href="/register" class="px-8 py-3 text-lg text-white rounded-full bg-cyan-800">Start your 7 day free trial</a>
            </div>
        </div>
    </section>

    <!-- Features Section -->
    <section class="py-20 bg-cyan-800">
        <div class="container px-6 mx-auto">
            <div class="text-center">
                <h2 class="mb-12 text-3xl font-bold text-white">Why Choose Plane Club?</h2>
            </div>
            <div class="flex flex-wrap items-center justify-center">
                <!-- Feature 1 -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="flex flex-col items-center p-6">
                        <!-- Icon -->
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="tag" class="w-12 h-12 text-white" />
                        </div>
                        <!-- Feature Title -->
                        <h3 class="mb-2 text-2xl font-bold text-white">Detailed Tagging</h3>
                        <!-- Feature Description -->
                        <p class="text-xl text-center text-white">Tag aircraft, airlines, and locations with ease.</p>
                    </div>
                </div>
                <!-- Feature 2 -->
                <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="flex flex-col items-center p-6">
                        <!-- Icon -->
                        <div class="flex items-center justify-center mb-4">
                            <x-icon name="photo" class="w-12 h-12 text-white" />
                        </div>
                        <!-- Feature Title -->
                        <h3 class="mb-2 text-2xl font-bold text-white">Personalized Gallery</h3>
                        <!-- Feature Description -->
                        <p class="text-xl text-center text-white">Create your own gallery and database.</p>
                    </div>
                </div>
                <!-- Feature 3 -->
                {{-- <div class="w-full px-4 mb-8 md:w-1/3">
                    <div class="flex flex-col items-center p-6">
                        <!-- Icon -->
                        <div class="flex items-center justify-center mb-4">
                            <!-- Replace with your icon or SVG -->
                            <x-icon name="share" class="w-12 h-12 text-white" />
                        </div>
                        <!-- Feature Title -->
                        <h3 class="mb-2 text-2xl font-bold text-white">Community Sharing</h3>
                        <!-- Feature Description -->
                        <p class="text-xl text-center text-white">Share with the plane spotting community.</p>
                    </div>
                </div> --}}
            </div>
        </div>
    </section>

    <!-- Subscription Tiers Section -->
    <section class="py-20 bg-white">
        <div class="container px-6 mx-auto">
            <div class="mb-12 text-center">
                <h2 class="text-3xl font-bold text-gray-800">Plans</h2>
                <p class="text-gray-600">Choose the plan that best fits your needs.</p>
            </div>
            <div class="flex flex-wrap items-center justify-center">
                    <div class="w-full p-4 mb-8 md:w-1/3">
                        <div class="p-6 bg-gray-100 rounded-lg shadow-md">
                            <h3 class="mb-4 text-2xl font-bold text-gray-800">Hobby</h3>
                            <p class="mb-4 text-4xl font-bold text-gray-800">£15/month</p>
                            <p class="mb-6 text-gray-600">Storage: 200 GB</p>
                        </div>
                    </div>
                    <div class="w-full p-4 mb-8 md:w-1/3">
                        <div class="p-6 bg-gray-100 rounded-lg shadow-md">
                            <h3 class="mb-4 text-2xl font-bold text-gray-800">Aviator</h3>
                            <p class="mb-4 text-4xl font-bold text-gray-800">£25/month</p>
                            <p class="mb-6 text-gray-600">Storage: 500 GB</p>
                        </div>
                    </div>
                    <div class="w-full p-4 mb-8 md:w-1/3">
                        <div class="p-6 bg-gray-100 rounded-lg shadow-md">
                            <h3 class="mb-4 text-2xl font-bold text-gray-800">Pro</h3>
                            <p class="mb-4 text-4xl font-bold text-gray-800">£75/month</p>
                            <p class="mb-6 text-gray-600">Storage: 2 TB</p>
                        </div>
                    </div>
            </div>
            <!-- Call to Action Button -->
            <div class="mt-8 text-center">
                <a href="/register" class="px-8 py-3 text-lg text-white rounded-full bg-cyan-800 hover:bg-cyan-700">Start your 7-day free trial</a>
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
