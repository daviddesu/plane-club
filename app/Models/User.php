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

    public function aircraftLogs(): HasMany
    {
        return $this->hasMany(AircraftLog::class);
    }

    public function mediaItems(): HasMany
    {
        return $this->hasMany(Media::class);
    }

    public function subscribedStripe()
    {
        return $this->subscribed(env('STRIPE_PRODUCT_ID'));
    }

    public function getTotalStorageInGB()
    {
        return $this->used_disk / (1024 * 1024 * 1024);
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
        return true;
        $subscription = $this->subscription(env('STRIPE_PRODUCT_ID'));
        if(!$subscription) return false;
        return $subscription->stripe_price == env('STRIPE_PRICE_ID_TIER1');
    }

    public function hasExceededStorageLimit()
    {
        return $this->getTotalStorageInGB() > $this->getStorageLimitInGBAttribute();
    }
}
