<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Facades\Storage;

class Team extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'tag',
        'logo_url',
        'description',
        'captain_id',
        'is_active',
        'is_verified',
        'is_recruiting',
        'recruitment_message',
        'disbanded_at',
        'header_image',
        'avatar_path',
        'social_links',
        'website',
    ];

    protected $casts = [
        'is_active' => 'boolean',
        'is_verified' => 'boolean',
        'is_recruiting' => 'boolean',
        'disbanded_at' => 'datetime',
        'social_links' => 'array',
    ];

    public function getAvatarUrlAttribute(): ?string
    {
        if ($this->avatar_path) {
            return Storage::url($this->avatar_path);
        }

        return $this->logo_url;
    }

    public function getHeaderImageUrlAttribute(): ?string
    {
        if ($this->header_image) {
            return Storage::url($this->header_image);
        }

        return null;
    }

    public function captain(): BelongsTo
    {
        return $this->belongsTo(User::class, 'captain_id');
    }

    public function members(): BelongsToMany
    {
        return $this->belongsToMany(User::class, 'team_members')
            ->withPivot(['role', 'status', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function activeMembers(): BelongsToMany
    {
        return $this->members()->wherePivot('status', 'active');
    }

    public function tournaments(): BelongsToMany
    {
        return $this->belongsToMany(Tournament::class, 'tournament_registrations')
            ->withPivot(['status', 'seed', 'approved_at'])
            ->withTimestamps();
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function invitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function pendingInvitations(): HasMany
    {
        return $this->invitations()->where('status', 'pending')->where('expires_at', '>', now());
    }

    public function applications(): HasMany
    {
        return $this->hasMany(TeamApplication::class);
    }

    public function pendingApplications(): HasMany
    {
        return $this->applications()->where('status', 'pending');
    }

    public function matchesAsTeam1(): HasMany
    {
        return $this->hasMany(TournamentMatch::class, 'team1_id');
    }

    public function matchesAsTeam2(): HasMany
    {
        return $this->hasMany(TournamentMatch::class, 'team2_id');
    }

    public function wonMatches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class, 'winner_id');
    }

    public function scrimsAsTeam1(): HasMany
    {
        return $this->hasMany(ScrimMatch::class, 'team1_id');
    }

    public function scrimsAsTeam2(): HasMany
    {
        return $this->hasMany(ScrimMatch::class, 'team2_id');
    }

    public function wonScrims(): HasMany
    {
        return $this->hasMany(ScrimMatch::class, 'winner_id');
    }

    public function scrimInvitationsSent(): HasMany
    {
        return $this->hasMany(ScrimInvitation::class, 'inviting_team_id');
    }

    public function scrimInvitationsReceived(): HasMany
    {
        return $this->hasMany(ScrimInvitation::class, 'invited_team_id');
    }

    public function isUserMember(User $user): bool
    {
        return $this->activeMembers()->where('user_id', $user->id)->exists();
    }

    public function isUserCaptainOrOfficer(User $user): bool
    {
        return $this->activeMembers()
            ->where('user_id', $user->id)
            ->whereIn('team_members.role', ['captain', 'officer'])
            ->exists();
    }

    public function getMemberCountAttribute(): int
    {
        return $this->activeMembers()->count();
    }

    public function getStatusBadgeAttribute(): string
    {
        if (!$this->is_active) {
            return 'bg-red-500/20 text-red-400';
        }
        if ($this->is_verified) {
            return 'bg-green-500/20 text-green-400';
        }
        return 'bg-gray-500/20 text-gray-400';
    }

    public function getStatusTextAttribute(): string
    {
        if (!$this->is_active) {
            return 'Disbanded';
        }
        if ($this->is_verified) {
            return 'Verified';
        }
        return 'Active';
    }

    // ==================== Game Statistics ====================

    public function getAggregatedGameStats(): array
    {
        $memberUuids = $this->activeMembers()
            ->whereNotNull('player_uuid')
            ->pluck('player_uuid')
            ->toArray();

        if (empty($memberUuids)) {
            return [
                'member_count' => 0,
                'total_kills' => 0, 'total_deaths' => 0, 'total_headshots' => 0,
                'total_playtime_hours' => 0, 'avg_kills' => 0, 'avg_deaths' => 0, 'avg_kd' => 0,
            ];
        }

        $stats = \Illuminate\Support\Facades\DB::table('player_stats')
            ->whereIn('player_uuid', $memberUuids)
            ->selectRaw('COUNT(*) as member_count')
            ->selectRaw('COALESCE(SUM(kills), 0) as total_kills')
            ->selectRaw('COALESCE(SUM(deaths), 0) as total_deaths')
            ->selectRaw('COALESCE(SUM(headshots), 0) as total_headshots')
            ->selectRaw('COALESCE(SUM(playtime_seconds), 0) as total_playtime_seconds')
            ->first();

        $memberCount = $stats->member_count ?: 1;

        return [
            'member_count' => $stats->member_count,
            'total_kills' => (int) $stats->total_kills,
            'total_deaths' => (int) $stats->total_deaths,
            'total_headshots' => (int) $stats->total_headshots,
            'total_playtime_hours' => round($stats->total_playtime_seconds / 3600, 1),
            'avg_kills' => round($stats->total_kills / $memberCount, 1),
            'avg_deaths' => round($stats->total_deaths / $memberCount, 1),
            'avg_kd' => $stats->total_deaths > 0 ? round($stats->total_kills / $stats->total_deaths, 2) : $stats->total_kills,
        ];
    }

    // ==================== Tournament Statistics ====================

    public function getStatistics(): array
    {
        $totalMatches = TournamentMatch::where(function ($q) {
            $q->where('team1_id', $this->id)->orWhere('team2_id', $this->id);
        })->where('status', 'completed')->count();

        $wins = TournamentMatch::where('winner_id', $this->id)->count();
        $losses = $totalMatches - $wins;

        $tournamentsParticipated = $this->tournaments()
            ->wherePivot('status', 'approved')
            ->count();

        $tournamentsWon = Tournament::where('winner_team_id', $this->id)->count();

        return [
            'total_matches' => $totalMatches,
            'wins' => $wins,
            'losses' => $losses,
            'win_rate' => $totalMatches > 0 ? round(($wins / $totalMatches) * 100, 1) : 0,
            'tournaments_participated' => $tournamentsParticipated,
            'tournaments_won' => $tournamentsWon,
            'current_streak' => $this->calculateStreak(),
        ];
    }

    protected function calculateStreak(): array
    {
        $recentMatches = TournamentMatch::where(function ($q) {
            $q->where('team1_id', $this->id)->orWhere('team2_id', $this->id);
        })
        ->where('status', 'completed')
        ->orderByDesc('completed_at')
        ->limit(10)
        ->get();

        if ($recentMatches->isEmpty()) {
            return ['type' => 'none', 'count' => 0];
        }

        $type = $recentMatches->first()->winner_id === $this->id ? 'win' : 'loss';
        $count = 0;

        foreach ($recentMatches as $match) {
            $won = $match->winner_id === $this->id;
            if (($type === 'win' && $won) || ($type === 'loss' && !$won)) {
                $count++;
            } else {
                break;
            }
        }

        return ['type' => $type, 'count' => $count];
    }

    public function getRecentForm(int $limit = 5): array
    {
        $recentMatches = TournamentMatch::where(function ($q) {
            $q->where('team1_id', $this->id)->orWhere('team2_id', $this->id);
        })
        ->where('status', 'completed')
        ->orderByDesc('completed_at')
        ->limit($limit)
        ->get();

        return $recentMatches->map(function ($match) {
            return $match->winner_id === $this->id ? 'W' : 'L';
        })->toArray();
    }
}
