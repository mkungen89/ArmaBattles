<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class DiscordWebhookService
{
    public function send(string $content, array $embeds = []): bool
    {
        $url = site_setting('discord_webhook_url');
        if (empty($url)) {
            return false;
        }

        try {
            $payload = [];
            if ($content) {
                $payload['content'] = $content;
            }
            if (! empty($embeds)) {
                $payload['embeds'] = $embeds;
            }

            $response = Http::timeout(5)->post($url, $payload);

            return $response->successful();
        } catch (\Exception $e) {
            Log::warning('Discord webhook failed: '.$e->getMessage());

            return false;
        }
    }

    public function sendEmbed(string $title, string $description, int $color = 0x22C55E, array $fields = []): bool
    {
        $embed = [
            'title' => $title,
            'description' => $description,
            'color' => $color,
            'timestamp' => now()->toIso8601String(),
        ];

        if (! empty($fields)) {
            $embed['fields'] = $fields;
        }

        return $this->send('', [$embed]);
    }

    /**
     * Send a tournament result notification
     */
    public function sendTournamentResult(array $tournament, array $winner): bool
    {
        if (! site_setting('discord_notify_tournament_results', false)) {
            return false;
        }

        $fields = [
            [
                'name' => 'Winner',
                'value' => $winner['name'],
                'inline' => true,
            ],
            [
                'name' => 'Format',
                'value' => ucfirst(str_replace('_', ' ', $tournament['format'])),
                'inline' => true,
            ],
            [
                'name' => 'Teams',
                'value' => (string) $tournament['team_count'],
                'inline' => true,
            ],
        ];

        $description = "Tournament **{$tournament['name']}** has concluded!";
        if (isset($tournament['url'])) {
            $description .= "\n\n[View Results]({$tournament['url']})";
        }

        return $this->sendEmbed(
            '🏆 Tournament Completed',
            $description,
            0x00ff00, // Green
            $fields
        );
    }

    /**
     * Send a match result notification
     */
    public function sendMatchResult(array $match): bool
    {
        if (! site_setting('discord_notify_match_results', false)) {
            return false;
        }

        $team1Score = $match['team1_score'] ?? 0;
        $team2Score = $match['team2_score'] ?? 0;
        $winner = $team1Score > $team2Score ? $match['team1_name'] : $match['team2_name'];

        $description = "**{$match['team1_name']}** {$team1Score} - {$team2Score} **{$match['team2_name']}**\n\n🏆 Winner: **{$winner}**";

        $fields = [];
        if (isset($match['tournament_name'])) {
            $fields[] = [
                'name' => 'Tournament',
                'value' => $match['tournament_name'],
                'inline' => true,
            ];
        }
        if (isset($match['round'])) {
            $fields[] = [
                'name' => 'Round',
                'value' => $match['round'],
                'inline' => true,
            ];
        }

        return $this->sendEmbed(
            '⚔️ Match Result',
            $description,
            0xffa500, // Orange
            $fields
        );
    }

    /**
     * Send a notable kill notification
     */
    public function sendNotableKill(array $kill): bool
    {
        if (! site_setting('discord_notify_notable_kills', false)) {
            return false;
        }

        $distance = $kill['distance'] ?? 0;
        $minDistance = (int) site_setting('discord_notable_kill_distance', 500);

        // Only send if distance meets threshold
        if ($distance < $minDistance) {
            return false;
        }

        $description = "**{$kill['killer_name']}** eliminated **{$kill['victim_name']}**";

        $fields = [
            [
                'name' => 'Distance',
                'value' => number_format($distance).'m',
                'inline' => true,
            ],
            [
                'name' => 'Weapon',
                'value' => $kill['weapon'] ?? 'Unknown',
                'inline' => true,
            ],
        ];

        if ($kill['headshot'] ?? false) {
            $fields[] = [
                'name' => 'Type',
                'value' => '💀 Headshot',
                'inline' => true,
            ];
        }

        return $this->sendEmbed(
            '🎯 Notable Kill!',
            $description,
            0xff0000, // Red
            $fields
        );
    }

    /**
     * Send a server restart notification
     */
    public function sendServerRestart(string $serverName, int $delayMinutes): bool
    {
        if (! site_setting('discord_notify_server_restart', true)) {
            return false;
        }

        return $this->sendEmbed(
            '🔄 Server Restart Scheduled',
            "**{$serverName}** will restart in **{$delayMinutes} minutes**",
            0xffff00 // Yellow
        );
    }

    /**
     * Check if webhook is configured
     */
    public function isConfigured(): bool
    {
        return ! empty(site_setting('discord_webhook_url'));
    }
}
