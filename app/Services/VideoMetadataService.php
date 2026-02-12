<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;

class VideoMetadataService
{
    /**
     * Fetch video metadata from URL
     */
    public function fetchMetadata(string $url): ?array
    {
        $platform = $this->detectPlatform($url);

        if (!$platform) {
            return null;
        }

        return match ($platform) {
            'youtube' => $this->fetchYouTubeMetadata($url),
            'twitch' => $this->fetchTwitchMetadata($url),
            'tiktok' => $this->fetchTikTokMetadata($url),
            'kick' => $this->fetchKickMetadata($url),
            default => null,
        };
    }

    /**
     * Detect platform from URL
     */
    private function detectPlatform(string $url): ?string
    {
        if (str_contains($url, 'youtube.com') || str_contains($url, 'youtu.be')) {
            return 'youtube';
        }

        if (str_contains($url, 'twitch.tv')) {
            return 'twitch';
        }

        if (str_contains($url, 'tiktok.com')) {
            return 'tiktok';
        }

        if (str_contains($url, 'kick.com')) {
            return 'kick';
        }

        return null;
    }

    /**
     * Fetch YouTube metadata via oEmbed
     */
    private function fetchYouTubeMetadata(string $url): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://www.youtube.com/oembed', [
                'url' => $url,
                'format' => 'json',
            ]);

            if (!$response->successful()) {
                Log::warning('YouTube oEmbed fetch failed', ['url' => $url, 'status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            return [
                'title' => $data['title'] ?? null,
                'description' => null, // oEmbed doesn't provide description
                'author' => $data['author_name'] ?? null,
                'thumbnail_url' => $data['thumbnail_url'] ?? null,
                'platform' => 'youtube',
            ];
        } catch (\Exception $e) {
            Log::error('YouTube metadata fetch error', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch Twitch metadata via oEmbed
     */
    private function fetchTwitchMetadata(string $url): ?array
    {
        try {
            $response = Http::timeout(10)->get('https://api.twitch.tv/v5/oembed', [
                'url' => $url,
            ]);

            if (!$response->successful()) {
                Log::warning('Twitch oEmbed fetch failed', ['url' => $url, 'status' => $response->status()]);
                return null;
            }

            $data = $response->json();

            return [
                'title' => $data['title'] ?? null,
                'description' => null,
                'author' => $data['author_name'] ?? null,
                'thumbnail_url' => $data['thumbnail_url'] ?? null,
                'platform' => 'twitch',
            ];
        } catch (\Exception $e) {
            Log::error('Twitch metadata fetch error', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch TikTok metadata (basic HTML parsing)
     */
    private function fetchTikTokMetadata(string $url): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            // Try to extract title from meta tags
            $title = null;
            if (preg_match('/<meta property="og:title" content="([^"]+)"/', $html, $matches)) {
                $title = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $description = null;
            if (preg_match('/<meta property="og:description" content="([^"]+)"/', $html, $matches)) {
                $description = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $thumbnail = null;
            if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) {
                $thumbnail = $matches[1];
            }

            return [
                'title' => $title,
                'description' => $description,
                'author' => null,
                'thumbnail_url' => $thumbnail,
                'platform' => 'tiktok',
            ];
        } catch (\Exception $e) {
            Log::error('TikTok metadata fetch error', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }

    /**
     * Fetch Kick metadata (basic HTML parsing)
     */
    private function fetchKickMetadata(string $url): ?array
    {
        try {
            $response = Http::timeout(10)
                ->withHeaders([
                    'User-Agent' => 'Mozilla/5.0 (Windows NT 10.0; Win64; x64) AppleWebKit/537.36',
                ])
                ->get($url);

            if (!$response->successful()) {
                return null;
            }

            $html = $response->body();

            $title = null;
            if (preg_match('/<meta property="og:title" content="([^"]+)"/', $html, $matches)) {
                $title = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $description = null;
            if (preg_match('/<meta property="og:description" content="([^"]+)"/', $html, $matches)) {
                $description = html_entity_decode($matches[1], ENT_QUOTES | ENT_HTML5, 'UTF-8');
            }

            $thumbnail = null;
            if (preg_match('/<meta property="og:image" content="([^"]+)"/', $html, $matches)) {
                $thumbnail = $matches[1];
            }

            return [
                'title' => $title,
                'description' => $description,
                'author' => null,
                'thumbnail_url' => $thumbnail,
                'platform' => 'kick',
            ];
        } catch (\Exception $e) {
            Log::error('Kick metadata fetch error', ['url' => $url, 'error' => $e->getMessage()]);
            return null;
        }
    }
}
