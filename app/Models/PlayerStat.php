<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerStat extends Model
{
    protected $table = 'player_stats';

    protected $fillable = [
        'server_id',
        'player_uuid',
        'player_name',
        'kills',
        'deaths',
        'headshots',
        'hits_head',
        'hits_torso',
        'hits_arms',
        'hits_legs',
        'total_hits',
        'total_damage_dealt',
        'team_kills',
        'total_roadkills',
        'playtime_seconds',
        'total_distance',
        'shots_fired',
        'grenades_thrown',
        'heals_given',
        'heals_received',
        'bases_captured',
        'supplies_delivered',
        'vehicles_destroyed',
        'xp_total',
        'last_seen_at',
        'level',
        'level_xp',
        'achievement_points',
    ];

    protected $casts = [
        'last_seen_at' => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_uuid', 'uuid');
    }

    public function achievements()
    {
        return $this->belongsToMany(Achievement::class, 'player_achievements', 'player_uuid', 'achievement_id', 'player_uuid', 'id')
            ->withPivot('earned_at')
            ->withTimestamps();
    }

    /**
     * Get player's level tier info
     */
    public function getTierAttribute(): array
    {
        return app(\App\Services\PlayerLevelService::class)->getTierForLevel($this->level ?? 1);
    }

    /**
     * Get progress to next level (0-100%)
     */
    public function getLevelProgressAttribute(): float
    {
        return app(\App\Services\PlayerLevelService::class)->getProgressToNextLevel($this);
    }

    /**
     * Get XP needed for next level
     */
    public function getXpToNextLevelAttribute(): int
    {
        return app(\App\Services\PlayerLevelService::class)->getXpToNextLevel($this);
    }

    /**
     * Get formatted level with tier
     */
    public function getLevelDisplayAttribute(): string
    {
        $tier = $this->tier;

        return "Level {$this->level} - {$tier['label']}";
    }
}
