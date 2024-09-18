<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Aircraft extends Model
{
    use HasFactory;

    public function aircraftLogs(): HasMany
    {
        return $this->hasMany(AircraftLog::class);
    }

    public function getFormattedName()
    {
        $output = "";
        $output .= $this->manufacturer ?? "";
        $output .= $this->model ? " ".$this->model : "";
        $output .= $this->varient ? "-".$this->varient : "";
        return $output;
    }
}
