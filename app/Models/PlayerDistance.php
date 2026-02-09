<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerDistance extends Model
{
    protected $table = 'player_distance';

    protected $fillable = [
        'server_id',
        'player_uuid',
        'player_name',
        'player_faction',
        'walking_distance',
        'walking_time_seconds',
        'total_vehicle_distance',
        'total_vehicle_time_seconds',
        'vehicles',
        'is_final_log',
        'occurred_at',
    ];

    protected $casts = [
        'walking_distance' => 'decimal:2',
        'walking_time_seconds' => 'decimal:2',
        'total_vehicle_distance' => 'decimal:2',
        'total_vehicle_time_seconds' => 'decimal:2',
        'vehicles' => 'array',
        'is_final_log' => 'boolean',
        'occurred_at' => 'datetime',
    ];
}
