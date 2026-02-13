<?php

namespace App\Console\Commands;

use App\Models\Achievement;
use App\Models\PlayerAchievement;
use App\Models\PlayerStat;
use App\Models\User;
use App\Notifications\AchievementEarnedNotification;
use App\Notifications\LevelUpNotification;
use App\Services\AchievementProgressService;
use App\Services\PlayerLevelService;
use Illuminate\Console\Command;

class CheckAchievements extends Command
{
    protected $signature = 'achievements:check';

    protected $description = 'Check all players against achievement thresholds and award any newly earned achievements';

    public function handle(): int
    {
        $progressService = app(AchievementProgressService::class);
        $levelService = app(PlayerLevelService::class);

        $achievements = Achievement::whereNotNull('stat_field')
            ->whereNotNull('threshold')
            ->get();

        if ($achievements->isEmpty()) {
            $this->info('No achievements with stat thresholds found.');

            return 0;
        }

        $this->info("Checking {$achievements->count()} achievements against all players...");

        $awarded = 0;
        $progressUpdated = 0;

        PlayerStat::query()->chunk(200, function ($players) use ($achievements, $progressService, $levelService, &$awarded, &$progressUpdated) {
            foreach ($players as $player) {
                foreach ($achievements as $achievement) {
                    $statValue = $player->{$achievement->stat_field} ?? 0;

                    if ($statValue >= $achievement->threshold) {
                        $created = PlayerAchievement::firstOrCreate(
                            [
                                'player_uuid' => $player->player_uuid,
                                'achievement_id' => $achievement->id,
                            ],
                            [
                                'earned_at' => now(),
                            ]
                        );

                        if ($created->wasRecentlyCreated) {
                            $awarded++;

                            // Award achievement points and check for level-up
                            $player->refresh(); // Ensure fresh data
                            $newLevel = $levelService->addAchievementPoints($player, $achievement->points);

                            $user = User::where('player_uuid', $player->player_uuid)->first();
                            if ($user) {
                                $user->notify(new AchievementEarnedNotification($achievement));

                                // If player leveled up, send level-up notification
                                if ($newLevel) {
                                    $tier = $levelService->getTierForLevel($newLevel);
                                    $user->notify(new LevelUpNotification($newLevel, $tier));
                                }
                            }

                            // Clean up progress tracking for this earned achievement
                            $progressService->cleanupEarnedProgress($player->player_uuid, $achievement->id);
                        }
                    }
                }

                // Update progress for all unearned achievements
                $progressService->updateProgress($player->player_uuid);
                $progressUpdated++;
            }
        });

        $this->info("Done! Awarded {$awarded} new achievement(s).");
        $this->info("Updated progress for {$progressUpdated} players.");

        return 0;
    }
}
