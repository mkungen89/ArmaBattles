<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerHealing extends Model
{
    protected $table = 'player_healing_rjs';

    protected $fillable = [
        'server_id',
        'healer_name',
        'healer_uuid',
        'healer_id',
        'healer_platform',
        'healer_faction',
        'patient_name',
        'patient_uuid',
        'patient_id',
        'patient_platform',
        'patient_faction',
        'patient_is_ai',
        'action',
        'item',
        'is_self',
        'occurred_at',
    ];

    protected $casts = [
        'patient_is_ai' => 'boolean',
        'is_self' => 'boolean',
        'occurred_at' => 'datetime',
    ];
}
