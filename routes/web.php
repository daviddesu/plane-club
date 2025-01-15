<?php

use App\Http\Controllers\AircraftController;
use App\Http\Controllers\AircraftLogController;
use App\Http\Controllers\AirlineController;
use App\Http\Controllers\AirportController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Middleware\Subscribed;
use Illuminate\Support\Facades\Redirect;
use Illuminate\Support\Facades\Session;
use Masmerise\Toaster\Toaster;

Route::get('/', [SalesController::class, 'index'])
    ->name('signup');

Route::get('/logs', [AircraftLogController::class, 'index'])
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

Route::get('/airports', [AirportController::class, 'getAirportsSearch'])
    ->middleware(['auth', 'verified'])
    ->name('airports');

Route::get('/airlines', [AirlineController::class, 'getAirlinesSearch'])
    ->middleware(['auth', 'verified'])
    ->name('airlines');

Route::get('/aircraft', [AircraftController::class, 'getAircraftSearch'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft');

require __DIR__ . '/auth.php';
