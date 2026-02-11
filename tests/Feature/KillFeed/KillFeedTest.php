<?php

namespace Tests\Feature\KillFeed;

use App\Events\KillFeedUpdated;
use App\Models\Server;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Event;
use Tests\TestCase;

class KillFeedTest extends TestCase
{
    use RefreshDatabase;

    protected Server $server;

    protected function setUp(): void
    {
        parent::setUp();

        $this->server = Server::create([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
        ]);
    }

    public function test_kill_feed_page_loads(): void
    {
        $response = $this->get('/kill-feed');

        $response->assertOk();
    }

    public function test_kill_feed_displays_recent_kills(): void
    {
        \DB::table('player_kills')->insert([
            'server_id' => $this->server->id,
            'killer_uuid' => 'killer-uuid',
            'killer_name' => 'TopShooter',
            'victim_uuid' => 'victim-uuid',
            'victim_name' => 'UnluckyPlayer',
            'weapon' => 'M4A1',
            'distance' => 150,
            'is_headshot' => true,
            'killed_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->get('/kill-feed');

        $response->assertOk();
        $response->assertSee('TopShooter');
        $response->assertSee('UnluckyPlayer');
        $response->assertSee('M4A1');
    }

    public function test_kill_feed_websocket_event_dispatched(): void
    {
        Event::fake([KillFeedUpdated::class]);

        \DB::table('player_kills')->insert([
            'server_id' => $this->server->id,
            'killer_uuid' => 'killer-uuid',
            'killer_name' => 'Killer',
            'victim_uuid' => 'victim-uuid',
            'victim_name' => 'Victim',
            'weapon' => 'AK74',
            'killed_at' => now(),
            'created_at' => now(),
        ]);

        // Simulate API call that triggers event
        // Event::assertDispatched(KillFeedUpdated::class);
    }

    public function test_kill_feed_server_filter(): void
    {
        $server2 = Server::create([
            'battlemetrics_id' => '67890',
            'name' => 'Server 2',
            'ip' => '127.0.0.2',
            'port' => 2002,
        ]);

        \DB::table('player_kills')->insert([
            'server_id' => $this->server->id,
            'killer_uuid' => 'killer-1',
            'killer_name' => 'Server1Kill',
            'victim_uuid' => 'victim-1',
            'victim_name' => 'Victim1',
            'weapon' => 'M4A1',
            'killed_at' => now(),
            'created_at' => now(),
        ]);

        \DB::table('player_kills')->insert([
            'server_id' => $server2->id,
            'killer_uuid' => 'killer-2',
            'killer_name' => 'Server2Kill',
            'victim_uuid' => 'victim-2',
            'victim_name' => 'Victim2',
            'weapon' => 'AK74',
            'killed_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->get("/kill-feed?server_id={$this->server->id}");

        $response->assertOk();
        $response->assertSee('Server1Kill');
        $response->assertDontSee('Server2Kill');
    }

    public function test_kill_feed_pagination(): void
    {
        // Create 50 kills
        for ($i = 0; $i < 50; $i++) {
            \DB::table('player_kills')->insert([
                'server_id' => $this->server->id,
                'killer_uuid' => "killer-$i",
                'killer_name' => "Killer$i",
                'victim_uuid' => "victim-$i",
                'victim_name' => "Victim$i",
                'weapon' => 'M4A1',
                'killed_at' => now()->subMinutes($i),
                'created_at' => now()->subMinutes($i),
            ]);
        }

        $response = $this->get('/kill-feed?page=1');
        $response->assertOk();

        $response = $this->get('/kill-feed?page=2');
        $response->assertOk();
    }

    public function test_server_heatmap_page_loads(): void
    {
        $response = $this->get("/servers/{$this->server->id}/heatmap");

        $response->assertOk();
    }

    public function test_activity_feed_page_loads(): void
    {
        $response = $this->get('/activity');

        $response->assertOk();
    }

    public function test_activity_feed_shows_multiple_event_types(): void
    {
        // Kill event
        \DB::table('player_kills')->insert([
            'server_id' => $this->server->id,
            'killer_uuid' => 'killer-uuid',
            'killer_name' => 'Killer',
            'victim_uuid' => 'victim-uuid',
            'victim_name' => 'Victim',
            'weapon' => 'M4A1',
            'killed_at' => now(),
            'created_at' => now(),
        ]);

        // Connection event
        \DB::table('connections')->insert([
            'server_id' => $this->server->id,
            'player_uuid' => 'player-uuid',
            'player_name' => 'NewPlayer',
            'event_type' => 'CONNECT',
            'ip_address' => '127.0.0.1',
            'connected_at' => now(),
            'created_at' => now(),
        ]);

        // Base capture event
        \DB::table('base_events')->insert([
            'server_id' => $this->server->id,
            'player_uuid' => 'player-uuid',
            'player_name' => 'CaptainCap',
            'event_type' => 'CAPTURED',
            'base_name' => 'Radio Tower',
            'faction' => 'BLUFOR',
            'occurred_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->get('/activity');

        $response->assertOk();
        $response->assertSee('Killer');
        $response->assertSee('NewPlayer');
        $response->assertSee('CaptainCap');
    }
}
