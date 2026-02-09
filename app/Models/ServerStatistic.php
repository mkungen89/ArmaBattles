<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ServerStatistic extends Model
{
    use HasFactory;

    protected $fillable = [
        'server_id',
        'server_session_id',
        'players',
        'max_players',
        'status',
        'recorded_at',
    ];

    protected $casts = [
        'players' => 'integer',
        'max_players' => 'integer',
        'recorded_at' => 'datetime',
    ];

    /**
     * Get the server this statistic belongs to
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the session this statistic belongs to
     */
    public function session(): BelongsTo
    {
        return $this->belongsTo(ServerSession::class, 'server_session_id');
    }

    /**
     * Get utilization percentage
     */
    public function getUtilizationAttribute(): float
    {
        if ($this->max_players === 0) return 0;
        return round(($this->players / $this->max_players) * 100, 1);
    }
}
