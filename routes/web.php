<?php

use App\Http\Controllers\AircraftLogController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;

Route::get('/', [SalesController::class, 'index'])
    ->name('signup');

Route::get('/logs', [AircraftLogController::class, 'index'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_logs');

Route::get('/log/{id}', [AircraftLogController::class, 'viewAircraftLog'])
    ->middleware(['auth', 'verified'])
    ->name('aircraft_log');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');

require __DIR__ . '/auth.php';
