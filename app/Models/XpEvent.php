<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class XpEvent extends Model
{
    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'player_id',
        'player_faction',
        'reward_type',
        'reward_type_raw',
        'xp_amount',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
