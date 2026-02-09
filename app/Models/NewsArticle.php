<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class NewsArticle extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'content',
        'excerpt',
        'featured_image',
        'author_id',
        'status',
        'is_pinned',
        'published_at',
    ];

    protected $casts = [
        'is_pinned' => 'boolean',
        'published_at' => 'datetime',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($article) {
            if (empty($article->slug)) {
                $slug = Str::slug($article->title);
                $original = $slug;
                $count = 1;
                while (static::where('slug', $slug)->exists()) {
                    $slug = $original . '-' . $count++;
                }
                $article->slug = $slug;
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function author(): BelongsTo
    {
        return $this->belongsTo(User::class, 'author_id');
    }

    public function comments(): HasMany
    {
        return $this->hasMany(NewsComment::class, 'article_id');
    }

    public function hoorahs(): HasMany
    {
        return $this->hasMany(NewsHoorah::class, 'article_id');
    }

    public function scopePublished($query)
    {
        return $query->where('status', 'published')->whereNotNull('published_at');
    }

    public function scopePinnedFirst($query)
    {
        return $query->orderByDesc('is_pinned');
    }

    public function getFeaturedImageUrlAttribute(): ?string
    {
        if (!$this->featured_image) {
            return null;
        }
        return \Illuminate\Support\Facades\Storage::url($this->featured_image);
    }

    public function getReadingTimeAttribute(): int
    {
        $wordCount = str_word_count(strip_tags($this->content));
        return max(1, (int) ceil($wordCount / 200));
    }

    public function getHoorahCountAttribute(): int
    {
        return $this->hoorahs()->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-yellow-500/20 text-yellow-400',
            'published' => 'bg-green-500/20 text-green-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }

    public function isHoorahedBy(?User $user): bool
    {
        if (!$user) {
            return false;
        }
        return $this->hoorahs()->where('user_id', $user->id)->exists();
    }
}
