<?php

use App\Http\Controllers\AircraftLogController;
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
    ->middleware(['auth', Subscribed::class])
    ->name('aircraft_logs');

Route::get('/images', [AircraftLogController::class, 'viewImages'])
    ->middleware(['auth', Subscribed::class])
    ->name('aircraft_images');

Route::get('/log/{id}', [AircraftLogController::class, 'viewAircraftLog'])
    ->middleware(['auth', 'verified', Subscribed::class])
    ->name('aircraft_log');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

Route::get('/checkout', [SalesController::class, 'checkout'])
    ->middleware(['auth', 'verified'])
    ->name('checkout');

Route::get('/checkout/success', [SalesController::class, 'checkoutSuccess'])
    ->middleware(['auth', 'verified', Subscribed::class])
    ->name('checkout-success');

Route::view('/checkout/cancel', [SalesController::class, 'checkoutCancel'])
    ->middleware(['auth', 'verified'])
    ->name('checkout-cancel');

require __DIR__ . '/auth.php';
