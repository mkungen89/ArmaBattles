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
            ->orderByDesc('created_at')
            ->get()
            ->groupBy('favoritable_type');

        $players = collect();
        $teams = collect();
        $servers = collect();

        if ($favorites->has(User::class)) {
            $playerIds = $favorites[User::class]->pluck('favoritable_id');
            $players = User::whereIn('id', $playerIds)->get();
        }

        if ($favorites->has(Team::class)) {
            $teamIds = $favorites[Team::class]->pluck('favoritable_id');
            $teams = Team::whereIn('id', $teamIds)->with('captain')->get();
        }

        if ($favorites->has(Server::class)) {
            $serverIds = $favorites[Server::class]->pluck('favoritable_id');
            $servers = Server::whereIn('id', $serverIds)->get();
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

        $added = $request->user()->toggleFavorite($model);

        if ($request->wantsJson()) {
            return response()->json(['favorited' => $added]);
        }

        return back()->with('success', $added ? 'Added to favorites.' : 'Removed from favorites.');
    }
}
