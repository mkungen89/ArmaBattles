<?php

namespace App\Services\Streaming;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class TwitchStreamService
{
    protected ?string $clientId;
    protected ?string $clientSecret;
    protected ?string $accessToken;

    public function __construct()
    {
        $this->clientId = config('services.twitch.client_id');
        $this->clientSecret = config('services.twitch.client_secret');
    }

    /**
     * Get OAuth access token
     */
    protected function getAccessToken(): ?string
    {
        return Cache::remember('twitch_access_token', 3600, function () {
            try {
                $response = Http::post('https://id.twitch.tv/oauth2/token', [
                    'client_id' => $this->clientId,
                    'client_secret' => $this->clientSecret,
                    'grant_type' => 'client_credentials',
                ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }
            } catch (\Exception $e) {
                Log::error('Twitch OAuth failed: ' . $e->getMessage());
            }

            return null;
        });
    }

    /**
     * Get channel information (name, bio, followers)
     */
    public function getChannelInfo(string $username): ?array
    {
        if (!$this->clientId || !$this->clientSecret) {
            return null;
        }

        $token = $this->getAccessToken();

        if (!$token) {
            return null;
        }

        try {
            // Get user info
            $userResponse = Http::withHeaders([
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.twitch.tv/helix/users', [
                'login' => $username,
            ]);

            if (!$userResponse->successful() || empty($userResponse->json('data'))) {
                return null;
            }

            $user = $userResponse->json('data.0');
            $userId = $user['id'];

            // Get follower count
            $followersResponse = Http::withHeaders([
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.twitch.tv/helix/channels/followers', [
                'broadcaster_id' => $userId,
            ]);

            $followerCount = 0;
            if ($followersResponse->successful()) {
                $followerCount = $followersResponse->json('total', 0);
            }

            return [
                'channel_name' => $user['display_name'] ?? $username,
                'bio' => $user['description'] ?? null,
                'follower_count' => $followerCount,
            ];

        } catch (\Exception $e) {
            Log::error('Twitch channel info error for ' . $username . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a Twitch channel is live
     */
    public function isLive(string $username): array
    {
        if (!$this->clientId || !$this->clientSecret) {
            return ['is_live' => false];
        }

        $token = $this->getAccessToken();

        if (!$token) {
            return ['is_live' => false];
        }

        try {
            // Get user ID first
            $userResponse = Http::withHeaders([
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.twitch.tv/helix/users', [
                'login' => $username,
            ]);

            if (!$userResponse->successful() || empty($userResponse->json('data'))) {
                return ['is_live' => false];
            }

            $userId = $userResponse->json('data.0.id');

            // Check if streaming
            $streamResponse = Http::withHeaders([
                'Client-ID' => $this->clientId,
                'Authorization' => 'Bearer ' . $token,
            ])->get('https://api.twitch.tv/helix/streams', [
                'user_id' => $userId,
            ]);

            if (!$streamResponse->successful()) {
                return ['is_live' => false];
            }

            $data = $streamResponse->json('data');

            if (empty($data)) {
                return ['is_live' => false];
            }

            $stream = $data[0];

            return [
                'is_live' => true,
                'title' => $stream['title'] ?? null,
                'viewers' => $stream['viewer_count'] ?? 0,
                'started_at' => $stream['started_at'] ?? null,
                'game' => $stream['game_name'] ?? null,
            ];

        } catch (\Exception $e) {
            Log::error('Twitch API error for ' . $username . ': ' . $e->getMessage());
            return ['is_live' => false];
        }
    }
}
