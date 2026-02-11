<?php

namespace App\Services;

use App\Models\PlayerStat;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlayerLevelService
{
    /**
     * Level tier definitions
     */
    public const TIERS = [
        'recruit' => ['min' => 1, 'max' => 10, 'color' => 'gray', 'label' => 'Recruit'],
        'soldier' => ['min' => 11, 'max' => 25, 'color' => 'green', 'label' => 'Soldier'],
        'veteran' => ['min' => 26, 'max' => 40, 'color' => 'blue', 'label' => 'Veteran'],
        'elite' => ['min' => 41, 'max' => 60, 'color' => 'purple', 'label' => 'Elite'],
        'master' => ['min' => 61, 'max' => 80, 'color' => 'yellow', 'label' => 'Master'],
        'legend' => ['min' => 81, 'max' => 100, 'color' => 'red', 'label' => 'Legend'],
    ];

    /**
     * Base XP required for level 1 → 2
     */
    protected const BASE_XP = 1000;

    /**
     * Exponential growth factor
     */
    protected const GROWTH_FACTOR = 1.15;

    /**
     * Calculate XP required for a specific level
     */
    public function xpRequiredForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        // Exponential curve: base_xp * (level ^ growth_factor)
        return (int) round(self::BASE_XP * pow($level, self::GROWTH_FACTOR));
    }

    /**
     * Calculate total XP required to reach a level
     */
    public function totalXpForLevel(int $targetLevel): int
    {
        $total = 0;
        for ($level = 2; $level <= $targetLevel; $level++) {
            $total += $this->xpRequiredForLevel($level);
        }

        return $total;
    }

    /**
     * Calculate level from total XP
     */
    public function calculateLevelFromXp(int $totalXp): int
    {
        if ($totalXp < self::BASE_XP) {
            return 1;
        }

        $level = 1;
        $xpUsed = 0;

        // Find highest level we can afford
        while ($level < 100) {
            $nextLevelXp = $this->xpRequiredForLevel($level + 1);
            if ($xpUsed + $nextLevelXp > $totalXp) {
                break;
            }

            $xpUsed += $nextLevelXp;
            $level++;
        }

        return $level;
    }

    /**
     * Get progress to next level as percentage (0-100)
     */
    public function getProgressToNextLevel(PlayerStat $stats): float
    {
        if ($stats->level >= 100) {
            return 100.0;
        }

        $currentLevelTotalXp = $this->totalXpForLevel($stats->level);
        $nextLevelTotalXp = $this->totalXpForLevel($stats->level + 1);
        $xpInCurrentLevel = $stats->level_xp - $currentLevelTotalXp;
        $xpNeededForNextLevel = $nextLevelTotalXp - $currentLevelTotalXp;

        if ($xpNeededForNextLevel <= 0) {
            return 100.0;
        }

        return round(($xpInCurrentLevel / $xpNeededForNextLevel) * 100, 1);
    }

    /**
     * Get XP needed for next level
     */
    public function getXpToNextLevel(PlayerStat $stats): int
    {
        if ($stats->level >= 100) {
            return 0;
        }

        $currentLevelTotalXp = $this->totalXpForLevel($stats->level);
        $nextLevelTotalXp = $this->totalXpForLevel($stats->level + 1);

        return $nextLevelTotalXp - $stats->level_xp;
    }

    /**
     * Get tier info for a level
     */
    public function getTierForLevel(int $level): array
    {
        foreach (self::TIERS as $key => $tier) {
            if ($level >= $tier['min'] && $level <= $tier['max']) {
                return array_merge($tier, ['key' => $key]);
            }
        }

        // Default to legend if somehow > 100
        return array_merge(self::TIERS['legend'], ['key' => 'legend']);
    }

    /**
     * Update player level based on XP and achievements
     */
    public function updatePlayerLevel(PlayerStat $stats): ?int
    {
        // Calculate total XP (game XP + achievement points)
        $totalXp = ($stats->xp_total ?? 0) + ($stats->achievement_points ?? 0);

        // Update level_xp
        $stats->level_xp = $totalXp;

        // Calculate new level
        $newLevel = $this->calculateLevelFromXp($totalXp);
        $oldLevel = $stats->level ?? 1;

        if ($newLevel !== $oldLevel) {
            $stats->level = $newLevel;
            $stats->save();

            return $newLevel; // Return new level for notification
        }

        $stats->save();

        return null; // No level up
    }

    /**
     * Add achievement points and check for level up
     */
    public function addAchievementPoints(PlayerStat $stats, int $points): ?int
    {
        $stats->achievement_points = ($stats->achievement_points ?? 0) + $points;

        return $this->updatePlayerLevel($stats);
    }

    /**
     * Recalculate all player levels (for migration/fix)
     */
    public function recalculateAllLevels(): int
    {
        $updated = 0;

        DB::table('player_stats')->orderBy('id')->chunk(100, function ($statsRecords) use (&$updated) {
            foreach ($statsRecords as $record) {
                $stats = PlayerStat::find($record->id);
                if ($stats) {
                    $this->updatePlayerLevel($stats);
                    $updated++;
                }
            }
        });

        Log::info("Recalculated levels for {$updated} players");

        return $updated;
    }

    /**
     * Get level leaderboard
     */
    public function getLeaderboard(int $limit = 100): array
    {
        return DB::table('player_stats')
            ->select('player_uuid', 'player_name', 'level', 'level_xp', 'achievement_points')
            ->orderBy('level', 'desc')
            ->orderBy('level_xp', 'desc')
            ->limit($limit)
            ->get()
            ->toArray();
    }
}
