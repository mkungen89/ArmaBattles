<?php

namespace App\Services;

use App\Models\PlayerStat;
use App\Models\RankLogo;
use App\Models\User;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class PlayerLevelService
{
    /**
     * Legacy tier definitions (kept for backwards compatibility)
     * @deprecated Use getRankInfo() instead
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
     * Get base XP from settings (with fallback)
     */
    protected function getBaseXp(): int
    {
        return (int) site_setting('leveling_base_xp', 1000);
    }

    /**
     * Get XP curve exponent from settings (with fallback)
     */
    protected function getGrowthFactor(): float
    {
        return (float) site_setting('leveling_xp_curve_exponent', 1.15);
    }

    /**
     * Get max level from settings (with fallback)
     */
    protected function getMaxLevel(): int
    {
        return (int) site_setting('leveling_level_cap', 500);
    }

    /**
     * Get achievement points weight from settings (with fallback)
     */
    protected function getAchievementPointsWeight(): float
    {
        return (float) site_setting('leveling_achievement_points_weight', 1.0);
    }

    /**
     * Calculate XP required for a specific level
     */
    public function xpRequiredForLevel(int $level): int
    {
        if ($level <= 1) {
            return 0;
        }

        // Exponential curve: base_xp * (level ^ growth_factor)
        $baseXp = $this->getBaseXp();
        $growthFactor = $this->getGrowthFactor();

        return (int) round($baseXp * pow($level, $growthFactor));
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
        $baseXp = $this->getBaseXp();
        if ($totalXp < $baseXp) {
            return 1;
        }

        $level = 1;
        $xpUsed = 0;
        $maxLevel = $this->getMaxLevel();

        // Find highest level we can afford
        while ($level < $maxLevel) {
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
        $maxLevel = $this->getMaxLevel();
        if ($stats->level >= $maxLevel) {
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
        $maxLevel = $this->getMaxLevel();
        if ($stats->level >= $maxLevel) {
            return 0;
        }

        $currentLevelTotalXp = $this->totalXpForLevel($stats->level);
        $nextLevelTotalXp = $this->totalXpForLevel($stats->level + 1);

        return $nextLevelTotalXp - $stats->level_xp;
    }

    /**
     * Get tier info for a level (legacy method)
     * @deprecated Use getRankInfo() instead
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
     * Get rank number (1-50) for a given level
     */
    public function getRankForLevel(int $level): int
    {
        // Every 10 levels = 1 rank
        // Level 1-10 = Rank 1, Level 11-20 = Rank 2, etc.
        return (int) ceil($level / 10);
    }

    /**
     * Get era number (1-10) for a given level
     */
    public function getEraForLevel(int $level): int
    {
        // Every 50 levels = 1 era
        // Level 1-50 = Era 1, Level 51-100 = Era 2, etc.
        return (int) ceil($level / 50);
    }

    /**
     * Get rank info (logo, name, era, etc) for a given level
     * Returns null if no rank found
     */
    public function getRankInfo(int $level): ?RankLogo
    {
        return RankLogo::forLevel($level);
    }

    /**
     * Get progress within current rank (0-100%)
     * Each rank spans 10 levels
     */
    public function getProgressInRank(PlayerStat $stats): float
    {
        $rank = $this->getRankForLevel($stats->level);
        $rankLogo = RankLogo::forRank($rank);

        if (!$rankLogo) {
            return 0.0;
        }

        // Get total XP at start of rank
        $rankStartTotalXp = $this->totalXpForLevel($rankLogo->min_level);
        // Get total XP at end of rank
        $rankEndTotalXp = $this->totalXpForLevel($rankLogo->max_level + 1); // +1 because we want XP to reach next level

        // Current player XP within this rank
        $xpInRank = $stats->level_xp - $rankStartTotalXp;
        $xpNeededForRank = $rankEndTotalXp - $rankStartTotalXp;

        if ($xpNeededForRank <= 0) {
            return 100.0;
        }

        return round(($xpInRank / $xpNeededForRank) * 100, 1);
    }

    /**
     * Get progress to next rank (XP needed)
     */
    public function getXpToNextRank(PlayerStat $stats): int
    {
        $rank = $this->getRankForLevel($stats->level);
        $rankLogo = RankLogo::forRank($rank);

        if (!$rankLogo) {
            return 0;
        }

        // Max level reached
        if ($stats->level >= 500) {
            return 0;
        }

        // Get total XP needed to reach next rank (which starts at max_level + 1)
        $nextRankStartLevel = $rankLogo->max_level + 1;
        $xpToNextRank = $this->totalXpForLevel($nextRankStartLevel);

        return max(0, $xpToNextRank - $stats->level_xp);
    }

    /**
     * Update player level based on XP and achievements
     */
    public function updatePlayerLevel(PlayerStat $stats): ?int
    {
        // Calculate total XP (game XP + weighted achievement points)
        $achievementWeight = $this->getAchievementPointsWeight();
        $totalXp = ($stats->xp_total ?? 0) + (int)(($stats->achievement_points ?? 0) * $achievementWeight);

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
