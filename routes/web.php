<?php

use App\Http\Controllers\AircraftLogController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AircraftLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_logs');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
