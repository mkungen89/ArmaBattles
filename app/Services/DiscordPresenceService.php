<?php

namespace App\Services;

use App\Events\DiscordPresenceUpdated;
use App\Models\DiscordRichPresence;
use App\Models\Server;
use App\Models\Tournament;
use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class DiscordPresenceService
{
    /**
     * Update presence for a user playing on a server
     */
    public function updatePlayingPresence(User $user, Server $server, array $details = []): void
    {
        if (! $user->hasDiscordPresenceEnabled()) {
            return;
        }

        $activityDetails = [
            'server_name' => $server->name,
            'server_id' => $server->id,
            'player_count' => $server->current_players.'/'.$server->max_players,
            'map' => $server->map ?? 'Unknown',
            ...$details,
        ];

        $this->updateOrCreatePresence($user, [
            'current_activity' => 'playing',
            'activity_details' => $activityDetails,
            'server_id' => $server->id,
            'tournament_id' => null,
            'started_at' => now(),
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Update presence for a user watching a tournament
     */
    public function updateWatchingPresence(User $user, Tournament $tournament, array $details = []): void
    {
        if (! $user->hasDiscordPresenceEnabled()) {
            return;
        }

        $activityDetails = [
            'tournament_name' => $tournament->name,
            'tournament_id' => $tournament->id,
            'match_status' => $details['match_status'] ?? 'Viewing bracket',
            'team_count' => $tournament->approvedRegistrationsCount(),
            ...$details,
        ];

        $this->updateOrCreatePresence($user, [
            'current_activity' => 'watching_tournament',
            'activity_details' => $activityDetails,
            'server_id' => null,
            'tournament_id' => $tournament->id,
            'started_at' => now(),
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Update presence for a user browsing the community
     */
    public function updateBrowsingPresence(User $user, array $details = []): void
    {
        if (! $user->hasDiscordPresenceEnabled()) {
            return;
        }

        $activityDetails = [
            'page' => $details['page'] ?? 'Community',
            ...$details,
        ];

        $this->updateOrCreatePresence($user, [
            'current_activity' => 'browsing',
            'activity_details' => $activityDetails,
            'server_id' => null,
            'tournament_id' => null,
            'started_at' => now(),
            'last_updated_at' => now(),
        ]);
    }

    /**
     * Clear presence for a user
     */
    public function clearPresence(User $user): void
    {
        $presence = $user->discordPresence;

        if ($presence) {
            $presence->update([
                'current_activity' => null,
                'activity_details' => null,
                'server_id' => null,
                'tournament_id' => null,
                'started_at' => null,
                'last_updated_at' => now(),
            ]);
        }
    }

    /**
     * Enable Discord presence for a user
     */
    public function enablePresence(User $user, ?string $discordUserId = null, ?string $activityType = null): void
    {
        $data = [
            'enabled' => true,
            'discord_user_id' => $discordUserId,
        ];

        if ($activityType) {
            $data['activity_type'] = $activityType;
            $data['current_activity'] = $activityType;
        }

        $this->updateOrCreatePresence($user, $data);
    }

    /**
     * Disable Discord presence for a user
     */
    public function disablePresence(User $user): void
    {
        $presence = $user->discordPresence;

        if ($presence) {
            $presence->update(['enabled' => false]);
            $this->clearPresence($user);
        }
    }

    /**
     * Get formatted presence payload for Discord RPC
     */
    public function getDiscordPayload(DiscordRichPresence $presence): array
    {
        if (! $presence->enabled || ! $presence->current_activity) {
            return [];
        }

        $payload = [
            'details' => $presence->getActivityStatus(),
            'state' => $presence->getActivityState(),
            'timestamps' => [
                'start' => $presence->started_at?->timestamp,
            ],
            'assets' => [
                'large_image' => $presence->getActivityLargeImage(),
                'large_text' => 'Arma Reforger Community',
            ],
        ];

        if ($smallImage = $presence->getActivitySmallImage()) {
            $payload['assets']['small_image'] = $smallImage;
            $payload['assets']['small_text'] = ucfirst($presence->current_activity);
        }

        // Add buttons for joining/viewing
        if ($presence->isPlaying() && $presence->server_id) {
            $payload['buttons'] = [
                [
                    'label' => 'View Server',
                    'url' => route('servers.show', $presence->server_id),
                ],
            ];
        } elseif ($presence->isWatching() && $presence->tournament_id) {
            $payload['buttons'] = [
                [
                    'label' => 'View Tournament',
                    'url' => route('tournaments.show', $presence->tournament_id),
                ],
            ];
        }

        return $payload;
    }

    /**
     * Get all users with active Discord presence
     */
    public function getActivePresences(): \Illuminate\Database\Eloquent\Collection
    {
        return DiscordRichPresence::with(['user', 'server', 'tournament'])
            ->where('enabled', true)
            ->whereNotNull('current_activity')
            ->get();
    }

    /**
     * Batch update presences that need refreshing
     */
    public function refreshStalePresences(): int
    {
        $stalePresences = DiscordRichPresence::where('enabled', true)
            ->whereNotNull('current_activity')
            ->where(function ($query) {
                $query->whereNull('last_updated_at')
                    ->orWhere('last_updated_at', '<', now()->subSeconds(30));
            })
            ->get();

        $updated = 0;

        foreach ($stalePresences as $presence) {
            try {
                $presence->update(['last_updated_at' => now()]);
                $updated++;
            } catch (\Exception $e) {
                Log::error('Failed to refresh Discord presence', [
                    'user_id' => $presence->user_id,
                    'error' => $e->getMessage(),
                ]);
            }
        }

        return $updated;
    }

    /**
     * Update or create presence record
     */
    private function updateOrCreatePresence(User $user, array $data): void
    {
        $presence = DiscordRichPresence::updateOrCreate(
            ['user_id' => $user->id],
            array_merge($data, ['user_id' => $user->id])
        );

        // Clear cache for this user's presence
        Cache::forget("discord_presence_{$user->id}");

        // Broadcast presence update via WebSocket
        DiscordPresenceUpdated::dispatch(
            $user->id,
            $presence->getActivityStatus(),
            $presence->getActivityState(),
            $presence->started_at?->toIso8601String(),
            $presence->enabled
        );
    }
}
