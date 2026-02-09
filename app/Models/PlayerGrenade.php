<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerGrenade extends Model
{
    protected $table = 'player_grenades';

    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'player_id',
        'player_platform',
        'player_faction',
        'grenade_type',
        'position',
        'occurred_at',
    ];

    protected $casts = [
        'position' => 'array',
        'occurred_at' => 'datetime',
    ];
}
