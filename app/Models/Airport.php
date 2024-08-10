<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;


class Airport extends Model
{
    use HasFactory;

    public function aircraftLogs(): HasMany
    {
        return $this->hasMany(AircraftLog::class);
    }
}
