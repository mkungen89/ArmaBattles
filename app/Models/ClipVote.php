<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ClipVote extends Model
{
    protected $fillable = [
        'user_id',
        'clip_id',
        'vote_type',
    ];

    /**
     * Get the user who voted
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the clip that was voted for
     */
    public function clip(): BelongsTo
    {
        return $this->belongsTo(HighlightClip::class, 'clip_id');
    }
}
