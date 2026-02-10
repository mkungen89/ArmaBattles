<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class DiscordRichPresence extends Model
{
    protected $table = 'discord_rich_presence';

    protected $fillable = [
        'user_id',
        'discord_user_id',
        'current_activity',
        'activity_details',
        'server_id',
        'tournament_id',
        'started_at',
        'enabled',
        'last_updated_at',
    ];

    protected $casts = [
        'activity_details' => 'array',
        'started_at' => 'datetime',
        'last_updated_at' => 'datetime',
        'enabled' => 'boolean',
    ];

    // Relationships

    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    // Helper Methods

    public function isPlaying(): bool
    {
        return $this->current_activity === 'playing';
    }

    public function isWatching(): bool
    {
        return $this->current_activity === 'watching_tournament';
    }

    public function isBrowsing(): bool
    {
        return $this->current_activity === 'browsing';
    }

    public function getActivityStatus(): string
    {
        return match ($this->current_activity) {
            'playing' => $this->activity_details['server_name'] ?? 'Playing Arma Reforger',
            'watching_tournament' => $this->activity_details['tournament_name'] ?? 'Watching Tournament',
            'browsing' => 'Browsing Community',
            default => 'Online',
        };
    }

    public function getActivityState(): ?string
    {
        return match ($this->current_activity) {
            'playing' => $this->activity_details['player_count'] ?? null,
            'watching_tournament' => $this->activity_details['match_status'] ?? null,
            default => null,
        };
    }

    public function getActivityLargeImage(): string
    {
        return match ($this->current_activity) {
            'playing' => 'arma_reforger_logo',
            'watching_tournament' => 'tournament_icon',
            default => 'community_logo',
        };
    }

    public function getActivitySmallImage(): ?string
    {
        return match ($this->current_activity) {
            'playing' => 'playing_icon',
            'watching_tournament' => 'watching_icon',
            default => null,
        };
    }

    public function needsUpdate(): bool
    {
        if (! $this->last_updated_at) {
            return true;
        }

        // Update every 30 seconds
        return $this->last_updated_at->addSeconds(30)->isPast();
    }

    public function getElapsedTime(): int
    {
        if (! $this->started_at) {
            return 0;
        }

        return now()->diffInSeconds($this->started_at);
    }
}
