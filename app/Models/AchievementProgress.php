<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class AchievementProgress extends Model
{
    protected $table = 'achievement_progress';

    protected $fillable = [
        'player_uuid',
        'achievement_id',
        'current_value',
        'target_value',
        'percentage',
    ];

    protected function casts(): array
    {
        return [
            'current_value' => 'integer',
            'target_value' => 'integer',
            'percentage' => 'decimal:2',
        ];
    }

    public function achievement(): BelongsTo
    {
        return $this->belongsTo(Achievement::class);
    }

    public function playerStat(): BelongsTo
    {
        return $this->belongsTo(PlayerStat::class, 'player_uuid', 'player_uuid');
    }
}
