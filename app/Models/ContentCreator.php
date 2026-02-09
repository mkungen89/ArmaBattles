<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class ContentCreator extends Model
{
    protected $fillable = [
        'user_id',
        'platform',
        'channel_url',
        'channel_name',
        'is_verified',
        'is_live',
        'follower_count',
        'viewer_count',
        'stream_title',
        'stream_thumbnail_url',
        'bio',
        'last_live_at',
        'verified_at',
    ];

    protected $casts = [
        'is_verified' => 'boolean',
        'is_live' => 'boolean',
        'follower_count' => 'integer',
        'viewer_count' => 'integer',
        'last_live_at' => 'datetime',
        'verified_at' => 'datetime',
    ];

    /**
     * Get the user
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get platform badge color
     */
    public function getPlatformColorAttribute(): string
    {
        return match($this->platform) {
            'twitch' => 'text-purple-400',
            'youtube' => 'text-red-400',
            'tiktok' => 'text-pink-400',
            'kick' => 'text-green-400',
            default => 'text-gray-400',
        };
    }

    /**
     * Get platform name
     */
    public function getPlatformNameAttribute(): string
    {
        return match($this->platform) {
            'twitch' => 'Twitch',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'kick' => 'Kick',
            default => ucfirst($this->platform),
        };
    }

    /**
     * Get embed code for the stream
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if (!$this->is_live) {
            return null;
        }

        // Extract channel identifier from URL
        $identifier = $this->getChannelIdentifier();

        return match($this->platform) {
            'twitch' => "https://player.twitch.tv/?channel={$identifier}&parent=" . parse_url(config('app.url'), PHP_URL_HOST),
            'youtube' => $this->extractYouTubeEmbedUrl(),
            default => null,
        };
    }

    /**
     * Extract channel identifier from URL
     */
    private function getChannelIdentifier(): ?string
    {
        if ($this->platform === 'twitch') {
            // https://twitch.tv/username -> username
            if (preg_match('/twitch\.tv\/([^\/]+)/', $this->channel_url, $matches)) {
                return $matches[1];
            }
        }

        return null;
    }

    /**
     * Extract YouTube embed URL
     */
    private function extractYouTubeEmbedUrl(): ?string
    {
        // For YouTube, we'd need the live video ID, which requires API integration
        // For now, return null - this can be implemented when YouTube API is added
        return null;
    }

    /**
     * Check if creator was recently live (within 7 days)
     */
    public function wasRecentlyLive(): bool
    {
        return $this->last_live_at && $this->last_live_at->isAfter(now()->subDays(7));
    }

    /**
     * Scope to get only verified creators
     */
    public function scopeVerified($query)
    {
        return $query->where('is_verified', true);
    }

    /**
     * Scope to get only live creators
     */
    public function scopeLive($query)
    {
        return $query->where('is_live', true);
    }

    /**
     * Scope to filter by platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }
}
