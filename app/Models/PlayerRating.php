<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class PlayerRating extends Model
{
    protected $fillable = [
        'user_id',
        'player_uuid',
        'rating',
        'rating_deviation',
        'volatility',
        'rank_tier',
        'ranked_kills',
        'ranked_deaths',
        'games_played',
        'placement_games',
        'is_placed',
        'peak_rating',
        'season_start_rating',
        'current_season',
        'opted_in_at',
        'last_rated_at',
    ];

    protected function casts(): array
    {
        return [
            'rating' => 'decimal:2',
            'rating_deviation' => 'decimal:2',
            'volatility' => 'decimal:6',
            'ranked_kills' => 'integer',
            'ranked_deaths' => 'integer',
            'games_played' => 'integer',
            'placement_games' => 'integer',
            'is_placed' => 'boolean',
            'peak_rating' => 'decimal:2',
            'season_start_rating' => 'decimal:2',
            'current_season' => 'integer',
            'opted_in_at' => 'datetime',
            'last_rated_at' => 'datetime',
        ];
    }

    // Tier thresholds
    public const TIERS = [
        'elite'    => ['min' => 2200, 'label' => 'Elite',    'color' => 'text-red-400',    'bg' => 'bg-red-500/20 border-red-500/30',    'icon' => '/images/tiers/elite.png'],
        'master'   => ['min' => 2000, 'label' => 'Master',   'color' => 'text-amber-400',  'bg' => 'bg-amber-500/20 border-amber-500/30', 'icon' => '/images/tiers/master.png'],
        'diamond'  => ['min' => 1800, 'label' => 'Diamond',  'color' => 'text-cyan-400',   'bg' => 'bg-cyan-500/20 border-cyan-500/30',   'icon' => '/images/tiers/diamond.png'],
        'platinum' => ['min' => 1600, 'label' => 'Platinum', 'color' => 'text-blue-300',   'bg' => 'bg-blue-500/20 border-blue-500/30',   'icon' => '/images/tiers/platinum.png'],
        'gold'     => ['min' => 1400, 'label' => 'Gold',     'color' => 'text-yellow-400', 'bg' => 'bg-yellow-500/20 border-yellow-500/30','icon' => '/images/tiers/gold.png'],
        'silver'   => ['min' => 1200, 'label' => 'Silver',   'color' => 'text-gray-300',   'bg' => 'bg-gray-500/20 border-gray-500/30',   'icon' => '/images/tiers/silver.png'],
        'bronze'   => ['min' => 0,    'label' => 'Bronze',   'color' => 'text-orange-400', 'bg' => 'bg-orange-500/20 border-orange-500/30','icon' => '/images/tiers/bronze.png'],
        'unranked' => ['min' => -1,   'label' => 'Unranked', 'color' => 'text-gray-500',   'bg' => 'bg-gray-700/20 border-gray-600/30',   'icon' => null],
    ];

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function history(): HasMany
    {
        return $this->hasMany(RatingHistory::class);
    }

    public static function calculateTier(float $rating, bool $isPlaced): string
    {
        if (!$isPlaced) {
            return 'unranked';
        }

        foreach (self::TIERS as $tier => $config) {
            if ($tier === 'unranked') {
                continue;
            }
            if ($rating >= $config['min']) {
                return $tier;
            }
        }

        return 'bronze';
    }

    public function getTierLabelAttribute(): string
    {
        return self::TIERS[$this->rank_tier]['label'] ?? 'Unranked';
    }

    public function getTierColorAttribute(): string
    {
        return self::TIERS[$this->rank_tier]['color'] ?? 'text-gray-500';
    }

    public function getTierBgAttribute(): string
    {
        return self::TIERS[$this->rank_tier]['bg'] ?? 'bg-gray-700/20 border-gray-600/30';
    }

    public function getTierIconAttribute(): ?string
    {
        return self::TIERS[$this->rank_tier]['icon'] ?? null;
    }

    public function getKdRatioAttribute(): float
    {
        if ($this->ranked_deaths === 0) {
            return (float) $this->ranked_kills;
        }

        return round($this->ranked_kills / $this->ranked_deaths, 2);
    }

    public function getConfidenceAttribute(): string
    {
        if ($this->rating_deviation <= 60) {
            return 'High';
        } elseif ($this->rating_deviation <= 120) {
            return 'Medium';
        }

        return 'Low';
    }

    public function scopeCompetitive($query)
    {
        return $query->whereNotNull('opted_in_at');
    }

    public function scopePlaced($query)
    {
        return $query->where('is_placed', true);
    }

    public function scopeRanked($query)
    {
        return $query->competitive()->placed()->orderByDesc('rating');
    }
}
