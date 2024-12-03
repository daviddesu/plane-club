<?php
namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class LightroomAsset extends Model
{
    protected $fillable = [
        'user_id',
        'aircraft_log_id',
        'asset_id',
        'album_id',
        'file_name',
        'media_type',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function aircraftLog()
    {
        return $this->belongsTo(AircraftLog::class);
    }
}
