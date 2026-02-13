<?php

namespace App\Http\Controllers;

use App\Models\Favorite;
use App\Models\Server;
use App\Models\Team;
use App\Models\User;
use Illuminate\Http\Request;

class FavoriteController extends Controller
{
    public function index(Request $request)
    {
        $user = $request->user();

        $favorites = Favorite::where('user_id', $user->id)
            ->with('favoritable')
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('favoritable_type');

        $players = collect();
        $teams = collect();
        $servers = collect();

        if ($favorites->has(User::class)) {
            $players = $favorites[User::class]->pluck('favoritable')->filter();
        }

        if ($favorites->has(Team::class)) {
            $teams = $favorites[Team::class]->pluck('favoritable')->filter();
            // Load captain relationship for teams
            $teamIds = $teams->pluck('id')->toArray();
            if (!empty($teamIds)) {
                $teams = Team::whereIn('id', $teamIds)->with('captain')->get();
            }
        }

        if ($favorites->has(Server::class)) {
            $servers = $favorites[Server::class]->pluck('favoritable')->filter();
        }

        return view('favorites.index', compact('players', 'teams', 'servers'));
    }

    public function toggle(Request $request)
    {
        $request->validate([
            'type' => 'required|in:player,team,server',
            'id' => 'required|integer',
        ]);

        $model = match ($request->type) {
            'player' => User::findOrFail($request->id),
            'team' => Team::findOrFail($request->id),
            'server' => Server::findOrFail($request->id),
        };

        // Prevent users from favoriting themselves
        if ($request->type === 'player' && $request->user() && $model->id === $request->user()->id) {
            if ($request->wantsJson()) {
                return response()->json(['error' => 'You cannot favorite yourself.'], 422);
            }

            return back()->with('error', 'You cannot favorite yourself.');
        }

        $added = $request->user()->toggleFavorite($model);

        if ($request->wantsJson()) {
            return response()->json(['favorited' => $added]);
        }

        return back()->with('success', $added ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
