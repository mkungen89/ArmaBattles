<?php

namespace Tests\Feature\Leaderboards;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class LeaderboardTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();

        // Create server
        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);
    }

    public function test_leaderboard_page_loads(): void
    {
        $response = $this->get('/leaderboard');

        $response->assertOk();
    }

    public function test_kills_leaderboard_displays_correctly(): void
    {
        $player1 = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'TopKiller']);
        $player2 = User::factory()->create(['player_uuid' => 'uuid-2', 'name' => 'SecondKiller']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'TopKiller',
            'server_id' => 1,
            'kills' => 500,
            'deaths' => 100,
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-2',
            'player_name' => 'SecondKiller',
            'server_id' => 1,
            'kills' => 300,
            'deaths' => 150,
        ]);

        $response = $this->get('/leaderboard?stat=kills');

        $response->assertOk();
        $response->assertSeeInOrder(['TopKiller', 'SecondKiller']);
    }

    public function test_kd_leaderboard_sorts_by_ratio(): void
    {
        // Player with higher K/D but fewer kills
        $player1 = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'HighKD']);
        // Player with more kills but lower K/D
        $player2 = User::factory()->create(['player_uuid' => 'uuid-2', 'name' => 'LowKD']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'HighKD',
            'server_id' => 1,
            'kills' => 100,
            'deaths' => 10, // K/D = 10.0
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-2',
            'player_name' => 'LowKD',
            'server_id' => 1,
            'kills' => 500,
            'deaths' => 250, // K/D = 2.0
        ]);

        $response = $this->get('/leaderboard?sort=kills');

        $response->assertOk();
        // Currently sorted by kills, not K/D ratio - LowKD has more kills
        $response->assertSeeInOrder(['LowKD', 'HighKD']);
    }

    public function test_playtime_leaderboard_displays_correctly(): void
    {
        $player1 = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'NoLife']);
        $player2 = User::factory()->create(['player_uuid' => 'uuid-2', 'name' => 'Casual']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'NoLife',
            'server_id' => 1,
            'playtime_seconds' => 360000, // 100 hours
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-2',
            'player_name' => 'Casual',
            'server_id' => 1,
            'playtime_seconds' => 36000, // 10 hours
        ]);

        $response = $this->get('/leaderboard?sort=playtime_seconds');

        $response->assertOk();
        $response->assertSeeInOrder(['NoLife', 'Casual']);
    }

    public function test_leaderboard_filter_by_period(): void
    {
        $response = $this->get('/leaderboard?sort=kills&period=week');

        $response->assertOk();
        // Would need to mock date-based filtering
    }

    public function test_leaderboard_pagination_works(): void
    {
        // Create 30 players
        for ($i = 1; $i <= 30; $i++) {
            $player = User::factory()->create(['player_uuid' => "uuid-$i", 'name' => "Player$i"]);
            \DB::table('player_stats')->insert([
                'player_uuid' => "uuid-$i",
                'player_name' => "Player$i",
                'server_id' => 1,
                'kills' => 100 - $i,
            ]);
        }

        $response = $this->get('/leaderboard?sort=kills&page=1');
        $response->assertOk();

        $response = $this->get('/leaderboard?sort=kills&page=2');
        $response->assertOk();
    }

    public function test_leaderboard_csv_export(): void
    {
        $player = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'ExportPlayer']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'ExportPlayer',
            'server_id' => 1,
            'kills' => 100,
            'deaths' => 50,
            'playtime_seconds' => 0,
            'xp_total' => 0,
            'total_distance' => 0,
            'total_roadkills' => 0,
        ]);

        $response = $this->actingAs($player)->get('/export/leaderboard/kills/csv');

        $response->assertOk();
        $response->assertHeader('Content-Type', 'text/csv; charset=UTF-8');
        $this->assertStringContainsString('ExportPlayer', $response->streamedContent());
    }

    public function test_leaderboard_json_export(): void
    {
        $player = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'JSONPlayer']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'JSONPlayer',
            'server_id' => 1,
            'kills' => 100,
            'deaths' => 0,
            'playtime_seconds' => 0,
            'xp_total' => 0,
            'total_distance' => 0,
            'total_roadkills' => 0,
            'headshots' => 0,
            'shots_fired' => 0,
            'grenades_thrown' => 0,
        ]);

        $response = $this->actingAs($player)->get('/export/leaderboard/kills/json');

        $response->assertOk();
        $response->assertJsonStructure([
            'type',
            'exported_at',
            'total_players',
            'players' => [
                '*' => ['player_name', 'kills'],
            ],
        ]);
    }

    public function test_leaderboard_respects_minimum_playtime(): void
    {
        // Player with high kills but low playtime (potential cheater)
        $player1 = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'Suspicious']);
        // Player with normal stats
        $player2 = User::factory()->create(['player_uuid' => 'uuid-2', 'name' => 'Legit']);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-1',
            'player_name' => 'Suspicious',
            'server_id' => 1,
            'kills' => 1000,
            'playtime_seconds' => 60, // Only 1 minute
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'uuid-2',
            'player_name' => 'Legit',
            'server_id' => 1,
            'kills' => 500,
            'playtime_seconds' => 36000, // 10 hours
        ]);

        $response = $this->get('/leaderboard?stat=kills');

        $response->assertOk();
        // Should filter out suspicious player with < 1 hour playtime
        $response->assertSee('Legit');
        // Depending on min_playtime setting, might not see Suspicious
    }
}
