<?php

namespace App\Http\Controllers\Api;

use App\Http\Controllers\Controller;
use App\Models\Kill;
use App\Models\DamageEvent;
use App\Models\Connection;
use App\Models\BaseEvent;
use App\Models\BuildingEvent;
use App\Models\ConsciousnessEvent;
use App\Models\GroupEvent;
use App\Models\XpEvent;
use App\Models\ChatEvent;
use App\Models\EditorAction;
use App\Models\GmSession;
use App\Models\PlayerDistance;
use App\Models\PlayerGrenade;
use App\Models\PlayerShooting;
use App\Models\PlayerHealing;
use App\Models\PlayerStat;
use App\Models\ServerStatus;
use App\Models\SupplyDelivery;
use App\Models\GameEvent;
use App\Models\KillLog;
use App\Models\Player;
use App\Models\PlayerSession;
use Carbon\Carbon;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;

class GameEventController extends Controller
{
    /**
     * Store a kill event
     */
    public function storeKill(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'killer_name' => 'nullable|string|max:255',
            'killer_uuid' => 'nullable|string|max:255',
            'killer_faction' => 'nullable|string|max:255',
            'killer_id' => 'nullable|integer',
            'killer_platform' => 'nullable|string|max:50',
            'killer_role' => 'nullable|string|max:255',
            'killer_position' => 'nullable|array',
            'killer_in_vehicle' => 'nullable|boolean',
            'killer_vehicle' => 'nullable|string|max:255',
            'killer_vehicle_prefab' => 'nullable|string|max:255',
            'victim_name' => 'nullable|string|max:255',
            'victim_uuid' => 'nullable|string|max:255',
            'victim_faction' => 'nullable|string|max:255',
            'victim_id' => 'nullable|integer',
            'victim_is_ai' => 'nullable|boolean',
            'victim_role' => 'nullable|string|max:255',
            'victim_position' => 'nullable|array',
            'victim_platform' => 'nullable|string|max:50',
            'ai_type' => 'nullable|string|max:255',
            'weapon_name' => 'nullable|string|max:255',
            'weapon_type' => 'nullable|string|max:255',
            'damage_type' => 'nullable|string|max:255',
            'kill_distance' => 'nullable|numeric',
            'is_team_kill' => 'nullable|boolean',
            'event_type' => 'nullable|string|max:50',
            'timestamp' => 'nullable|string',
        ]);

        $kill = Kill::create([
            ...$validated,
            'killer_position' => isset($validated['killer_position']) ? json_encode($validated['killer_position']) : null,
            'victim_position' => isset($validated['victim_position']) ? json_encode($validated['victim_position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerKillStats($validated);

        return response()->json(['success' => true, 'id' => $kill->id]);
    }

    /**
     * Store damage events (batch)
     */
    public function storeDamageEvents(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'events' => 'required|array',
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
                ...$event,
                'occurred_at' => $event['timestamp'] ?? now(),
            ]);
            $inserted++;
        }

        $this->updateHitZoneStats($validated['events']);

        return response()->json(['success' => true, 'inserted' => $inserted]);
    }

    /**
     * Store connection event
     */
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
            'profile_name' => 'nullable|string|max:255',
            'timestamp' => 'nullable|string',
        ]);

        $connection = Connection::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerOnlineStatus($validated);

        return response()->json(['success' => true, 'id' => $connection->id]);
    }

    /**
     * Store base event (capture/seized)
     */
    public function storeBaseEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|in:BASE_SEIZED,BASE_CAPTURE',
            'base_name' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'player_name' => 'nullable|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_faction' => 'nullable|string|max:255',
            'xp_awarded' => 'nullable|integer',
            'capturing_faction' => 'nullable|string|max:255',
            'previous_faction' => 'nullable|string|max:255',
            'player_count' => 'nullable|integer',
            'player_ids' => 'nullable|string',
            'player_names' => 'nullable|string',
            'timestamp' => 'nullable|string',
        ]);

        $baseEvent = BaseEvent::create([
            ...$validated,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerBaseCaptureStats($validated);

        return response()->json(['success' => true, 'id' => $baseEvent->id]);
    }

    /**
     * Store building event
     */
    public function storeBuildingEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:255',
            'composition_name' => 'nullable|string|max:255',
            'composition_type' => 'nullable|string|max:255',
            'prefab_id' => 'nullable|integer',
            'provider' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'timestamp' => 'nullable|string',
        ]);

        $buildingEvent = BuildingEvent::create([
            ...$validated,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $buildingEvent->id]);
    }

    /**
     * Store consciousness event (knocked/unconscious)
     */
    public function storeConsciousnessEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|max:50',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_faction' => 'nullable|string|max:255',
            'state' => 'nullable|string|max:50',
            'position' => 'nullable|array',
            'knocker_name' => 'nullable|string|max:255',
            'knocker_uuid' => 'nullable|string|max:255',
            'knocker_id' => 'nullable|integer',
            'knocker_faction' => 'nullable|string|max:255',
            'is_friendly_knock' => 'nullable|boolean',
            'is_self_knock' => 'nullable|boolean',
            'timestamp' => 'nullable|string',
        ]);

        $event = ConsciousnessEvent::create([
            ...$validated,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store group event (squad join/leave)
     */
    public function storeGroupEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|in:GROUP_JOINED,GROUP_LEFT',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_faction' => 'nullable|string|max:255',
            'group_name' => 'required|string|max:255',
            'timestamp' => 'nullable|string',
        ]);

        $event = GroupEvent::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store XP event
     */
    public function storeXpEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_faction' => 'nullable|string|max:255',
            'reward_type' => 'required|string|max:100',
            'reward_type_raw' => 'nullable|integer',
            'xp_amount' => 'required|integer',
            'timestamp' => 'nullable|string',
        ]);

        $event = XpEvent::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerXp($validated);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store chat event
     */
    public function storeChatEvent(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'message' => 'required|string|max:1000',
            'channel' => 'nullable|string|max:50',
            'timestamp' => 'nullable|string',
        ]);

        $event = ChatEvent::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store editor action (GM action)
     */
    public function storeEditorAction(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'action' => 'required|string|max:255',
            'hovered_entity_component_name' => 'nullable|string|max:255',
            'hovered_entity_component_owner_id' => 'nullable|integer',
            'selected_entity_components_owners_ids' => 'nullable|string|max:500',
            'selected_entity_components_names' => 'nullable|string|max:500',
            'timestamp' => 'nullable|string',
        ]);

        $event = EditorAction::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store GM session (enter/exit)
     */
    public function storeGmSession(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'event_type' => 'required|string|in:GM_ENTER,GM_EXIT',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'duration' => 'nullable|integer',
            'timestamp' => 'nullable|string',
        ]);

        $event = GmSession::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    /**
     * Store distance/playtime data
     */
    public function storeDistance(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:255',
            'walking_distance' => 'nullable|numeric',
            'walking_time_seconds' => 'nullable|numeric',
            'total_vehicle_distance' => 'nullable|numeric',
            'total_vehicle_time_seconds' => 'nullable|numeric',
            'vehicles' => 'nullable|array',
            'is_final_log' => 'nullable|boolean',
            'timestamp' => 'nullable|string',
        ]);

        $distance = PlayerDistance::create([
            ...$validated,
            'vehicles' => isset($validated['vehicles']) ? json_encode($validated['vehicles']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        if ($validated['is_final_log'] ?? false) {
            $this->updatePlayerDistanceTotals($validated);
        }

        return response()->json(['success' => true, 'id' => $distance->id]);
    }

    /**
     * Store grenade event
     */
    public function storeGrenade(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:255',
            'grenade_type' => 'required|string|max:100',
            'position' => 'nullable|array',
            'timestamp' => 'nullable|string',
        ]);

        $grenade = PlayerGrenade::create([
            ...$validated,
            'position' => isset($validated['position']) ? json_encode($validated['position']) : null,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerGrenadeStats($validated);

        return response()->json(['success' => true, 'id' => $grenade->id]);
    }

    /**
     * Store shooting event
     */
    public function storeShooting(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_platform' => 'nullable|string|max:50',
            'player_faction' => 'nullable|string|max:255',
            'weapons' => 'required|string|max:500',
            'total_rounds' => 'required|integer',
            'timestamp' => 'nullable|string',
        ]);

        $shooting = PlayerShooting::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerShotCount($validated);

        return response()->json(['success' => true, 'id' => $shooting->id]);
    }

    /**
     * Store healing event
     */
    public function storeHealing(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'healer_name' => 'required|string|max:255',
            'healer_uuid' => 'nullable|string|max:255',
            'healer_id' => 'nullable|integer',
            'healer_platform' => 'nullable|string|max:50',
            'healer_faction' => 'nullable|string|max:255',
            'patient_name' => 'nullable|string|max:255',
            'patient_uuid' => 'nullable|string|max:255',
            'patient_id' => 'nullable|integer',
            'patient_platform' => 'nullable|string|max:50',
            'patient_faction' => 'nullable|string|max:255',
            'patient_is_ai' => 'nullable|boolean',
            'action' => 'required|string|max:50',
            'item' => 'required|string|max:100',
            'is_self' => 'nullable|boolean',
            'timestamp' => 'nullable|string',
        ]);

        $healing = PlayerHealing::create([
            ...$validated,
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerHealingStats($validated);

        return response()->json(['success' => true, 'id' => $healing->id]);
    }

    /**
     * Store server status
     */
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

        ServerStatus::updateOrCreate(
            ['server_id' => $validated['server_id']],
            [
                ...$validated,
                'last_updated' => now(),
            ]
        );

        return response()->json(['success' => true]);
    }

    /**
     * Store supply delivery event
     */
    public function storeSupplyDelivery(Request $request): JsonResponse
    {
        $validated = $request->validate([
            'server_id' => 'required|integer',
            'player_name' => 'required|string|max:255',
            'player_uuid' => 'nullable|string|max:255',
            'player_id' => 'nullable|integer',
            'player_faction' => 'nullable|string|max:255',
            'position' => 'nullable|array',
            'estimated_amount' => 'nullable|integer',
            'xp_awarded' => 'nullable|integer',
            'timestamp' => 'nullable|string',
        ]);

        $event = SupplyDelivery::create([
            ...$validated,
            'position' => json_encode($validated['position'] ?? null),
            'occurred_at' => $validated['timestamp'] ?? now(),
        ]);

        $this->updatePlayerSupplyStats($validated);

        return response()->json(['success' => true, 'id' => $event->id]);
    }

    // === Helper Methods ===

    /**
     * Get or create player stat record
     */
    private function getOrCreatePlayerStat(string $uuid, string $playerName, int $serverId): PlayerStat
    {
        return PlayerStat::firstOrCreate(
            ['player_uuid' => $uuid],
            [
                'player_name' => $playerName,
                'server_id' => $serverId,
                'kills' => 0,
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
                'vehicles_destroyed' => 0,
                'last_seen_at' => now(),
            ]
        );
    }

    /**
     * Update kill stats for killer and victim
     */
    private function updatePlayerKillStats(array $data): void
    {
        $serverId = $data['server_id'] ?? 1;

        // Update killer stats
        if (!empty($data['killer_uuid'])) {
            $killerStat = $this->getOrCreatePlayerStat(
                $data['killer_uuid'],
                $data['killer_name'] ?? 'Unknown',
                $serverId
            );
            $killerStat->increment('kills');

            // Track team kills
            if (!empty($data['is_team_kill']) && $data['is_team_kill']) {
                $killerStat->increment('team_kills');
            }

            $killerStat->update(['last_seen_at' => now()]);

            // Also update players table
            Player::where('uuid', $data['killer_uuid'])->increment('kills');
        }

        // Update victim stats (deaths)
        if (!empty($data['victim_uuid']) && empty($data['victim_is_ai'])) {
            $victimStat = $this->getOrCreatePlayerStat(
                $data['victim_uuid'],
                $data['victim_name'] ?? 'Unknown',
                $serverId
            );
            $victimStat->increment('deaths');
            $victimStat->update(['last_seen_at' => now()]);

            // Also update players table
            Player::where('uuid', $data['victim_uuid'])->increment('deaths');
        }
    }

    /**
     * Update hit zone stats from damage events
     */
    private function updateHitZoneStats(array $events): void
    {
        foreach ($events as $event) {
            if (!empty($event['killer_uuid']) && !empty($event['hit_zone_name'])) {
                $serverId = $event['server_id'] ?? 1;
                $hitZone = strtoupper($event['hit_zone_name']);
                $damageAmount = $event['damage_amount'] ?? 0;

                $stat = $this->getOrCreatePlayerStat(
                    $event['killer_uuid'],
                    $event['killer_name'] ?? 'Unknown',
                    $serverId
                );

                // Track total hits and damage
                $stat->increment('total_hits');
                if ($damageAmount > 0) {
                    $stat->increment('total_damage_dealt', $damageAmount);
                }

                // Track hit zones
                switch ($hitZone) {
                    case 'HEAD':
                        $stat->increment('hits_head');
                        $stat->increment('headshots'); // Keep headshots for backwards compatibility
                        break;
                    case 'UPPERTORSO':
                    case 'LOWERTORSO':
                        $stat->increment('hits_torso');
                        break;
                    case 'LEFTARM':
                    case 'RIGHTARM':
                        $stat->increment('hits_arms');
                        break;
                    case 'LEFTLEG':
                    case 'RIGHTLEG':
                        $stat->increment('hits_legs');
                        break;
                }

                $stat->update(['last_seen_at' => now()]);
            }
        }
    }

    /**
     * Update player online status on connect/disconnect
     */
    private function updatePlayerOnlineStatus(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $isConnect = $data['event_type'] === 'CONNECT';

            // Update player_stats
            $stat = $this->getOrCreatePlayerStat(
                $data['player_uuid'],
                $data['player_name'],
                $serverId
            );
            $stat->update(['last_seen_at' => now()]);

            // Update players table
            Player::updateOrCreate(
                ['uuid' => $data['player_uuid']],
                [
                    'player_name' => $data['player_name'],
                    'last_seen' => now(),
                    'server_id' => $serverId,
                ]
            );

            if ($isConnect) {
                Player::where('uuid', $data['player_uuid'])->increment('sessions');
            }
        }
    }

    /**
     * Update player XP
     */
    private function updatePlayerXp(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            Player::where('uuid', $data['player_uuid'])
                ->increment('xp', $data['xp_amount']);
        }
    }

    /**
     * Update distance and playtime totals
     */
    private function updatePlayerDistanceTotals(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $walkingDistance = $data['walking_distance'] ?? 0;
            $vehicleDistance = $data['total_vehicle_distance'] ?? 0;
            $totalDistance = $walkingDistance + $vehicleDistance;
            $playtime = ($data['walking_time_seconds'] ?? 0) + ($data['total_vehicle_time_seconds'] ?? 0);

            // Update player_stats
            $stat = $this->getOrCreatePlayerStat(
                $data['player_uuid'],
                $data['player_name'] ?? 'Unknown',
                $serverId
            );
            $stat->increment('total_distance', $totalDistance);
            $stat->increment('playtime_seconds', $playtime);
            $stat->update(['last_seen_at' => now()]);

            // Update players table
            Player::where('uuid', $data['player_uuid'])
                ->increment('distance_traveled', $totalDistance);
            Player::where('uuid', $data['player_uuid'])
                ->increment('total_playtime', $playtime);
        }
    }

    /**
     * Update shots fired count
     */
    private function updatePlayerShotCount(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;

            $stat = $this->getOrCreatePlayerStat(
                $data['player_uuid'],
                $data['player_name'] ?? 'Unknown',
                $serverId
            );
            $stat->increment('shots_fired', $data['total_rounds'] ?? 0);
            $stat->update(['last_seen_at' => now()]);
        }
    }

    /**
     * Update grenades thrown count
     */
    private function updatePlayerGrenadeStats(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;

            $stat = $this->getOrCreatePlayerStat(
                $data['player_uuid'],
                $data['player_name'] ?? 'Unknown',
                $serverId
            );
            $stat->increment('grenades_thrown');
            $stat->update(['last_seen_at' => now()]);
        }
    }

    /**
     * Update healing stats
     */
    private function updatePlayerHealingStats(array $data): void
    {
        $serverId = $data['server_id'] ?? 1;

        // Update healer stats
        if (!empty($data['healer_uuid'])) {
            $healerStat = $this->getOrCreatePlayerStat(
                $data['healer_uuid'],
                $data['healer_name'] ?? 'Unknown',
                $serverId
            );
            $healerStat->increment('heals_given');
            $healerStat->update(['last_seen_at' => now()]);
        }

        // Update patient stats (if not self-heal and not AI)
        if (!empty($data['patient_uuid']) && empty($data['is_self']) && empty($data['patient_is_ai'])) {
            $patientStat = $this->getOrCreatePlayerStat(
                $data['patient_uuid'],
                $data['patient_name'] ?? 'Unknown',
                $serverId
            );
            $patientStat->increment('heals_received');
        }
    }

    /**
     * Update base capture stats
     */
    private function updatePlayerBaseCaptureStats(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;

            $stat = $this->getOrCreatePlayerStat(
                $data['player_uuid'],
                $data['player_name'] ?? 'Unknown',
                $serverId
            );
            $stat->increment('bases_captured');
            $stat->update(['last_seen_at' => now()]);
        }
    }

    /**
     * Update supply delivery stats
     */
    private function updatePlayerSupplyStats(array $data): void
    {
        if (!empty($data['player_uuid'])) {
            $serverId = $data['server_id'] ?? 1;
            $amount = $data['estimated_amount'] ?? 1;

            $stat = $this->getOrCreatePlayerStat(
                $data['player_uuid'],
                $data['player_name'] ?? 'Unknown',
                $serverId
            );
            $stat->increment('supplies_delivered', $amount);
            $stat->update(['last_seen_at' => now()]);

            // Also update players table
            Player::where('uuid', $data['player_uuid'])
                ->increment('total_supplies_delivered', $amount);
        }
    }

    // ============================
    // Legacy SAT Webhook Methods
    // ============================

    public function handle(Request $request): JsonResponse
    {
        Log::channel('daily')->info('=== GAME EVENT RECEIVED ===', [
            'body' => $request->all(),
            'ip' => $request->ip(),
        ]);

        $payload = $request->all();
        $serverId = $request->input('server_id')
            ?? $request->input('serverId')
            ?? $request->input('server')
            ?? 1;

        if ($request->has('events') && is_array($request->input('events'))) {
            return $this->handleEventsArray($request->input('events'), $serverId, $payload);
        }

        return $this->handleSingleEvent($request, $serverId, $payload);
    }

    /**
     * Legacy webhook endpoint
     */
    public function legacyWebhook(Request $request): JsonResponse
    {
        return $this->handle($request);
    }

    private function handleEventsArray(array $events, int $serverId, array $fullPayload): JsonResponse
    {
        $results = [];

        foreach ($events as $event) {
            $eventName = $event['name'] ?? $event['event'] ?? 'unknown';
            $eventData = $event['data'] ?? [];
            $timestamp = $this->parseTimestampValue($event['timestamp'] ?? null);

            GameEvent::create([
                'server_id' => $serverId,
                'event_type' => $eventName,
                'payload' => $event,
                'event_timestamp' => $timestamp,
            ]);

            $result = match ($eventName) {
                'serveradmintools_player_killed' => $this->handleSATPlayerKilled($eventData, $serverId, $timestamp),
                'serveradmintools_player_joined' => $this->handleSATPlayerJoined($eventData, $serverId, $timestamp),
                'serveradmintools_player_left' => $this->handleSATPlayerLeft($eventData, $serverId, $timestamp),
                'serveradmintools_game_started' => $this->handleSATGameStarted($eventData, $serverId, $timestamp),
                'serveradmintools_game_ended' => $this->handleSATGameEnded($eventData, $serverId, $timestamp),
                'serveradmintools_conflict_base_captured' => $this->handleSATBaseCaptured($eventData, $serverId, $timestamp),
                default => ['success' => true, 'event' => $eventName, 'note' => 'logged'],
            };

            $results[] = $result;
        }

        return response()->json([
            'success' => true,
            'message' => 'Events processed',
            'count' => count($events),
            'results' => $results,
        ]);
    }

    private function handleSATPlayerKilled(array $data, int $serverId, Carbon $timestamp): array
    {
        Log::info('Processing SAT Player Killed Event', $data);

        $victimName = $data['player'] ?? 'Unknown';
        $killerRaw = $data['instigator'] ?? 'Unknown';
        $isFriendlyFire = $data['friendly'] ?? false;

        $killerIsAi = strtoupper($killerRaw) === 'AI';
        $killerName = $killerIsAi ? null : $killerRaw;

        $killerId = $data['instigator_identity'] ?? $data['instigatorIdentity'] ?? $data['killer_id'] ?? null;
        $victimId = $data['identity'] ?? $data['player_identity'] ?? $data['playerIdentity'] ?? $data['victim_id'] ?? null;
        $victimIsAi = isset($data['player_is_ai']) ? (bool)$data['player_is_ai'] : false;
        $weapon = $data['weapon'] ?? $data['cause'] ?? null;
        $weaponPrefab = $data['weapon_prefab'] ?? $data['weaponPrefab'] ?? null;
        $distance = $data['distance'] ?? null;

        $killLog = KillLog::create([
            'server_id' => $serverId,
            'killer_id' => $killerId,
            'killer_name' => $killerIsAi ? 'AI' : $killerName,
            'killer_is_ai' => $killerIsAi,
            'victim_id' => $victimId,
            'victim_name' => $victimName,
            'victim_is_ai' => $victimIsAi,
            'weapon' => $weapon,
            'weapon_prefab' => $weaponPrefab,
            'is_friendly_fire' => (bool) $isFriendlyFire,
            'distance' => $distance,
            'event_timestamp' => $timestamp,
        ]);

        if ($killerId && !$killerIsAi) {
            Player::updateOrCreate(
                ['uuid' => $killerId],
                ['player_name' => $killerName ?? 'Unknown', 'last_seen' => $timestamp]
            )->increment('kills');
        }

        if ($victimId && !$victimIsAi) {
            Player::updateOrCreate(
                ['uuid' => $victimId],
                ['player_name' => $victimName, 'last_seen' => $timestamp]
            )->increment('deaths');
        }

        Log::info('SAT Kill logged', ['id' => $killLog->id, 'killer' => $killerName ?? 'AI', 'victim' => $victimName]);

        return [
            'success' => true,
            'event' => 'player_killed',
            'id' => $killLog->id,
        ];
    }

    private function handleSATPlayerJoined(array $data, int $serverId, Carbon $timestamp): array
    {
        Log::info('Processing SAT Player Joined Event', $data);

        $playerName = $data['player'] ?? $data['name'] ?? 'Unknown';
        $playerId = $data['identity'] ?? $data['uid'] ?? $data['player_id'] ?? null;

        $session = PlayerSession::create([
            'server_id' => $serverId,
            'player_name' => $playerName,
            'player_uuid' => $playerId,
            'event_type' => 'connect',
            'timestamp' => $timestamp,
        ]);

        $playerData = [
            'player_name' => $playerName,
            'last_seen' => $timestamp,
            'server_id' => $serverId,
        ];

        if ($playerId) {
            $player = Player::updateOrCreate(['uuid' => $playerId], $playerData);
        } else {
            $player = Player::updateOrCreate(['player_name' => $playerName], $playerData);
        }

        if ($player->wasRecentlyCreated) {
            $player->update(['first_seen' => $timestamp]);
        }

        $player->increment('sessions');

        Log::info('SAT Player joined logged', ['id' => $session->id, 'player' => $playerName, 'uuid' => $playerId]);

        return [
            'success' => true,
            'event' => 'player_joined',
            'id' => $session->id,
        ];
    }

    private function handleSATPlayerLeft(array $data, int $serverId, Carbon $timestamp): array
    {
        Log::info('Processing SAT Player Left Event', $data);

        $playerName = $data['player'] ?? $data['name'] ?? 'Unknown';
        $playerId = $data['identity'] ?? $data['uid'] ?? $data['player_id'] ?? null;

        $session = PlayerSession::create([
            'server_id' => $serverId,
            'player_name' => $playerName,
            'player_uuid' => $playerId,
            'event_type' => 'disconnect',
            'timestamp' => $timestamp,
        ]);

        if ($playerId) {
            Player::where('uuid', $playerId)->update(['last_seen' => $timestamp]);
        } else {
            Player::where('player_name', $playerName)->update(['last_seen' => $timestamp]);
        }

        Log::info('SAT Player left logged', ['id' => $session->id, 'player' => $playerName]);

        return [
            'success' => true,
            'event' => 'player_left',
            'id' => $session->id,
        ];
    }

    private function handleSATGameStarted(array $data, int $serverId, Carbon $timestamp): array
    {
        Log::info('SAT Game Started Event', $data);

        return [
            'success' => true,
            'event' => 'game_started',
        ];
    }

    private function handleSATGameEnded(array $data, int $serverId, Carbon $timestamp): array
    {
        Log::info('SAT Game Ended Event', $data);

        return [
            'success' => true,
            'event' => 'game_ended',
        ];
    }

    private function handleSATBaseCaptured(array $data, int $serverId, Carbon $timestamp): array
    {
        Log::info('SAT Base Captured Event', $data);

        $baseName = $data['base'] ?? $data['name'] ?? 'Unknown';
        $faction = $data['faction'] ?? $data['team'] ?? null;

        return [
            'success' => true,
            'event' => 'base_captured',
            'base' => $baseName,
            'faction' => $faction,
        ];
    }

    private function handleSingleEvent(Request $request, int $serverId, array $payload): JsonResponse
    {
        $eventType = $request->input('event')
            ?? $request->input('event_name')
            ?? $request->input('event_type')
            ?? $request->input('type')
            ?? $request->input('eventType')
            ?? 'unknown';

        $timestamp = $this->parseTimestamp($request);

        GameEvent::create([
            'server_id' => $serverId,
            'event_type' => $eventType,
            'payload' => $payload,
            'event_timestamp' => $timestamp,
        ]);

        $result = match ($eventType) {
            'serveradmintools_player_killed',
            'player_killed',
            'kill' => $this->handlePlayerKilled($request, $serverId, $timestamp),

            'serveradmintools_player_joined',
            'player_joined',
            'player_connected',
            'connect' => $this->handlePlayerJoined($request, $serverId, $timestamp),

            'serveradmintools_player_left',
            'player_left',
            'player_disconnected',
            'disconnect' => $this->handlePlayerLeft($request, $serverId, $timestamp),

            default => [
                'success' => true,
                'message' => 'Event received',
                'event_type' => $eventType,
            ],
        };

        return response()->json($result);
    }

    private function parseTimestampValue($ts): Carbon
    {
        if ($ts) {
            if (is_numeric($ts) && strlen((string)$ts) > 10) {
                return Carbon::createFromTimestampMs($ts);
            }
            if (is_numeric($ts)) {
                return Carbon::createFromTimestamp($ts);
            }
            try {
                return Carbon::parse($ts);
            } catch (\Exception $e) {
                // Fall through to default
            }
        }

        return now();
    }

    private function parseTimestamp(Request $request): Carbon
    {
        $ts = $request->input('timestamp')
            ?? $request->input('time')
            ?? $request->input('event_time')
            ?? null;

        return $this->parseTimestampValue($ts);
    }

    private function handlePlayerKilled(Request $request, int $serverId, Carbon $timestamp): array
    {
        $killerId = $request->input('killer_id') ?? $request->input('killerId') ?? null;
        $killerName = $request->input('killer_name') ?? $request->input('killerName') ?? $request->input('killer') ?? null;
        $killerIsAi = $request->input('killer_is_ai') ?? $request->input('killerIsAI') ?? false;
        $victimId = $request->input('victim_id') ?? $request->input('victimId') ?? $request->input('player_id') ?? null;
        $victimName = $request->input('victim_name') ?? $request->input('victimName') ?? $request->input('victim') ?? null;
        $victimIsAi = $request->input('victim_is_ai') ?? $request->input('victimIsAI') ?? false;
        $weapon = $request->input('weapon') ?? $request->input('weapon_name') ?? null;
        $isFriendlyFire = $request->input('is_friendly_fire') ?? $request->input('friendlyFire') ?? false;
        $distance = $request->input('distance') ?? null;

        $killLog = KillLog::create([
            'server_id' => $serverId,
            'killer_id' => $killerId,
            'killer_name' => $killerName,
            'killer_is_ai' => (bool) $killerIsAi,
            'victim_id' => $victimId,
            'victim_name' => $victimName,
            'victim_is_ai' => (bool) $victimIsAi,
            'weapon' => $weapon,
            'is_friendly_fire' => (bool) $isFriendlyFire,
            'distance' => $distance,
            'event_timestamp' => $timestamp,
        ]);

        return ['success' => true, 'message' => 'Kill event recorded', 'id' => $killLog->id];
    }

    private function handlePlayerJoined(Request $request, int $serverId, Carbon $timestamp): array
    {
        $playerName = $request->input('player_name') ?? $request->input('playerName') ?? $request->input('player') ?? 'Unknown';
        $playerId = $request->input('player_id') ?? $request->input('playerId') ?? $request->input('uid') ?? null;

        $session = PlayerSession::create([
            'server_id' => $serverId,
            'player_name' => $playerName,
            'player_uuid' => $playerId,
            'event_type' => 'connect',
            'timestamp' => $timestamp,
        ]);

        return ['success' => true, 'message' => 'Player join recorded', 'id' => $session->id];
    }

    private function handlePlayerLeft(Request $request, int $serverId, Carbon $timestamp): array
    {
        $playerName = $request->input('player_name') ?? $request->input('playerName') ?? $request->input('player') ?? 'Unknown';
        $playerId = $request->input('player_id') ?? $request->input('playerId') ?? $request->input('uid') ?? null;

        $session = PlayerSession::create([
            'server_id' => $serverId,
            'player_name' => $playerName,
            'player_uuid' => $playerId,
            'event_type' => 'disconnect',
            'timestamp' => $timestamp,
        ]);

        return ['success' => true, 'message' => 'Player disconnect recorded', 'id' => $session->id];
    }
}
