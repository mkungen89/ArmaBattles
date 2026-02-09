<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\DB;

class GameEventController extends Controller
{
    // ============ KILLS & COMBAT ============

    public function playerKill(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'killer_name' => 'required|string|max:255',
            'killer_uuid' => 'nullable|string|max:255',
            'killer_faction' => 'nullable|string|max:255',
            'victim_type' => 'required|string|max:50',
            'victim_name' => 'nullable|string|max:255',
            'victim_uuid' => 'nullable|string|max:255',
            'weapon_name' => 'required|string|max:255',
            'weapon_type' => 'nullable|string|max:100',
            'kill_distance' => 'nullable|numeric',
            'is_team_kill' => 'nullable|boolean',
            'is_headshot' => 'nullable|boolean',
            'event_type' => 'nullable|string|max:50',
            'timestamp' => 'required|integer',
            // New ReforgerJS fields
            'killer_in_vehicle' => 'nullable|boolean',
            'killer_vehicle' => 'nullable|string|max:255',
            'killer_vehicle_prefab' => 'nullable|string|max:255',
            'killer_position' => 'nullable|array',
            'victim_position' => 'nullable|array',
            'ai_type' => 'nullable|string|max:100',
            'killer_role' => 'nullable|string|max:100',
            'killer_platform' => 'nullable|string|max:50',
            'damage_type' => 'nullable|string|max:100',
        ]);

        $killId = DB::table('player_kills')->insertGetId([
            'server_id' => $validated['server_id'],
            'killer_name' => $validated['killer_name'],
            'killer_uuid' => $validated['killer_uuid'] ?? null,
            'killer_faction' => str_replace('#AR-Faction_', '', $validated['killer_faction'] ?? ''),
            'killer_in_vehicle' => $validated['killer_in_vehicle'] ?? false,
            'killer_vehicle' => $validated['killer_vehicle'] ?? null,
            'killer_vehicle_prefab' => $validated['killer_vehicle_prefab'] ?? null,
            'killer_position' => json_encode($validated['killer_position'] ?? null),
            'killer_role' => $validated['killer_role'] ?? null,
            'killer_platform' => $validated['killer_platform'] ?? null,
            'damage_type' => $validated['damage_type'] ?? null,
            'victim_type' => $validated['victim_type'],
            'ai_type' => $validated['ai_type'] ?? null,
            'victim_name' => $validated['victim_name'] ?? null,
            'victim_uuid' => $validated['victim_uuid'] ?? null,
            'victim_position' => json_encode($validated['victim_position'] ?? null),
            'weapon_name' => $validated['weapon_name'],
            'weapon_type' => str_replace('WT_', '', $validated['weapon_type'] ?? ''),
            'kill_distance' => $validated['kill_distance'] ?? 0,
            'is_team_kill' => $validated['is_team_kill'] ?? false,
            'is_headshot' => $validated['is_headshot'] ?? false,
            'event_type' => $validated['event_type'] ?? 'UNKNOWN',
            'killed_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update player stats
        $this->incrementPlayerStat($validated['server_id'], $validated['killer_uuid'], $validated['killer_name'], 'kills');
        if ($validated['is_headshot'] ?? false) {
            $this->incrementPlayerStat($validated['server_id'], $validated['killer_uuid'], $validated['killer_name'], 'headshots');
        }
        if ($validated['is_team_kill'] ?? false) {
            $this->incrementPlayerStat($validated['server_id'], $validated['killer_uuid'], $validated['killer_name'], 'team_kills');
        }
        if ($validated['victim_type'] === 'PLAYER' && !empty($validated['victim_uuid'])) {
            $this->incrementPlayerStat($validated['server_id'], $validated['victim_uuid'], $validated['victim_name'] ?? 'Unknown', 'deaths');
        }

        Log::info('Kill recorded', [
            'killer' => $validated['killer_name'],
            'weapon' => $validated['weapon_name'],
            'distance' => $validated['kill_distance'] ?? 0,
        ]);

        return response()->json(['success' => true, 'kill_id' => $killId]);
    }

    public function playerDamage(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'attacker_name' => 'required|string|max:255',
            'attacker_uuid' => 'nullable|string|max:255',
            'victim_name' => 'required|string|max:255',
            'victim_uuid' => 'nullable|string|max:255',
            'weapon_name' => 'nullable|string|max:255',
            'damage' => 'required|numeric',
            'hit_zone' => 'nullable|string|max:100',
            'is_friendly_fire' => 'nullable|boolean',
            'timestamp' => 'required|integer',
        ]);

        $isHeadshot = strtolower($validated['hit_zone'] ?? '') === 'head';

        DB::table('player_damage')->insert([
            'server_id' => $validated['server_id'],
            'attacker_name' => $validated['attacker_name'],
            'attacker_uuid' => $validated['attacker_uuid'] ?? null,
            'victim_name' => $validated['victim_name'],
            'victim_uuid' => $validated['victim_uuid'] ?? null,
            'weapon_name' => $validated['weapon_name'] ?? 'Unknown',
            'damage' => $validated['damage'],
            'hit_zone' => $validated['hit_zone'] ?? null,
            'is_friendly_fire' => $validated['is_friendly_fire'] ?? false,
            'is_headshot' => $isHeadshot,
            'damaged_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ============ PLAYERS ============

    public function playerConnect(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'platform' => 'nullable|string|max:50',
            'ip_address' => 'nullable|string|max:45',
            'timestamp' => 'required|integer',
        ]);

        $sessionId = DB::table('player_sessions')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'platform' => str_replace('platform-', '', $validated['platform'] ?? 'unknown'),
            'ip_address' => $validated['ip_address'] ?? null,
            'connected_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create/update player stats record
        if ($validated['player_uuid']) {
            $this->ensurePlayerStats($validated['server_id'], $validated['player_uuid'], $validated['player_name']);
        }

        return response()->json(['success' => true, 'session_id' => $sessionId]);
    }

    public function playerDisconnect(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'timestamp' => 'required|integer',
        ]);

        // Update the latest session for this player
        $session = DB::table('player_sessions')
            ->where('server_id', $validated['server_id'])
            ->where('player_name', $validated['player_name'])
            ->whereNull('disconnected_at')
            ->orderBy('connected_at', 'desc')
            ->first();

        if ($session) {
            $disconnectedAt = $this->timestampToDatetime($validated['timestamp']);
            $playtime = strtotime($disconnectedAt) - strtotime($session->connected_at);

            DB::table('player_sessions')
                ->where('id', $session->id)
                ->update([
                    'disconnected_at' => $disconnectedAt,
                    'playtime_seconds' => max(0, $playtime),
                    'updated_at' => now(),
                ]);

            // Update total playtime in stats
            if ($validated['player_uuid']) {
                $this->incrementPlayerStat($validated['server_id'], $validated['player_uuid'], $validated['player_name'], 'playtime_seconds', max(0, $playtime));
            }
        }

        return response()->json(['success' => true]);
    }

    public function playerStats(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_uuid' => 'required|string|max:255',
            'player_name' => 'nullable|string|max:255',
            'kills' => 'nullable|integer',
            'deaths' => 'nullable|integer',
            'playtime' => 'nullable|numeric',
            'timestamp' => 'required|integer',
        ]);

        DB::table('player_stats')->updateOrInsert(
            ['server_id' => $validated['server_id'], 'player_uuid' => $validated['player_uuid']],
            [
                'player_name' => $validated['player_name'] ?? 'Unknown',
                'kills' => $validated['kills'] ?? 0,
                'deaths' => $validated['deaths'] ?? 0,
                'playtime_seconds' => (int)($validated['playtime'] ?? 0),
                'last_seen_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    // ============ MEDICAL ============

    public function playerHealing(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'healer_name' => 'required|string|max:255',
            'healer_uuid' => 'nullable|string|max:255',
            'patient_name' => 'required|string|max:255',
            'patient_uuid' => 'nullable|string|max:255',
            'item_used' => 'required|string|max:100',
            'health_restored' => 'nullable|numeric',
            'timestamp' => 'required|integer',
        ]);

        $isSelfHeal = $validated['healer_name'] === $validated['patient_name'];

        DB::table('healing_events')->insert([
            'server_id' => $validated['server_id'],
            'healer_name' => $validated['healer_name'],
            'healer_uuid' => $validated['healer_uuid'] ?? null,
            'patient_name' => $validated['patient_name'],
            'patient_uuid' => $validated['patient_uuid'] ?? null,
            'item_used' => $validated['item_used'],
            'health_restored' => $validated['health_restored'] ?? 0,
            'is_self_heal' => $isSelfHeal,
            'healed_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->incrementPlayerStat($validated['server_id'], $validated['healer_uuid'], $validated['healer_name'], 'heals_given');
        if (!$isSelfHeal) {
            $this->incrementPlayerStat($validated['server_id'], $validated['patient_uuid'], $validated['patient_name'], 'heals_received');
        }

        return response()->json(['success' => true]);
    }

    // ============ OBJECTIVES ============

    public function baseCapture(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'base_name' => 'required|string|max:255',
            'capturing_faction' => 'required|string|max:100',
            'losing_faction' => 'nullable|string|max:100',
            'participating_players' => 'nullable|array',
            'capture_type' => 'nullable|string|max:50',
            'timestamp' => 'required|integer',
        ]);

        DB::table('base_captures')->insert([
            'server_id' => $validated['server_id'],
            'base_name' => $validated['base_name'],
            'capturing_faction' => $validated['capturing_faction'],
            'losing_faction' => $validated['losing_faction'] ?? null,
            'participating_players' => json_encode($validated['participating_players'] ?? []),
            'capture_type' => $validated['capture_type'] ?? 'capture',
            'captured_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update stats for participating players
        foreach ($validated['participating_players'] ?? [] as $uuid) {
            DB::table('player_stats')
                ->where('player_uuid', $uuid)
                ->increment('bases_captured', 1, ['updated_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    public function supplyDelivery(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'supply_type' => 'required|string|max:100',
            'amount' => 'nullable|integer',
            'destination' => 'nullable|string|max:255',
            'timestamp' => 'required|integer',
        ]);

        DB::table('supply_deliveries')->insert([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'supply_type' => $validated['supply_type'],
            'amount' => $validated['amount'] ?? 1,
            'destination' => $validated['destination'] ?? null,
            'delivered_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->incrementPlayerStat($validated['server_id'], $validated['player_uuid'], $validated['player_name'], 'supplies_delivered');

        return response()->json(['success' => true]);
    }

    // ============ WEAPONS & VEHICLES ============

    public function shotFired(Request $request)
    {
        $result = $this->recordWeaponUsage($request, 'shot_fired');
        $this->incrementPlayerStat($request->input('server_id'), $request->input('player_uuid'), $request->input('player_name'), 'shots_fired', $request->input('count', 1));
        return $result;
    }

    public function grenadeThrown(Request $request)
    {
        $result = $this->recordWeaponUsage($request, 'grenade_thrown');
        $this->incrementPlayerStat($request->input('server_id'), $request->input('player_uuid'), $request->input('player_name'), 'grenades_thrown');
        return $result;
    }

    public function explosivePlaced(Request $request)
    {
        return $this->recordWeaponUsage($request, 'explosive_placed');
    }

    private function recordWeaponUsage(Request $request, string $actionType)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'weapon_name' => 'required|string|max:255',
            'weapon_type' => 'nullable|string|max:100',
            'count' => 'nullable|integer',
            'timestamp' => 'required|integer',
        ]);

        DB::table('weapon_usage')->insert([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'action_type' => $actionType,
            'weapon_name' => $validated['weapon_name'],
            'weapon_type' => $validated['weapon_type'] ?? null,
            'count' => $validated['count'] ?? 1,
            'used_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function vehicleEvent(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'nullable|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'vehicle_type' => 'required|string|max:100',
            'vehicle_name' => 'required|string|max:255',
            'event_type' => 'required|string|max:50',
            'destroyed_by' => 'nullable|string|max:255',
            'destroyed_by_uuid' => 'nullable|string|max:255',
            'destruction_weapon' => 'nullable|string|max:255',
            'timestamp' => 'required|integer',
        ]);

        DB::table('vehicle_events')->insert([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'] ?? null,
            'player_uuid' => $validated['player_uuid'] ?? null,
            'vehicle_type' => $validated['vehicle_type'],
            'vehicle_name' => $validated['vehicle_name'],
            'event_type' => $validated['event_type'],
            'destroyed_by' => $validated['destroyed_by'] ?? null,
            'destroyed_by_uuid' => $validated['destroyed_by_uuid'] ?? null,
            'destruction_weapon' => $validated['destruction_weapon'] ?? null,
            'event_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        if ($validated['event_type'] === 'destroyed' && $validated['destroyed_by_uuid']) {
            DB::table('player_stats')
                ->where('player_uuid', $validated['destroyed_by_uuid'])
                ->increment('vehicles_destroyed', 1, ['updated_at' => now()]);
        }

        return response()->json(['success' => true]);
    }

    // ============ SOCIAL & ADMIN ============

    public function chatMessage(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'channel' => 'required|string|max:50',
            'message' => 'required|string|max:2000',
            'recipient_name' => 'nullable|string|max:255',
            'recipient_uuid' => 'nullable|string|max:255',
            'timestamp' => 'required|integer',
        ]);

        DB::table('chat_messages')->insert([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'channel' => $validated['channel'],
            'message' => $validated['message'],
            'recipient_name' => $validated['recipient_name'] ?? null,
            'recipient_uuid' => $validated['recipient_uuid'] ?? null,
            'sent_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function squadChange(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'action_type' => 'required|string|max:50',
            'squad_name' => 'nullable|string|max:255',
            'faction' => 'nullable|string|max:100',
            'role' => 'nullable|string|max:50',
            'timestamp' => 'required|integer',
        ]);

        DB::table('squad_changes')->insert([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'action_type' => $validated['action_type'],
            'squad_name' => $validated['squad_name'] ?? null,
            'faction' => $validated['faction'] ?? null,
            'role' => $validated['role'] ?? null,
            'changed_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    public function gmAction(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'gm_name' => 'required|string|max:255',
            'gm_uuid' => 'nullable|string|max:255',
            'action_type' => 'required|string|max:100',
            'target_type' => 'nullable|string|max:50',
            'target_name' => 'nullable|string|max:255',
            'action_details' => 'nullable|array',
            'timestamp' => 'required|integer',
        ]);

        DB::table('gm_actions')->insert([
            'server_id' => $validated['server_id'],
            'gm_name' => $validated['gm_name'],
            'gm_uuid' => $validated['gm_uuid'] ?? null,
            'action_type' => $validated['action_type'],
            'target_type' => $validated['target_type'] ?? null,
            'target_name' => $validated['target_name'] ?? null,
            'action_details' => json_encode($validated['action_details'] ?? []),
            'action_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ============ GAME STATE ============

    public function gameStart(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'game_mode' => 'required|string|max:100',
            'map_name' => 'nullable|string|max:255',
            'scenario' => 'nullable|string|max:255',
            'timestamp' => 'required|integer',
        ]);

        $sessionId = DB::table('game_sessions')->insertGetId([
            'server_id' => $validated['server_id'],
            'game_mode' => $validated['game_mode'],
            'map_name' => $validated['map_name'] ?? null,
            'scenario' => $validated['scenario'] ?? null,
            'started_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'session_id' => $sessionId]);
    }

    public function gameEnd(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'winner_faction' => 'nullable|string|max:100',
            'final_scores' => 'nullable|array',
            'timestamp' => 'required|integer',
        ]);

        // Update the latest game session
        $session = DB::table('game_sessions')
            ->where('server_id', $validated['server_id'])
            ->whereNull('ended_at')
            ->orderBy('started_at', 'desc')
            ->first();

        if ($session) {
            $endedAt = $this->timestampToDatetime($validated['timestamp']);
            $duration = strtotime($endedAt) - strtotime($session->started_at);

            DB::table('game_sessions')
                ->where('id', $session->id)
                ->update([
                    'ended_at' => $endedAt,
                    'winner_faction' => $validated['winner_faction'] ?? null,
                    'final_scores' => json_encode($validated['final_scores'] ?? []),
                    'duration_seconds' => max(0, $duration),
                    'updated_at' => now(),
                ]);
        }

        return response()->json(['success' => true]);
    }

    public function serverStatus(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'players_online' => 'nullable|integer',
            'max_players' => 'nullable|integer',
            'ai_count' => 'nullable|integer',
            'fps' => 'nullable|numeric',
            'memory_mb' => 'nullable|integer',
            'uptime_seconds' => 'nullable|integer',
            'timestamp' => 'required|integer',
        ]);

        DB::table('server_status')->insert([
            'server_id' => $validated['server_id'],
            'players_online' => $validated['players_online'] ?? 0,
            'max_players' => $validated['max_players'] ?? 64,
            'ai_count' => $validated['ai_count'] ?? 0,
            'fps' => $validated['fps'] ?? null,
            'memory_mb' => $validated['memory_mb'] ?? null,
            'uptime_seconds' => $validated['uptime_seconds'] ?? 0,
            'recorded_at' => $this->timestampToDatetime($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true]);
    }

    // ============ REFORGERJS SUPPORT ENDPOINTS ============

    public function playerDistance(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'required|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:100',
            'walking_distance' => 'required|numeric',
            'walking_time_seconds' => 'required|numeric',
            'total_vehicle_distance' => 'required|numeric',
            'total_vehicle_time_seconds' => 'required|numeric',
            'vehicles' => 'nullable|array',
            'is_final_log' => 'nullable|boolean',
            'timestamp' => 'required',
        ]);

        $distanceId = DB::table('player_distance')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'],
            'player_id' => $validated['player_id'] ?? null,
            'player_platform' => $validated['player_platform'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'walking_distance' => $validated['walking_distance'],
            'walking_time_seconds' => $validated['walking_time_seconds'],
            'total_vehicle_distance' => $validated['total_vehicle_distance'],
            'total_vehicle_time_seconds' => $validated['total_vehicle_time_seconds'],
            'vehicles' => json_encode($validated['vehicles'] ?? []),
            'is_final_log' => $validated['is_final_log'] ?? false,
            'event_time' => $this->parseTimestamp($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update player's total distance in stats if final log
        if ($validated['is_final_log'] ?? false) {
            $totalDistance = $validated['walking_distance'] + $validated['total_vehicle_distance'];
            $this->incrementPlayerStat(
                $validated['server_id'],
                $validated['player_uuid'],
                $validated['player_name'],
                'total_distance',
                (int) $totalDistance
            );
        }

        return response()->json(['success' => true, 'id' => $distanceId]);
    }

    public function playerGrenades(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'required|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:100',
            'grenade_type' => 'required|string|max:255',
            'position' => 'nullable|array',
            'timestamp' => 'required',
        ]);

        $grenadeId = DB::table('player_grenades')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'],
            'player_id' => $validated['player_id'] ?? null,
            'player_platform' => $validated['player_platform'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'grenade_type' => $validated['grenade_type'],
            'position' => json_encode($validated['position'] ?? null),
            'event_time' => $this->parseTimestamp($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update player stats
        $this->incrementPlayerStat(
            $validated['server_id'],
            $validated['player_uuid'],
            $validated['player_name'],
            'grenades_thrown'
        );

        return response()->json(['success' => true, 'id' => $grenadeId]);
    }

    public function playerShooting(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'required|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:100',
            'weapons' => 'required|string|max:1000',
            'total_rounds' => 'required|integer',
            'timestamp' => 'required',
        ]);

        $shootingId = DB::table('player_shooting')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'],
            'player_id' => $validated['player_id'] ?? null,
            'player_platform' => $validated['player_platform'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'weapons' => $validated['weapons'],
            'total_rounds' => $validated['total_rounds'],
            'event_time' => $this->parseTimestamp($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update player stats
        $this->incrementPlayerStat(
            $validated['server_id'],
            $validated['player_uuid'],
            $validated['player_name'],
            'shots_fired',
            $validated['total_rounds']
        );

        return response()->json(['success' => true, 'id' => $shootingId]);
    }

    public function playerHealingRjs(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'healer_name' => 'required|string|max:255',
            'healer_uuid' => 'required|string|max:255',
            'healer_id' => 'nullable|integer',
            'target_name' => 'nullable|string|max:255',
            'target_uuid' => 'nullable|string|max:255',
            'target_id' => 'nullable|integer',
            'heal_type' => 'required|string|max:100',
            'heal_amount' => 'nullable|numeric',
            'is_self_heal' => 'nullable|boolean',
            'timestamp' => 'required',
        ]);

        $isSelfHeal = $validated['is_self_heal'] ?? ($validated['healer_uuid'] === ($validated['target_uuid'] ?? null));

        $healingId = DB::table('player_healing_rjs')->insertGetId([
            'server_id' => $validated['server_id'],
            'healer_name' => $validated['healer_name'],
            'healer_uuid' => $validated['healer_uuid'],
            'healer_id' => $validated['healer_id'] ?? null,
            'target_name' => $validated['target_name'] ?? null,
            'target_uuid' => $validated['target_uuid'] ?? null,
            'target_id' => $validated['target_id'] ?? null,
            'heal_type' => $validated['heal_type'],
            'heal_amount' => $validated['heal_amount'] ?? null,
            'is_self_heal' => $isSelfHeal,
            'event_time' => $this->parseTimestamp($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update player stats
        $this->incrementPlayerStat(
            $validated['server_id'],
            $validated['healer_uuid'],
            $validated['healer_name'],
            'heals_given'
        );

        if (!$isSelfHeal && !empty($validated['target_uuid'])) {
            $this->incrementPlayerStat(
                $validated['server_id'],
                $validated['target_uuid'],
                $validated['target_name'] ?? 'Unknown',
                'heals_received'
            );
        }

        return response()->json(['success' => true, 'id' => $healingId]);
    }

    public function vehicleEventsRjs(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'vehicle_name' => 'required|string|max:255',
            'vehicle_prefab' => 'nullable|string|max:255',
            'player_name' => 'nullable|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'destroyed_by' => 'nullable|string|max:255',
            'timestamp' => 'required',
        ]);

        $eventId = DB::table('vehicle_events_rjs')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'vehicle_name' => $validated['vehicle_name'],
            'vehicle_prefab' => $validated['vehicle_prefab'] ?? null,
            'player_name' => $validated['player_name'] ?? null,
            'player_uuid' => $validated['player_uuid'] ?? null,
            'position' => json_encode($validated['position'] ?? null),
            'destroyed_by' => $validated['destroyed_by'] ?? null,
            'event_time' => $this->parseTimestamp($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update vehicle destroyed stats
        if ($validated['event_type'] === 'VEHICLE_DESTROYED' && !empty($validated['player_uuid'])) {
            $this->incrementPlayerStat(
                $validated['server_id'],
                $validated['player_uuid'],
                $validated['player_name'] ?? 'Unknown',
                'vehicles_destroyed'
            );
        }

        return response()->json(['success' => true, 'id' => $eventId]);
    }

    public function supplyDeliveriesRjs(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'required|string|max:255',
            'supply_type' => 'required|string|max:100',
            'amount' => 'required|integer',
            'base_name' => 'nullable|string|max:255',
            'timestamp' => 'required',
        ]);

        $deliveryId = DB::table('supply_deliveries_rjs')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'],
            'supply_type' => $validated['supply_type'],
            'amount' => $validated['amount'],
            'base_name' => $validated['base_name'] ?? null,
            'event_time' => $this->parseTimestamp($validated['timestamp']),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Update player stats
        $this->incrementPlayerStat(
            $validated['server_id'],
            $validated['player_uuid'],
            $validated['player_name'],
            'supplies_delivered',
            $validated['amount']
        );

        return response()->json(['success' => true, 'id' => $deliveryId]);
    }

    // ============ LEGACY WEBHOOK ============

    public function legacyWebhook(Request $request)
    {
        // Handle Server Admin Tools webhooks
        Log::info('Legacy webhook received', $request->all());
        return response()->json(['success' => true, 'message' => 'Event received']);
    }

    // ============ HELPERS ============

    private function timestampToDatetime(int $timestamp): string
    {
        // Handle both seconds and milliseconds
        if ($timestamp > 9999999999) {
            $timestamp = (int)($timestamp / 1000);
        }
        return date('Y-m-d H:i:s', $timestamp);
    }

    private function parseTimestamp($timestamp): string
    {
        // Handle various timestamp formats
        if (is_numeric($timestamp)) {
            return $this->timestampToDatetime((int) $timestamp);
        }
        // Try parsing as date string
        try {
            return date('Y-m-d H:i:s', strtotime($timestamp));
        } catch (\Exception $e) {
            return date('Y-m-d H:i:s');
        }
    }

    private function ensurePlayerStats(int $serverId, string $uuid, string $name): void
    {
        DB::table('player_stats')->updateOrInsert(
            ['server_id' => $serverId, 'player_uuid' => $uuid],
            [
                'player_name' => $name,
                'last_seen_at' => now(),
                'updated_at' => now(),
                'created_at' => now(),
            ]
        );
    }

    private function incrementPlayerStat(int $serverId, ?string $uuid, ?string $name, string $field, int $amount = 1): void
    {
        if (!$uuid) return;

        // Ensure player exists
        $this->ensurePlayerStats($serverId, $uuid, $name ?? 'Unknown');

        DB::table('player_stats')
            ->where('server_id', $serverId)
            ->where('player_uuid', $uuid)
            ->increment($field, $amount, ['updated_at' => now()]);
    }
}
