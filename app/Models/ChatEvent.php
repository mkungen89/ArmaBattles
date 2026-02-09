<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ChatEvent extends Model
{
    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'player_id',
        'message',
        'channel',
        'occurred_at',
    ];

    protected $casts = [
        'occurred_at' => 'datetime',
    ];
}
