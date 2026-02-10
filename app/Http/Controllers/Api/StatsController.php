<?php

namespace App\Http\Controllers\Api;

use App\Events\ActivityFeedUpdated;
use App\Events\BaseEventOccurred;
use App\Events\KillFeedUpdated;
use App\Events\PlayerConnected;
use App\Events\ServerStatusUpdated;
use App\Http\Controllers\Controller;
use App\Jobs\SendDiscordNotification;
use App\Models\DamageEvent;
use App\Models\PlayerDistance;
use App\Models\PlayerReport;
use App\Models\XpEvent;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class StatsController extends Controller
{
    // ========================================
    // WRITE ENDPOINTS (från servern)
    // ========================================

    public function storeServerStatus(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'server_name' => 'nullable|string|max:255',
            'map' => 'nullable|string|max:255',
            'players' => 'nullable|integer',
            'max_players' => 'nullable|integer',
            'ping' => 'nullable|integer',
            'timestamp' => 'nullable|string',
        ]);

        DB::table('server_status')->updateOrInsert(
            ['server_id' => $validated['server_id']],
            [
                'server_name' => $validated['server_name'] ?? 'Unknown',
                'map' => $validated['map'] ?? 'Unknown',
                'players' => $validated['players'] ?? 0,
                'max_players' => $validated['max_players'] ?? 64,
                'ping' => $validated['ping'] ?? 0,
                'recorded_at' => now(),
                'last_updated' => now(),
            ]
        );

        ServerStatusUpdated::dispatch(
            $validated['server_id'],
            $validated['players'] ?? 0,
            $validated['max_players'] ?? 64,
            $validated['map'] ?? null,
            $validated['server_name'] ?? null,
        );

        return response()->json(['success' => true]);
    }

    public function storeKill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'killer_name' => 'nullable|string|max:255',
            'killer_uuid' => 'nullable|string|max:255',
            'killer_faction' => 'nullable|string|max:255',
            'victim_name' => 'nullable|string|max:255',
            'victim_uuid' => 'nullable|string|max:255',
            'victim_type' => 'nullable|string|max:50',
            'weapon_name' => 'nullable|string|max:255',
            'weapon_type' => 'nullable|string|max:255',
            'kill_distance' => 'nullable|numeric',
            'is_headshot' => 'nullable|boolean',
            'is_team_kill' => 'nullable|boolean',
            'is_roadkill' => 'nullable|boolean',
            'event_type' => 'nullable|string|max:50',
            'timestamp' => 'nullable|string',
            'killer_position' => 'nullable|array',
            'killer_position.*' => 'numeric',
            'victim_position' => 'nullable|array',
            'victim_position.*' => 'numeric',
        ]);

        $id = DB::table('player_kills')->insertGetId([
            'server_id' => $validated['server_id'],
            'killer_name' => $validated['killer_name'] ?? 'Unknown',
            'killer_uuid' => $validated['killer_uuid'] ?? null,
            'killer_faction' => $validated['killer_faction'] ?? null,
            'victim_name' => $validated['victim_name'] ?? null,
            'victim_uuid' => $validated['victim_uuid'] ?? null,
            'victim_type' => $validated['victim_type'] ?? 'PLAYER',
            'weapon_name' => $validated['weapon_name'] ?? 'Unknown',
            'weapon_type' => $validated['weapon_type'] ?? null,
            'kill_distance' => $validated['kill_distance'] ?? 0,
            'is_headshot' => $validated['is_headshot'] ?? false,
            'is_team_kill' => $validated['is_team_kill'] ?? false,
            'is_roadkill' => $validated['is_roadkill'] ?? false,
            'event_type' => $validated['event_type'] ?? 'KILL',
            'killed_at' => $validated['timestamp'] ?? now(),
            'killer_position' => isset($validated['killer_position']) ? json_encode($validated['killer_position']) : null,
            'victim_position' => isset($validated['victim_position']) ? json_encode($validated['victim_position']) : null,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updatePlayerKillStats($validated);

        $this->queueRatedKillIfEligible($id, $validated);

        KillFeedUpdated::dispatch(
            $validated['server_id'],
            $validated['killer_name'] ?? 'Unknown',
            $validated['victim_name'] ?? null,
            $validated['weapon_name'] ?? null,
            (float) ($validated['kill_distance'] ?? 0),
            ! empty($validated['is_headshot']),
            ! empty($validated['is_team_kill']),
            ! empty($validated['is_roadkill']),
            $validated['victim_type'] ?? 'PLAYER',
            $id,
        );

        ActivityFeedUpdated::dispatch('kill', [
            'server_id' => $validated['server_id'],
            'killer_name' => $validated['killer_name'] ?? 'Unknown',
            'victim_name' => $validated['victim_name'] ?? null,
            'weapon_name' => $validated['weapon_name'] ?? null,
            'is_headshot' => ! empty($validated['is_headshot']),
        ]);

        // Discord notable kill notification
        $killDistance = $validated['kill_distance'] ?? 0;
        $threshold = (int) site_setting('discord_notable_kill_distance', 500);
        if ($killDistance >= $threshold
            && site_setting('discord_notify_notable_kills')
            && site_setting('discord_webhook_url')
        ) {
            $isHeadshot = ! empty($validated['is_headshot']);
            SendDiscordNotification::dispatch(
                ($isHeadshot ? 'Headshot! ' : '').'Notable Kill',
                sprintf(
                    '**%s** killed **%s** from **%dm** with %s',
                    $validated['killer_name'] ?? 'Unknown',
                    $validated['victim_name'] ?? 'Unknown',
                    round($killDistance),
                    $validated['weapon_name'] ?? 'Unknown'
                ),
                $isHeadshot ? 0xF59E0B : 0xEF4444,
            );
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storePlayerStats(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_uuid' => 'required|string|max:255',
            'player_name' => 'required|string|max:255',
            'kills' => 'nullable|integer',
            'deaths' => 'nullable|integer',
            'headshots' => 'nullable|integer',
            'team_kills' => 'nullable|integer',
            'playtime_seconds' => 'nullable|integer',
            'total_distance' => 'nullable|numeric',
            'shots_fired' => 'nullable|integer',
            'grenades_thrown' => 'nullable|integer',
            'heals_given' => 'nullable|integer',
            'heals_received' => 'nullable|integer',
            'bases_captured' => 'nullable|integer',
            'supplies_delivered' => 'nullable|integer',
            'xp_total' => 'nullable|integer',
        ]);

        DB::table('player_stats')->updateOrInsert(
            ['player_uuid' => $validated['player_uuid']],
            [
                'server_id' => $validated['server_id'],
                'player_name' => $validated['player_name'],
                'kills' => $validated['kills'] ?? 0,
                'deaths' => $validated['deaths'] ?? 0,
                'headshots' => $validated['headshots'] ?? 0,
                'team_kills' => $validated['team_kills'] ?? 0,
                'playtime_seconds' => $validated['playtime_seconds'] ?? 0,
                'total_distance' => $validated['total_distance'] ?? 0,
                'shots_fired' => $validated['shots_fired'] ?? 0,
                'grenades_thrown' => $validated['grenades_thrown'] ?? 0,
                'heals_given' => $validated['heals_given'] ?? 0,
                'heals_received' => $validated['heals_received'] ?? 0,
                'bases_captured' => $validated['bases_captured'] ?? 0,
                'supplies_delivered' => $validated['supplies_delivered'] ?? 0,
                'xp_total' => $validated['xp_total'] ?? 0,
                'last_seen_at' => now(),
                'updated_at' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    public function storeConnection(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|in:CONNECT,DISCONNECT',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:255',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('connections')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_id' => $validated['player_id'] ?? null,
            'player_platform' => $validated['player_platform'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updatePlayerOnlineStatus($validated);

        PlayerConnected::dispatch(
            $validated['server_id'],
            $validated['event_type'],
            $validated['player_name'],
            $validated['player_uuid'] ?? null,
            $validated['player_faction'] ?? null,
        );

        if ($validated['event_type'] === 'CONNECT') {
            ActivityFeedUpdated::dispatch('connection', [
                'server_id' => $validated['server_id'],
                'player_name' => $validated['player_name'],
                'event_type' => $validated['event_type'],
            ]);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeBaseEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'base_name' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'player_name' => 'nullable|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'capturing_faction' => 'nullable|string|max:255',
            'previous_faction' => 'nullable|string|max:255',
            'xp_awarded' => 'nullable|integer',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('base_events')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'base_name' => $validated['base_name'] ?? null,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'player_name' => $validated['player_name'] ?? null,
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'capturing_faction' => $validated['capturing_faction'] ?? null,
            'previous_faction' => $validated['previous_faction'] ?? null,
            'xp_awarded' => $validated['xp_awarded'] ?? null,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        BaseEventOccurred::dispatch(
            $validated['server_id'],
            $validated['event_type'],
            $validated['base_name'] ?? null,
            $validated['player_name'] ?? null,
            $validated['capturing_faction'] ?? null,
        );

        $captureTypes = ['CAPTURED', 'CAPTURE', 'BASE_SEIZED', 'BASE_CAPTURE'];
        if (in_array(strtoupper($validated['event_type']), $captureTypes)) {
            ActivityFeedUpdated::dispatch('base_capture', [
                'server_id' => $validated['server_id'],
                'base_name' => $validated['base_name'] ?? null,
                'player_name' => $validated['player_name'] ?? null,
                'capturing_faction' => $validated['capturing_faction'] ?? null,
            ]);
        }

        // Update bases_captured counter
        if (! empty($validated['player_uuid']) && in_array(strtoupper($validated['event_type']), $captureTypes)) {
            $serverId = $validated['server_id'] ?? 1;
            $this->getOrCreatePlayerStat($validated['player_uuid'], $validated['player_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $validated['player_uuid'])->increment('bases_captured');

            // Bases captured doesn't affect leaderboards directly, but clear cache for consistency
            $this->clearLeaderboardCaches();

            $this->queueRatedObjectiveIfEligible('base_capture', $validated['player_uuid'], [
                'event_id' => $id,
                'server_id' => $validated['server_id'],
                'timestamp' => $validated['timestamp'] ?? now(),
            ]);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeBuildingEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'composition_name' => 'nullable|string|max:255',
            'composition_type' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('building_events')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'composition_name' => $validated['composition_name'] ?? null,
            'composition_type' => $validated['composition_type'] ?? null,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Building placed = engineer role reward for rating
        $buildTypes = ['BUILDING_PLACED', 'BUILD', 'PLACED'];
        if (in_array(strtoupper($validated['event_type']), $buildTypes)) {
            $this->queueRatedObjectiveIfEligible('building', $validated['player_uuid'] ?? null, [
                'event_id' => $id,
                'server_id' => $validated['server_id'],
                'timestamp' => $validated['timestamp'] ?? now(),
            ]);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeConsciousnessEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:50',
            'position' => 'nullable|array',
            'knocker_name' => 'nullable|string|max:255',
            'knocker_uuid' => 'nullable|string|max:255',
            'is_friendly_knock' => 'nullable|boolean',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('consciousness_events')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'state' => $validated['state'] ?? null,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'knocker_name' => $validated['knocker_name'] ?? null,
            'knocker_uuid' => $validated['knocker_uuid'] ?? null,
            'is_friendly_knock' => $validated['is_friendly_knock'] ?? false,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeGroupEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'group_name' => 'required|string|max:255',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('group_events')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'group_name' => $validated['group_name'],
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeXpEvent(Request $request): JsonResponse
    {
        $data = $request->all();

        $event = XpEvent::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'xp_amount' => $data['xp_amount'] ?? 0,
            'reward_type' => $data['xp_type'] ?? $data['reward_type'] ?? null,
            'occurred_at' => $data['timestamp'] ?? now(),
        ]);

        $this->updatePlayerXp($data);

        // Vehicle destruction = high-value tactical event for rating
        $rewardType = $data['xp_type'] ?? $data['reward_type'] ?? null;
        if ($rewardType && strtoupper($rewardType) === 'ENEMY_KILL_VEH') {
            $this->queueRatedObjectiveIfEligible('vehicle_destroy', $data['player_uuid'] ?? null, [
                'event_id' => $event->id,
                'server_id' => $data['server_id'] ?? null,
                'timestamp' => $data['timestamp'] ?? now(),
            ]);
        }

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    public function storeDamageEvents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events' => 'required|array|min:1',
            'events.*.server_id' => 'required|integer',
            'events.*.damage_type' => 'nullable|string|max:50',
            'events.*.damage_amount' => 'nullable|numeric',
            'events.*.hit_zone_name' => 'nullable|string|max:50',
            'events.*.killer_name' => 'nullable|string|max:255',
            'events.*.killer_uuid' => 'nullable|string|max:255',
            'events.*.killer_id' => 'nullable|integer',
            'events.*.killer_faction' => 'nullable|string|max:255',
            'events.*.victim_name' => 'nullable|string|max:255',
            'events.*.victim_uuid' => 'nullable|string|max:255',
            'events.*.victim_id' => 'nullable|integer',
            'events.*.victim_faction' => 'nullable|string|max:255',
            'events.*.weapon_name' => 'nullable|string|max:255',
            'events.*.distance' => 'nullable|numeric',
            'events.*.is_friendly_fire' => 'nullable|boolean',
            'events.*.timestamp' => 'nullable|string',
        ]);

        $inserted = 0;

        foreach ($validated['events'] as $event) {
            DamageEvent::create([
                'server_id' => $event['server_id'],
                'damage_type' => $event['damage_type'] ?? null,
                'damage_amount' => $event['damage_amount'] ?? null,
                'hit_zone_name' => $event['hit_zone_name'] ?? null,
                'killer_name' => $event['killer_name'] ?? null,
                'killer_uuid' => $event['killer_uuid'] ?? null,
                'killer_id' => $event['killer_id'] ?? null,
                'killer_faction' => $event['killer_faction'] ?? null,
                'victim_name' => $event['victim_name'] ?? null,
                'victim_uuid' => $event['victim_uuid'] ?? null,
                'victim_id' => $event['victim_id'] ?? null,
                'victim_faction' => $event['victim_faction'] ?? null,
                'weapon_name' => $event['weapon_name'] ?? null,
                'distance' => $event['distance'] ?? null,
                'is_friendly_fire' => $event['is_friendly_fire'] ?? false,
                'occurred_at' => $event['timestamp'] ?? now(),
            ]);

            $this->updateHitZoneStats($event);

            // Friendly fire damage = rating penalty (less severe than team kill)
            if (! empty($event['is_friendly_fire']) && ! empty($event['killer_uuid'])) {
                $this->queueRatedObjectiveIfEligible('friendly_fire', $event['killer_uuid'], [
                    'server_id' => $event['server_id'],
                    'timestamp' => $event['timestamp'] ?? now(),
                ]);
            }

            $inserted++;
        }

        return response()->json(['success' => true, 'inserted' => $inserted]);
    }

    public function storeChatEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'message' => 'required|string|max:1000',
            'channel' => 'nullable|string|max:50',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('chat_events')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'message' => $validated['message'],
            'channel' => $validated['channel'] ?? 'global',
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeEditorAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'action' => 'required|string|max:255',
            'hovered_entity_component_name' => 'nullable|string|max:500',
            'hovered_entity_component_owner_id' => 'nullable|string|max:255',
            'selected_entity_components_owners_ids' => 'nullable|string',
            'selected_entity_components_names' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('editor_actions')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_id' => $validated['player_id'] ?? null,
            'action' => $validated['action'],
            'hovered_entity_component_name' => $validated['hovered_entity_component_name'] ?? null,
            'hovered_entity_component_owner_id' => $validated['hovered_entity_component_owner_id'] ?? null,
            'selected_entity_components_owners_ids' => $validated['selected_entity_components_owners_ids'] ?? null,
            'selected_entity_components_names' => $validated['selected_entity_components_names'] ?? null,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeGmSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|in:GM_ENTER,GM_EXIT',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'duration' => 'nullable|integer',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('gm_sessions')->insertGetId([
            'server_id' => $validated['server_id'],
            'event_type' => $validated['event_type'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'duration' => $validated['duration'] ?? null,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeShooting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'weapons' => 'nullable|string|max:500',
            'total_rounds' => 'required|integer',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('player_shooting')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'weapons' => $validated['weapons'] ?? null,
            'total_rounds' => $validated['total_rounds'],
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updatePlayerShotCount($validated);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeDistance(Request $request): JsonResponse
    {
        $data = $request->all();

        $event = PlayerDistance::create([
            'server_id' => $data['server_id'] ?? 1,
            'player_uuid' => $data['player_uuid'] ?? null,
            'player_name' => $data['player_name'] ?? null,
            'player_faction' => $data['player_faction'] ?? null,
            'walking_distance' => $data['walking_distance'] ?? 0,
            'walking_time_seconds' => $data['walking_time_seconds'] ?? 0,
            'total_vehicle_distance' => $data['total_vehicle_distance'] ?? 0,
            'total_vehicle_time_seconds' => $data['total_vehicle_time_seconds'] ?? 0,
            'vehicles' => $data['vehicles'] ?? [],
            'is_final_log' => $data['is_final_log'] ?? false,
            'occurred_at' => $data['timestamp'] ?? now(),
        ]);

        $this->updatePlayerDistanceTotals($data);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    public function storeHealing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'healer_name' => 'required|string|max:255',
            'healer_uuid' => 'nullable|string|max:255',
            'healer_id' => 'nullable|integer',
            'patient_name' => 'nullable|string|max:255',
            'patient_uuid' => 'nullable|string|max:255',
            'patient_id' => 'nullable|integer',
            'action' => 'nullable|string|max:50',
            'item' => 'nullable|string|max:100',
            'is_self' => 'nullable|boolean',
            'timestamp' => 'nullable|string',
        ]);

        // Map to actual database columns
        $healType = $validated['item'] ?? $validated['action'] ?? 'unknown';

        $id = DB::table('player_healing_rjs')->insertGetId([
            'server_id' => $validated['server_id'],
            'healer_name' => $validated['healer_name'],
            'healer_uuid' => $validated['healer_uuid'] ?? null,
            'healer_id' => $validated['healer_id'] ?? null,
            'target_name' => $validated['patient_name'] ?? null,
            'target_uuid' => $validated['patient_uuid'] ?? null,
            'target_id' => $validated['patient_id'] ?? null,
            'heal_type' => $healType,
            'is_self_heal' => $validated['is_self'] ?? false,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updatePlayerHealingStats($validated);

        // Only non-self heals count for rating (rewarding teamplay)
        if (empty($validated['is_self'])) {
            $this->queueRatedObjectiveIfEligible('heal', $validated['healer_uuid'] ?? null, [
                'event_id' => $id,
                'server_id' => $validated['server_id'],
                'timestamp' => $validated['timestamp'] ?? now(),
            ]);
        }

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeGrenade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'grenade_type' => 'required|string|max:100',
            'position' => 'nullable|array',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('player_grenades')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'grenade_type' => $validated['grenade_type'],
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updatePlayerGrenadeStats($validated);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storeSupplies(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_faction' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'estimated_amount' => 'nullable|integer',
            'xp_awarded' => 'nullable|integer',
            'timestamp' => 'nullable|string',
        ]);

        $id = DB::table('supply_deliveries')->insertGetId([
            'server_id' => $validated['server_id'],
            'player_name' => $validated['player_name'],
            'player_uuid' => $validated['player_uuid'] ?? null,
            'player_faction' => $validated['player_faction'] ?? null,
            'supply_type' => 'delivery',
            'amount' => $validated['estimated_amount'] ?? 0,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'estimated_amount' => $validated['estimated_amount'] ?? 0,
            'xp_awarded' => $validated['xp_awarded'] ?? 0,
            'delivered_at' => now(),
            'occurred_at' => $validated['timestamp'] ?? now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $this->updatePlayerSupplyStats($validated);

        $this->queueRatedObjectiveIfEligible('supply', $validated['player_uuid'] ?? null, [
            'event_id' => $id,
            'server_id' => $validated['server_id'],
            'timestamp' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $id]);
    }

    public function storePlayerReport(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'reporter_name' => 'required|string|max:255',
            'reporter_uuid' => 'nullable|string|max:255',
            'reporter_id' => 'nullable|integer',
            'target_name' => 'required|string|max:255',
            'reason' => 'nullable|string|max:2000',
            'channel' => 'nullable|string|max:50',
            'timestamp' => 'required|string',
        ]);

        $report = PlayerReport::create([
            'server_id' => $validated['server_id'],
            'reporter_name' => $validated['reporter_name'],
            'reporter_uuid' => $validated['reporter_uuid'] ?? null,
            'reporter_id' => $validated['reporter_id'] ?? null,
            'target_name' => $validated['target_name'],
            'reason' => $validated['reason'] ?? '',
            'channel' => $validated['channel'] ?? null,
            'status' => 'pending',
            'reported_at' => $validated['timestamp'],
        ]);

        return response()->json([
            'success' => true,
            'id' => $report->id,
        ], 201);
    }

    // ========================================
    // READ ENDPOINTS (för hemsidan)
    // ========================================

    // === Servers ===

    public function getServers(): JsonResponse
    {
        $servers = DB::table('servers')->get();

        return response()->json($servers);
    }

    public function getServer($id): JsonResponse
    {
        $server = DB::table('servers')->where('id', $id)->first();
        if (! $server) {
            return response()->json(['error' => 'Server not found'], 404);
        }

        return response()->json($server);
    }

    public function getServerStatus($id): JsonResponse
    {
        $status = DB::table('server_status')->where('server_id', $id)->first();

        return response()->json($status);
    }

    public function getServerPlayers($id): JsonResponse
    {
        $players = DB::table('connections')
            ->where('server_id', $id)
            ->where('event_type', 'CONNECT')
            ->whereNotExists(function ($query) {
                $query->select(DB::raw(1))
                    ->from('connections as c2')
                    ->whereColumn('c2.player_uuid', 'connections.player_uuid')
                    ->whereColumn('c2.server_id', 'connections.server_id')
                    ->where('c2.event_type', 'DISCONNECT')
                    ->whereColumn('c2.occurred_at', '>', 'connections.occurred_at');
            })
            ->orderByDesc('occurred_at')
            ->limit(100)
            ->get();

        return response()->json($players);
    }

    // === Players ===

    public function getPlayers(Request $request): JsonResponse
    {
        $query = DB::table('player_stats');

        if ($request->has('search')) {
            $search = $request->input('search');
            $query->where('player_name', 'like', "%{$search}%");
        }

        $sortField = $request->input('sort', 'last_seen_at');
        $sortDir = $request->input('direction', 'desc');
        $allowedSorts = ['player_name', 'kills', 'deaths', 'playtime_seconds', 'last_seen_at'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDir);
        }

        $players = $query->paginate($request->input('per_page', 25));

        return response()->json($players);
    }

    public function getPlayer($id): JsonResponse
    {
        $player = DB::table('player_stats')->where('player_uuid', $id)->first();
        if (! $player) {
            return response()->json(['error' => 'Player not found'], 404);
        }

        return response()->json($player);
    }

    public function getPlayerStats($id): JsonResponse
    {
        $stats = DB::table('player_stats')->where('player_uuid', $id)->first();

        return response()->json($stats);
    }

    public function getPlayerKills($id, Request $request): JsonResponse
    {
        $kills = DB::table('player_kills')
            ->where('killer_uuid', $id)
            ->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($kills);
    }

    public function getPlayerDeaths($id, Request $request): JsonResponse
    {
        $deaths = DB::table('player_kills')
            ->where('victim_uuid', $id)
            ->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($deaths);
    }

    public function getPlayerConnections($id, Request $request): JsonResponse
    {
        $connections = DB::table('connections')
            ->where('player_uuid', $id)
            ->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($connections);
    }

    public function getPlayerXp($id, Request $request): JsonResponse
    {
        $xp = DB::table('xp_events')
            ->where('player_uuid', $id)
            ->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($xp);
    }

    public function getPlayerDistance($id): JsonResponse
    {
        $distance = DB::table('player_distance')
            ->where('player_uuid', $id)
            ->orderByDesc('occurred_at')
            ->first();

        return response()->json($distance);
    }

    public function getPlayerShooting($id, Request $request): JsonResponse
    {
        $shooting = DB::table('player_shooting')
            ->where('player_uuid', $id)
            ->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 25));

        return response()->json($shooting);
    }

    // === Leaderboards ===

    public function getKillsLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $cacheKey = "leaderboard:kills:limit_{$limit}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit) {
            return DB::table('player_stats')
                ->orderByDesc('kills')
                ->limit($limit)
                ->get(['player_uuid', 'player_name', 'kills', 'deaths', 'playtime_seconds']);
        });

        return response()->json($leaderboard);
    }

    public function getDeathsLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $cacheKey = "leaderboard:deaths:limit_{$limit}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit) {
            return DB::table('player_stats')
                ->orderByDesc('deaths')
                ->limit($limit)
                ->get(['player_uuid', 'player_name', 'kills', 'deaths', 'playtime_seconds']);
        });

        return response()->json($leaderboard);
    }

    public function getKdLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $minKills = $request->input('min_kills', 10);
        $cacheKey = "leaderboard:kd:limit_{$limit}:min_{$minKills}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit, $minKills) {
            return DB::table('player_stats')
                ->where('player_kills_count', '>=', $minKills)
                ->selectRaw('player_uuid, player_name, kills, player_kills_count, deaths, playtime_seconds, CASE WHEN deaths > 0 THEN ROUND(player_kills_count::numeric / deaths, 2) ELSE player_kills_count END as kd_ratio')
                ->orderByDesc('kd_ratio')
                ->limit($limit)
                ->get();
        });

        return response()->json($leaderboard);
    }

    public function getPlaytimeLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $cacheKey = "leaderboard:playtime:limit_{$limit}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit) {
            return DB::table('player_stats')
                ->orderByDesc('playtime_seconds')
                ->limit($limit)
                ->get(['player_uuid', 'player_name', 'playtime_seconds', 'kills', 'deaths']);
        });

        return response()->json($leaderboard);
    }

    public function getXpLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $cacheKey = "leaderboard:xp:limit_{$limit}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit) {
            return DB::table('player_stats')
                ->orderByDesc('xp_total')
                ->limit($limit)
                ->get(['player_uuid', 'player_name', 'xp_total', 'kills', 'deaths']);
        });

        return response()->json($leaderboard);
    }

    public function getDistanceLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $cacheKey = "leaderboard:distance:limit_{$limit}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit) {
            return DB::table('player_stats')
                ->orderByDesc('total_distance')
                ->limit($limit)
                ->get(['player_uuid', 'player_name', 'total_distance', 'playtime_seconds']);
        });

        return response()->json($leaderboard);
    }

    public function getRoadkillLeaderboard(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 100);
        $cacheKey = "leaderboard:roadkills:limit_{$limit}";

        $leaderboard = Cache::remember($cacheKey, 300, function () use ($limit) {
            return DB::table('player_stats')
                ->where('total_roadkills', '>', 0)
                ->orderByDesc('total_roadkills')
                ->limit($limit)
                ->get(['player_uuid', 'player_name', 'total_roadkills', 'kills', 'deaths']);
        });

        return response()->json($leaderboard);
    }

    // === Events/Logs ===

    public function getKills(Request $request): JsonResponse
    {
        $query = DB::table('player_kills');

        if ($request->has('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }
        if ($request->has('victim_type')) {
            $query->where('victim_type', $request->input('victim_type'));
        }

        $kills = $query->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($kills);
    }

    public function getConnections(Request $request): JsonResponse
    {
        $query = DB::table('connections');

        if ($request->has('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }
        if ($request->has('event_type')) {
            $query->where('event_type', $request->input('event_type'));
        }

        $connections = $query->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($connections);
    }

    public function getBaseEvents(Request $request): JsonResponse
    {
        $query = DB::table('base_events');

        if ($request->has('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }

        $events = $query->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($events);
    }

    public function getChatMessages(Request $request): JsonResponse
    {
        $query = DB::table('chat_events');

        if ($request->has('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }
        if ($request->has('channel')) {
            $query->where('channel', $request->input('channel'));
        }

        $messages = $query->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($messages);
    }

    public function getGmSessions(Request $request): JsonResponse
    {
        $query = DB::table('gm_sessions');

        if ($request->has('server_id')) {
            $query->where('server_id', $request->input('server_id'));
        }

        $sessions = $query->orderByDesc('occurred_at')
            ->paginate($request->input('per_page', 50));

        return response()->json($sessions);
    }

    // === Statistics/Aggregates ===

    public function getOverview(): JsonResponse
    {
        $stats = [
            'total_players' => DB::table('player_stats')->count(),
            'total_kills' => DB::table('player_stats')->sum('kills'),
            'total_deaths' => DB::table('player_stats')->sum('deaths'),
            'total_playtime_seconds' => DB::table('player_stats')->sum('playtime_seconds'),
            'total_connections' => DB::table('connections')->count(),
            'total_kill_events' => DB::table('player_kills')->count(),
            'ai_kills' => DB::table('player_kills')->where('victim_type', 'AI')->count(),
            'player_kills' => DB::table('player_kills')->where('victim_type', '!=', 'AI')->orWhereNull('victim_type')->count(),
            'active_players_24h' => DB::table('player_stats')->where('last_seen_at', '>=', now()->subDay())->count(),
            'total_headshots' => DB::table('player_stats')->sum('headshots'),
            'total_heals' => DB::table('player_healing_rjs')->count(),
            'total_base_captures' => DB::table('base_events')->count(),
            'total_supply_deliveries' => DB::table('supply_deliveries')->count(),
        ];

        return response()->json($stats);
    }

    public function getWeaponStats(Request $request): JsonResponse
    {
        $limit = $request->input('limit', 20);

        $weapons = DB::table('player_kills')
            ->selectRaw('weapon_name, COUNT(*) as total_kills, SUM(CASE WHEN is_headshot THEN 1 ELSE 0 END) as headshots')
            ->whereNotNull('weapon_name')
            ->groupBy('weapon_name')
            ->orderByDesc('total_kills')
            ->limit($limit)
            ->get();

        return response()->json($weapons);
    }

    public function getFactionStats(): JsonResponse
    {
        $factions = DB::table('player_kills')
            ->selectRaw('killer_faction as faction, COUNT(*) as kills')
            ->whereNotNull('killer_faction')
            ->groupBy('killer_faction')
            ->orderByDesc('kills')
            ->get();

        return response()->json($factions);
    }

    public function getBaseStats(): JsonResponse
    {
        $bases = DB::table('base_events')
            ->selectRaw('base_name, COUNT(*) as capture_count')
            ->whereNotNull('base_name')
            ->groupBy('base_name')
            ->orderByDesc('capture_count')
            ->get();

        return response()->json($bases);
    }

    // ========================================
    // PUBLIC READ ENDPOINTS (no auth)
    // ========================================

    public function getHeatmapData(Request $request): JsonResponse
    {
        $request->validate([
            'server_id' => 'required|integer',
            'period' => 'nullable|string|in:24h,7d,30d,all',
            'type' => 'nullable|string|in:kills,deaths,both',
        ]);

        $serverId = $request->input('server_id');
        $period = $request->input('period', 'all');
        $type = $request->input('type', 'both');

        $query = DB::table('player_kills')
            ->where('server_id', $serverId)
            ->whereNotNull('killer_position')
            ->whereNotNull('victim_position');

        if ($period !== 'all') {
            $since = match ($period) {
                '24h' => now()->subHours(24),
                '7d' => now()->subDays(7),
                '30d' => now()->subDays(30),
            };
            $query->where('killed_at', '>=', $since);
        }

        $kills = $query->orderByDesc('killed_at')
            ->limit(5000)
            ->get(['killer_position', 'victim_position', 'weapon_name', 'is_headshot', 'killed_at', 'killer_name', 'victim_name']);

        $points = [];

        foreach ($kills as $kill) {
            $killerPos = json_decode($kill->killer_position, true);
            $victimPos = json_decode($kill->victim_position, true);

            if ($type !== 'deaths' && $killerPos && count($killerPos) >= 2) {
                $points[] = [
                    'type' => 'kill',
                    'x' => (float) $killerPos[0],
                    'z' => (float) ($killerPos[2] ?? $killerPos[1]),
                    'weapon' => $kill->weapon_name,
                    'headshot' => (bool) $kill->is_headshot,
                    'player' => $kill->killer_name,
                    'time' => $kill->killed_at,
                ];
            }

            if ($type !== 'kills' && $victimPos && count($victimPos) >= 2) {
                $points[] = [
                    'type' => 'death',
                    'x' => (float) $victimPos[0],
                    'z' => (float) ($victimPos[2] ?? $victimPos[1]),
                    'weapon' => $kill->weapon_name,
                    'headshot' => (bool) $kill->is_headshot,
                    'player' => $kill->victim_name,
                    'time' => $kill->killed_at,
                ];
            }
        }

        return response()->json([
            'points' => $points,
            'total' => count($points),
            'period' => $period,
            'type' => $type,
        ]);
    }

    // ========================================
    // HELPER METHODS
    // ========================================

    private function getOrCreatePlayerStat(string $uuid, string $playerName, int $serverId): object
    {
        $stat = DB::table('player_stats')->where('player_uuid', $uuid)->first();

        if (! $stat) {
            DB::table('player_stats')->insert([
                'player_uuid' => $uuid,
                'player_name' => $playerName,
                'server_id' => $serverId,
                'kills' => 0,
                'player_kills_count' => 0,
                'deaths' => 0,
                'headshots' => 0,
                'team_kills' => 0,
                'playtime_seconds' => 0,
                'total_distance' => 0,
                'shots_fired' => 0,
                'grenades_thrown' => 0,
                'heals_given' => 0,
                'heals_received' => 0,
                'bases_captured' => 0,
                'supplies_delivered' => 0,
                'xp_total' => 0,
                'last_seen_at' => now(),
                'created_at' => now(),
                'updated_at' => now(),
            ]);
            $stat = DB::table('player_stats')->where('player_uuid', $uuid)->first();
        }

        return $stat;
    }

    private function queueRatedKillIfEligible(int $killId, array $data): void
    {
        try {
            $killerUuid = $data['killer_uuid'] ?? null;
            $victimUuid = $data['victim_uuid'] ?? null;
            $isTeamKill = ! empty($data['is_team_kill']);

            if (! $killerUuid) {
                return;
            }

            // Regular kills require victim UUID; team kills only need the killer
            if (! $isTeamKill && ! $victimUuid) {
                return;
            }

            $victimType = $data['victim_type'] ?? 'PLAYER';

            $ratingService = app(\App\Services\RatingCalculationService::class);

            if ($ratingService->isRatedKill($killerUuid, $victimUuid, $victimType, $isTeamKill)) {
                $ratingService->queueRatedKill($killId, [
                    'killer_uuid' => $killerUuid,
                    'victim_uuid' => $victimUuid,
                    'is_headshot' => ! empty($data['is_headshot']),
                    'is_team_kill' => $isTeamKill,
                    'kill_distance' => $data['kill_distance'] ?? 0,
                    'weapon_name' => $data['weapon_name'] ?? null,
                    'server_id' => $data['server_id'] ?? null,
                    'killed_at' => $data['timestamp'] ?? now(),
                ]);
            }
        } catch (\Throwable $e) {
            // Never break the kill endpoint
            \Illuminate\Support\Facades\Log::warning('Failed to queue rated kill', [
                'kill_id' => $killId,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function queueRatedObjectiveIfEligible(string $eventType, ?string $playerUuid, array $data = []): void
    {
        try {
            if (! $playerUuid) {
                return;
            }

            $ratingService = app(\App\Services\RatingCalculationService::class);

            if ($ratingService->isCompetitivePlayer($playerUuid)) {
                $ratingService->queueRatedObjective($eventType, $playerUuid, $data);
            }
        } catch (\Throwable $e) {
            \Illuminate\Support\Facades\Log::warning('Failed to queue rated objective', [
                'event_type' => $eventType,
                'player_uuid' => $playerUuid,
                'error' => $e->getMessage(),
            ]);
        }
    }

    private function updatePlayerKillStats(array $data): void
    {
        $serverId = $data['server_id'] ?? 1;

        // Update killer stats
        if (! empty($data['killer_uuid'])) {
            $this->getOrCreatePlayerStat($data['killer_uuid'], $data['killer_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->increment('kills');

            if (($data['victim_type'] ?? 'PLAYER') !== 'AI') {
                DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->increment('player_kills_count');
            }

            if (! empty($data['is_team_kill'])) {
                DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->increment('team_kills');
            }
            if (! empty($data['is_headshot'])) {
                DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->increment('headshots');
            }
            if (! empty($data['is_roadkill'])) {
                DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->increment('total_roadkills');
            }

            DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->update(['last_seen_at' => now()]);
        }

        // Update victim stats (deaths)
        if (! empty($data['victim_uuid']) && ($data['victim_type'] ?? '') !== 'AI') {
            $this->getOrCreatePlayerStat($data['victim_uuid'], $data['victim_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['victim_uuid'])->increment('deaths');
            DB::table('player_stats')->where('player_uuid', $data['victim_uuid'])->update(['last_seen_at' => now()]);
        }

        // Invalidate leaderboard caches (affects kills, deaths, K/D, headshots, roadkills)
        $this->clearLeaderboardCaches();
    }

    private function updatePlayerOnlineStatus(array $data): void
    {
        if (! empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $this->getOrCreatePlayerStat($data['player_uuid'], $data['player_name'], $serverId);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->update(['last_seen_at' => now()]);
        }
    }

    private function updatePlayerXp(array $data): void
    {
        if (! empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $this->getOrCreatePlayerStat($data['player_uuid'], $data['player_name'], $serverId);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->increment('xp_total', $data['xp_amount']);

            // Invalidate XP leaderboard cache
            $this->clearLeaderboardCaches();
        }
    }

    private function updatePlayerDistanceTotals(array $data): void
    {
        if (! empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $walkingDistance = $data['walking_distance'] ?? 0;
            $vehicleDistance = $data['total_vehicle_distance'] ?? 0;
            $totalDistance = $walkingDistance + $vehicleDistance;
            $playtime = ($data['walking_time_seconds'] ?? 0) + ($data['total_vehicle_time_seconds'] ?? 0);

            $this->getOrCreatePlayerStat($data['player_uuid'], $data['player_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->increment('total_distance', $totalDistance);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->increment('playtime_seconds', $playtime);

            // Invalidate distance and playtime leaderboard caches
            $this->clearLeaderboardCaches();
        }
    }

    private function updatePlayerShotCount(array $data): void
    {
        if (! empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $this->getOrCreatePlayerStat($data['player_uuid'], $data['player_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->increment('shots_fired', $data['total_rounds'] ?? 0);
        }
    }

    private function updatePlayerGrenadeStats(array $data): void
    {
        if (! empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $this->getOrCreatePlayerStat($data['player_uuid'], $data['player_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->increment('grenades_thrown');
        }
    }

    private function updatePlayerHealingStats(array $data): void
    {
        $serverId = $data['server_id'] ?? 1;

        if (! empty($data['healer_uuid'])) {
            $this->getOrCreatePlayerStat($data['healer_uuid'], $data['healer_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['healer_uuid'])->increment('heals_given');
        }

        if (! empty($data['patient_uuid']) && empty($data['is_self']) && empty($data['patient_is_ai'])) {
            $this->getOrCreatePlayerStat($data['patient_uuid'], $data['patient_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['patient_uuid'])->increment('heals_received');
        }
    }

    private function updatePlayerSupplyStats(array $data): void
    {
        if (! empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $amount = $data['estimated_amount'] ?? 1;
            $this->getOrCreatePlayerStat($data['player_uuid'], $data['player_name'] ?? 'Unknown', $serverId);
            DB::table('player_stats')->where('player_uuid', $data['player_uuid'])->increment('supplies_delivered', $amount);
        }
    }

    private function updateHitZoneStats(array $data): void
    {
        if (empty($data['killer_uuid'])) {
            return;
        }

        $serverId = $data['server_id'] ?? 1;
        $this->getOrCreatePlayerStat($data['killer_uuid'], $data['killer_name'] ?? 'Unknown', $serverId);

        $hitZone = $data['hit_zone_name'] ?? null;
        $damageAmount = $data['damage_amount'] ?? 0;

        $updates = ['total_hits' => DB::raw('total_hits + 1')];

        if ($damageAmount > 0) {
            $updates['total_damage_dealt'] = DB::raw('total_damage_dealt + '.number_format((float) $damageAmount, 2, '.', ''));
        }

        if ($hitZone) {
            $zone = strtoupper($hitZone);

            if ($zone === 'HEAD') {
                $updates['hits_head'] = DB::raw('hits_head + 1');
                $updates['headshots'] = DB::raw('headshots + 1');
            } elseif (in_array($zone, ['UPPERTORSO', 'LOWERTORSO'])) {
                $updates['hits_torso'] = DB::raw('hits_torso + 1');
            } elseif (in_array($zone, ['LEFTARM', 'RIGHTARM'])) {
                $updates['hits_arms'] = DB::raw('hits_arms + 1');
            } elseif (in_array($zone, ['LEFTLEG', 'RIGHTLEG'])) {
                $updates['hits_legs'] = DB::raw('hits_legs + 1');
            }
            // SCR_CharacterResilienceHitZone and other special zones are skipped
        }

        $updates['last_seen_at'] = now();

        DB::table('player_stats')->where('player_uuid', $data['killer_uuid'])->update($updates);
    }

    /**
     * Clear all leaderboard caches
     * Called when player stats are updated to ensure fresh data
     */
    private function clearLeaderboardCaches(): void
    {
        // Clear caches for common limit values (25, 50, 100)
        $commonLimits = [25, 50, 100];

        foreach ($commonLimits as $limit) {
            // Kills leaderboard
            Cache::forget("leaderboard:kills:limit_{$limit}");

            // Deaths leaderboard
            Cache::forget("leaderboard:deaths:limit_{$limit}");

            // K/D leaderboard (with common min_kills values)
            foreach ([5, 10, 20, 50] as $minKills) {
                Cache::forget("leaderboard:kd:limit_{$limit}:min_{$minKills}");
            }

            // Playtime leaderboard
            Cache::forget("leaderboard:playtime:limit_{$limit}");

            // XP leaderboard
            Cache::forget("leaderboard:xp:limit_{$limit}");

            // Distance leaderboard
            Cache::forget("leaderboard:distance:limit_{$limit}");

            // Roadkills leaderboard
            Cache::forget("leaderboard:roadkills:limit_{$limit}");
        }
    }
}
