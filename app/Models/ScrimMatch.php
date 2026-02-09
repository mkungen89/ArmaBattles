<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasOne;

class ScrimMatch extends Model
{
    protected $fillable = [
        'team1_id',
        'team2_id',
        'created_by',
        'scheduled_at',
        'status',
        'team1_score',
        'team2_score',
        'winner_id',
        'server_id',
        'password',
        'notes',
        'map',
        'duration_minutes',
        'completed_at',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'completed_at' => 'datetime',
        'team1_score' => 'integer',
        'team2_score' => 'integer',
        'duration_minutes' => 'integer',
    ];

    /**
     * Get team 1
     */
    public function team1(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    /**
     * Get team 2
     */
    public function team2(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    /**
     * Get the user who created this scrim
     */
    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    /**
     * Get the winning team
     */
    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    /**
     * Get the server
     */
    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    /**
     * Get the invitation for this scrim
     */
    public function invitation(): HasOne
    {
        return $this->hasOne(ScrimInvitation::class);
    }

    /**
     * Check if scrim is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if scrim is scheduled
     */
    public function isScheduled(): bool
    {
        return $this->status === 'scheduled';
    }

    /**
     * Check if scrim is in progress
     */
    public function isInProgress(): bool
    {
        return $this->status === 'in_progress';
    }

    /**
     * Check if scrim is completed
     */
    public function isCompleted(): bool
    {
        return $this->status === 'completed';
    }

    /**
     * Check if scrim is cancelled
     */
    public function isCancelled(): bool
    {
        return $this->status === 'cancelled';
    }

    /**
     * Get status badge color
     */
    public function getStatusColorAttribute(): string
    {
        return match($this->status) {
            'pending' => 'text-yellow-400',
            'scheduled' => 'text-blue-400',
            'in_progress' => 'text-green-400',
            'completed' => 'text-gray-400',
            'cancelled' => 'text-red-400',
            default => 'text-gray-400',
        };
    }

    /**
     * Get status label
     */
    public function getStatusLabelAttribute(): string
    {
        return match($this->status) {
            'pending' => 'Pending',
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => 'Unknown',
        };
    }
}
