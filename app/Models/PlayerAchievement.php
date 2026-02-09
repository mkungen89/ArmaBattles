<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class PlayerAchievement extends Model
{
    protected $fillable = [
        'player_uuid',
        'achievement_id',
        'earned_at',
    ];

    protected $casts = [
        'earned_at' => 'datetime',
    ];

    /**
     * The achievement that was earned.
     */
    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    /**
     * The player stats record for this achievement.
     */
    public function player(): BelongsTo
    {
        return $this->belongsTo(PlayerStat::class, 'player_uuid', 'player_uuid');
    }
}
