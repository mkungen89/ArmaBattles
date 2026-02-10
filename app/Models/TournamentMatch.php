<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\HasMany;

class TournamentMatch extends Model
{
    use HasFactory;

    protected $fillable = [
        'tournament_id',
        'round',
        'match_number',
        'bracket',
        'team1_id',
        'team2_id',
        'winner_id',
        'winner_goes_to',
        'loser_goes_to',
        'team1_score',
        'team2_score',
        'status',
        'match_type',
        'scheduled_at',
        'check_in_opens_at',
        'check_in_closes_at',
        'team1_checked_in',
        'team2_checked_in',
        'started_at',
        'completed_at',
        'notes',
    ];

    protected $casts = [
        'scheduled_at' => 'datetime',
        'check_in_opens_at' => 'datetime',
        'check_in_closes_at' => 'datetime',
        'team1_checked_in' => 'boolean',
        'team2_checked_in' => 'boolean',
        'started_at' => 'datetime',
        'completed_at' => 'datetime',
    ];

    public function tournament(): BelongsTo
    {
        return $this->belongsTo(Tournament::class);
    }

    public function team1(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team1_id');
    }

    public function team2(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'team2_id');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_id');
    }

    public function games(): HasMany
    {
        return $this->hasMany(MatchGame::class, 'match_id')->orderBy('game_number');
    }

    public function checkIns(): HasMany
    {
        return $this->hasMany(MatchCheckIn::class, 'match_id');
    }

    public function reports(): HasMany
    {
        return $this->hasMany(MatchReport::class, 'match_id')->orderBy('reported_at', 'desc');
    }

    public function winnerMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'winner_goes_to');
    }

    public function loserMatch(): BelongsTo
    {
        return $this->belongsTo(TournamentMatch::class, 'loser_goes_to');
    }

    public function getRoundLabelAttribute(): string
    {
        if ($this->bracket === 'grand_final') {
            return 'Grand Final';
        }

        if ($this->bracket === 'losers') {
            return 'Losers Round '.abs($this->round);
        }

        $totalRounds = $this->tournament->matches()
            ->where('bracket', 'main')
            ->max('round');

        return match ($totalRounds - $this->round) {
            0 => 'Final',
            1 => 'Semi-Final',
            2 => 'Quarter-Final',
            default => "Round {$this->round}",
        };
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'bg-gray-500/20 text-gray-400',
            'scheduled' => 'bg-blue-500/20 text-blue-400',
            'in_progress' => 'bg-yellow-500/20 text-yellow-400',
            'completed' => 'bg-green-500/20 text-green-400',
            'disputed' => 'bg-red-500/20 text-red-400',
            'cancelled' => 'bg-gray-500/20 text-gray-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'pending' => 'Pending',
            'scheduled' => 'Scheduled',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'disputed' => 'Disputed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getMatchTypeTextAttribute(): string
    {
        return match ($this->match_type) {
            'best_of_1' => 'Best of 1',
            'best_of_3' => 'Best of 3',
            'best_of_5' => 'Best of 5',
            default => $this->match_type,
        };
    }

    public function getScoreDisplayAttribute(): string
    {
        if ($this->team1_score === null && $this->team2_score === null) {
            return 'vs';
        }

        return ($this->team1_score ?? 0).' - '.($this->team2_score ?? 0);
    }

    public function isByeMatch(): bool
    {
        return ($this->team1_id && ! $this->team2_id) || (! $this->team1_id && $this->team2_id);
    }

    public function isReady(): bool
    {
        return $this->team1_id && $this->team2_id && $this->status === 'pending';
    }

    public function canCheckIn(): bool
    {
        if (! $this->check_in_opens_at || ! $this->check_in_closes_at) {
            return false;
        }

        $now = now();

        return $now->gte($this->check_in_opens_at) && $now->lte($this->check_in_closes_at);
    }

    public function isCheckInOpen(): bool
    {
        return $this->canCheckIn();
    }

    public function hasTeamCheckedIn(Team $team): bool
    {
        if ($team->id === $this->team1_id) {
            return $this->team1_checked_in;
        }
        if ($team->id === $this->team2_id) {
            return $this->team2_checked_in;
        }

        return false;
    }

    public function checkInTeam(Team $team, User $user): bool
    {
        if (! $this->canCheckIn()) {
            return false;
        }

        if ($team->id !== $this->team1_id && $team->id !== $this->team2_id) {
            return false;
        }

        if ($this->hasTeamCheckedIn($team)) {
            return false;
        }

        MatchCheckIn::create([
            'match_id' => $this->id,
            'team_id' => $team->id,
            'user_id' => $user->id,
            'checked_in_at' => now(),
        ]);

        if ($team->id === $this->team1_id) {
            $this->update(['team1_checked_in' => true]);
        } else {
            $this->update(['team2_checked_in' => true]);
        }

        return true;
    }

    public function bothTeamsCheckedIn(): bool
    {
        return $this->team1_checked_in && $this->team2_checked_in;
    }
}
