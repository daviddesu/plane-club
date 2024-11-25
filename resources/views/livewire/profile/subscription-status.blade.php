<?php

use Livewire\Volt\Component;
use Masmerise\Toaster\Toaster;


new class extends Component {
    public $subscription;
    public $availablePlans = [];
    public $currentPlanId;
    public $newPlanId;

    public function mount()
    {
        $user = auth()->user();
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));

        // Define your available plans
        $this->availablePlans = [
            [
                'name' => 'Hobby',
                'price' => '£15/month',
                'storage' => '200 GB',
                'stripe_price_id' => env('STRIPE_PRICE_ID_TIER1'),
            ],
            [
                'name' => 'Aviator',
                'price' => '£25/month',
                'storage' => 'up to 500 GB',
                'stripe_price_id' => env('STRIPE_PRICE_ID_TIER2'),
            ],
            [
                'name' => 'Captain',
                'price' => '£75/month',
                'storage' => '2 TB',
                'stripe_price_id' => env('STRIPE_PRICE_ID_TIER3'),
            ],
        ];

        // Get the current plan ID
        if ($this->subscription && $this->subscription->valid()) {
            $this->currentPlanId = $this->subscription->stripe_price;
        }

        // Initialize newPlanId with the current plan
        $this->newPlanId = $this->currentPlanId;
    }

    public function cancelSubscription()
    {
        $user = auth()->user();
        $user->subscription(env('STRIPE_PRODUCT_ID'))->cancel();

        // Refresh the subscription property
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));

        Toaster::warning('Your subscription has been canceled and will end on ' . $this->subscription->ends_at?->format('Y-m-d') . '.');
    }

    public function resumeSubscription()
    {
        $user = auth()->user();
        $user->subscription(env('STRIPE_PRODUCT_ID'))->resume();

        // Refresh the subscription property
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));

        Toaster::success('Your subscription has been resumed.');
    }

    public function changePlan()
    {
        $user = auth()->user();

        if ($this->newPlanId === $this->currentPlanId) {
            Toaster::info('You are already on this plan.');
            return;
        }

        try {
            // Swap the subscription to the new plan
            $user->subscription(env('STRIPE_PRODUCT_ID'))->swap($this->newPlanId);

            // Refresh the subscription and current plan ID
            $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));
            $this->currentPlanId = $this->subscription->stripe_price;

            Toaster::success('Your subscription has been updated to the new plan.');
        } catch (\Exception $e) {
            Toaster::error('An error occurred while updating your subscription: ' . $e->getMessage());
        }
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
                <p>Your subscription has been canceled and will end on {{ $subscription->ends_at->format('Y-m-d') }}. After this date, your account and data will be deleted.</p>
                <x-primary-button wire:click="resumeSubscription">Resume Subscription</x-primary-button>
            </div>
        @elseif ($subscription && $subscription->valid())
            <div class="mt-6 space-y-6">
                <p>Your subscription is <x-badge positive label="active" />.</p>
                <p>
                    Current Plan:
                    @php
                        $currentPlan = collect($availablePlans)->firstWhere('stripe_price_id', $currentPlanId);
                    @endphp
                    {{ $currentPlan['name'] ?? 'Unknown Plan' }}
                </p>
                <p>Next Billing Date: {{ date('Y-m-d', $subscription->asStripeSubscription()->current_period_end) }}</p>

                <!-- Plan Selection Form -->
                <form wire:submit.prevent="changePlan">
                    <div class="mt-4">
                        <label for="plan">Select a new plan:</label>
                        <select wire:model="newPlanId" id="plan" class="block w-full mt-1">
                            @foreach ($availablePlans as $plan)
                                <option value="{{ $plan['stripe_price_id'] }}">{{ $plan['name'] }} - {{ $plan['price'] }} - up to {{ $plan['storage'] }}</option>
                            @endforeach
                        </select>
                    </div>
                    <x-primary-button type="submit" class="mt-4">Update Plan</x-primary-button>
                </form>

                <x-danger-button wire:click="cancelSubscription" class="mt-4">Cancel Subscription</x-danger-button>
            </div>
        @else
            <div class="mt-6 space-y-6">
                <p>Your subscription is inactive. Your account and data will be deleted on {{ optional($subscription->ends_at)->addMonth()->format('Y-m-d') ?? 'N/A' }}.</p>
                <x-primary-button wire:click="navigateToCheckout">Subscribe Now</x-primary-button>
            </div>
        @endif
    </div>
</section>


