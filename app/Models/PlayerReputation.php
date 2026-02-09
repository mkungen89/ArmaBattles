<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerReputation extends Model
{
    protected $fillable = [
        'user_id',
        'total_score',
        'positive_votes',
        'negative_votes',
        'teamwork_count',
        'leadership_count',
        'sportsmanship_count',
        'last_decay_at',
    ];

    protected function casts(): array
    {
        return [
            'total_score' => 'integer',
            'positive_votes' => 'integer',
            'negative_votes' => 'integer',
            'teamwork_count' => 'integer',
            'leadership_count' => 'integer',
            'sportsmanship_count' => 'integer',
            'last_decay_at' => 'datetime',
        ];
    }

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function votes(): HasMany
    {
        return $this->hasMany(ReputationVote::class, 'target_id', 'user_id');
    }

    /**
     * Check if user has "Trusted Player" status (100+ rep)
     */
    public function isTrusted(): bool
    {
        return $this->total_score >= 100;
    }

    /**
     * Check if user has concerning low reputation (-50 or lower)
     */
    public function isFlagged(): bool
    {
        return $this->total_score <= -50;
    }

    /**
     * Get reputation badge color
     */
    public function getBadgeColorAttribute(): string
    {
        $score = $this->total_score;

        if ($score >= 100) {
            return 'text-green-400'; // Trusted
        } elseif ($score >= 50) {
            return 'text-blue-400'; // Good
        } elseif ($score >= 0) {
            return 'text-gray-400'; // Neutral
        } elseif ($score >= -50) {
            return 'text-yellow-400'; // Poor
        } else {
            return 'text-red-400'; // Flagged
        }
    }

    /**
     * Get reputation label
     */
    public function getLabelAttribute(): string
    {
        $score = $this->total_score;

        if ($score >= 100) {
            return 'Trusted Player';
        } elseif ($score >= 50) {
            return 'Good Standing';
        } elseif ($score >= 0) {
            return 'Neutral';
        } elseif ($score >= -50) {
            return 'Poor Standing';
        } else {
            return 'Flagged';
        }
    }
}
