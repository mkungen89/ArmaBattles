<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Connection extends Model
{
    protected $fillable = [
        'server_id',
        'event_type',
        'player_name',
        'player_uuid',
        'player_id',
        'player_platform',
        'player_faction',
        'profile_name',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
