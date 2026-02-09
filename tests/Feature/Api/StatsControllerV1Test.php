<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class StatsControllerV1Test extends TestCase
{
    use RefreshDatabase;

    protected User $apiUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiUser = User::factory()->create(['role' => 'admin']);
    }

    // === Write Endpoints ===

    public function test_v1_player_kills_creates_record(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-kills', [
            'server_id' => 1,
            'killer_name' => 'Killer123',
            'killer_uuid' => 'uuid-killer-123',
            'victim_name' => 'Victim456',
            'victim_uuid' => 'uuid-victim-456',
            'weapon_name' => 'M4A1',
            'kill_distance' => 200.0,
            'is_headshot' => true,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('player_kills', [
            'killer_name' => 'Killer123',
            'victim_name' => 'Victim456',
            'weapon_name' => 'M4A1',
            'is_headshot' => true,
        ]);

        // Check stats aggregation
        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-killer-123',
            'kills' => 1,
            'headshots' => 1,
        ]);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-victim-456',
            'deaths' => 1,
        ]);
    }

    public function test_v1_damage_events_batch_creates_records(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/damage-events', [
            'events' => [
                [
                    'server_id' => 1,
                    'killer_uuid' => 'uuid-killer-1',
                    'killer_name' => 'Attacker',
                    'victim_uuid' => 'uuid-victim-1',
                    'victim_name' => 'Target',
                    'hit_zone_name' => 'Head',
                    'damage_amount' => 100.0,
                    'is_friendly_fire' => false,
                    'timestamp' => '2026-02-08T10:00:00.000Z',
                ],
                [
                    'server_id' => 1,
                    'killer_uuid' => 'uuid-killer-1',
                    'killer_name' => 'Attacker',
                    'victim_uuid' => 'uuid-victim-2',
                    'victim_name' => 'Target2',
                    'hit_zone_name' => 'Torso',
                    'damage_amount' => 50.0,
                    'is_friendly_fire' => false,
                    'timestamp' => '2026-02-08T10:00:01.000Z',
                ],
            ],
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true, 'inserted' => 2]);

        $this->assertDatabaseHas('damage_events', [
            'killer_uuid' => 'uuid-killer-1',
            'victim_uuid' => 'uuid-victim-1',
            'hit_zone_name' => 'Head',
        ]);

        // Check stats aggregation
        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-killer-1',
            'hits_head' => 1,
            'total_hits' => 2,
            'total_damage_dealt' => 150.0,
        ]);
    }

    public function test_v1_connections_creates_record(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/connections', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'event_type' => 'CONNECT',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('connections', [
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'event_type' => 'CONNECT',
        ]);
    }

    public function test_v1_xp_events_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/xp-events', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'xp_amount' => 500,
            'xp_type' => 'KILL',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('xp_events', [
            'player_uuid' => 'uuid-player-1',
            'xp_amount' => 500,
            'reward_type' => 'KILL',
        ]);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-player-1',
            'xp_total' => 500,
        ]);
    }

    public function test_v1_base_events_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/base-events', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'event_type' => 'CAPTURED',
            'base_name' => 'Main Base',
            'faction' => 'US',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('base_events', [
            'player_uuid' => 'uuid-player-1',
            'event_type' => 'CAPTURED',
            'base_name' => 'Main Base',
        ]);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-player-1',
            'bases_captured' => 1,
        ]);
    }

    public function test_v1_player_distance_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-distance', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'walking_distance' => 1000.0,
            'walking_time_seconds' => 1800,
            'total_vehicle_distance' => 500.0,
            'total_vehicle_time_seconds' => 1800,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('player_distance', [
            'player_uuid' => 'uuid-player-1',
            'walking_distance' => 1000.0,
        ]);

        // total_distance = walking_distance + total_vehicle_distance = 1500
        // playtime = walking_time_seconds + total_vehicle_time_seconds = 3600
        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-player-1',
            'total_distance' => 1500.0,
            'playtime_seconds' => 3600,
        ]);
    }

    public function test_v1_player_shooting_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-shooting', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'total_rounds' => 100,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('player_shooting', [
            'player_uuid' => 'uuid-player-1',
            'total_rounds' => 100,
        ]);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-player-1',
            'shots_fired' => 100,
        ]);
    }

    public function test_v1_player_grenades_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-grenades', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'grenade_type' => 'RGD5',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('player_grenades', [
            'player_uuid' => 'uuid-player-1',
            'grenade_type' => 'RGD5',
        ]);

        // Each grenade event increments the counter by 1
        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-player-1',
            'grenades_thrown' => 1,
        ]);
    }

    public function test_v1_player_healing_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-healing', [
            'server_id' => 1,
            'healer_uuid' => 'uuid-healer-1',
            'healer_name' => 'Medic123',
            'patient_uuid' => 'uuid-patient-1',
            'patient_name' => 'Patient456',
            'item' => 'Bandage',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('player_healing_rjs', [
            'healer_uuid' => 'uuid-healer-1',
            'target_uuid' => 'uuid-patient-1',
        ]);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-healer-1',
            'heals_given' => 1,
        ]);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-patient-1',
            'heals_received' => 1,
        ]);
    }

    public function test_v1_supply_deliveries_creates_record_and_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/supply-deliveries', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'estimated_amount' => 10,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('supply_deliveries', [
            'player_uuid' => 'uuid-player-1',
            'estimated_amount' => 10,
        ]);

        // supplies_delivered increments by estimated_amount (10)
        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-player-1',
            'supplies_delivered' => 10,
        ]);
    }

    public function test_v1_chat_events_creates_record(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/chat-events', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'message' => 'Hello world',
            'channel' => 'global',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200)
            ->assertJson(['success' => true]);

        $this->assertDatabaseHas('chat_events', [
            'player_uuid' => 'uuid-player-1',
            'message' => 'Hello world',
            'channel' => 'global',
        ]);
    }

    // === Read Endpoints ===

    public function test_v1_leaderboards_kills_returns_top_players(): void
    {
        Sanctum::actingAs($this->apiUser);

        // Create test data
        $this->postJson('/api/v1/player-kills', [
            'server_id' => 1,
            'killer_name' => 'TopKiller',
            'killer_uuid' => 'uuid-top-killer',
            'victim_name' => 'Victim1',
            'victim_uuid' => 'uuid-victim-1',
            'weapon_name' => 'M4A1',
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response = $this->getJson('/api/v1/leaderboards/kills?limit=10');

        $response->assertStatus(200)
            ->assertJsonStructure([
                '*' => [
                    'player_uuid',
                    'player_name',
                    'kills',
                ],
            ]);
    }

    public function test_v1_players_show_returns_player_details(): void
    {
        Sanctum::actingAs($this->apiUser);

        // Create player with stats
        $this->postJson('/api/v1/player-stats', [
            'server_id' => 1,
            'player_uuid' => 'uuid-player-1',
            'player_name' => 'Player123',
            'kills' => 50,
            'deaths' => 25,
        ]);

        $response = $this->getJson('/api/v1/players/uuid-player-1');

        $response->assertStatus(200)
            ->assertJson([
                'player_uuid' => 'uuid-player-1',
                'player_name' => 'Player123',
                'kills' => 50,
                'deaths' => 25,
            ]);
    }

    public function test_v1_stats_overview_returns_summary(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->getJson('/api/v1/stats/overview');

        $response->assertStatus(200)
            ->assertJsonStructure([
                'total_players',
                'total_kills',
                'total_deaths',
            ]);
    }

    // === Validation ===

    public function test_v1_endpoints_require_authentication(): void
    {
        $response = $this->postJson('/api/v1/player-kills', [
            'killer_name' => 'Test',
            'victim_name' => 'Test',
        ]);

        $response->assertStatus(401);
    }

    public function test_v1_validation_errors_return_422(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-kills', [
            'killer_name' => '', // Invalid
        ]);

        $response->assertStatus(422)
            ->assertJsonStructure(['message', 'errors']);
    }

    // === Roadkill Detection ===

    public function test_v1_roadkill_detection_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-kills', [
            'server_id' => 1,
            'killer_name' => 'Driver',
            'killer_uuid' => 'uuid-driver',
            'victim_name' => 'Pedestrian',
            'victim_uuid' => 'uuid-pedestrian',
            'weapon_name' => 'Vehicle',
            'is_roadkill' => true,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-driver',
            'total_roadkills' => 1,
        ]);
    }

    // === Team Kill Detection ===

    public function test_v1_team_kill_detection_updates_stats(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-kills', [
            'server_id' => 1,
            'killer_name' => 'TeamKiller',
            'killer_uuid' => 'uuid-tk',
            'victim_name' => 'Teammate',
            'victim_uuid' => 'uuid-teammate',
            'weapon_name' => 'M4A1',
            'is_team_kill' => true,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ]);

        $response->assertStatus(200);

        $this->assertDatabaseHas('player_stats', [
            'player_uuid' => 'uuid-tk',
            'team_kills' => 1,
        ]);
    }
}
