<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\Relations\BelongsTo;

class RatingHistory extends Model
{
    protected $table = 'rating_history';

    protected $fillable = [
        'player_rating_id',
        'player_uuid',
        'rating_before',
        'rating_after',
        'rd_before',
        'rd_after',
        'volatility_before',
        'volatility_after',
        'rank_tier_before',
        'rank_tier_after',
        'period_kills',
        'period_deaths',
        'period_encounters',
        'season',
        'period_start',
        'period_end',
    ];

    protected function casts(): array
    {
        return [
            'rating_before' => 'decimal:2',
            'rating_after' => 'decimal:2',
            'rd_before' => 'decimal:2',
            'rd_after' => 'decimal:2',
            'volatility_before' => 'decimal:6',
            'volatility_after' => 'decimal:6',
            'period_kills' => 'integer',
            'period_deaths' => 'integer',
            'period_encounters' => 'integer',
            'season' => 'integer',
            'period_start' => 'datetime',
            'period_end' => 'datetime',
        ];
    }

    public function playerRating(): BelongsTo
    {
        return $this->belongsTo(PlayerRating::class);
    }
}
