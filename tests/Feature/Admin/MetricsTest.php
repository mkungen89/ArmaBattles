<?php

namespace Tests\Feature\Admin;

use App\Models\AnalyticsEvent;
use App\Models\SystemMetric;
use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use Tests\TestCase;

class MetricsTest extends TestCase
{
    use RefreshDatabase;

    protected User $admin;

    protected function setUp(): void
    {
        parent::setUp();

        $this->admin = User::factory()->create(['role' => 'admin']);
    }

    public function test_metrics_dashboard_loads(): void
    {
        $response = $this->actingAs($this->admin)->get('/admin/metrics');

        $response->assertOk();
    }

    public function test_non_admin_cannot_access_metrics(): void
    {
        $user = User::factory()->create(['role' => 'user']);

        $response = $this->actingAs($user)->get('/admin/metrics');

        $response->assertStatus(403);
    }

    public function test_analytics_data_endpoint_returns_stats(): void
    {
        $this->markTestSkipped('Uses PostgreSQL-specific date_trunc() function, not compatible with SQLite');

        // Create some analytics events
        AnalyticsEvent::create([
            'event_type' => 'page_view',
            'event_name' => '/teams',
            'user_id' => $this->admin->id,
            'created_at' => now(),
        ]);

        AnalyticsEvent::create([
            'event_type' => 'page_view',
            'event_name' => '/tournaments',
            'user_id' => $this->admin->id,
            'created_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/metrics/api/analytics?range=24h');

        $response->assertOk();
        $response->assertJsonStructure([
            'page_views',
            'top_pages',
        ]);
    }

    public function test_api_usage_endpoint_returns_token_stats(): void
    {
        $this->markTestSkipped('Uses PostgreSQL-specific syntax, not compatible with SQLite');

        $response = $this->actingAs($this->admin)
            ->get('/admin/metrics/api/usage?range=24h');

        $response->assertOk();
        $response->assertJsonStructure([
            'requests_by_token',
            'top_endpoints',
        ]);
    }

    public function test_performance_endpoint_returns_system_metrics(): void
    {
        SystemMetric::create([
            'cache_hits' => 100,
            'cache_misses' => 10,
            'jobs_processed' => 50,
            'jobs_failed' => 2,
            'queue_size' => 5,
            'memory_usage_mb' => 512,
            'cpu_load_1m' => 0.75,
            'disk_usage_percent' => 45,
            'api_requests_count' => 1000,
            'api_p50_ms' => 120,
            'api_p95_ms' => 350,
            'api_p99_ms' => 500,
            'recorded_at' => now(),
        ]);

        $response = $this->actingAs($this->admin)
            ->get('/admin/metrics/api/performance?range=24h');

        $response->assertOk();
        $response->assertJsonStructure([
            'labels',
            'api_p50',
            'api_p95',
            'api_p99',
            'memory',
            'cpu',
            'cache_hit_rate',
            'queue_size',
            'jobs_processed',
            'jobs_failed',
        ]);
    }

    public function test_metrics_collection_command_stores_data(): void
    {
        $this->artisan('metrics:collect')
            ->assertSuccessful();

        // Just check that a record was created in the last minute
        $this->assertTrue(
            \DB::table('system_metrics')
                ->where('recorded_at', '>=', now()->subMinute())
                ->exists()
        );
    }

    public function test_old_analytics_cleaned_up(): void
    {
        // Create old event (91 days)
        AnalyticsEvent::create([
            'event_type' => 'page_view',
            'event_name' => '/old',
            'created_at' => now()->subDays(91),
        ]);

        // Create recent event
        AnalyticsEvent::create([
            'event_type' => 'page_view',
            'event_name' => '/new',
            'created_at' => now(),
        ]);

        $this->artisan('analytics:clean')
            ->assertSuccessful();

        $this->assertDatabaseMissing('analytics_events', [
            'event_name' => '/old',
        ]);

        $this->assertDatabaseHas('analytics_events', [
            'event_name' => '/new',
        ]);
    }
}
