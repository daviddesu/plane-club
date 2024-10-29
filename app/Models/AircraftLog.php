<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Database\Eloquent\Relations\HasOne;

class AircraftLog extends Model
{
    use HasFactory;

    protected $casts = [
        'logged_at' => 'datetime',
    ];

    protected $fillable = [
        'description',
        'arrival_airport_id',
        'departure_airport_id',
        'status',
        'logged_at',
        'airline_id',
        'registration',
        'aircraft_id'
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function media(): HasOne
    {
        return $this->hasOne(Media::class);
    }

    public function departureAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

    public function arrivalAirport(): BelongsTo
    {
        return $this->belongsTo(Airport::class);
    }

    public function airline(): BelongsTo
    {
        return $this->belongsTo(Airline::class);
    }

    public function aircraft(): BelongsTo
    {
        return $this->belongsTo(Aircraft::class);
    }
}
