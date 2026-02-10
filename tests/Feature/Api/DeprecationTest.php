<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Laravel\Sanctum\Sanctum;
use Tests\TestCase;

class DeprecationTest extends TestCase
{
    use RefreshDatabase;

    protected User $apiUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiUser = User::factory()->create(['role' => 'admin']);
    }

    public function test_legacy_api_returns_deprecation_headers(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/player-stats', [
            'server_id' => 1,
            'player_uuid' => 'uuid-1',
            'player_name' => 'Player1',
        ]);

        $response->assertStatus(200)
            ->assertHeader('X-API-Deprecated', 'true')
            ->assertHeader('X-API-Deprecation-Date', '2026-02-08')
            ->assertHeader('X-API-Sunset-Date', '2026-06-01')
            ->assertHeader('Deprecation', 'true')
            ->assertHeader('Sunset', 'Sat, 01 Jun 2026 00:00:00 GMT');
    }

    public function test_legacy_api_includes_link_to_v1(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/player-stats', [
            'server_id' => 1,
            'player_uuid' => 'uuid-1',
            'player_name' => 'Player1',
        ]);

        $response->assertStatus(200)
            ->assertHeader('Link');

        $linkHeader = $response->headers->get('Link');
        $this->assertStringContainsString('/api/v1/', $linkHeader);
        $this->assertStringContainsString('rel="alternate"', $linkHeader);
    }

    public function test_v1_api_does_not_have_deprecation_headers(): void
    {
        Sanctum::actingAs($this->apiUser);

        $response = $this->postJson('/api/v1/player-stats', [
            'server_id' => 1,
            'player_uuid' => 'uuid-1',
            'player_name' => 'Player1',
        ]);

        $response->assertStatus(200)
            ->assertHeaderMissing('X-API-Deprecated')
            ->assertHeaderMissing('Deprecation')
            ->assertHeaderMissing('Sunset');
    }

    public function test_all_legacy_write_endpoints_have_deprecation_headers(): void
    {
        Sanctum::actingAs($this->apiUser);

        $endpoints = [
            '/api/player-stats',
            '/api/player-kills',
            '/api/connections',
            '/api/xp-events',
            '/api/base-events',
            '/api/damage-events',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->postJson($endpoint, $this->getValidPayloadFor($endpoint));

            $response->assertHeader('X-API-Deprecated', 'true');
        }
    }

    public function test_all_legacy_read_endpoints_have_deprecation_headers(): void
    {
        Sanctum::actingAs($this->apiUser);

        $endpoints = [
            '/api/servers',
            '/api/players',
            '/api/leaderboards/kills',
            '/api/stats/overview',
        ];

        foreach ($endpoints as $endpoint) {
            $response = $this->getJson($endpoint);

            $response->assertHeader('X-API-Deprecated', 'true');
        }
    }

    protected function getValidPayloadFor(string $endpoint): array
    {
        $basePayload = [
            'server_id' => 1,
            'timestamp' => '2026-02-08T10:00:00.000Z',
        ];

        return match ($endpoint) {
            '/api/player-stats' => $basePayload + [
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
            ],
            '/api/player-kills' => $basePayload + [
                'killer_name' => 'Killer',
                'killer_uuid' => 'uuid-k',
                'victim_name' => 'Victim',
                'victim_uuid' => 'uuid-v',
                'weapon_name' => 'Gun',
            ],
            '/api/connections' => $basePayload + [
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
                'event_type' => 'CONNECT',
            ],
            '/api/xp-events' => $basePayload + [
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
                'xp_amount' => 100,
                'xp_type' => 'KILL',
            ],
            '/api/base-events' => $basePayload + [
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
                'event_type' => 'CAPTURED',
                'base_name' => 'Base 1',
                'faction' => 'US',
            ],
            '/api/damage-events' => [
                'events' => [
                    $basePayload + [
                        'killer_uuid' => 'uuid-k',
                        'killer_name' => 'Killer',
                        'victim_uuid' => 'uuid-v',
                        'victim_name' => 'Victim',
                        'hit_zone_name' => 'Head',
                        'damage_amount' => 100,
                        'is_friendly_fire' => false,
                    ],
                ],
            ],
            default => $basePayload,
        };
    }
}
