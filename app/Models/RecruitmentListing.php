<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RecruitmentListing extends Model
{
    protected $fillable = [
        'user_id',
        'team_id',
        'listing_type',
        'message',
        'preferred_roles',
        'playstyle',
        'region',
        'availability',
        'is_featured',
        'is_active',
        'expires_at',
    ];

    protected $casts = [
        'preferred_roles' => 'array',
        'is_featured' => 'boolean',
        'is_active' => 'boolean',
        'expires_at' => 'datetime',
    ];

    /**
     * Get the user who created the listing
     */
    public function user()
    {
        return $this->belongsTo(User::class);
    }

    /**
     * Get the team looking for players
     */
    public function team()
    {
        return $this->belongsTo(Team::class);
    }

    /**
     * Scope for active listings
     */
    public function scopeActive($query)
    {
        return $query->where('is_active', true)
            ->where(function ($q) {
                $q->whereNull('expires_at')
                    ->orWhere('expires_at', '>', now());
            });
    }

    /**
     * Scope for featured listings
     */
    public function scopeFeatured($query)
    {
        return $query->where('is_featured', true);
    }

    /**
     * Scope for player listings
     */
    public function scopePlayersLookingForTeam($query)
    {
        return $query->where('listing_type', 'player_looking_for_team');
    }

    /**
     * Scope for team listings
     */
    public function scopeTeamsLookingForPlayers($query)
    {
        return $query->where('listing_type', 'team_looking_for_players');
    }

    /**
     * Scope by region
     */
    public function scopeRegion($query, string $region)
    {
        return $query->where('region', $region);
    }

    /**
     * Scope by playstyle
     */
    public function scopePlaystyle($query, string $playstyle)
    {
        return $query->where('playstyle', $playstyle);
    }

    /**
     * Check if listing is expired
     */
    public function isExpired(): bool
    {
        return $this->expires_at && $this->expires_at->isPast();
    }

    /**
     * Check if user is looking for team
     */
    public function isPlayerListing(): bool
    {
        return $this->listing_type === 'player_looking_for_team';
    }

    /**
     * Check if team is looking for players
     */
    public function isTeamListing(): bool
    {
        return $this->listing_type === 'team_looking_for_players';
    }
}
