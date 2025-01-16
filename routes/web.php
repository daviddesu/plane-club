<?php

use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AircraftLogController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;
use App\Http\Middleware\Subscribed;
use Livewire\Volt\Volt;


Route::get('/', [SalesController::class, 'index'])
    ->name('signup');

Volt::route('/sighting/create', 'aircraft_log.create')
    ->middleware(['auth', 'verified'])
    ->name('sighting_create');

Route::get('/sightings', [AircraftLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_logs');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::view('privacy-policy', 'privacy-policy')
    ->name('privacy-policy');

Route::view('terms-conditions', 'terms-conditions')
    ->name('terms-conditions');

Route::view('cookie-policy', 'cookie-policy')
    ->name('cookie-policy');

Route::get('/checkout', [SalesController::class, 'checkout'])
    ->middleware(['auth'])
    ->name('checkout');

Route::get('/checkout/success', [SalesController::class, 'checkoutSuccess'])
    ->middleware(['auth', Subscribed::class])
    ->name('checkout-success');

Route::get('/checkout/cancel', [SalesController::class, 'checkoutCancel'])
    ->middleware(['auth'])
    ->name('checkout-cancel');

require __DIR__ . '/auth.php';
