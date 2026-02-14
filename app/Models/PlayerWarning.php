<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class PlayerWarning extends Model
{
    protected $fillable = [
        'user_id',
        'moderator_id',
        'warning_type',
        'reason',
        'evidence',
        'severity',
        'auto_ban_triggered',
        'expires_at',
    ];

    protected $casts = [
        'auto_ban_triggered' => 'boolean',
        'expires_at' => 'datetime',
    ];

    public function user()
    {
        return $this->belongsTo(User::class);
    }

    public function moderator()
    {
        return $this->belongsTo(User::class, 'moderator_id');
    }

    public function scopeActive($query)
    {
        return $query->where(function ($q) {
            $q->whereNull('expires_at')
                ->orWhere('expires_at', '>', now());
        });
    }

    public function scopeSeverity($query, string $severity)
    {
        return $query->where('severity', $severity);
    }

    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    public static function getWarningTypes(): array
    {
        return [
            'spam' => 'Spam',
            'toxicity' => 'Toxic Behavior',
            'cheating_accusation' => 'Cheating Accusation',
            'inappropriate_behavior' => 'Inappropriate Behavior',
        ];
    }
}
