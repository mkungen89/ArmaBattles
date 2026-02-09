<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GroupEvent extends Model
{
    protected $fillable = [
        'server_id',
        'event_type',
        'player_name',
        'player_uuid',
        'player_id',
        'player_faction',
        'group_name',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
