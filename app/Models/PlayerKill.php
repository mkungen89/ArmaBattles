<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerKill extends Model
{
    protected $fillable = [
        'server_id',
        'killer_name',
        'killer_uuid',
        'killer_faction',
        'victim_type',
        'victim_name',
        'victim_uuid',
        'weapon_name',
        'weapon_type',
        'weapon_source',
        'sight_name',
        'attachments',
        'grenade_type',
        'kill_distance',
        'is_team_kill',
        'is_roadkill',
        'event_type',
        'killed_at',
    ];

    protected $casts = [
        'is_team_kill' => 'boolean',
        'is_roadkill' => 'boolean',
        'kill_distance' => 'decimal:2',
        'killed_at' => 'datetime',
    ];

    public function scopeRoadkills($query)
    {
        return $query->where('is_roadkill', true);
    }

    public function killer(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'killer_uuid', 'uuid');
    }

    public function victim(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'victim_uuid', 'uuid');
    }
}
