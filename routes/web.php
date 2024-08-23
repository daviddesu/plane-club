<?php

use App\Http\Controllers\AircraftLogController;
use App\Http\Controllers\AirportController;
use Illuminate\Support\Facades\Route;


Route::get('/', [AircraftLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_logs');

Route::get('/log/{id}', [AircraftLogController::class, 'viewAircraftLog'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_log');

Route::get('/airports', [AirportController::class, 'getAirportsSearch'])
    ->middleware(['auth', 'verified'])
    ->name('airports');


Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
