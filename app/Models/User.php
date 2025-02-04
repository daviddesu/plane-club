<?php

namespace App\Models;

use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Laravel\Cashier\Billable;

class User extends Authenticatable implements MustVerifyEmail
{
    use HasFactory, Notifiable, Billable;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'name',
        'username',
        'email',
        'password',
        'marketing_preferences',
        'used_disk',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var array<int, string>
     */
    protected $hidden = [
        'password',
        'remember_token',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }

    public function sightings()
    {
        return $this->hasMany(Sighting::class);
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function subscribedStripe()
    {
        return $this->subscribed(env('STRIPE_PRODUCT_ID'));
    }

    public function getStorageLimitInGBAttribute()
    {
        $subscription = $this->subscription(env('STRIPE_PRODUCT_ID'));

        if ($subscription && $subscription->valid()) {
            switch ($subscription->stripe_price) {
                case env('STRIPE_PRICE_ID_TIER1'):
                    return 1000;
            }
        }

        return 5;
    }

    public function isPro()
    {
        $subscription = $this->subscription(env('STRIPE_PRODUCT_ID'));
        return ($subscription && $subscription->valid());
    }

    public function hasExceededUploadLimit()
    {
        if ($this->isPro()) return false;

        return $this->sightings()
            ->whereHas('media')
            ->whereYear('created_at', now()->year)
            ->whereMonth('created_at', now()->month)
            ->count() >= 30;
    }
}
