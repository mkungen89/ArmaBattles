<?php

namespace Tests\Feature\Profile;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class ProfileTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_user_can_view_own_profile(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee($user->name);
    }

    public function test_user_can_view_public_profile(): void
    {
        $user = User::factory()->create([
            'profile_visibility' => 'public',
        ]);

        $response = $this->get("/players/{$user->id}");

        $response->assertOk();
        $response->assertSee($user->name);
    }

    public function test_private_profile_not_visible_to_guests(): void
    {
        $user = User::factory()->create([
            'profile_visibility' => 'private',
        ]);

        $response = $this->get("/players/{$user->id}");

        $response->assertForbidden();
    }

    public function test_private_profile_visible_to_owner(): void
    {
        $user = User::factory()->create([
            'profile_visibility' => 'private',
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
    }

    public function test_profile_displays_game_stats(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid-profile']);

        \DB::table('servers')->insert([
            'battlemetrics_id' => '12345',
            'name' => 'Test Server',
            'ip' => '127.0.0.1',
            'port' => 2001,
            'created_at' => now(),
            'updated_at' => now(),
        ]);

        \DB::table('player_stats')->insert([
            'player_uuid' => 'test-uuid-profile',
            'player_name' => $user->name,
            'server_id' => 1,
            'kills' => 500,
            'deaths' => 250,
            'headshots' => 125,
            'playtime_seconds' => 36000,
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('500'); // kills
        $response->assertSee('250'); // deaths
    }

    public function test_profile_shows_active_team(): void
    {
        $user = User::factory()->create();
        $team = \App\Models\Team::factory()->create([
            'name' => 'Elite Squad',
            'captain_id' => $user->id,
        ]);

        $team->members()->attach($user->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('Elite Squad');
    }

    public function test_profile_shows_achievements(): void
    {
        $user = User::factory()->create(['player_uuid' => 'test-uuid-ach']);

        $achievement = \App\Models\Achievement::create([
            'name' => 'First Kill',
            'slug' => 'first-kill',
            'description' => 'Got your first kill',
            'category' => 'combat',
            'icon' => 'target',
            'color' => '#ff0000',
            'points' => 10,
            'stat_field' => 'kills',
            'threshold' => 1,
        ]);

        \App\Models\AchievementProgress::create([
            'player_uuid' => 'test-uuid-ach',
            'achievement_id' => $achievement->id,
            'current_value' => 1,
            'target_value' => 1,
            'percentage' => 100.0,
        ]);

        // Mark as earned
        \DB::table('player_achievements')->insert([
            'player_uuid' => 'test-uuid-ach',
            'achievement_id' => $achievement->id,
            'earned_at' => now(),
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('First Kill');
    }

    public function test_profile_shows_social_links(): void
    {
        $user = User::factory()->create([
            'social_links' => [
                'twitch' => 'https://twitch.tv/testuser',
                'youtube' => 'https://youtube.com/@testuser',
            ],
        ]);

        $response = $this->actingAs($user)->get('/profile');

        $response->assertOk();
        $response->assertSee('twitch.tv/testuser');
        $response->assertSee('youtube.com/@testuser');
    }

    public function test_user_can_update_social_links(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile/social-links', [
            'twitch' => 'https://twitch.tv/newuser',
            'youtube' => 'https://youtube.com/@newuser',
            'twitter' => 'https://twitter.com/newuser',
        ]);

        $response->assertRedirect();
        $user->refresh();

        $this->assertEquals([
            'twitch' => 'https://twitch.tv/newuser',
            'youtube' => 'https://youtube.com/@newuser',
            'twitter' => 'https://twitter.com/newuser',
        ], $user->social_links);
    }

    public function test_user_can_toggle_profile_visibility(): void
    {
        $user = User::factory()->create(['profile_visibility' => 'public']);

        $response = $this->actingAs($user)->post('/profile/settings', [
            'profile_visibility' => 'private',
        ]);

        $response->assertRedirect();
        $user->refresh();
        $this->assertEquals('private', $user->profile_visibility);
    }
}
