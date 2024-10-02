<?php
use Livewire\Volt\Component;
use Illuminate\Support\Facades\Auth;

new class extends Component
{


}

?>

<x-app-layout>
    <!-- Hero Section -->
<section class="h-screen bg-center bg-cover bg-grey" style="background-image: url('{{ asset('storage/hero-image.jpg') }}');">
    <div class="flex items-center justify-center h-full bg-gray-900 bg-opacity-50">
      <div class="text-center">
        <h1 class="mb-4 text-3xl font-bold text-white md:text-5xl">Capture Every Flight and Aircraft Spotted</h1>
        <p class="mb-8 text-xl text-gray-200">Log your aviation journey.</p>
        <a href="/register" class="px-8 py-3 text-lg text-white bg-blue-600 rounded-full">Start Your 14-Day Free Trial</a>
    </div>
    </div>
  </section>

  <!-- Features Section -->
  <section class="py-20">
    <div class="container px-6 mx-auto">
      <h2 class="mb-12 text-3xl font-bold text-center">Why Choose Plane Club?</h2>
      <div class="flex flex-wrap">
        <!-- Feature 1 -->
        <div class="w-full px-4 mb-8 text-center md:w-1/3">
          <img src="your-feature1-image-url.jpg" alt="Comprehensive Logging" class="mx-auto mb-4">
          <h3 class="mb-2 text-xl font-semibold">Comprehensive Logging</h3>
          <p>Add logs of airports, aircraft types, airlines, and aircraft registrations you've spotted or flown on.</p>
        </div>
        <!-- Feature 2 -->
        <div class="w-full px-4 mb-8 text-center md:w-1/3">
          <img src="your-feature2-image-url.jpg" alt="Photo Gallery" class="mx-auto mb-4">
          <h3 class="mb-2 text-xl font-semibold">Photo Gallery</h3>
          <p>Upload and showcase your aircraft photos in a personalized gallery.</p>
        </div>
        <!-- Feature 3 -->
        <div class="w-full px-4 mb-8 text-center md:w-1/3">
          <img src="your-feature3-image-url.jpg" alt="Personal Database" class="mx-auto mb-4">
          <h3 class="mb-2 text-xl font-semibold">Personal Database</h3>
          <p>Maintain a detailed database of all your flights and spotted planes, all in one place.</p>
        </div>
      </div>
      <p class="mt-8 text-xl text-center">Experience all these features £19.99/month</p>

    </div>
  </section>

  <!-- Call to Action -->
  <section class="py-20 bg-blue-600">
    <div class="container px-6 mx-auto text-center">
        <h2 class="mb-4 text-3xl font-bold text-white">Ready to Take Your Hobby to New Heights?</h2>
        <p class="mb-8 text-xl text-white">Continue your aviation journey with Plane Club for just £19.99 per month.</p>
      <a href="/signup" class="px-8 py-3 text-lg text-blue-600 bg-white rounded-full">Start Your 14-Day Free Trial</a>
    </div>
  </section>

  <!-- Footer -->
  <footer class="py-6 bg-gray-800">
    <div class="container px-6 mx-auto text-center">
      <p class="text-white">&copy; 2024 Plane Club. All rights reserved.</p>
    </div>
  </footer>
</x-app-layout>
