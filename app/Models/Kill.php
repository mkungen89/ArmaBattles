<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Kill extends Model
{
    protected $table = 'kills';

    protected $fillable = [
        'server_id',
        'killer_name',
        'killer_uuid',
        'killer_faction',
        'killer_id',
        'killer_platform',
        'killer_role',
        'killer_position',
        'killer_in_vehicle',
        'killer_vehicle',
        'killer_vehicle_prefab',
        'victim_name',
        'victim_uuid',
        'victim_faction',
        'victim_id',
        'victim_is_ai',
        'victim_role',
        'victim_position',
        'victim_platform',
        'ai_type',
        'weapon_name',
        'weapon_type',
        'damage_type',
        'kill_distance',
        'is_team_kill',
        'event_type',
        'occurred_at',
    ];

    protected $casts = [
        'killer_position' => 'array',
        'victim_position' => 'array',
        'killer_in_vehicle' => 'boolean',
        'victim_is_ai' => 'boolean',
        'is_team_kill' => 'boolean',
        'kill_distance' => 'decimal:2',
        'occurred_at' => 'datetime',
    ];
}
