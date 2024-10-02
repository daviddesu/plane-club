<?php

use App\Http\Controllers\AircraftLogController;
use App\Http\Controllers\SalesController;
use Illuminate\Support\Facades\Route;
use Illuminate\Http\Request;
use App\Http\Middleware\Subscribed;


Route::get('/', [SalesController::class, 'index'])
    ->name('signup');

Route::get('/logs', [AircraftLogController::class, 'index'])
    ->middleware(['auth', 'verified', Subscribed::class])
    ->name('aircraft_logs');

Route::get('/log/{id}', [AircraftLogController::class, 'viewAircraftLog'])
    ->middleware(['auth', 'verified', Subscribed::class])
    ->name('aircraft_log');

Route::view('profile', 'profile')
    ->middleware(['auth'])
    ->name('profile');


Route::get('/checkout', function (Request $request) {
    return $request->user()
        ->newSubscription('default', 'prod_QxPNhmWnhgxJSD')
        ->trialDays(14)
        ->allowPromotionCodes()
        ->checkout([
            'success_url' => route('checkout-success'),
            'cancel_url' => route('checkout-cancel'),
        ]);
})->name('checkout');

Route::view('/checkout/success', 'checkout.success')->name('checkout-success');
Route::view('/checkout/cancel', 'checkout.cancel')->name('checkout-cancel');

require __DIR__ . '/auth.php';
