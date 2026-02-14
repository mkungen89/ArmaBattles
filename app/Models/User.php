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
        'twitch_id',
        'twitch_username',
        'twitch_email',
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
        'banned_until',
        'hardware_id',
        'ban_count',
        'ip_address',
        'last_login_at',
        'last_seen_at',
        'profile_visibility',
        'notification_preferences',
        'two_factor_secret',
        'two_factor_recovery_codes',
        'two_factor_confirmed_at',
        'social_links',
        'looking_for_team',
        'preferred_roles',
        'playstyle',
        'region',
        'availability',
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
            'banned_until' => 'datetime',
            'last_login_at' => 'datetime',
            'last_seen_at' => 'datetime',
            'notification_preferences' => 'array',
            'social_links' => 'array',
            'two_factor_secret' => 'encrypted',
            'two_factor_recovery_codes' => 'encrypted',
            'two_factor_confirmed_at' => 'datetime',
            'looking_for_team' => 'boolean',
            'preferred_roles' => 'array',
        ];
    }

    public function getAvatarDisplayAttribute(): string
    {
        if ($this->custom_avatar) {
            return Storage::url($this->custom_avatar);
        }

        return $this->avatar_full ?? $this->avatar ?? asset('images/avatar.png');
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

    public function ban(?string $reason = null, $bannedUntil = null, ?string $banType = 'permanent', ?User $admin = null): void
    {
        $this->update([
            'is_banned' => true,
            'ban_reason' => $reason,
            'banned_at' => now(),
            'banned_until' => $bannedUntil,
            'ban_count' => $this->ban_count + 1,
        ]);

        // Log to ban history
        BanHistory::create([
            'user_id' => $this->id,
            'action' => 'banned',
            'reason' => $reason,
            'ban_type' => $banType,
            'banned_until' => $bannedUntil,
            'hardware_id' => $this->hardware_id,
            'ip_address' => $this->ip_address,
            'actioned_by' => $admin?->id,
        ]);
    }

    public function unban(?User $admin = null): void
    {
        $this->update([
            'is_banned' => false,
            'ban_reason' => null,
            'banned_at' => null,
            'banned_until' => null,
        ]);

        // Log to ban history
        BanHistory::create([
            'user_id' => $this->id,
            'action' => 'unbanned',
            'actioned_by' => $admin?->id,
        ]);
    }

    public function isTempBanned(): bool
    {
        return $this->is_banned && $this->banned_until !== null && $this->banned_until->isFuture();
    }

    public function isPermanentlyBanned(): bool
    {
        return $this->is_banned && $this->banned_until === null;
    }

    public function tempBanExpired(): bool
    {
        return $this->is_banned && $this->banned_until !== null && $this->banned_until->isPast();
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
        return \DB::transaction(function () use ($model) {
            $existing = $this->favorites()
                ->where('favoritable_type', get_class($model))
                ->where('favoritable_id', $model->getKey())
                ->lockForUpdate()
                ->first();

            if ($existing) {
                $existing->delete();

                return false;
            }

            try {
                $this->favorites()->create([
                    'favoritable_type' => get_class($model),
                    'favoritable_id' => $model->getKey(),
                ]);

                return true;
            } catch (\Illuminate\Database\QueryException $e) {
                // Handle race condition - if unique constraint fails, the favorite was just added
                // by another concurrent request, so we treat this as already favorited
                if ($e->getCode() === '23505' || str_contains($e->getMessage(), 'unique')) {
                    return true;
                }
                throw $e;
            }
        });
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

    /**
     * Get user's ban appeals
     */
    public function banAppeals(): HasMany
    {
        return $this->hasMany(BanAppeal::class);
    }

    /**
     * Get user's pending ban appeal
     */
    public function pendingBanAppeal(): ?\App\Models\BanAppeal
    {
        return $this->banAppeals()->where('status', 'pending')->first();
    }

    /**
     * Get user's ban history
     */
    public function banHistory(): HasMany
    {
        return $this->hasMany(BanHistory::class);
    }

    /**
     * Check if user has a pending ban appeal
     */
    public function hasPendingBanAppeal(): bool
    {
        return $this->banAppeals()->where('status', 'pending')->exists();
    }

    /**
     * Get user's recruitment listings
     */
    public function recruitmentListings(): HasMany
    {
        return $this->hasMany(RecruitmentListing::class);
    }

    /**
     * Get user's active recruitment listing
     */
    public function activeRecruitmentListing(): ?RecruitmentListing
    {
        return $this->recruitmentListings()
            ->active()
            ->playersLookingForTeam()
            ->first();
    }

    /**
     * Check if user has an active recruitment listing
     */
    public function hasActiveRecruitmentListing(): bool
    {
        return $this->recruitmentListings()
            ->active()
            ->playersLookingForTeam()
            ->exists();
    }

    /**
     * Get user's warnings
     */
    public function warnings(): HasMany
    {
        return $this->hasMany(PlayerWarning::class);
    }

    /**
     * Get user's moderator notes
     */
    public function moderatorNotes(): HasMany
    {
        return $this->hasMany(ModeratorNote::class);
    }
}
