<?php

namespace App\Services\Streaming;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class YouTubeStreamService
{
    protected ?string $apiKey;

    public function __construct()
    {
        $this->apiKey = config('services.youtube.api_key');
    }

    /**
     * Get channel information (name, bio, subscribers)
     * Supports both channel IDs and @username format
     */
    public function getChannelInfo(string $channelIdOrUsername): ?array
    {
        if (empty($this->apiKey)) {
            return null;
        }

        try {
            // If it's a username (doesn't start with UC), try to resolve it first
            if (!str_starts_with($channelIdOrUsername, 'UC')) {
                // Try to get channel by forUsername
                $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'snippet,statistics',
                    'forHandle' => $channelIdOrUsername, // New API parameter for @username
                    'key' => $this->apiKey,
                ]);

                // Fallback to forUsername for older API versions
                if (!$response->successful() || empty($response->json('items'))) {
                    $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                        'part' => 'snippet,statistics',
                        'forUsername' => $channelIdOrUsername,
                        'key' => $this->apiKey,
                    ]);
                }
            } else {
                // It's a channel ID, use it directly
                $response = Http::get('https://www.googleapis.com/youtube/v3/channels', [
                    'part' => 'snippet,statistics',
                    'id' => $channelIdOrUsername,
                    'key' => $this->apiKey,
                ]);
            }

            if (!$response->successful() || empty($response->json('items'))) {
                return null;
            }

            $channel = $response->json('items.0');

            return [
                'channel_name' => $channel['snippet']['title'] ?? null,
                'bio' => $channel['snippet']['description'] ?? null,
                'follower_count' => (int) ($channel['statistics']['subscriberCount'] ?? 0),
            ];

        } catch (\Exception $e) {
            Log::error('YouTube channel info error for ' . $channelIdOrUsername . ': ' . $e->getMessage());
            return null;
        }
    }

    /**
     * Check if a YouTube channel is live
     */
    public function isLive(string $channelId): array
    {
        if (empty($this->apiKey)) {
            return ['is_live' => false];
        }

        try {
            $response = Http::get('https://www.googleapis.com/youtube/v3/search', [
                'part' => 'snippet',
                'channelId' => $channelId,
                'eventType' => 'live',
                'type' => 'video',
                'key' => $this->apiKey,
            ]);

            if (!$response->successful()) {
                return ['is_live' => false];
            }

            $items = $response->json('items', []);

            if (empty($items)) {
                return ['is_live' => false];
            }

            $stream = $items[0];

            // Get video details for viewer count
            $videoId = $stream['id']['videoId'] ?? null;
            $viewerCount = 0;

            if ($videoId) {
                $videoResponse = Http::get('https://www.googleapis.com/youtube/v3/videos', [
                    'part' => 'liveStreamingDetails',
                    'id' => $videoId,
                    'key' => $this->apiKey,
                ]);

                if ($videoResponse->successful()) {
                    $viewerCount = $videoResponse->json('items.0.liveStreamingDetails.concurrentViewers', 0);
                }
            }

            return [
                'is_live' => true,
                'title' => $stream['snippet']['title'] ?? null,
                'viewers' => (int) $viewerCount,
                'started_at' => $stream['snippet']['publishedAt'] ?? null,
                'video_id' => $videoId,
            ];

        } catch (\Exception $e) {
            Log::error('YouTube API error for ' . $channelId . ': ' . $e->getMessage());
            return ['is_live' => false];
        }
    }
}
