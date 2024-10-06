<?php

namespace App\Http\Controllers;

use Illuminate\View\View;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Masmerise\Toaster\Toaster;

class SalesController extends Controller
{
    public function index(): View|RedirectResponse
    {
        if (Auth::check()) {
            return redirect()->route('aircraft_logs_images');
        }
        return view('signup', []);
    }

    public function checkout(Request $request)
    {
        return $request
            ->user()
            ->newSubscription(env("STRIPE_PRODUCT_ID"), env("STRIPE_PRICE_ID"))
            ->trialDays(15)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('checkout-success'),
                'cancel_url' => route('checkout-cancel'),
            ]);
    }


    public function checkoutSuccess(Request $request)
    {
        session()->flash('success-message', 'Welcome to Plane Club. Its great to have you on board!');

        return redirect()->route('aircraft_logs_images');

    }

    public function checkoutCancel()
    {
        return redirect()->route('profile');
    }

}
