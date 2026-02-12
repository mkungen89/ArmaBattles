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
     * Check if user has "Trusted Player" status
     */
    public function isTrusted(): bool
    {
        $trustedTier = (int) site_setting('reputation_tier_trusted', 100);
        return $this->total_score >= $trustedTier;
    }

    /**
     * Check if user has concerning low reputation
     */
    public function isFlagged(): bool
    {
        $poorTier = (int) site_setting('reputation_tier_poor', -50);
        return $this->total_score < $poorTier;
    }

    /**
     * Get reputation badge color
     */
    public function getBadgeColorAttribute(): string
    {
        $score = $this->total_score;
        $trustedTier = (int) site_setting('reputation_tier_trusted', 100);
        $goodTier = (int) site_setting('reputation_tier_good', 50);
        $poorTier = (int) site_setting('reputation_tier_poor', -50);

        if ($score >= $trustedTier) {
            return 'text-green-400'; // Trusted
        } elseif ($score >= $goodTier) {
            return 'text-blue-400'; // Good
        } elseif ($score >= 0) {
            return 'text-gray-400'; // Neutral
        } elseif ($score >= $poorTier) {
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
        $trustedTier = (int) site_setting('reputation_tier_trusted', 100);
        $goodTier = (int) site_setting('reputation_tier_good', 50);
        $poorTier = (int) site_setting('reputation_tier_poor', -50);

        if ($score >= $trustedTier) {
            return 'Trusted Player';
        } elseif ($score >= $goodTier) {
            return 'Good Standing';
        } elseif ($score >= 0) {
            return 'Neutral';
        } elseif ($score >= $poorTier) {
            return 'Poor Standing';
        } else {
            return 'Flagged';
        }
    }
}
