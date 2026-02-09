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
}
