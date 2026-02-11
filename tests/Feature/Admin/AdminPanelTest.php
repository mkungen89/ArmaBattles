<?php

namespace Tests\Feature\Admin;

use App\Models\AdminAuditLog;
use App\Models\Server;
use App\Models\User;
use App\Models\Vehicle;
use App\Models\Weapon;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class AdminPanelTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        Storage::fake('public');
    }

    public function test_admin_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_admin_panel(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin');

        $response->assertStatus(403);
    }

    // === Weapons Management ===

    public function test_admin_can_create_weapon(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/weapons', [
            'name' => 'M4A1',
            'display_name' => 'M4A1 Carbine',
            'weapon_type' => 'assault_rifle',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('weapons', [
            'name' => 'M4A1',
        ]);
    }

    public function test_admin_can_upload_weapon_image(): void
    {
        $weapon = Weapon::create([
            'name' => 'AK74',
            'display_name' => 'AK-74',
        ]);

        $file = UploadedFile::fake()->image('ak74.png');

        $response = $this->actingAs($this->admin)
            ->post("/admin/weapons/{$weapon->id}/upload-image", [
                'image' => $file,
            ]);

        $response->assertRedirect();
        $weapon->refresh();
        $this->assertNotNull($weapon->image_path);
        Storage::disk('public')->assertExists($weapon->image_path);
    }

    public function test_admin_can_delete_weapon_image(): void
    {
        $weapon = Weapon::create([
            'name' => 'AK74',
            'display_name' => 'AK-74',
            'image_path' => 'weapons/test.png',
        ]);

        Storage::disk('public')->put('weapons/test.png', 'content');

        $response = $this->actingAs($this->admin)
            ->delete("/admin/weapons/{$weapon->id}/delete-image");

        $response->assertRedirect();
        $weapon->refresh();
        $this->assertNull($weapon->image_path);
    }

    // === Vehicles Management ===

    public function test_admin_can_create_vehicle(): void
    {
        $response = $this->actingAs($this->admin)->post('/admin/vehicles', [
            'name' => 'UAZ469',
            'display_name' => 'UAZ-469',
            'vehicle_type' => 'light_vehicle',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicles', [
            'name' => 'UAZ469',
        ]);
    }

    public function test_admin_can_sync_vehicles_from_distance_data(): void
    {
        $this->markTestSkipped('Uses PostgreSQL-specific syntax (vehicles::text), not compatible with SQLite tests');

        // Create player distance record with vehicles JSON
        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('player_distance')->insert([
            'server_id' => 1,
            'player_uuid' => 'test-uuid',
            'player_name' => 'Test',
            'vehicles' => json_encode([
                ['name' => 'UAZ469', 'distance' => 1000],
                ['name' => 'M1025', 'distance' => 500],
            ]),
            'recorded_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/vehicles/sync-from-distance');

        $response->assertRedirect();
        $this->assertDatabaseHas('vehicles', ['name' => 'UAZ469']);
        $this->assertDatabaseHas('vehicles', ['name' => 'M1025']);
    }

    // === Audit Log ===

    public function test_audit_log_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/audit-log');

        $response->assertOk();
    }

    public function test_audit_log_records_admin_actions(): void
    {
        $this->actingAs($this->admin)->post('/admin/weapons', [
            'name' => 'TestGun',
            'display_name' => 'Test Gun',
        ]);

        $this->assertDatabaseHas('admin_audit_logs', [
            'user_id' => $this->admin->id,
            'action' => 'weapon.created',
        ]);
    }

    public function test_audit_log_can_filter_by_user(): void
    {
        $admin2 = User::factory()->create(['role' => 'admin']);

        // Admin 1 creates a weapon
        $this->actingAs($this->admin)->post('/admin/weapons', [
            'name' => 'Admin1Weapon',
            'display_name' => 'Admin 1 Weapon',
        ]);

        // Admin 2 creates a weapon
        $this->actingAs($admin2)->post('/admin/weapons', [
            'name' => 'Admin2Weapon',
            'display_name' => 'Admin 2 Weapon',
        ]);

        // Filter by admin 1
        $response = $this->actingAs($this->admin)
            ->get("/admin/audit-log?user_id={$this->admin->id}");

        $response->assertOk();
        $response->assertSee('Admin1Weapon');
        $response->assertDontSee('Admin2Weapon');
    }

    public function test_audit_log_csv_export(): void
    {
        AdminAuditLog::create([
            'user_id' => $this->admin->id,
            'action' => 'test.action',
            'ip_address' => '127.0.0.1',
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/audit-log/export');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
    }

    // === Anticheat Dashboard ===

    public function test_anticheat_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/anticheat');

        $response->assertOk();
    }

    public function test_anticheat_events_page_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/anticheat/events');

        $response->assertOk();
    }

    public function test_anticheat_stats_api_endpoint(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/anticheat/api/stats?range=24h');

        $response->assertOk();
        $response->assertJsonStructure([
            'stats',
        ]);
    }

    // === Game Stats Dashboard ===

    public function test_game_stats_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/game-stats');

        $response->assertOk();
    }

    public function test_game_stats_player_profile_loads(): void
    {
        $player = User::factory()->create(['player_uuid' => 'test-uuid']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'test-uuid',
            'player_name' => $player->name,
            'server_id' => 1,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get("/admin/game-stats/players/{$player->player_uuid}");

        $response->assertOk();
    }

    public function test_game_stats_events_table_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/game-stats/kills');

        $response->assertOk();
    }

    public function test_game_stats_api_tokens_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/game-stats/api-tokens');

        $response->assertOk();
    }

    public function test_admin_can_create_api_token(): void
    {
        $server = Server::create([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
        ]);

        $response = $this->actingAs($this->admin)
            ->post('/admin/game-stats/api-tokens', [
                'token_name' => 'Test Token',
                'token_type' => 'standard',
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('personal_access_tokens', [
            'name' => 'Test Token',
        ]);
    }

    public function test_admin_can_revoke_api_token(): void
    {
        $apiUser = User::firstOrCreate(
            ['email' => 'api@armabattles.se'],
            [
                'name' => 'API Service',
                'password' => bcrypt(bin2hex(random_bytes(32))),
                'role' => 'admin',
            ]
        );

        $token = $apiUser->createToken('Test Token', ['game-stats:write']);

        $response = $this->actingAs($this->admin)
            ->delete("/admin/game-stats/api-tokens/{$token->accessToken->id}");

        $response->assertRedirect();
        $this->assertDatabaseMissing('personal_access_tokens', [
            'id' => $token->accessToken->id,
        ]);
    }
}
