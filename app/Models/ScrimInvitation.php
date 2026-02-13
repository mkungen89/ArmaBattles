<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ScrimInvitation extends Model
{
    protected $fillable = [
        'scrim_match_id',
        'inviting_team_id',
        'invited_team_id',
        'proposed_time',
        'message',
        'status',
        'responded_at',
        'expires_at',
    ];

    protected $casts = [
        'proposed_time' => 'datetime',
        'responded_at' => 'datetime',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the scrim match
     */
    public function scrimMatch(): BelongsTo
    {
        return $this->belongsTo(ScrimMatch::class);
    }

    /**
     * Get the inviting team
     */
    public function invitingTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'inviting_team_id');
    }

    /**
     * Get the invited team
     */
    public function invitedTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'invited_team_id');
    }

    /**
     * Check if invitation is pending
     */
    public function isPending(): bool
    {
        return $this->status === 'pending';
    }

    /**
     * Check if invitation is accepted
     */
    public function isAccepted(): bool
    {
        return $this->status === 'accepted';
    }

    /**
     * Check if invitation is declined
     */
    public function isDeclined(): bool
    {
        return $this->status === 'declined';
    }

    /**
     * Check if invitation is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at->isPast();
    }

    /**
     * Check if invitation can be responded to
     */
    public function canRespond(): bool
    {
        return $this->isPending() && ! $this->isExpired();
    }
}
