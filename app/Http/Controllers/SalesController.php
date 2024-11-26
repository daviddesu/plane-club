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
            return redirect()->route('aircraft_logs');
        }
        return view('signup', []);
    }

    public function checkout(Request $request)
    {
        $plan = $request->input('plan');

        $priceId = $this->getPriceIdForPlan($plan);

        if (!$priceId) {
            return redirect()->back()->withErrors('Invalid subscription plan selected.');
        }

        return $request->user()
            ->newSubscription(env("STRIPE_PRODUCT_ID"), $priceId)
            ->trialDays(8)
            ->allowPromotionCodes()
            ->checkout([
                'success_url' => route('checkout-success'),
                'cancel_url' => route('checkout-cancel'),
            ]);
    }

    protected function getPriceIdForPlan($plan)
    {
        switch ($plan) {
            case 'tier1':
                return env('STRIPE_PRICE_ID_TIER1');
            case 'tier2':
                return env('STRIPE_PRICE_ID_TIER2');
            case 'tier3':
                return env('STRIPE_PRICE_ID_TIER3');
            default:
                return null;
        }
    }

    public function checkoutSuccess(Request $request)
    {
        session()->flash('success-message', 'Welcome to Plane Club. It\'s great to have you on board!');
        return redirect()->route('verification.notice');
    }

    public function checkoutCancel()
    {
        return redirect()->route('profile');
    }

}
