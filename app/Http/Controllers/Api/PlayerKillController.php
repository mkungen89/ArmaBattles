<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Player;
use App\Models\PlayerKill;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class PlayerKillController extends Controller
{
    public function store(Request $request): JsonResponse
    {
        // Validate request
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'killer_name' => 'required|string|max:255',
            'killer_uuid' => 'nullable|string|max:255',
            'killer_faction' => 'nullable|string|max:255',
            'victim_type' => 'required|string|max:50', // AI, PLAYER
            'victim_name' => 'nullable|string|max:255',
            'victim_uuid' => 'nullable|string|max:255',
            'weapon_name' => 'required|string|max:255',
            'weapon_type' => 'nullable|string|max:100', // WT_RIFLE, WT_PISTOL, etc.
            'weapon_source' => 'nullable|string|max:100',
            'sight_name' => 'nullable|string|max:255',
            'attachments' => 'nullable|string|max:500',
            'grenade_type' => 'nullable|string|max:100',
            'kill_distance' => 'nullable|numeric',
            'is_team_kill' => 'nullable|boolean',
            'event_type' => 'nullable|string|max:50', // AI_KILLED, PLAYER_KILLED
            'timestamp' => 'required|integer',
        ]);

        // Save to database
        $kill = PlayerKill::create([
            'server_id' => $validated['server_id'],
            'killer_name' => $validated['killer_name'],
            'killer_uuid' => $validated['killer_uuid'] ?? null,
            'killer_faction' => $validated['killer_faction'] ?? null,
            'victim_type' => $validated['victim_type'],
            'victim_name' => $validated['victim_name'] ?? null,
            'victim_uuid' => $validated['victim_uuid'] ?? null,
            'weapon_name' => $validated['weapon_name'],
            'weapon_type' => $validated['weapon_type'] ?? null,
            'weapon_source' => $validated['weapon_source'] ?? null,
            'sight_name' => $validated['sight_name'] ?? null,
            'attachments' => $validated['attachments'] ?? null,
            'grenade_type' => $validated['grenade_type'] ?? null,
            'kill_distance' => $validated['kill_distance'] ?? 0,
            'is_team_kill' => $validated['is_team_kill'] ?? false,
            'event_type' => $validated['event_type'] ?? 'UNKNOWN',
            'killed_at' => date('Y-m-d H:i:s', $validated['timestamp'] / 1000),
        ]);

        // Update player stats
        if (!empty($validated['killer_uuid'])) {
            $player = Player::updateOrCreate(
                ['uuid' => $validated['killer_uuid']],
                [
                    'player_name' => $validated['killer_name'],
                    'last_seen' => now(),
                ]
            );
            $player->increment('kills');
        }

        // Update victim stats if player
        if ($validated['victim_type'] === 'PLAYER' && !empty($validated['victim_uuid'])) {
            $victim = Player::updateOrCreate(
                ['uuid' => $validated['victim_uuid']],
                [
                    'player_name' => $validated['victim_name'] ?? 'Unknown',
                    'last_seen' => now(),
                ]
            );
            $victim->increment('deaths');
        }

        Log::info('Kill recorded', [
            'killer' => $validated['killer_name'],
            'victim_type' => $validated['victim_type'],
            'weapon' => $validated['weapon_name'],
            'distance' => $validated['kill_distance'] ?? 0,
        ]);

        return response()->json([
            'success' => true,
            'message' => 'Kill recorded',
            'kill_id' => $kill->id
        ]);
    }
}
