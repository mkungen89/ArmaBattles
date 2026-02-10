<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class ServerSession extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'session_number',
        'started_at',
        'ended_at',
        'last_seen_at',
        'peak_players',
        'average_players',
        'total_snapshots',
        'is_current',
    ];

    protected $casts = [
        'session_number' => 'integer',
        'peak_players' => 'integer',
        'average_players' => 'float',
        'total_snapshots' => 'integer',
        'is_current' => 'boolean',
        'started_at' => 'datetime',
        'ended_at' => 'datetime',
        'last_seen_at' => 'datetime',
    ];

    /**
     * Get the server this session belongs to
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get statistics for this session
     */
    public function statistics(): HasMany
    {
        return $this->hasMany(ServerStatistic::class);
    }

    /**
     * Get session duration
     */
    public function getDurationAttribute(): string
    {
        $end = $this->ended_at ?? now();

        return $this->started_at->diffForHumans($end, ['parts' => 2, 'short' => true]);
    }

    /**
     * Get formatted uptime
     */
    public function getUptimeAttribute(): string
    {
        if (! $this->started_at) {
            return 'Unknown';
        }

        $diff = $this->started_at->diff(now());

        $parts = [];
        if ($diff->d > 0) {
            $parts[] = $diff->d.'d';
        }
        if ($diff->h > 0) {
            $parts[] = $diff->h.'h';
        }
        if ($diff->i > 0) {
            $parts[] = $diff->i.'m';
        }

        return implode(' ', $parts) ?: '< 1m';
    }
}
