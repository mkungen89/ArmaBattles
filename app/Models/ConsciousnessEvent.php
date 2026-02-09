<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ConsciousnessEvent extends Model
{
    protected $fillable = [
        'server_id',
        'event_type',
        'player_name',
        'player_uuid',
        'player_id',
        'player_faction',
        'state',
        'position',
        'knocker_name',
        'knocker_uuid',
        'knocker_id',
        'knocker_faction',
        'is_friendly_knock',
        'is_self_knock',
        'occurred_at',
    ];

    protected $casts = [
        'position' => 'array',
        'is_friendly_knock' => 'boolean',
        'is_self_knock' => 'boolean',
        'occurred_at' => 'datetime',
    ];
}
