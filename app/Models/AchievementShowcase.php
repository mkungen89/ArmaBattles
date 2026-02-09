<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class AchievementShowcase extends Model
{
    protected $fillable = [
        'player_uuid',
        'pinned_achievements',
    ];

    protected function casts(): array
    {
        return [
            'pinned_achievements' => 'array',
        ];
    }

    public function playerStat()
    {
        return $this->belongsTo(PlayerStat::class, 'player_uuid', 'player_uuid');
    }

    public function pinnedAchievements()
    {
        return Achievement::whereIn('id', $this->pinned_achievements ?? [])->get();
    }
}
