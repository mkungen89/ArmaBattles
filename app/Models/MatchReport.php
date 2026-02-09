<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class MatchReport extends Model
{
    protected $fillable = [
        'match_id',
        'referee_id',
        'winning_team_id',
        'team1_score',
        'team2_score',
        'notes',
        'incidents',
        'status',
        'reported_at',
    ];

    protected function casts(): array
    {
        return [
            'incidents' => 'array',
            'reported_at' => 'datetime',
        ];
    }

    public function match(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'match_id');
    }

    public function referee(): BelongsTo
    {
        return $this->belongsTo(User::class, 'referee_id');
    }

    public function winningTeam(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winning_team_id');
    }
}
