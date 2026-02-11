<?php

namespace Tests\Feature\Discord;

use App\Models\DiscordRichPresence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscordTest extends TestCase
{
    use RefreshDatabase;

    public function test_discord_settings_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get('/discord/settings');

        $response->assertOk();
    }

    public function test_user_can_enable_rich_presence(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/discord/enable', [
            'activity_type' => 'playing',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('discord_rich_presences', [
            'user_id' => $user->id,
            'is_enabled' => true,
        ]);
    }

    public function test_user_can_disable_rich_presence(): void
    {
        $user = User::factory()->create();
        DiscordRichPresence::create([
            'user_id' => $user->id,
            'activity_type' => 'playing',
            'is_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post('/discord/disable');

        $response->assertRedirect();
        $this->assertDatabaseHas('discord_rich_presences', [
            'user_id' => $user->id,
            'is_enabled' => false,
        ]);
    }

    public function test_presence_updates_with_activity(): void
    {
        $user = User::factory()->create();
        DiscordRichPresence::create([
            'user_id' => $user->id,
            'activity_type' => 'browsing',
            'is_enabled' => true,
        ]);

        $response = $this->actingAs($user)->post('/discord/update-activity', [
            'activity_type' => 'watching_tournament',
            'details' => 'Championship Finals',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('discord_rich_presences', [
            'user_id' => $user->id,
            'activity_type' => 'watching_tournament',
        ]);
    }

    public function test_presence_api_endpoint_returns_rpc_payload(): void
    {
        $user = User::factory()->create();
        DiscordRichPresence::create([
            'user_id' => $user->id,
            'activity_type' => 'playing',
            'details' => 'Conflict on Everon',
            'is_enabled' => true,
        ]);

        $response = $this->actingAs($user)->get('/api/discord/presence');

        $response->assertOk();
        $response->assertJsonStructure([
            'state',
            'details',
            'large_image',
            'small_image',
        ]);
    }
}
