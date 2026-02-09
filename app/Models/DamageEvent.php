<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class DamageEvent extends Model
{
    protected $fillable = [
        'server_id',
        'damage_type',
        'damage_amount',
        'hit_zone_name',
        'killer_name',
        'killer_uuid',
        'killer_id',
        'killer_faction',
        'victim_name',
        'victim_uuid',
        'victim_id',
        'victim_faction',
        'weapon_name',
        'distance',
        'is_friendly_fire',
        'occurred_at',
    ];

    protected $casts = [
        'damage_amount' => 'decimal:4',
        'distance' => 'decimal:2',
        'is_friendly_fire' => 'boolean',
        'occurred_at' => 'datetime',
    ];
}
