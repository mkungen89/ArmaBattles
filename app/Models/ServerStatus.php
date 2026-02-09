<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ServerStatus extends Model
{
    protected $table = 'server_status';

    protected $fillable = [
        'server_id',
        'server_name',
        'map',
        'players',
        'max_players',
        'ping',
        'last_updated',
    ];

    protected $casts = [
        'last_updated' => 'datetime',
    ];
}
