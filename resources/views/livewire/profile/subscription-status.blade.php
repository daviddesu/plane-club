<?php

use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;


new class extends Component {
    public $subscription;

    public function mount()
    {
        $this->subscription = auth()->user()->subscription(env('STRIPE_PRODUCT_ID'));
    }

    public function cancelSubscription()
    {
        $user = auth()->user();
        $user->subscription(env('STRIPE_PRODUCT_ID'))->cancel();

        // Refresh the subscription property
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));

        Toaster::warning('Your subscription has been canceled and will end on ' . $this->subscription->ends_at->format('Y-m-d') . '.');
    }

    public function resumeSubscription()
    {
        $user = auth()->user();
        $user->subscription(env('STRIPE_PRODUCT_ID'))->resume();

        // Refresh the subscription property
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));

        Toaster::success('Your subscription has been resumed.');
    }

    public function navigateToCheckout()
    {
        $this->redirectRoute('checkout');
    }

};

?>

<section>
    <header>
        <h2 class="text-lg font-medium text-gray-900 dark:text-gray-100">Subscription</h2>
    </header>
    <div>

        @if ($subscription && $subscription->onGracePeriod())

            <div class="mt-6 space-y-6">
                <p>Your subscription has been canceled and will end on {{ $subscription?->ends_at->format('Y-m-d') }}. After this date your account and data will be deleted.</p>
                <x-primary-button wire:click="resumeSubscription">Resume Subscription</x-primary-button>
            </div>
        @elseif ($subscription && $subscription->valid())
            <div class="mt-6 space-y-6">
                <p>Your subscription is <x-badge positive label="active" />.</p>
                <p>Next Billing Date: {{ date('Y-m-d', $subscription->asStripeSubscription()->current_period_end) }}</p>
                <x-danger-button wire:click="cancelSubscription">Cancel Subscription</x-danger-button>
            </div>
        @else
            <div class="mt-6 space-y-6">
                <p>You do not have an active subscription. Your data account will be deleted on {{ $subscription?->ends_at->add('1M')->format('Y-m-d') }}</p>
            </div>
        @endif
    </div>
</section>

