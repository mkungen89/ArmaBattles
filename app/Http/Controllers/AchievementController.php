<?php

namespace App\Http\Controllers;

use App\Models\Achievement;
use App\Models\AchievementProgress;
use App\Models\AchievementShowcase;
use App\Models\PlayerStat;
use App\Services\AchievementProgressService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AchievementController extends Controller
{
    protected AchievementProgressService $progressService;

    public function __construct(AchievementProgressService $progressService)
    {
        $this->progressService = $progressService;
    }

    /**
     * Display all achievements with rarity and player progress
     */
    public function index(Request $request)
    {
        $category = $request->input('category', 'all');

        $achievementsQuery = Achievement::query()
            ->withCount('players as unlock_count')
            ->orderBy('category')
            ->orderBy('sort_order')
            ->orderBy('id');

        if ($category !== 'all') {
            $achievementsQuery->where('category', $category);
        }

        $achievements = $achievementsQuery->get();

        // Calculate rarity for each achievement
        $totalPlayers = PlayerStat::count();
        foreach ($achievements as $achievement) {
            if ($totalPlayers > 0) {
                $achievement->calculated_rarity = round(($achievement->unlock_count / $totalPlayers) * 100, 2);
            } else {
                $achievement->calculated_rarity = 0.0;
            }
        }

        // Get user's earned achievements and progress
        $earnedAchievements = collect();
        $achievementProgress = collect();

        if (auth()->check() && auth()->user()->player_uuid) {
            $playerUuid = auth()->user()->player_uuid;

            // Get earned achievements
            $earnedAchievementIds = DB::table('player_achievements')
                ->where('player_uuid', $playerUuid)
                ->pluck('achievement_id')
                ->toArray();

            $earnedAchievements = collect($earnedAchievementIds);

            // Get progress for unearned achievements
            $achievementProgress = AchievementProgress::where('player_uuid', $playerUuid)
                ->get()
                ->keyBy('achievement_id');
        }

        $categories = Achievement::select('category')->distinct()->pluck('category');

        return view('achievements.index', compact(
            'achievements',
            'earnedAchievements',
            'achievementProgress',
            'category',
            'categories',
            'totalPlayers'
        ));
    }

    /**
     * Update achievement showcase (pin/unpin achievements)
     */
    public function updateShowcase(Request $request)
    {
        $validated = $request->validate([
            'pinned_achievements' => 'required|array|max:3',
            'pinned_achievements.*' => 'exists:achievements,id',
        ]);

        if (! auth()->user()->player_uuid) {
            return back()->with('error', 'You need a player UUID to manage your showcase.');
        }

        // Verify user has earned all pinned achievements
        $playerUuid = auth()->user()->player_uuid;
        $earnedIds = DB::table('player_achievements')
            ->where('player_uuid', $playerUuid)
            ->pluck('achievement_id')
            ->toArray();

        foreach ($validated['pinned_achievements'] as $achievementId) {
            if (! in_array($achievementId, $earnedIds)) {
                return back()->with('error', 'You can only pin achievements you have earned.');
            }
        }

        AchievementShowcase::updateOrCreate(
            ['player_uuid' => $playerUuid],
            ['pinned_achievements' => $validated['pinned_achievements']]
        );

        return back()->with('success', 'Achievement showcase updated!');
    }

    /**
     * Get showcase for a specific player
     */
    public function getShowcase(string $playerUuid)
    {
        $showcase = AchievementShowcase::where('player_uuid', $playerUuid)->first();

        if (! $showcase) {
            return response()->json(['pinned_achievements' => []]);
        }

        $achievements = Achievement::whereIn('id', $showcase->pinned_achievements ?? [])
            ->withCount('players as unlock_count')
            ->get();

        $totalPlayers = PlayerStat::count();
        foreach ($achievements as $achievement) {
            if ($totalPlayers > 0) {
                $achievement->calculated_rarity = round(($achievement->unlock_count / $totalPlayers) * 100, 2);
            } else {
                $achievement->calculated_rarity = 0.0;
            }
        }

        return response()->json([
            'pinned_achievements' => $achievements,
        ]);
    }
}
