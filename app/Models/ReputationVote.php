<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ReputationVote extends Model
{
    protected $fillable = [
        'voter_id',
        'target_id',
        'vote_type',
        'category',
        'comment',
    ];

    public function voter(): BelongsTo
    {
        return $this->belongsTo(User::class, 'voter_id');
    }

    public function target(): BelongsTo
    {
        return $this->belongsTo(User::class, 'target_id');
    }

    /**
     * Check if vote can be changed (within 24 hours)
     */
    public function canBeChanged(): bool
    {
        return $this->created_at->diffInHours(now()) < 24;
    }
}
