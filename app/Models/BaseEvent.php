<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class BaseEvent extends Model
{
    protected $fillable = [
        'server_id',
        'event_type',
        'base_name',
        'position',
        'player_name',
        'player_uuid',
        'player_id',
        'player_faction',
        'xp_awarded',
        'capturing_faction',
        'previous_faction',
        'player_count',
        'player_ids',
        'player_names',
        'occurred_at',
    ];

    protected $casts = [
        'position' => 'array',
        'occurred_at' => 'datetime',
    ];
}
