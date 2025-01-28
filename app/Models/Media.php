<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class Media extends Model
{
    use HasFactory;

    protected $fillable = [
        'path',
        'type',
        'sighting_id',
        'thumbnail_path',
        'status',
        'raw_video_path',
        'size',
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the post that owns the comment.
     */
    public function sighting(): BelongsTo
    {
        return $this->belongsTo(Sighting::class);
    }

    public function isVideo(): bool
    {
        return $this->type == \App\Enums\Media::VIDEO->value;
    }

    public function isProcessing(): bool
    {
        return $this->status == "processing";
    }
}
