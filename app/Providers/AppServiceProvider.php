<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use PostHog\PostHog;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        PostHog::init(
            'phc_9P4bssfAQEZUcYEYjvQFrRcS3ApwrNTMDPQySGFlqAl',
            [
                'host' => 'https://us.i.posthog.com'
            ]
        );
    }
}
