<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class GameEvent extends Model
{
    protected $table = 'game_events';

    public $timestamps = false;

    protected $fillable = [
        'server_id',
        'event_type',
        'payload',
        'event_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'server_id' => 'integer',
            'payload' => 'array',
            'event_timestamp' => 'datetime',
            'created_at' => 'datetime',
        ];
    }
}
