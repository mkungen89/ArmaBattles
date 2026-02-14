<?php

namespace App\Console\Commands;

use App\Models\ContentCreator;
use App\Services\Streaming\TwitchStreamService;
use App\Services\Streaming\YouTubeStreamService;
use App\Services\Streaming\KickStreamService;
use Illuminate\Console\Command;

class CheckCreatorsLiveStatus extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'creators:check-live';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Check live status for all content creators';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $this->info('Checking live status for all creators...');

        $creators = ContentCreator::where('is_verified', true)
            ->whereNotNull('channel_url')
            ->get();

        $liveCount = 0;
        $twitchService = new TwitchStreamService();
        $youtubeService = new YouTubeStreamService();
        $kickService = new KickStreamService();

        foreach ($creators as $creator) {
            $isLive = false;
            $liveData = [];

            // Check platform based on creator's platform field
            switch ($creator->platform) {
                case 'twitch':
                    $username = $this->extractUsername($creator->channel_url, 'twitch');
                    $result = $twitchService->isLive($username);
                    if ($result['is_live']) {
                        $isLive = true;
                        $liveData = array_merge($result, ['platform' => 'twitch']);
                    }
                    break;

                case 'youtube':
                    $channelId = $this->extractChannelId($creator->channel_url);
                    $result = $youtubeService->isLive($channelId);
                    if ($result['is_live']) {
                        $isLive = true;
                        $liveData = array_merge($result, ['platform' => 'youtube']);
                    }
                    break;

                case 'kick':
                    $username = $this->extractUsername($creator->channel_url, 'kick');
                    $result = $kickService->isLive($username);
                    if ($result['is_live']) {
                        $isLive = true;
                        $liveData = array_merge($result, ['platform' => 'kick']);
                    }
                    break;
            }

            // Update creator
            if ($isLive) {
                $creator->update([
                    'is_live' => true,
                    'live_platform' => $liveData['platform'],
                    'live_title' => $liveData['title'] ?? null,
                    'live_viewers' => $liveData['viewers'] ?? 0,
                    'live_started_at' => $liveData['started_at'] ?? now(),
                    'live_checked_at' => now(),
                    'last_live_at' => now(),
                ]);
                $liveCount++;
                $this->line("✓ {$creator->channel_name} is LIVE on {$liveData['platform']} ({$liveData['viewers']} viewers)");
            } else {
                // Only update if previously live
                if ($creator->is_live) {
                    $creator->update([
                        'is_live' => false,
                        'live_platform' => null,
                        'live_title' => null,
                        'live_viewers' => null,
                        'live_started_at' => null,
                        'live_checked_at' => now(),
                    ]);
                } else {
                    $creator->update(['live_checked_at' => now()]);
                }
            }
        }

        $this->info("✓ Checked {$creators->count()} creators. {$liveCount} currently live.");

        return Command::SUCCESS;
    }

    /**
     * Extract username from URL
     */
    protected function extractUsername(string $url, string $platform): string
    {
        // Remove trailing slash
        $url = rtrim($url, '/');

        // Extract username from URL
        if ($platform === 'twitch') {
            // https://twitch.tv/username
            return basename($url);
        }

        if ($platform === 'kick') {
            // https://kick.com/username
            return basename($url);
        }

        return $url;
    }

    /**
     * Extract YouTube channel ID from URL
     */
    protected function extractChannelId(string $url): string
    {
        // https://youtube.com/@username or /channel/UC...
        if (strpos($url, '/channel/') !== false) {
            $parts = explode('/channel/', $url);
            return basename($parts[1]);
        }

        if (strpos($url, '/@') !== false) {
            // For @username format, we'd need to resolve to channel ID
            // For now, return the username with @ removed
            return str_replace('@', '', basename($url));
        }

        return basename($url);
    }
}
