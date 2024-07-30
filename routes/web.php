<?php

use App\Http\Controllers\AircraftLogController;
use Illuminate\Support\Facades\Route;

Route::view('/', 'welcome');

Route::get('logs', [AircraftLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_logs');

Route::view('dashboard', 'dashboard')
    ->middleware(['auth', 'verified'])
    ->name('dashboard');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
