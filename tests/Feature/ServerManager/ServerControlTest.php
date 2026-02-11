<?php

namespace Tests\Feature\ServerManager;

use App\Models\Server;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ServerControlTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
        $this->server = Server::create([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'is_managed' => true,
        ]);
    }

    public function test_server_manager_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/server');

        $response->assertOk();
        $response->assertSee('Server Manager');
    }

    public function test_non_admin_cannot_access_server_manager(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/server');

        $response->assertStatus(403);
    }

    public function test_server_status_endpoint_returns_data(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/api/status');

        $response->assertOk();
        $response->assertJsonStructure([
            'health',
            'status',
        ]);
    }

    public function test_player_history_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/player-history');

        $response->assertOk();
    }

    public function test_performance_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/performance');

        $response->assertOk();
    }

    public function test_scheduled_restarts_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/scheduled-restarts');

        $response->assertOk();
    }

    public function test_admin_can_create_scheduled_restart(): void
    {
        $response = $this->actingAs($this->admin)
            ->post('/admin/server/scheduled-restarts', [
                'server_id' => $this->server->id,
                'type' => 'daily',
                'time' => '03:00',
                'warning_minutes' => 15,
            ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('scheduled_restarts', [
            'server_id' => $this->server->id,
            'type' => 'daily',
        ]);
    }

    public function test_quick_messages_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/quick-messages');

        $response->assertOk();
    }

    public function test_mod_updates_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/mod-updates');

        $response->assertOk();
    }

    public function test_server_comparison_page_loads(): void
    {
        $response = $this->actingAs($this->admin)
            ->get('/admin/server/comparison');

        $response->assertOk();
    }

    public function test_server_list_page_loads(): void
    {
        $response = $this->get('/servers');

        $response->assertOk();
        $response->assertSee($this->server->name);
    }

    public function test_server_detail_page_loads(): void
    {
        $response = $this->get("/servers/{$this->server->id}");

        $response->assertOk();
        $response->assertSee($this->server->name);
    }

    public function test_server_stats_page_loads(): void
    {
        $response = $this->get("/servers/{$this->server->id}/stats");

        $response->assertOk();
    }

    public function test_server_widget_page_loads(): void
    {
        $response = $this->get("/servers/{$this->server->id}/widget");

        $response->assertOk();
    }

    public function test_server_widget_api_returns_json(): void
    {
        $response = $this->get("/servers/{$this->server->id}/widget/api");

        $response->assertOk();
        $response->assertJson([
            'name' => $this->server->name,
        ]);
    }

    public function test_server_embed_page_loads(): void
    {
        $response = $this->get("/servers/{$this->server->id}/embed");

        $response->assertOk();
    }
}
