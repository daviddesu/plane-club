<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{


}

?>

<x-app-layout>
    <!-- Hero Section -->
<section class="h-screen bg-center bg-cover bg-grey-200">
    <div class="flex items-center justify-center h-full bg-gray-400 bg-opacity-50">
      <div class="text-center">
        <img src="/logo.png" class="object-center h-80" />
        <h1 class="mb-4 text-3xl font-bold text-cyan-800 md:text-5xl">Capture Every Aircraft</h1>
        <p class="mb-8 text-xl text-cyan-600">Log your aviation journey for £19.99/month</p>
        <a href="/register" class="px-8 py-3 text-lg text-white rounded-full bg-cyan-800">Ready to board</a>
    </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="py-20">
    <div class="container px-6 mx-auto">
        <h4 class="mb-12 text-3xl text-center text-cyan-800">Seamlessly upload and showcase your photos and videos. Tagging them with detailed information about the aircraft, airlines, and locations.</h4>
      <h2 class="mb-12 text-3xl font-bold text-center text-slate-700">Why Choose Plane Club?</h2>
      <div class="flex flex-wrap">
        <!-- Feature 1 -->
        <div class="w-full px-4 mb-8 text-center md:w-1/3">
          <h3 class="mb-2 text-xl font-semibold text-slate-700">Comprehensive Logging</h3>
          <p class="text-slate-700">Add images and videos alongside the routes, aircraft types, airlines, and aircraft registrations you've spotted or flown on.</p>
        </div>
        <!-- Feature 2 -->
        <div class="w-full px-4 mb-8 text-center md:w-1/3">
            <h3 class="mb-2 text-xl font-semibold text-slate-700">Photo Gallery</h3>
          <p class="text-slate-700">Leave the overbearing quality rules on the ramp. Instantly upload and showcase your aircraft photos and videos in a personalized gallery.Plane club is designed for hobbyists and professionals.</p>
        </div>
        <!-- Feature 3 -->
        <div class="w-full px-4 mb-8 text-center md:w-1/3">
            <h3 class="mb-2 text-xl font-semibold text-slate-700">Personal Database</h3>
          <p class="text-slate-700">Maintain a detailed database of all your aircraft, all in one place. Filter down your history to see all photos and videos by aircraft type, route or airline.</p>
        </div>
      </div>
      <p class="mt-8 text-xl text-center text-cyan-800">Experience all these features for £19.99/month</p>

    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-20 bg-cyan-800">
    <div class="container px-6 mx-auto text-center">
        <h2 class="mb-4 text-3xl font-bold text-white">Ready to Take Your Hobby to New Heights?</h2>
        <p class="mb-8 text-xl text-white">Continue your aviation journey with Plane Club for just £19.99 per month.</p>
      <a href="/signup" class="px-8 py-3 text-lg bg-white rounded-full text-cyan-800">Ready to board</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-6 bg-gray-800">
    <div class="container grid grid-cols-2 px-10 text-center">
        <div>
            <a class="text-white" href="/privacy-policy">Privacy Policy</a><br>
            <a class="text-white" href="/terms-conditions">Terms of Service</a><br>
            <a class="text-white" href="/cookie policy">Cookie Policy</a><br>
            <a class="text-white" href="#" class="termly-display-preferences">Consent Preferences</a>
        </div>
        <div>
            <p class="text-white">Contact: <a href="mailto:support@planeclub.app">support@planeclub.app</a></p><br>
            <p class="text-white underline" >Address</p>
            <p class="text-white" >Third Floor, 3 Hill Street</p>
            <p class="text-white" >Edinburgh</p>
            <p class="text-white" >EH2 3JP</p>
        </div>
    </div>
    <div class="container px-6 pt-4 mx-auto text-center">
      <p class="text-white">&copy; 2024 Plane Club LTD All rights reserved.</p>
    </div>
  </footer>
</x-app-layout>
