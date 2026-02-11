<?php

namespace Tests\Feature\Favorites;

use App\Models\Favorite;
use App\Models\Server;
use App\Models\Team;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class FavoriteTest extends TestCase
{
    use RefreshDatabase;

    public function test_favorites_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertOk();
    }

    public function test_user_can_favorite_player(): void
    {
        $user = User::factory()->create();
        $player = User::factory()->create();

        $response = $this->actingAs($user)->post('/favorites/toggle', [
            'type' => 'player',
            'id' => $player->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_type' => User::class,
            'favoritable_id' => $player->id,
        ]);
    }

    public function test_user_can_favorite_team(): void
    {
        $user = User::factory()->create();
        $captain = User::factory()->create();
        $team = Team::factory()->create(['captain_id' => $captain->id]);

        $response = $this->actingAs($user)->post('/favorites/toggle', [
            'type' => 'team',
            'id' => $team->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Team::class,
            'favoritable_id' => $team->id,
        ]);
    }

    public function test_user_can_favorite_server(): void
    {
        $user = User::factory()->create();
        $server = Server::create([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
        ]);

        $response = $this->actingAs($user)->post('/favorites/toggle', [
            'type' => 'server',
            'id' => $server->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('favorites', [
            'user_id' => $user->id,
            'favoritable_type' => Server::class,
            'favoritable_id' => $server->id,
        ]);
    }

    public function test_user_can_unfavorite(): void
    {
        $user = User::factory()->create();
        $player = User::factory()->create();

        $favorite = Favorite::create([
            'user_id' => $user->id,
            'favoritable_type' => User::class,
            'favoritable_id' => $player->id,
        ]);

        $response = $this->actingAs($user)->post('/favorites/toggle', [
            'type' => 'player',
            'id' => $player->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'id' => $favorite->id,
        ]);
    }

    public function test_user_cannot_favorite_same_item_twice(): void
    {
        $user = User::factory()->create();
        $player = User::factory()->create();

        // First toggle - adds favorite
        $this->actingAs($user)->post('/favorites/toggle', [
            'type' => 'player',
            'id' => $player->id,
        ]);

        // Second toggle - removes favorite
        $response = $this->actingAs($user)->post('/favorites/toggle', [
            'type' => 'player',
            'id' => $player->id,
        ]);

        $response->assertRedirect();
        $this->assertDatabaseMissing('favorites', [
            'user_id' => $user->id,
            'favoritable_type' => User::class,
            'favoritable_id' => $player->id,
        ]);
    }

    public function test_favorites_page_shows_all_types(): void
    {
        $user = User::factory()->create();
        $player = User::factory()->create(['name' => 'Favorite Player']);
        $captain = User::factory()->create();
        $team = Team::factory()->create([
            'captain_id' => $captain->id,
            'name' => 'Favorite Team',
        ]);
        $server = Server::create([
            'battlemetrics_id' => '12345',
            'name' => 'Favorite Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'favoritable_type' => User::class,
            'favoritable_id' => $player->id,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'favoritable_type' => Team::class,
            'favoritable_id' => $team->id,
        ]);

        Favorite::create([
            'user_id' => $user->id,
            'favoritable_type' => Server::class,
            'favoritable_id' => $server->id,
        ]);

        $response = $this->actingAs($user)->get('/favorites');

        $response->assertOk();
        $response->assertSee('Favorite Player');
        $response->assertSee('Favorite Team');
        $response->assertSee('Favorite Server');
    }

    public function test_guest_cannot_favorite(): void
    {
        $player = User::factory()->create();

        $response = $this->post('/favorites/toggle', [
            'type' => 'player',
            'id' => $player->id,
        ]);

        $response->assertRedirect('/login');
    }
}
