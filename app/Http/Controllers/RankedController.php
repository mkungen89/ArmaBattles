<?php

namespace App\Http\Controllers;

use App\Models\PlayerRating;
use App\Models\RatingHistory;
use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class RankedController extends Controller
{
    public function index(Request $request)
    {
        $ratings = PlayerRating::ranked()
            ->join('users', 'player_ratings.user_id', '=', 'users.id')
            ->select('player_ratings.*', 'users.name', 'users.avatar', 'users.avatar_full', 'users.custom_avatar')
            ->paginate(25);

        $totalCompetitive = PlayerRating::competitive()->count();
        $totalPlaced = PlayerRating::competitive()->placed()->count();

        return view('ranked.index', compact('ratings', 'totalCompetitive', 'totalPlaced'));
    }

    public function show(User $user)
    {
        $playerRating = $user->playerRating;

        if (! $playerRating || ! $playerRating->opted_in_at) {
            abort(404, 'This player is not in competitive mode.');
        }

        $recentHistory = RatingHistory::where('player_rating_id', $playerRating->id)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        // Rank among placed players
        $rank = null;
        if ($playerRating->is_placed) {
            $rank = PlayerRating::ranked()
                ->where('rating', '>', $playerRating->rating)
                ->count() + 1;
        }

        return view('ranked.show', compact('user', 'playerRating', 'recentHistory', 'rank'));
    }

    public function history(User $user): JsonResponse
    {
        $playerRating = $user->playerRating;

        if (! $playerRating || ! $playerRating->opted_in_at) {
            return response()->json(['labels' => [], 'data' => []]);
        }

        $history = RatingHistory::where('player_rating_id', $playerRating->id)
            ->orderBy('created_at')
            ->get(['rating_after', 'created_at']);

        return response()->json([
            'labels' => $history->map(fn ($h) => $h->created_at->format('M j, H:i'))->values(),
            'data' => $history->pluck('rating_after')->values(),
        ]);
    }

    public function about()
    {
        $tierDistribution = PlayerRating::competitive()
            ->placed()
            ->select('rank_tier', DB::raw('COUNT(*) as count'))
            ->groupBy('rank_tier')
            ->pluck('count', 'rank_tier')
            ->toArray();

        return view('ranked.about', compact('tierDistribution'));
    }

    public function optIn(Request $request)
    {
        $user = Auth::user();

        if (! $user->hasLinkedArmaId()) {
            return back()->with('error', 'You must link your Arma Reforger ID before enabling competitive mode.');
        }

        $rating = PlayerRating::firstOrCreate(
            ['user_id' => $user->id],
            [
                'player_uuid' => $user->player_uuid,
                'rating' => 1500,
                'rating_deviation' => 350,
                'volatility' => 0.06,
                'rank_tier' => 'unranked',
                'opted_in_at' => now(),
            ]
        );

        if (! $rating->opted_in_at) {
            $rating->update(['opted_in_at' => now(), 'player_uuid' => $user->player_uuid]);
        }

        // Clear cached UUID set
        Cache::forget('competitive_player_uuids');

        return back()->with('success', 'Competitive mode enabled! Your kills against other competitive players will now affect your rating.');
    }

    public function optOut(Request $request)
    {
        $user = Auth::user();
        $rating = $user->playerRating;

        if ($rating) {
            $rating->update(['opted_in_at' => null]);
            Cache::forget('competitive_player_uuids');
        }

        return back()->with('success', 'Competitive mode disabled. Your rating data is preserved — you can re-enable anytime.');
    }
}
