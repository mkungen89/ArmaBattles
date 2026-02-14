<?php

namespace App\Services;

use App\Services\Streaming\TwitchStreamService;
use App\Services\Streaming\YouTubeStreamService;
use App\Services\Streaming\KickStreamService;
use App\Services\Streaming\TikTokStreamService;
use Illuminate\Support\Facades\Log;

class ContentCreatorInfoService
{
    protected TwitchStreamService $twitchService;
    protected YouTubeStreamService $youtubeService;
    protected KickStreamService $kickService;
    protected TikTokStreamService $tiktokService;

    public function __construct(
        TwitchStreamService $twitchService,
        YouTubeStreamService $youtubeService,
        KickStreamService $kickService,
        TikTokStreamService $tiktokService
    ) {
        $this->twitchService = $twitchService;
        $this->youtubeService = $youtubeService;
        $this->kickService = $kickService;
        $this->tiktokService = $tiktokService;
    }

    /**
     * Extract username or channel ID from URL based on platform
     */
    public function extractIdentifierFromUrl(string $url, string $platform): ?string
    {
        $url = trim($url);

        switch ($platform) {
            case 'twitch':
                // https://www.twitch.tv/username or https://twitch.tv/username
                if (preg_match('#twitch\.tv/([a-zA-Z0-9_]+)#', $url, $matches)) {
                    return $matches[1];
                }
                break;

            case 'youtube':
                // https://www.youtube.com/channel/UCxxxxx or https://youtube.com/@username
                if (preg_match('#youtube\.com/channel/([a-zA-Z0-9_-]+)#', $url, $matches)) {
                    return $matches[1];
                }
                // Handle @username format - need to resolve to channel ID
                if (preg_match('#youtube\.com/@([a-zA-Z0-9_-]+)#', $url, $matches)) {
                    // For now, return the username - this would need API call to resolve
                    return $matches[1];
                }
                // Handle /c/ or /user/ formats
                if (preg_match('#youtube\.com/(c|user)/([a-zA-Z0-9_-]+)#', $url, $matches)) {
                    return $matches[2];
                }
                break;

            case 'kick':
                // https://www.kick.com/username or https://kick.com/username
                if (preg_match('#kick\.com/([a-zA-Z0-9_-]+)#', $url, $matches)) {
                    return $matches[1];
                }
                break;

            case 'tiktok':
                // https://www.tiktok.com/@username or https://tiktok.com/@username
                if (preg_match('#tiktok\.com/@([a-zA-Z0-9_.]+)#', $url, $matches)) {
                    return $matches[1];
                }
                break;
        }

        return null;
    }

    /**
     * Fetch channel information from platform API
     */
    public function fetchChannelInfo(string $platform, string $channelUrl): ?array
    {
        $identifier = $this->extractIdentifierFromUrl($channelUrl, $platform);

        if (!$identifier) {
            Log::warning("Could not extract identifier from URL: {$channelUrl} for platform: {$platform}");
            return null;
        }

        try {
            switch ($platform) {
                case 'twitch':
                    return $this->twitchService->getChannelInfo($identifier);

                case 'youtube':
                    return $this->youtubeService->getChannelInfo($identifier);

                case 'kick':
                    return $this->kickService->getChannelInfo($identifier);

                case 'tiktok':
                    return $this->tiktokService->getChannelInfo($identifier);

                default:
                    return null;
            }
        } catch (\Exception $e) {
            Log::error("Error fetching channel info for {$platform}/{$identifier}: " . $e->getMessage());
            return null;
        }
    }
}
