<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BuildingEvent extends Model
{
    protected $fillable = [
        'server_id',
        'event_type',
        'player_name',
        'player_uuid',
        'player_id',
        'player_platform',
        'player_faction',
        'composition_name',
        'composition_type',
        'prefab_id',
        'provider',
        'position',
        'occurred_at',
    ];

    protected $casts = [
        'position' => 'array',
        'occurred_at' => 'datetime',
    ];
}
