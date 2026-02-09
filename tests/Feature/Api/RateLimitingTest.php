<?php

namespace Tests\Feature\Api;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Support\Facades\Cache;
use Tests\TestCase;

class RateLimitingTest extends TestCase
{
    use RefreshDatabase;

    protected User $apiUser;

    protected function setUp(): void
    {
        parent::setUp();
        $this->apiUser = User::factory()->create(['role' => 'admin']);
        Cache::flush(); // Clear rate limiter cache between tests
    }

    public function test_standard_token_has_60_per_minute_limit(): void
    {
        // Create standard token
        $token = $this->apiUser->createToken('test-standard', ['*']);

        // Make requests up to limit
        for ($i = 0; $i < 60; $i++) {
            $response = $this->withToken($token->plainTextToken)
                ->postJson('/api/v1/player-stats', [
                    'server_id' => 1,
                    'player_uuid' => "uuid-{$i}",
                    'player_name' => "Player{$i}",
                ]);

            $response->assertStatus(200);
            $this->assertEquals('60', $response->headers->get('X-RateLimit-Limit'));
        }

        // 61st request should be rate limited
        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-61',
                'player_name' => 'Player61',
            ]);

        $response->assertStatus(429)
            ->assertJson(['error' => 'Too Many Requests'])
            ->assertHeader('Retry-After');
    }

    public function test_high_volume_token_has_180_per_minute_limit(): void
    {
        // Create high-volume token
        $token = $this->apiUser->createToken('test-high-volume', ['*', 'high-volume']);

        // Make a few requests to verify limit
        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('180', $response->headers->get('X-RateLimit-Limit'));
    }

    public function test_premium_token_has_300_per_minute_limit(): void
    {
        // Create premium token
        $token = $this->apiUser->createToken('test-premium', ['*', 'premium']);

        // Make a few requests to verify limit
        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
            ]);

        $response->assertStatus(200);
        $this->assertEquals('300', $response->headers->get('X-RateLimit-Limit'));
    }

    public function test_rate_limit_headers_are_present(): void
    {
        $token = $this->apiUser->createToken('test', ['*']);

        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
            ]);

        $response->assertStatus(200)
            ->assertHeader('X-RateLimit-Limit')
            ->assertHeader('X-RateLimit-Remaining')
            ->assertHeader('X-RateLimit-Reset');

        // First request should have 59 remaining (60 - 1)
        $this->assertEquals('59', $response->headers->get('X-RateLimit-Remaining'));
    }

    public function test_rate_limit_remaining_decrements(): void
    {
        $token = $this->apiUser->createToken('test', ['*']);

        // First request
        $response1 = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-1',
                'player_name' => 'Player1',
            ]);

        $remaining1 = (int) $response1->headers->get('X-RateLimit-Remaining');

        // Second request
        $response2 = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-2',
                'player_name' => 'Player2',
            ]);

        $remaining2 = (int) $response2->headers->get('X-RateLimit-Remaining');

        // Remaining should decrement
        $this->assertEquals($remaining1 - 1, $remaining2);
    }

    public function test_rate_limit_applies_per_token(): void
    {
        // Create two tokens
        $token1 = $this->apiUser->createToken('test-1', ['*']);
        $token2 = $this->apiUser->createToken('test-2', ['*']);

        // Make a few requests with token1
        for ($i = 0; $i < 5; $i++) {
            $this->withToken($token1->plainTextToken)
                ->postJson('/api/v1/player-stats', [
                    'server_id' => 1,
                    'player_uuid' => "uuid-{$i}",
                    'player_name' => "Player{$i}",
                ])
                ->assertStatus(200);
        }

        // Token1 should have used 5 attempts
        $response1 = $this->withToken($token1->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-check1',
                'player_name' => 'PlayerCheck1',
            ]);
        $remaining1 = (int) $response1->headers->get('X-RateLimit-Remaining');

        // Reset auth guards so Sanctum re-resolves the bearer token
        // (RequestGuard caches the resolved user between postJson() calls)
        $this->app['auth']->forgetGuards();

        // Token2 should have a full quota (independent counter)
        $response2 = $this->withToken($token2->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-check2',
                'player_name' => 'PlayerCheck2',
            ]);
        $remaining2 = (int) $response2->headers->get('X-RateLimit-Remaining');

        // Token2 should have more remaining than token1 (per-token isolation)
        $this->assertGreaterThan($remaining1, $remaining2);
        // Token1 has made 6 requests, token2 has made 1
        $this->assertEquals(54, $remaining1); // 60 - 6
        $this->assertEquals(59, $remaining2); // 60 - 1
    }

    public function test_rate_limit_429_includes_retry_after(): void
    {
        $token = $this->apiUser->createToken('test', ['*']);

        // Exhaust rate limit
        for ($i = 0; $i < 60; $i++) {
            $this->withToken($token->plainTextToken)
                ->postJson('/api/v1/player-stats', [
                    'server_id' => 1,
                    'player_uuid' => "uuid-{$i}",
                    'player_name' => "Player{$i}",
                ]);
        }

        // Next request should include Retry-After
        $response = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-overflow',
                'player_name' => 'PlayerOverflow',
            ]);

        $response->assertStatus(429)
            ->assertHeader('Retry-After')
            ->assertHeader('X-RateLimit-Reset')
            ->assertJsonStructure(['error', 'message', 'retry_after']);

        $retryAfter = (int) $response->headers->get('Retry-After');
        $this->assertGreaterThan(0, $retryAfter);
        $this->assertLessThanOrEqual(60, $retryAfter);
    }

    public function test_unauthenticated_requests_bypass_rate_limiter(): void
    {
        // Unauthenticated request should fail at auth, not rate limit
        $response = $this->postJson('/api/v1/player-stats', [
            'server_id' => 1,
            'player_uuid' => 'uuid-1',
            'player_name' => 'Player1',
        ]);

        $response->assertStatus(401)
            ->assertDontSeeText('rate limit');
    }

    public function test_rate_limit_applies_to_all_v1_endpoints(): void
    {
        $token = $this->apiUser->createToken('test', ['*']);

        // Make requests to different endpoints
        $endpoints = [
            ['/api/v1/player-stats', ['server_id' => 1, 'player_uuid' => 'uuid-1', 'player_name' => 'P1']],
            ['/api/v1/player-kills', ['server_id' => 1, 'killer_name' => 'K1', 'killer_uuid' => 'uuid-k1', 'victim_name' => 'V1', 'victim_uuid' => 'uuid-v1', 'weapon_name' => 'Gun', 'timestamp' => '2026-02-08T10:00:00.000Z']],
            ['/api/v1/connections', ['server_id' => 1, 'player_uuid' => 'uuid-2', 'player_name' => 'P2', 'event_type' => 'CONNECT', 'timestamp' => '2026-02-08T10:00:00.000Z']],
        ];

        foreach ($endpoints as [$url, $data]) {
            $response = $this->withToken($token->plainTextToken)
                ->postJson($url, $data);

            $response->assertStatus(200)
                ->assertHeader('X-RateLimit-Limit')
                ->assertHeader('X-RateLimit-Remaining');
        }

        // All requests should count against the same limit
        $lastResponse = $this->withToken($token->plainTextToken)
            ->postJson('/api/v1/player-stats', [
                'server_id' => 1,
                'player_uuid' => 'uuid-3',
                'player_name' => 'P3',
            ]);

        $remaining = (int) $lastResponse->headers->get('X-RateLimit-Remaining');
        $this->assertLessThan(60, $remaining);
    }
}
