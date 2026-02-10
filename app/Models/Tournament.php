<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Support\Str;

class Tournament extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'rules',
        'banner_url',
        'format',
        'status',
        'max_teams',
        'min_teams',
        'team_size',
        'swiss_rounds',
        'registration_starts_at',
        'registration_ends_at',
        'starts_at',
        'ends_at',
        'created_by',
        'winner_team_id',
        'server_id',
        'is_featured',
        'require_approval',
        'prize_pool',
        'stream_url',
    ];

    protected $casts = [
        'registration_starts_at' => 'datetime',
        'registration_ends_at' => 'datetime',
        'starts_at' => 'datetime',
        'ends_at' => 'datetime',
        'is_featured' => 'boolean',
        'require_approval' => 'boolean',
    ];

    protected static function boot()
    {
        parent::boot();

        static::creating(function ($tournament) {
            if (empty($tournament->slug)) {
                $tournament->slug = Str::slug($tournament->name);
            }
        });
    }

    public function getRouteKeyName(): string
    {
        return 'slug';
    }

    public function creator(): BelongsTo
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function winner(): BelongsTo
    {
        return $this->belongsTo(Team::class, 'winner_team_id');
    }

    public function server(): BelongsTo
    {
        return $this->belongsTo(Server::class);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'tournament_registrations')
            ->withPivot(['status', 'seed', 'rejection_reason', 'approved_at'])
            ->withTimestamps();
    }

    public function approvedTeams(): BelongsToMany
    {
        return $this->teams()->wherePivot('status', 'approved')->orderByPivot('seed');
    }

    public function pendingTeams(): BelongsToMany
    {
        return $this->teams()->wherePivot('status', 'pending');
    }

    public function registrations(): HasMany
    {
        return $this->hasMany(TournamentRegistration::class);
    }

    public function matches(): HasMany
    {
        return $this->hasMany(TournamentMatch::class)->orderBy('round')->orderBy('match_number');
    }

    public function isRegistrationOpen(): bool
    {
        if ($this->status !== 'registration_open') {
            return false;
        }

        $now = now();

        if ($this->registration_starts_at && $now->lt($this->registration_starts_at)) {
            return false;
        }

        if ($this->registration_ends_at && $now->gt($this->registration_ends_at)) {
            return false;
        }

        return true;
    }

    public function canTeamRegister(Team $team): bool
    {
        if (! $this->isRegistrationOpen()) {
            return false;
        }

        if ($this->approvedTeams()->count() + $this->pendingTeams()->count() >= $this->max_teams) {
            return false;
        }

        if ($this->teams()->where('team_id', $team->id)->exists()) {
            return false;
        }

        if ($team->activeMembers()->count() < $this->team_size) {
            return false;
        }

        return true;
    }

    public function getStatusBadgeAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'bg-gray-500/20 text-gray-400',
            'registration_open' => 'bg-green-500/20 text-green-400',
            'registration_closed' => 'bg-yellow-500/20 text-yellow-400',
            'in_progress' => 'bg-blue-500/20 text-blue-400',
            'completed' => 'bg-purple-500/20 text-purple-400',
            'cancelled' => 'bg-red-500/20 text-red-400',
            default => 'bg-gray-500/20 text-gray-400',
        };
    }

    public function getStatusTextAttribute(): string
    {
        return match ($this->status) {
            'draft' => 'Draft',
            'registration_open' => 'Registration Open',
            'registration_closed' => 'Registration Closed',
            'in_progress' => 'In Progress',
            'completed' => 'Completed',
            'cancelled' => 'Cancelled',
            default => ucfirst($this->status),
        };
    }

    public function getFormatTextAttribute(): string
    {
        return match ($this->format) {
            'single_elimination' => 'Single Elimination',
            'double_elimination' => 'Double Elimination',
            'round_robin' => 'Round Robin',
            'swiss' => 'Swiss System',
            default => ucfirst(str_replace('_', ' ', $this->format)),
        };
    }

    public function getTeamCountAttribute(): int
    {
        return $this->approvedTeams()->count();
    }

    public function getCurrentRoundAttribute(): ?int
    {
        return $this->matches()
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->min('round');
    }

    public function getCompletedMatchesCountAttribute(): int
    {
        return $this->matches()->where('status', 'completed')->count();
    }

    public function getTotalMatchesCountAttribute(): int
    {
        return $this->matches()->count();
    }
}
