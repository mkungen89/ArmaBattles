<?php

namespace App\Services\Streaming;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class KickStreamService
{
    /**
     * Get channel information (name, bio, followers)
     * Kick has a public API without authentication
     */
    public function getChannelInfo(string $username): ?array
    {
        try {
            $response = Http::get("https://kick.com/api/v2/channels/{$username}");

            if (!$response->successful()) {
                return null;
            }

            $data = $response->json();

            return [
                'channel_name' => $data['user']['username'] ?? $username,
                'bio' => $data['user']['bio'] ?? null,
                'follower_count' => (int) ($data['followers_count'] ?? 0),
            ];

        } catch (\Exception $e) {
            Log::error('Kick channel info error for ' . $username . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a Kick channel is live
     * Kick has a public API without authentication
     */
    public function isLive(string $username): array
    {
        try {
            $response = Http::get("https://kick.com/api/v2/channels/{$username}");

            if (!$response->successful()) {
                return ['is_live' => false];
            }

            $data = $response->json();
            $livestream = $data['livestream'] ?? null;

            if (!$livestream) {
                return ['is_live' => false];
            }

            return [
                'is_live' => true,
                'title' => $livestream['session_title'] ?? null,
                'viewers' => $livestream['viewer_count'] ?? 0,
                'started_at' => $livestream['created_at'] ?? null,
                'category' => $livestream['category']['name'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Kick API error for ' . $username . ': ' . $e->getMessage());
            return ['is_live' => false];
        }
    }
}
