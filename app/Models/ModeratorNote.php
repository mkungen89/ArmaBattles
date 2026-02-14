<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class ModeratorNote extends Model
{
    protected $fillable = [
        'user_id',
        'moderator_id',
        'note',
        'category',
        'is_flagged',
    ];

    protected $casts = [
        'is_flagged' => 'boolean',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function scopeFlagged($query)
    {
        return $query->where('is_flagged', true);
    }

    public function scopeCategory($query, string $category)
    {
        return $query->where('category', $category);
    }

    public static function getCategories(): array
    {
        return [
            'positive' => 'Positive',
            'negative' => 'Negative',
            'neutral' => 'Neutral',
            'watchlist' => 'Watchlist',
        ];
    }
}
