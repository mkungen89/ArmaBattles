<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class SupplyDelivery extends Model
{
    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'player_id',
        'player_faction',
        'position',
        'estimated_amount',
        'xp_awarded',
        'occurred_at',
    ];

    protected $casts = [
        'position' => 'array',
        'occurred_at' => 'datetime',
    ];

    public function player()
    {
        return $this->belongsTo(Player::class, 'player_uuid', 'uuid');
    }
}
