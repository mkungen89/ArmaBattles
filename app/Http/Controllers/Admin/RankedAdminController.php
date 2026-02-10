<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\PlayerRating;
use App\Traits\LogsAdminActions;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankedAdminController extends Controller
{
    use LogsAdminActions;

    public function index()
    {
        $totalCompetitive = PlayerRating::competitive()->count();
        $totalPlaced = PlayerRating::competitive()->placed()->count();
        $queueSize = DB::table('rated_kills_queue')->where('processed', false)->count();

        $tierDistribution = PlayerRating::competitive()
            ->placed()
            ->select('rank_tier', DB::raw('COUNT(*) as count'))
            ->groupBy('rank_tier')
            ->pluck('count', 'rank_tier')
            ->toArray();

        $avgRating = PlayerRating::competitive()->placed()->avg('rating');

        $recentHistory = DB::table('rating_history')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        // Suspicious: High rating with very high RD (could be manipulated)
        $suspicious = PlayerRating::competitive()
            ->where('rating', '>', 1800)
            ->where('rating_deviation', '>', 200)
            ->join('users', 'player_ratings.user_id', '=', 'users.id')
            ->select('player_ratings.*', 'users.name')
            ->limit(10)
            ->get();

        return view('admin.ranked.index', compact(
            'totalCompetitive', 'totalPlaced', 'queueSize',
            'tierDistribution', 'avgRating', 'recentHistory', 'suspicious'
        ));
    }

    public function reset(PlayerRating $rating)
    {
        $oldRating = $rating->rating;

        $rating->update([
            'rating' => 1500,
            'rating_deviation' => 350,
            'volatility' => 0.06,
            'rank_tier' => 'unranked',
            'ranked_kills' => 0,
            'ranked_deaths' => 0,
            'games_played' => 0,
            'placement_games' => 0,
            'is_placed' => false,
            'peak_rating' => 1500,
        ]);

        $this->logAction('ranked.reset', 'PlayerRating', $rating->id, [
            'player_uuid' => $rating->player_uuid,
            'old_rating' => $oldRating,
        ]);

        Cache::forget('competitive_player_uuids');

        return back()->with('success', "Rating reset for player {$rating->player_uuid}.");
    }
}
