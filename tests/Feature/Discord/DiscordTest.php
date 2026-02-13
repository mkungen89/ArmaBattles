<?php

namespace Tests\Feature\Discord;

use App\Models\DiscordRichPresence;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class DiscordTest extends TestCase
{
    use RefreshDatabase;

    protected function setUp(): void
    {
        parent::setUp();
    }

    public function test_discord_settings_page_loads(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->get(route('discord.settings'));

        $response->assertOk();
    }

    public function test_user_can_enable_rich_presence(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post(route('discord.enable'), [
            'current_activity' => 'playing',
        ]);

        $response->assertRedirect();
        $this->assertDatabaseHas('discord_rich_presence', [
            'user_id' => $user->id,
            'enabled' => true,
        ]);
    }

    public function test_user_can_disable_rich_presence(): void
    {
        $user = User::factory()->create();
        DiscordRichPresence::create([
            'user_id' => $user->id,
            'current_activity' => 'playing',
            'enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('discord.disable'));

        $response->assertRedirect();
        $this->assertDatabaseHas('discord_rich_presence', [
            'user_id' => $user->id,
            'enabled' => false,
        ]);
    }

    public function test_presence_updates_with_activity(): void
    {
        $user = User::factory()->create();
        DiscordRichPresence::create([
            'user_id' => $user->id,
            'current_activity' => 'browsing',
            'enabled' => true,
        ]);

        $response = $this->actingAs($user)->post(route('discord.update-activity'), [
            'activity_type' => 'browsing',
        ]);

        $response->assertOk();
        $this->assertDatabaseHas('discord_rich_presence', [
            'user_id' => $user->id,
            'current_activity' => 'browsing',
        ]);
    }

    public function test_presence_api_endpoint_returns_rpc_payload(): void
    {
        $user = User::factory()->create();
        DiscordRichPresence::create([
            'user_id' => $user->id,
            'current_activity' => 'playing',
            'activity_details' => ['server_name' => 'Conflict on Everon'],
            'enabled' => true,
        ]);

        $response = $this->actingAs($user)->get(route('api.discord.presence'));

        $response->assertOk();
        $response->assertJsonStructure([
            'enabled',
            'presence',
            'current_activity',
            'started_at',
            'elapsed_time',
        ]);
    }
}
