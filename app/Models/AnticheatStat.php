<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnticheatStat extends Model
{
    protected $fillable = [
        'server_id',
        'active_players',
        'online_players',
        'registered_players',
        'potential_cheaters',
        'banned_players',
        'confirmed_cheaters',
        'potentials_list',
        'top_movement',
        'top_collision',
        'event_time',
    ];

    protected $casts = [
        'banned_players' => 'array',
        'confirmed_cheaters' => 'array',
        'potentials_list' => 'array',
        'top_movement' => 'array',
        'top_collision' => 'array',
        'event_time' => 'datetime',
    ];
}
