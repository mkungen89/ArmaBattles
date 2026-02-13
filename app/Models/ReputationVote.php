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
        'created_at',
        'updated_at',
    ];

    protected $casts = [
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
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
     * Check if vote can be changed (within configured cooldown period)
     */
    public function canBeChanged(): bool
    {
        $cooldownHours = (int) site_setting('reputation_vote_cooldown_hours', 24);
        return $this->created_at->diffInHours(now()) < $cooldownHours;
    }
}
