<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnticheatController extends Controller
{
    public function storeEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|in:ENFORCEMENT_ACTION,ENFORCEMENT_SKIPPED,LIFESTATE,SPAWN_GRACE,OTHER,UNKNOWN',
            'player_name' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'is_admin' => 'nullable|boolean',
            'reason' => 'nullable|string|max:500',
            'raw' => 'nullable|string|max:2000',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('anticheat_events')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'player_name' => $validated['player_name'] ?? null,
            'player_id' => $validated['player_id'] ?? null,
            'is_admin' => $validated['is_admin'] ?? false,
            'reason' => $validated['reason'] ?? null,
            'raw' => $validated['raw'] ?? null,
            'event_time' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeStat(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'active_players' => 'nullable|integer',
            'online_players' => 'nullable|integer',
            'registered_players' => 'nullable|integer',
            'potential_cheaters' => 'nullable|integer',
            'banned_players' => 'nullable|string',
            'confirmed_cheaters' => 'nullable|string',
            'potentials_list' => 'nullable|string',
            'top_movement' => 'nullable|string',
            'top_collision' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('anticheat_stats')->insertGetId([
            'server_id' => $validated['server_id'],
            'active_players' => $validated['active_players'] ?? 0,
            'online_players' => $validated['online_players'] ?? 0,
            'registered_players' => $validated['registered_players'] ?? 0,
            'potential_cheaters' => $validated['potential_cheaters'] ?? 0,
            'banned_players' => $validated['banned_players'] ?? '[]',
            'confirmed_cheaters' => $validated['confirmed_cheaters'] ?? '[]',
            'potentials_list' => $validated['potentials_list'] ?? '[]',
            'top_movement' => $validated['top_movement'] ?? '[]',
            'top_collision' => $validated['top_collision'] ?? '[]',
            'event_time' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }
}
