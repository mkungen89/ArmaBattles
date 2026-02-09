<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'badge_path',
        'color',
        'category',
        'stat_field',
        'threshold',
        'points',
        'sort_order',
    ];

    protected $casts = [
        'threshold' => 'integer',
        'points' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Players who have earned this achievement.
     */
    public function players(): BelongsToMany
    {
        return $this->belongsToMany(PlayerStat::class, 'player_achievements', 'achievement_id', 'player_uuid', 'id', 'player_uuid')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Calculate rarity percentage (how many players have unlocked this)
     */
    public function getRarityPercentageAttribute(): float
    {
        $totalPlayers = PlayerStat::count();
        if ($totalPlayers === 0) {
            return 0.0;
        }

        $playersWithAchievement = $this->players()->count();
        return round(($playersWithAchievement / $totalPlayers) * 100, 2);
    }

    /**
     * Check if this is a rare achievement (< 1% unlock rate)
     */
    public function isRare(): bool
    {
        return $this->rarity_percentage < 1.0;
    }

    /**
     * Check if this is an ultra rare achievement (< 0.1% unlock rate)
     */
    public function isUltraRare(): bool
    {
        return $this->rarity_percentage < 0.1;
    }

    /**
     * Get badge URL or fallback to icon
     */
    public function getBadgeUrlAttribute(): ?string
    {
        if ($this->badge_path) {
            return asset('storage/' . $this->badge_path);
        }
        return null;
    }

    /**
     * Get rarity badge color
     */
    public function getRarityColorAttribute(): string
    {
        $rarity = $this->rarity_percentage;

        if ($rarity < 0.1) {
            return 'from-purple-500 to-pink-500'; // Ultra Rare
        } elseif ($rarity < 1.0) {
            return 'from-yellow-500 to-orange-500'; // Rare
        } elseif ($rarity < 10.0) {
            return 'from-blue-500 to-cyan-500'; // Uncommon
        } else {
            return 'from-gray-500 to-gray-600'; // Common
        }
    }

    /**
     * Get rarity label
     */
    public function getRarityLabelAttribute(): string
    {
        $rarity = $this->rarity_percentage;

        if ($rarity < 0.1) {
            return 'Ultra Rare';
        } elseif ($rarity < 1.0) {
            return 'Rare';
        } elseif ($rarity < 10.0) {
            return 'Uncommon';
        } else {
            return 'Common';
        }
    }
}
