<?php

namespace App\Http\Controllers;

use App\Models\User;
use App\Services\PlayerHistoryService;
use Illuminate\Http\Request;

class PlayerSearchController extends Controller
{
    public function search(Request $request, PlayerHistoryService $playerHistory)
    {
        $query = $request->get('q', '');
        $players = collect();
        $linkedUsers = collect();

        if (strlen($query) >= 2) {
            $results = $playerHistory->search($query, null, 50);

            $uuids = collect($results)->pluck('player_uuid')->filter()->toArray();
            $linkedUsers = User::whereIn('player_uuid', $uuids)
                ->get(['id', 'player_uuid', 'avatar', 'name'])
                ->keyBy('player_uuid');

            $players = collect($results);
        }

        return view('players.search', compact('players', 'linkedUsers', 'query'));
    }

    public function apiSearch(Request $request, PlayerHistoryService $playerHistory)
    {
        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $results = $playerHistory->search($query, null, 10);

        $uuids = collect($results)->pluck('player_uuid')->filter()->toArray();
        $linkedUsers = User::whereIn('player_uuid', $uuids)
            ->get(['id', 'player_uuid', 'avatar', 'name'])
            ->keyBy('player_uuid');

        $items = collect($results)->map(function ($r) use ($linkedUsers) {
            $user = $linkedUsers[$r->player_uuid] ?? null;

            return [
                'name' => $r->player_name,
                'uuid' => $r->player_uuid,
                'url' => $user ? route('players.show', $user->id) : null,
                'avatar' => $user?->avatar_display,
                'connections' => $r->connection_count,
            ];
        });

        return response()->json($items);
    }
}
