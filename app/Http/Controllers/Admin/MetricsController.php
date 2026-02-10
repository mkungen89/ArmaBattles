<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class MetricsController extends Controller
{
    public function index()
    {
        $now = now();

        // Summary stats (server-rendered)
        $pageViews24h = DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $now->copy()->subDay())
            ->count();

        $apiRequests24h = DB::table('analytics_events')
            ->where('event_type', 'api_request')
            ->where('created_at', '>=', $now->copy()->subDay())
            ->count();

        $uniqueVisitors24h = DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $now->copy()->subDay())
            ->distinct('ip_address')
            ->count('ip_address');

        $featureUses24h = DB::table('analytics_events')
            ->where('event_type', 'feature_use')
            ->where('created_at', '>=', $now->copy()->subDay())
            ->count();

        $tournamentRegistrations30d = 0;
        try {
            $tournamentRegistrations30d = DB::table('tournament_registrations')
                ->where('created_at', '>=', $now->copy()->subDays(30))
                ->count();
        } catch (\Throwable $e) {
            // table may not exist
        }

        $teamApplications30d = 0;
        try {
            $teamApplications30d = DB::table('team_applications')
                ->where('created_at', '>=', $now->copy()->subDays(30))
                ->count();
        } catch (\Throwable $e) {
            // table may not exist
        }

        return view('admin.metrics.index', compact(
            'pageViews24h',
            'apiRequests24h',
            'uniqueVisitors24h',
            'featureUses24h',
            'tournamentRegistrations30d',
            'teamApplications30d',
        ));
    }

    public function apiAnalyticsData(Request $request)
    {
        $hours = $this->parseRange($request);
        $since = now()->subHours($hours);

        // Page views over time (hourly buckets)
        $pageViewsOverTime = DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $since)
            ->selectRaw("date_trunc('hour', created_at) as hour, count(*) as count")
            ->groupByRaw("date_trunc('hour', created_at)")
            ->orderByRaw("date_trunc('hour', created_at)")
            ->get();

        // Top 15 pages
        $topPages = DB::table('analytics_events')
            ->where('event_type', 'page_view')
            ->where('created_at', '>=', $since)
            ->selectRaw('event_name, count(*) as views, count(distinct ip_address) as unique_visitors')
            ->groupBy('event_name')
            ->orderByDesc('views')
            ->limit(15)
            ->get();

        // Feature adoption
        $featureAdoption = DB::table('analytics_events')
            ->where('event_type', 'feature_use')
            ->where('created_at', '>=', $since)
            ->selectRaw('event_name, count(*) as uses, count(distinct user_id) as unique_users')
            ->groupBy('event_name')
            ->orderByDesc('uses')
            ->limit(15)
            ->get();

        return response()->json([
            'page_views_over_time' => [
                'labels' => $pageViewsOverTime->pluck('hour')->toArray(),
                'data' => $pageViewsOverTime->pluck('count')->toArray(),
            ],
            'top_pages' => $topPages,
            'feature_adoption' => $featureAdoption,
        ]);
    }

    public function apiUsageData(Request $request)
    {
        $hours = $this->parseRange($request);
        $since = now()->subHours($hours);

        // Requests per token (joined with personal_access_tokens)
        $perToken = DB::table('analytics_events')
            ->leftJoin('personal_access_tokens', 'analytics_events.token_id', '=', 'personal_access_tokens.id')
            ->where('analytics_events.event_type', 'api_request')
            ->where('analytics_events.created_at', '>=', $since)
            ->selectRaw("
                analytics_events.token_id,
                COALESCE(personal_access_tokens.name, 'Unknown') as token_name,
                count(*) as requests,
                round(avg(analytics_events.response_time_ms)::numeric, 0) as avg_ms
            ")
            ->groupBy('analytics_events.token_id', 'personal_access_tokens.name')
            ->orderByDesc('requests')
            ->limit(15)
            ->get();

        // Top 15 endpoints with avg/p95 response time
        $topEndpoints = DB::table('analytics_events')
            ->where('event_type', 'api_request')
            ->where('created_at', '>=', $since)
            ->whereNotNull('response_time_ms')
            ->selectRaw('
                event_name,
                count(*) as requests,
                round(avg(response_time_ms)::numeric, 0) as avg_ms,
                percentile_cont(0.95) WITHIN GROUP (ORDER BY response_time_ms) as p95_ms
            ')
            ->groupBy('event_name')
            ->orderByDesc('requests')
            ->limit(15)
            ->get();

        // API requests over time (hourly)
        $requestsOverTime = DB::table('analytics_events')
            ->where('event_type', 'api_request')
            ->where('created_at', '>=', $since)
            ->selectRaw("date_trunc('hour', created_at) as hour, count(*) as count")
            ->groupByRaw("date_trunc('hour', created_at)")
            ->orderByRaw("date_trunc('hour', created_at)")
            ->get();

        // Error rate
        $totalApiRequests = DB::table('analytics_events')
            ->where('event_type', 'api_request')
            ->where('created_at', '>=', $since)
            ->count();

        $errorRequests = DB::table('analytics_events')
            ->where('event_type', 'api_request')
            ->where('created_at', '>=', $since)
            ->where('response_status', '>=', 400)
            ->count();

        $errorRate = $totalApiRequests > 0 ? round(($errorRequests / $totalApiRequests) * 100, 1) : 0;

        return response()->json([
            'per_token' => $perToken,
            'top_endpoints' => $topEndpoints,
            'requests_over_time' => [
                'labels' => $requestsOverTime->pluck('hour')->toArray(),
                'data' => $requestsOverTime->pluck('count')->toArray(),
            ],
            'error_rate' => $errorRate,
            'total_requests' => $totalApiRequests,
            'error_requests' => $errorRequests,
        ]);
    }

    public function apiPerformanceData(Request $request)
    {
        $hours = $this->parseRange($request);
        $since = now()->subHours($hours);

        $data = DB::table('system_metrics')
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get();

        // Latest values for summary cards
        $latest = $data->last();

        // Cache hit rate over time
        $cacheHitRate = $data->map(function ($row) {
            $total = $row->cache_hits + $row->cache_misses;

            return $total > 0 ? round(($row->cache_hits / $total) * 100, 1) : null;
        })->toArray();

        return response()->json([
            'labels' => $data->pluck('recorded_at')->toArray(),
            'api_p50' => $data->pluck('api_p50_ms')->toArray(),
            'api_p95' => $data->pluck('api_p95_ms')->toArray(),
            'api_p99' => $data->pluck('api_p99_ms')->toArray(),
            'memory' => $data->pluck('memory_usage_mb')->toArray(),
            'cpu' => $data->pluck('cpu_load_1m')->toArray(),
            'cache_hit_rate' => $cacheHitRate,
            'queue_size' => $data->pluck('queue_size')->toArray(),
            'jobs_processed' => $data->pluck('jobs_processed')->toArray(),
            'jobs_failed' => $data->pluck('jobs_failed')->toArray(),
            'summary' => $latest ? [
                'api_p50' => $latest->api_p50_ms,
                'api_p95' => $latest->api_p95_ms,
                'cache_hit_rate' => ($latest->cache_hits + $latest->cache_misses) > 0
                    ? round(($latest->cache_hits / ($latest->cache_hits + $latest->cache_misses)) * 100, 1)
                    : null,
                'queue_size' => $latest->queue_size,
            ] : [
                'api_p50' => null,
                'api_p95' => null,
                'cache_hit_rate' => null,
                'queue_size' => null,
            ],
        ]);
    }

    private function parseRange(Request $request): int
    {
        return match ($request->input('range', '24h')) {
            '6h' => 6,
            '72h' => 72,
            '7d' => 168,
            default => 24,
        };
    }
}
