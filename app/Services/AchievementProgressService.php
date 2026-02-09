<?php

namespace App\Services;

use App\Models\Achievement;
use App\Models\AchievementProgress;
use App\Models\PlayerStat;
use Illuminate\Support\Collection;

class AchievementProgressService
{
    /**
     * Update achievement progress for a specific player
     */
    public function updateProgress(string $playerUuid): void
    {
        $playerStat = PlayerStat::where('player_uuid', $playerUuid)->first();

        if (!$playerStat) {
            return;
        }

        // Get all achievements that player hasn't earned yet
        $earnedAchievementIds = $playerStat->achievements()->pluck('achievements.id')->toArray();
        $unearnedAchievements = Achievement::whereNotIn('id', $earnedAchievementIds)
            ->whereNotNull('stat_field')
            ->whereNotNull('threshold')
            ->get();

        foreach ($unearnedAchievements as $achievement) {
            $this->updateSingleAchievementProgress($playerStat, $achievement);
        }
    }

    /**
     * Update progress for a single achievement
     */
    protected function updateSingleAchievementProgress(PlayerStat $playerStat, Achievement $achievement): void
    {
        $statField = $achievement->stat_field;
        $currentValue = $playerStat->$statField ?? 0;
        $targetValue = $achievement->threshold;

        if ($targetValue <= 0) {
            return;
        }

        $percentage = min(($currentValue / $targetValue) * 100, 100);

        AchievementProgress::updateOrCreate(
            [
                'player_uuid' => $playerStat->player_uuid,
                'achievement_id' => $achievement->id,
            ],
            [
                'current_value' => $currentValue,
                'target_value' => $targetValue,
                'percentage' => round($percentage, 2),
            ]
        );
    }

    /**
     * Get achievement progress for a player
     */
    public function getProgress(string $playerUuid): Collection
    {
        return AchievementProgress::with('achievement')
            ->where('player_uuid', $playerUuid)
            ->orderBy('percentage', 'desc')
            ->get();
    }

    /**
     * Get near-completion achievements (>= 75% progress)
     */
    public function getNearCompletionAchievements(string $playerUuid): Collection
    {
        return AchievementProgress::with('achievement')
            ->where('player_uuid', $playerUuid)
            ->where('percentage', '>=', 75)
            ->where('percentage', '<', 100)
            ->orderBy('percentage', 'desc')
            ->get();
    }

    /**
     * Clean up progress records for earned achievements
     */
    public function cleanupEarnedProgress(string $playerUuid, int $achievementId): void
    {
        AchievementProgress::where('player_uuid', $playerUuid)
            ->where('achievement_id', $achievementId)
            ->delete();
    }
}
