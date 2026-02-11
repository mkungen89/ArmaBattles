<?php

namespace App\Http\Controllers;

use App\Models\PlayerStat;
use App\Models\User;
use App\Services\PlayerLevelService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;

class LevelLeaderboardController extends Controller
{
    public function __construct(protected PlayerLevelService $levelService)
    {
    }

    public function index(Request $request)
    {
        $perPage = 50;
        $page = $request->get('page', 1);

        // Cache key for pagination
        $cacheKey = "levels:leaderboard:page_{$page}:limit_{$perPage}";

        $leaderboard = Cache::remember($cacheKey, now()->addMinutes(5), function () use ($perPage, $page) {
            return PlayerStat::select('player_uuid', 'player_name', 'level', 'level_xp', 'achievement_points', 'xp_total', 'kills', 'deaths', 'playtime_seconds')
                ->where('level', '>', 0)
                ->orderBy('level', 'desc')
                ->orderBy('level_xp', 'desc')
                ->paginate($perPage);
        });

        // Add rank to each player
        $startRank = ($page - 1) * $perPage + 1;
        foreach ($leaderboard as $index => $player) {
            $player->rank = $startRank + $index;
            $player->tier = $this->levelService->getTierForLevel($player->level);
            $player->progress = $this->levelService->getProgressToNextLevel($player);

            // Try to find associated user for avatar
            $player->user = Cache::remember("user:uuid:{$player->player_uuid}", now()->addHours(1), function () use ($player) {
                return User::where('player_uuid', $player->player_uuid)->first();
            });
        }

        // Stats for the page
        $stats = [
            'total_players' => Cache::remember('levels:total_players', now()->addMinutes(5), function () {
                return PlayerStat::where('level', '>', 0)->count();
            }),
            'max_level' => Cache::remember('levels:max_level', now()->addMinutes(5), function () {
                return PlayerStat::max('level') ?? 1;
            }),
            'avg_level' => Cache::remember('levels:avg_level', now()->addMinutes(5), function () {
                return round(PlayerStat::where('level', '>', 0)->avg('level'), 1);
            }),
            'legends_count' => Cache::remember('levels:legends_count', now()->addMinutes(5), function () {
                return PlayerStat::where('level', '>=', 81)->count();
            }),
        ];

        // Tier distribution
        $tierDistribution = Cache::remember('levels:tier_distribution', now()->addMinutes(5), function () {
            $distribution = [];
            foreach (PlayerLevelService::TIERS as $key => $tier) {
                $distribution[$key] = PlayerStat::whereBetween('level', [$tier['min'], $tier['max']])->count();
            }

            return $distribution;
        });

        return view('levels.index', compact('leaderboard', 'stats', 'tierDistribution'));
    }
}
