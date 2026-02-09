<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchGame extends Model
{
    use HasFactory;

    protected $fillable = [
        'match_id',
        'game_number',
        'map',
        'winner_id',
        'team1_score',
        'team2_score',
        'screenshot_urls',
        'notes',
    ];

    protected $casts = [
        'screenshot_urls' => 'array',
    ];

    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function getScoreDisplayAttribute(): string
    {
        return ($this->team1_score ?? 0) . ' - ' . ($this->team2_score ?? 0);
    }
}
