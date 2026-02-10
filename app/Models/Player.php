<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\HasMany;

class Player extends Model
{
    protected $fillable = [
        'uuid',
        'player_name',
        'total_playtime',
        'kills',
        'deaths',
        'xp',
        'distance_traveled',
        'score',
        'sessions',
        'server_id',
        'first_seen',
        'last_seen',
    ];

    protected function casts(): array
    {
        return [
            'total_playtime' => 'integer',
            'kills' => 'integer',
            'deaths' => 'integer',
            'xp' => 'integer',
            'distance_traveled' => 'decimal:2',
            'score' => 'integer',
            'sessions' => 'integer',
            'server_id' => 'integer',
            'first_seen' => 'datetime',
            'last_seen' => 'datetime',
        ];
    }

    public function killEvents(): HasMany
    {
        return $this->hasMany(PlayerKill::class, 'player_uuid', 'uuid');
    }

    public function sessionEvents(): HasMany
    {
        return $this->hasMany(PlayerSession::class, 'player_name', 'player_name');
    }

    public function getKdRatioAttribute(): float
    {
        if ($this->deaths === 0) {
            return (float) $this->kills;
        }

        return round($this->kills / $this->deaths, 2);
    }

    public function getFormattedPlaytimeAttribute(): string
    {
        $hours = floor($this->total_playtime / 3600);
        $minutes = floor(($this->total_playtime % 3600) / 60);

        return "{$hours}h {$minutes}m";
    }
}
