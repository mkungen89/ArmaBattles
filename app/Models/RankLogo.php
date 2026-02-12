<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class RankLogo extends Model
{
    protected $fillable = [
        'rank',
        'name',
        'era',
        'min_level',
        'max_level',
        'logo_path',
        'color',
        'sort_order',
    ];

    protected $casts = [
        'rank' => 'integer',
        'era' => 'integer',
        'min_level' => 'integer',
        'max_level' => 'integer',
        'sort_order' => 'integer',
    ];

    /**
     * Get the full URL for the rank logo
     */
    public function getLogoUrlAttribute(): ?string
    {
        if ($this->logo_path) {
            return asset('storage/' . $this->logo_path);
        }
        return null;
    }

    /**
     * Get rank logo for a specific level
     */
    public static function forLevel(int $level): ?self
    {
        return self::where('min_level', '<=', $level)
            ->where('max_level', '>=', $level)
            ->first();
    }

    /**
     * Get rank logo by rank number
     */
    public static function forRank(int $rank): ?self
    {
        return self::where('rank', $rank)->first();
    }

    /**
     * Get all ranks for an era
     */
    public static function forEra(int $era): \Illuminate\Database\Eloquent\Collection
    {
        return self::where('era', $era)
            ->orderBy('rank')
            ->get();
    }
}
