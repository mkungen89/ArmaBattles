<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameServerStatus extends Model
{
    protected $table = 'server_status';

    public $timestamps = false;

    protected $fillable = [
        'server_id',
        'server_name',
        'map',
        'players',
        'max_players',
        'ping',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'server_id' => 'integer',
            'players' => 'integer',
            'max_players' => 'integer',
            'ping' => 'integer',
            'timestamp' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
