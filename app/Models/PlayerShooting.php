<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerShooting extends Model
{
    protected $table = 'player_shooting';

    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'player_id',
        'player_platform',
        'player_faction',
        'weapons',
        'total_rounds',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
