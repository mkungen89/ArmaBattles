<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;

class Achievement extends Model
{
    use HasFactory;
    protected $fillable = [
        'slug',
        'name',
        'description',
        'icon',
        'badge_path',
        'badge_svg_url',
        'preset_badge',
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
     * Check if this is a legendary achievement
     */
    public function isLegendary(): bool
    {
        $legendaryMax = (float) site_setting('achievement_rarity_legendary_max', 5);
        return $this->rarity_percentage < $legendaryMax;
    }

    /**
     * Check if this is an epic achievement
     */
    public function isEpic(): bool
    {
        $epicMax = (float) site_setting('achievement_rarity_epic_max', 10);
        return $this->rarity_percentage < $epicMax;
    }

    /**
     * Check if this is a rare achievement
     */
    public function isRare(): bool
    {
        $rareMax = (float) site_setting('achievement_rarity_rare_max', 25);
        return $this->rarity_percentage < $rareMax;
    }

    /**
     * Get badge URL or fallback to icon
     * Priority: badge_svg_url → preset_badge → badge_path → null
     */
    public function getBadgeUrlAttribute(): ?string
    {
        if ($this->badge_svg_url) {
            return $this->badge_svg_url;
        }

        if ($this->preset_badge) {
            return asset('images/Achivements/'.$this->preset_badge);
        }

        if ($this->badge_path) {
            return asset('storage/'.$this->badge_path);
        }

        return null;
    }

    /**
     * Get rarity badge color
     */
    public function getRarityColorAttribute(): string
    {
        $rarity = $this->rarity_percentage;
        $legendaryMax = (float) site_setting('achievement_rarity_legendary_max', 5);
        $epicMax = (float) site_setting('achievement_rarity_epic_max', 10);
        $rareMax = (float) site_setting('achievement_rarity_rare_max', 25);
        $commonMax = (float) site_setting('achievement_rarity_common_max', 50);

        if ($rarity < $legendaryMax) {
            return 'from-purple-500 to-pink-500'; // Legendary
        } elseif ($rarity < $epicMax) {
            return 'from-yellow-500 to-orange-500'; // Epic
        } elseif ($rarity < $rareMax) {
            return 'from-blue-500 to-cyan-500'; // Rare
        } elseif ($rarity < $commonMax) {
            return 'from-green-500 to-emerald-500'; // Common
        } else {
            return 'from-gray-500 to-gray-600'; // Very Common
        }
    }

    /**
     * Get rarity label
     */
    public function getRarityLabelAttribute(): string
    {
        $rarity = $this->rarity_percentage;
        $legendaryMax = (float) site_setting('achievement_rarity_legendary_max', 5);
        $epicMax = (float) site_setting('achievement_rarity_epic_max', 10);
        $rareMax = (float) site_setting('achievement_rarity_rare_max', 25);
        $commonMax = (float) site_setting('achievement_rarity_common_max', 50);

        if ($rarity < $legendaryMax) {
            return 'Legendary';
        } elseif ($rarity < $epicMax) {
            return 'Epic';
        } elseif ($rarity < $rareMax) {
            return 'Rare';
        } elseif ($rarity < $commonMax) {
            return 'Common';
        } else {
            return 'Very Common';
        }
    }
}
