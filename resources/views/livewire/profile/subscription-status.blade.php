<?php

use Livewire\Volt\Component;
use Mary\Traits\Toast;

new class extends Component {

    use Toast;

    public $subscription;
    protected bool $pro = false;

    public function mount()
    {
        $user = auth()->user();
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));
        $this->completeSubPlanId = "tier1";

        // Get the current plan ID
        if ($this->subscription && $this->subscription->valid()) {
            $this->pro = false;
        }
    }

    public function cancelSubscription()
    {
        $user = auth()->user();
        $user->subscription(env('STRIPE_PRODUCT_ID'))->cancel();
        $this->pro = false;
        $this->toast(
            type: 'warning',
            title: 'Subscription Cancelled',
            description: 'Your subscription will end on ' . $this->subscription->ends_at?->format('Y-m-d') . '.',
        );
    }


    public function resumeSubscription()
    {
        $user = auth()->user();
        $user->subscription(env('STRIPE_PRODUCT_ID'))->resume();

        // Refresh the subscription property
        $this->subscription = $user->subscription(env('STRIPE_PRODUCT_ID'));
        $this->toast(
            type: 'Success',
            title: 'Your subscription has been resumed'
        );
    }

    public function upgradePro()
    {
        $this->redirectRoute('checkout');
    }

    public function downgradeFree()
    {
        $user = auth()->user();
        $freePlanStorageLimitGB = 10;

        // Get the user's current used disk space in GB
        $usedDiskBytes = $user->used_disk;
        $usedDiskGB = $usedDiskBytes / (1024 * 1024 * 1024); // Convert bytes to GB

        // Check if user's used disk exceeds the new plan's storage limit
        if ($usedDiskGB > $newPlanStorageLimitGB) {
            $this->toast(
                type: 'error',
                title: 'Failed todowngrade to free',
                description: 'Your current storage usage exceeds the plan\'s limit. Please reduce your storage usage before downgrading',
            );
            return;
        }

        $user->subscription(env('STRIPE_PRODUCT_ID'))->cancel();
        $this->pro = false;
        $this->toast(
            type: 'warning',
            title: 'Subscription downgraded to free tier',
            description: 'You will have pro access until ' . $this->subscription->ends_at?->format('Y-m-d') . '.',
        );
    }
};

?>

<section>
    <header>
        <h2 class="text-lg font-medium">Subscription</h2>
    </header>
    <div>
        @if ($subscription && $subscription->onGracePeriod())
            <div class="mt-6 space-y-6">
                <p>Your subscription has been canceled and will end on {{ $subscription->ends_at->format('Y-m-d') }}. After this date, your account and data will be deleted.</p>
                <x-mary-button wire:click="resumeSubscription" class="btn-primary" label="Resume Subscription" />
            </div>
        @elseif ($subscription && $subscription->valid())
            <div class="mt-6 space-y-6">
                <p>Your Pro subscription is <x-mary-badge positive label="active" /></p>
                <p>Next Billing Date: {{ date('Y-m-d', $subscription->asStripeSubscription()->current_period_end) }}</p>
                <x-mary-button wire:click="cancelSubscription" class="mt-4 btn-error" label="Cancel Subscription" />
            </div>
        @else
            <div class="mt-6 space-y-6">
                <p>Your Free subscription is <x-mary-badge positive label="active" /></p>
                <x-mary-button wire:click="upgradePro" class="btn-primary" label="Upgrade to Pro" />
            </div>
        @endif
    </div>
</section>


