<?php

namespace Tests\Feature\Players;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class PlayerComparisonTest extends TestCase
{
    use RefreshDatabase;

    public function test_player_search_page_loads(): void
    {
        $response = $this->get('/players');

        $response->assertOk();
    }

    public function test_player_search_returns_results(): void
    {
        $user = User::factory()->create([
            'name' => 'TestPlayer123',
            'player_uuid' => 'uuid-123',
        ]);

        // Create server and connection so player appears in search
        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('connections')->insert([
            'server_id' => 1,
            'player_uuid' => 'uuid-123',
            'player_name' => 'TestPlayer123',
            'event_type' => 'CONNECT',
            'occurred_at' => now(),
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        $response = $this->get('/players?q=TestPlayer');

        $response->assertOk();
        $response->assertSee('TestPlayer123');
    }

    public function test_player_comparison_page_loads(): void
    {
        $response = $this->get('/players/compare');

        $response->assertOk();
    }

    public function test_player_comparison_with_two_players(): void
    {
        $player1 = User::factory()->create([
            'player_uuid' => 'uuid-1',
            'name' => 'Player1',
        ]);

        $player2 = User::factory()->create([
            'player_uuid' => 'uuid-2',
            'name' => 'Player2',
        ]);

        // Create server
        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'Player1',
            'server_id' => 1,
            'kills' => 100,
            'deaths' => 50,
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-2',
            'player_name' => 'Player2',
            'server_id' => 1,
            'kills' => 80,
            'deaths' => 40,
        ]);

        $response = $this->get("/players/compare?p1={$player1->id}&p2={$player2->id}");

        $response->assertOk();
        $response->assertSee('Player1');
        $response->assertSee('Player2');
        $response->assertSee('100'); // kills
        $response->assertSee('80');
    }

    public function test_player_comparison_with_four_players(): void
    {
        $players = User::factory()->count(4)->create([
            'player_uuid' => fn() => 'uuid-'.fake()->uuid(),
        ]);

        $response = $this->get(
            "/players/compare?p1={$players[0]->id}&p2={$players[1]->id}&p3={$players[2]->id}&p4={$players[3]->id}"
        );

        $response->assertOk();
        foreach ($players as $player) {
            $response->assertSee($player->name);
        }
    }

    public function test_player_comparison_requires_at_least_two_players(): void
    {
        $player = User::factory()->create();

        $response = $this->get("/players/compare?p1={$player->id}");

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_head_to_head_endpoint_returns_matchup_data(): void
    {
        $player1 = User::factory()->create(['player_uuid' => 'uuid-1']);
        $player2 = User::factory()->create(['player_uuid' => 'uuid-2']);

        // Create a kill where player1 killed player2
        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('player_kills')->insert([
            'server_id' => 1,
            'killer_uuid' => 'uuid-1',
            'killer_name' => $player1->name,
            'victim_uuid' => 'uuid-2',
            'victim_name' => $player2->name,
            'weapon_name' => 'M4A1',
            'distance' => 100,
            'killed_at' => now(),
            'created_at' => now(),
        ]);

        $response = $this->get("/players/compare/head-to-head?p1={$player1->id}&p2={$player2->id}");

        $response->assertOk();
        $response->assertJson([
            'player1_kills' => 1,
            'player2_kills' => 0,
        ]);
    }

    public function test_player_autocomplete_endpoint_returns_suggestions(): void
    {
        $player1 = User::factory()->create(['name' => 'TestPlayer1', 'player_uuid' => 'uuid-1']);
        $player2 = User::factory()->create(['name' => 'TestPlayer2', 'player_uuid' => 'uuid-2']);
        $player3 = User::factory()->create(['name' => 'OtherPlayer', 'player_uuid' => 'uuid-3']);

        // Create server
        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        // Create player stats so searchPlayer can find them
        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'TestPlayer1',
            'server_id' => 1,
            'kills' => 10,
            'deaths' => 5,
        ]);
        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-2',
            'player_name' => 'TestPlayer2',
            'server_id' => 1,
            'kills' => 20,
            'deaths' => 10,
        ]);
        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-3',
            'player_name' => 'OtherPlayer',
            'server_id' => 1,
            'kills' => 5,
            'deaths' => 15,
        ]);

        $response = $this->get('/api/players/search?q=Test');

        $response->assertOk();
        $response->assertJsonCount(2);
        $response->assertJsonFragment(['name' => 'TestPlayer1']);
        $response->assertJsonFragment(['name' => 'TestPlayer2']);
    }

    public function test_player_autocomplete_minimum_query_length(): void
    {
        $response = $this->get('/api/players/search?q=T');

        $response->assertOk();
        $response->assertJsonCount(0);
    }
}
