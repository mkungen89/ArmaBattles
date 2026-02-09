<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CollectSystemMetrics extends Command
{
    protected $signature = 'metrics:collect';

    protected $description = 'Collect system performance metrics and store in system_metrics table';

    public function handle(): int
    {
        $now = now();
        $fiveMinutesAgo = $now->copy()->subMinutes(5);

        // Queue metrics
        $queueSize = 0;
        $jobsFailed = 0;
        try {
            $queueSize = DB::table('jobs')->count();
            $jobsFailed = DB::table('failed_jobs')
                ->where('failed_at', '>=', $fiveMinutesAgo)
                ->count();
        } catch (\Throwable $e) {
            // jobs/failed_jobs tables may not exist
        }

        // System metrics
        $memoryUsageMb = round(memory_get_usage(true) / 1024 / 1024, 2);
        $cpuLoad = 0.0;
        if (function_exists('sys_getloadavg')) {
            $load = sys_getloadavg();
            $cpuLoad = $load[0] ?? 0.0;
        }

        $diskUsagePercent = 0.0;
        $diskTotal = @disk_total_space('/');
        $diskFree = @disk_free_space('/');
        if ($diskTotal && $diskTotal > 0) {
            $diskUsagePercent = round((($diskTotal - $diskFree) / $diskTotal) * 100, 2);
        }

        // API request metrics from analytics_events (last 5 minutes)
        $apiRequestsCount = 0;
        $apiP50 = 0;
        $apiP95 = 0;
        $apiP99 = 0;

        try {
            $apiRequestsCount = DB::table('analytics_events')
                ->where('event_type', 'api_request')
                ->where('created_at', '>=', $fiveMinutesAgo)
                ->count();

            if ($apiRequestsCount > 0) {
                // Use PostgreSQL percentile_cont for accurate percentiles
                $percentiles = DB::selectOne("
                    SELECT
                        percentile_cont(0.50) WITHIN GROUP (ORDER BY response_time_ms) AS p50,
                        percentile_cont(0.95) WITHIN GROUP (ORDER BY response_time_ms) AS p95,
                        percentile_cont(0.99) WITHIN GROUP (ORDER BY response_time_ms) AS p99
                    FROM analytics_events
                    WHERE event_type = 'api_request'
                      AND response_time_ms IS NOT NULL
                      AND created_at >= ?
                ", [$fiveMinutesAgo]);

                $apiP50 = (int) ($percentiles->p50 ?? 0);
                $apiP95 = (int) ($percentiles->p95 ?? 0);
                $apiP99 = (int) ($percentiles->p99 ?? 0);
            }
        } catch (\Throwable $e) {
            // analytics_events table may not exist yet or DB doesn't support percentile_cont
        }

        DB::table('system_metrics')->insert([
            'cache_hits' => 0,
            'cache_misses' => 0,
            'jobs_processed' => 0,
            'jobs_failed' => $jobsFailed,
            'queue_size' => $queueSize,
            'memory_usage_mb' => $memoryUsageMb,
            'cpu_load_1m' => $cpuLoad,
            'disk_usage_percent' => $diskUsagePercent,
            'api_requests_count' => $apiRequestsCount,
            'api_p50_ms' => $apiP50,
            'api_p95_ms' => $apiP95,
            'api_p99_ms' => $apiP99,
            'recorded_at' => $now,
        ]);

        $this->info("System metrics collected at {$now}");

        return Command::SUCCESS;
    }
}
