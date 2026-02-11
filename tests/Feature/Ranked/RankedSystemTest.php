<?php

namespace Tests\Feature\Ranked;

use App\Models\PlayerRating;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class RankedSystemTest extends TestCase
{
    use RefreshDatabase;

    public function test_ranked_leaderboard_page_loads(): void
    {
        $response = $this->get('/ranked');

        $response->assertOk();
        $response->assertSee('Ranked');
    }

    public function test_user_can_view_ranked_about_page(): void
    {
        $response = $this->get('/ranked/about');

        $response->assertOk();
        $response->assertSee('Glicko-2');
    }

    public function test_user_can_opt_in_to_ranked(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid-ranked']);

        $response = $this->actingAs($user)->post('/ranked/opt-in');

        $response->assertRedirect();
        $this->assertDatabaseHas('player_ratings', [
            'player_uuid' => 'test-uuid-ranked',
        ]);
    }

    public function test_user_cannot_opt_in_without_player_uuid(): void
    {
        $user = User::factory()->create(['player_uuid' => null]);

        $response = $this->actingAs($user)->post('/ranked/opt-in');

        $response->assertRedirect();
        $response->assertSessionHas('error');
    }

    public function test_user_can_opt_out_of_ranked(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid-ranked']);
        PlayerRating::create([
            'user_id' => $user->id,
            'player_uuid' => 'test-uuid-ranked',
            'rating' => 1500,
            'rating_deviation' => 350,
            'volatility' => 0.06,
            'opted_in_at' => now(),
        ]);

        $response = $this->actingAs($user)->post('/ranked/opt-out');

        $response->assertRedirect();
        $this->assertDatabaseHas('player_ratings', [
            'player_uuid' => 'test-uuid-ranked',
            'opted_in_at' => null,
        ]);
    }

    public function test_unplaced_player_shows_placement_progress(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid-ranked']);
        PlayerRating::create([
            'user_id' => $user->id,
            'player_uuid' => 'test-uuid-ranked',
            'rating' => 1500,
            'rating_deviation' => 350,
            'volatility' => 0.06,
            'placement_games' => 5,
            'is_placed' => false,
            'opted_in_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/ranked');

        $response->assertOk();
        // Check placement progress is shown (5 out of 10 games)
        $response->assertSee('5');
        $response->assertSee('10');
    }

    public function test_placed_player_shows_tier(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid-ranked']);
        PlayerRating::create([
            'user_id' => $user->id,
            'player_uuid' => 'test-uuid-ranked',
            'rating' => 1600,
            'rating_deviation' => 150,
            'volatility' => 0.06,
            'placement_games' => 10,
            'is_placed' => true,
            'rank_tier' => 'platinum',
            'opted_in_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/ranked');

        $response->assertOk();
        $response->assertSee('Platinum');
    }

    public function test_leaderboard_displays_only_opted_in_players(): void
    {
        // Opted in and placed
        $user1 = User::factory()->create(['player_uuid' => 'uuid-1', 'name' => 'RankedPlayer']);
        PlayerRating::create([
            'user_id' => $user1->id,
            'player_uuid' => 'uuid-1',
            'rating' => 1800,
            'rating_deviation' => 150,
            'volatility' => 0.06,
            'is_placed' => true,
            'opted_in_at' => now(),
        ]);

        // Opted out
        $user2 = User::factory()->create(['player_uuid' => 'uuid-2', 'name' => 'UnrankedPlayer']);
        PlayerRating::create([
            'user_id' => $user2->id,
            'player_uuid' => 'uuid-2',
            'rating' => 1900,
            'rating_deviation' => 150,
            'volatility' => 0.06,
            'is_placed' => true,
            'opted_in_at' => null,
        ]);

        $response = $this->get('/ranked');

        $response->assertSee('RankedPlayer');
        $response->assertDontSee('UnrankedPlayer');
    }
}
