<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerSession extends Model
{
    protected $table = 'player_sessions';

    public $timestamps = false;

    protected $fillable = [
        'server_id',
        'player_name',
        'player_uuid',
        'event_type',
        'timestamp',
    ];

    protected function casts(): array
    {
        return [
            'server_id' => 'integer',
            'timestamp' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function player(): BelongsTo
    {
        return $this->belongsTo(Player::class, 'player_name', 'player_name');
    }
}
