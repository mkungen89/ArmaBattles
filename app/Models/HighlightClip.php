<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class HighlightClip extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'url',
        'platform',
        'author',
        'description',
        'thumbnail_url',
        'duration_seconds',
        'votes',
        'status',
        'is_featured',
        'featured_at',
    ];

    protected $casts = [
        'votes' => 'integer',
        'is_featured' => 'boolean',
        'featured_at' => 'datetime',
    ];

    /**
     * Get the user who submitted the clip
     */
    public function user(): BelongsTo
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get all votes for this clip
     */
    public function clipVotes(): HasMany
    {
        return $this->hasMany(ClipVote::class, 'clip_id');
    }

    /**
     * Get platform badge color
     */
    public function getPlatformColorAttribute(): string
    {
        return match ($this->platform) {
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
        return match ($this->platform) {
            'twitch' => 'Twitch',
            'youtube' => 'YouTube',
            'tiktok' => 'TikTok',
            'kick' => 'Kick',
            default => ucfirst($this->platform),
        };
    }

    /**
     * Get embed URL for the clip
     */
    public function getEmbedUrlAttribute(): ?string
    {
        if ($this->platform === 'youtube') {
            // Extract video ID from various YouTube URL formats
            $videoId = $this->extractYouTubeId();
            if ($videoId) {
                return "https://www.youtube.com/embed/{$videoId}";
            }
        } elseif ($this->platform === 'twitch') {
            // Extract clip slug from Twitch URL
            $clipSlug = $this->extractTwitchClipSlug();
            if ($clipSlug) {
                return "https://clips.twitch.tv/embed?clip={$clipSlug}&parent=".parse_url(config('app.url'), PHP_URL_HOST);
            }
        }

        return null;
    }

    /**
     * Extract YouTube video ID from URL
     */
    private function extractYouTubeId(): ?string
    {
        // youtube.com/watch?v=VIDEO_ID
        if (preg_match('/[?&]v=([^&]+)/', $this->url, $matches)) {
            return $matches[1];
        }

        // youtu.be/VIDEO_ID
        if (preg_match('/youtu\.be\/([^?]+)/', $this->url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Extract Twitch clip slug from URL
     */
    private function extractTwitchClipSlug(): ?string
    {
        // clips.twitch.tv/SLUG or twitch.tv/username/clip/SLUG
        if (preg_match('/clips\.twitch\.tv\/([^\/\?]+)/', $this->url, $matches)) {
            return $matches[1];
        }

        if (preg_match('/twitch\.tv\/[^\/]+\/clip\/([^\/\?]+)/', $this->url, $matches)) {
            return $matches[1];
        }

        return null;
    }

    /**
     * Check if user has voted for this clip
     */
    public function hasUserVoted(int $userId): bool
    {
        return $this->clipVotes()->where('user_id', $userId)->exists();
    }

    /**
     * Increment vote count
     */
    public function incrementVotes(): void
    {
        $this->increment('votes');
    }

    /**
     * Decrement vote count
     */
    public function decrementVotes(): void
    {
        $this->decrement('votes');
    }

    /**
     * Recalculate votes based on vote_type
     */
    public function recalculateVotes(): void
    {
        $upvotes = $this->clipVotes()->where('vote_type', 'upvote')->count();
        $downvotes = $this->clipVotes()->where('vote_type', 'downvote')->count();
        $this->votes = $upvotes - $downvotes;
        $this->save();

        // Check for auto-approval if still pending
        $this->checkAutoApproval();
    }

    /**
     * Check if clip should be auto-approved based on vote threshold
     */
    public function checkAutoApproval(): void
    {
        // Only auto-approve pending clips
        if ($this->status !== 'pending') {
            return;
        }

        $threshold = (int) site_setting('clip_approval_threshold', 0);

        // If threshold is 0, auto-approval is disabled
        if ($threshold === 0) {
            return;
        }

        // Auto-approve if votes meet or exceed threshold
        if ($this->votes >= $threshold) {
            $this->update(['status' => 'approved']);

            // Notify the user
            if ($this->user) {
                $this->user->notify(new \App\Notifications\VideoApprovedNotification($this));
            }

            // Invalidate clip of the week cache
            \Cache::forget('clip_of_the_week');
        }
    }

    /**
     * Scope to get featured clips
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope to get clips by platform
     */
    public function scopePlatform($query, string $platform)
    {
        return $query->where('platform', $platform);
    }

    /**
     * Scope to order by votes
     */
    public function scopePopular($query)
    {
        return $query->orderByDesc('votes');
    }

    /**
     * Scope to order by recent
     */
    public function scopeRecent($query)
    {
        return $query->orderByDesc('created_at');
    }
}
