<?php

namespace App\Models;

// use Illuminate\Contracts\Auth\MustVerifyEmail;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Database\Eloquent\Relations\HasMany;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Support\Facades\Storage;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    /** @use HasFactory<\Database\Factories\UserFactory> */
    use HasApiTokens, HasFactory, Notifiable;

    /**
     * The attributes that are mass assignable.
     *
     * @var list<string>
     */
    protected $fillable = [
        'name',
        'email',
        'password',
        'steam_id',
        'google_id',
        'google_email',
        'avatar',
        'avatar_full',
        'custom_avatar',
        'profile_url',
        'player_uuid',
        'discord_id',
        'discord_username',
        'role',
        'is_banned',
        'ban_reason',
        'banned_at',
        'last_login_at',
        'last_seen_at',
        'profile_visibility',
        'notification_preferences',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'social_links',
    ];

    /**
     * The attributes that should be hidden for serialization.
     *
     * @var list<string>
     */
    protected $hidden = [
        'password',
        'remember_token',
        'two_factor_secret',
        'two_factor_recovery_codes',
    ];

    /**
     * Get the attributes that should be cast.
     *
     * @return array<string, string>
     */
    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
            'is_banned' => 'boolean',
            'banned_at' => 'datetime',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'notification_preferences' => 'array',
            'social_links' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
        ];
    }

    public function getAvatarDisplayAttribute(): string
    {
        if ($this->custom_avatar) {
            return Storage::url($this->custom_avatar);
        }

        return $this->avatar_full ?? $this->avatar ?? '';
    }

    public function hasTwoFactorEnabled(): bool
    {
        return ! is_null($this->two_factor_secret) && ! is_null($this->two_factor_confirmed_at);
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isModerator(): bool
    {
        return in_array($this->role, ['admin', 'moderator']);
    }

    public function isGM(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'gm']);
    }

    public function isReferee(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'referee']);
    }

    public function isObserver(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'referee', 'observer']);
    }

    public function isCaster(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'caster']);
    }

    public function canManageTournaments(): bool
    {
        return in_array($this->role, ['admin', 'moderator', 'referee']);
    }

    public function ban(?string $reason = null): void
    {
        $this->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'banned_at' => now(),
        ]);
    }

    public function unban(): void
    {
        $this->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null,
        ]);
    }

    public function teams(): BelongsToMany
    {
        return $this->belongsToMany(Team::class, 'team_members')
            ->withPivot(['role', 'status', 'joined_at', 'left_at'])
            ->withTimestamps();
    }

    public function activeTeams(): BelongsToMany
    {
        return $this->teams()->wherePivot('status', 'active');
    }

    public function captainedTeams(): HasMany
    {
        return $this->hasMany(Team::class, 'captain_id');
    }

    public function teamInvitations(): HasMany
    {
        return $this->hasMany(TeamInvitation::class);
    }

    public function pendingInvitations(): HasMany
    {
        return $this->teamInvitations()
            ->where('status', 'pending')
            ->where('expires_at', '>', now());
    }

    public function teamApplications(): HasMany
    {
        return $this->hasMany(TeamApplication::class);
    }

    public function pendingApplications(): HasMany
    {
        return $this->teamApplications()->where('status', 'pending');
    }

    public function hasPendingApplicationTo(Team $team): bool
    {
        return $this->teamApplications()
            ->where('team_id', $team->id)
            ->where('status', 'pending')
            ->exists();
    }

    public function hasTeam(): bool
    {
        return $this->activeTeams()->exists();
    }

    public function getActiveTeamAttribute(): ?Team
    {
        return $this->activeTeams()->first();
    }

    public function createdTournaments(): HasMany
    {
        return $this->hasMany(Tournament::class, 'created_by');
    }

    public function favorites(): HasMany
    {
        return $this->hasMany(Favorite::class);
    }

    public function hasFavorited($model): bool
    {
        return $this->favorites()
            ->where('favoritable_type', get_class($model))
            ->where('favoritable_id', $model->getKey())
            ->exists();
    }

    public function toggleFavorite($model): bool
    {
        $existing = $this->favorites()
            ->where('favoritable_type', get_class($model))
            ->where('favoritable_id', $model->getKey())
            ->first();

        if ($existing) {
            $existing->delete();

            return false;
        }

        $this->favorites()->create([
            'favoritable_type' => get_class($model),
            'favoritable_id' => $model->getKey(),
        ]);

        return true;
    }

    /**
     * Get the player's game statistics
     */
    public function gameStats(): ?\App\Models\PlayerStat
    {
        if (! $this->player_uuid) {
            return null;
        }

        return \App\Models\PlayerStat::where('player_uuid', $this->player_uuid)->first();
    }

    /**
     * Check if user has linked their Arma UUID
     */
    public function hasLinkedArmaId(): bool
    {
        return ! empty($this->player_uuid);
    }

    /**
     * Get user's competitive rating
     */
    public function playerRating(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PlayerRating::class);
    }

    /**
     * Check if user has opted into competitive mode
     */
    public function isCompetitive(): bool
    {
        return $this->playerRating()->whereNotNull('opted_in_at')->exists();
    }

    /**
     * Get formatted rating display string
     */
    public function getRatingDisplay(): ?string
    {
        $rating = $this->playerRating;
        if (! $rating || ! $rating->opted_in_at) {
            return null;
        }

        if (! $rating->is_placed) {
            return "Placement {$rating->placement_games}/10";
        }

        return number_format($rating->rating, 0);
    }

    /**
     * Get user's reputation
     */
    public function reputation(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(PlayerReputation::class);
    }

    /**
     * Get votes given by this user
     */
    public function givenVotes(): HasMany
    {
        return $this->hasMany(ReputationVote::class, 'voter_id');
    }

    /**
     * Get votes received by this user
     */
    public function receivedVotes(): HasMany
    {
        return $this->hasMany(ReputationVote::class, 'target_id');
    }

    /**
     * Get user's content creator profile
     */
    public function contentCreator(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(ContentCreator::class);
    }

    /**
     * Get user's submitted highlight clips
     */
    public function highlightClips(): HasMany
    {
        return $this->hasMany(HighlightClip::class);
    }

    /**
     * Get user's clip votes
     */
    public function clipVotes(): HasMany
    {
        return $this->hasMany(ClipVote::class);
    }

    /**
     * Check if user is a content creator
     */
    public function isContentCreator(): bool
    {
        return $this->contentCreator()->exists();
    }

    /**
     * Check if user is a verified content creator
     */
    public function isVerifiedCreator(): bool
    {
        return $this->contentCreator()->where('is_verified', true)->exists();
    }

    /**
     * Check if user has linked their Discord
     */
    public function hasLinkedDiscord(): bool
    {
        return ! empty($this->discord_username);
    }

    /**
     * Get user's Discord Rich Presence
     */
    public function discordPresence(): \Illuminate\Database\Eloquent\Relations\HasOne
    {
        return $this->hasOne(DiscordRichPresence::class);
    }

    /**
     * Check if user has Discord Rich Presence enabled
     */
    public function hasDiscordPresenceEnabled(): bool
    {
        return $this->discordPresence()->where('enabled', true)->exists();
    }
}
