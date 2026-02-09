<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class KillLog extends Model
{
    protected $table = 'kill_logs';

    public $timestamps = false;

    protected $fillable = [
        'server_id',
        'killer_id',
        'killer_name',
        'killer_is_ai',
        'victim_id',
        'victim_name',
        'victim_is_ai',
        'weapon',
        'weapon_prefab',
        'is_friendly_fire',
        'distance',
        'killer_position',
        'victim_position',
        'event_timestamp',
    ];

    protected function casts(): array
    {
        return [
            'server_id' => 'integer',
            'killer_is_ai' => 'boolean',
            'victim_is_ai' => 'boolean',
            'is_friendly_fire' => 'boolean',
            'distance' => 'decimal:2',
            'killer_position' => 'array',
            'victim_position' => 'array',
            'event_timestamp' => 'datetime',
            'created_at' => 'datetime',
        ];
    }

    public function killer()
    {
        return $this->belongsTo(Player::class, 'killer_id', 'uuid');
    }

    public function victim()
    {
        return $this->belongsTo(Player::class, 'victim_id', 'uuid');
    }
}
