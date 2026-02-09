<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AnticheatEvent extends Model
{
    protected $fillable = [
        'server_id',
        'event_type',
        'player_name',
        'player_id',
        'is_admin',
        'reason',
        'raw',
        'event_time',
    ];

    protected $casts = [
        'is_admin' => 'boolean',
        'event_time' => 'datetime',
    ];
}
