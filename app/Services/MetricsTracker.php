<?php

namespace App\Services;

use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Log;

class MetricsTracker
{
    public function trackPageView(string $eventName, ?int $userId, ?string $ip, ?string $userAgent): void
    {
        try {
            DB::table('analytics_events')->insert([
                'event_type' => 'page_view',
                'event_name' => $eventName,
                'user_id' => $userId,
                'ip_address' => $ip,
                'user_agent' => $userAgent ? substr($userAgent, 0, 500) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('MetricsTracker::trackPageView failed', ['error' => $e->getMessage()]);
        }
    }

    public function trackApiRequest(
        string $endpoint,
        ?int $userId,
        ?int $tokenId,
        ?int $responseTimeMs,
        ?int $status,
        ?string $ip,
    ): void {
        try {
            DB::table('analytics_events')->insert([
                'event_type' => 'api_request',
                'event_name' => $endpoint,
                'user_id' => $userId,
                'token_id' => $tokenId,
                'ip_address' => $ip,
                'response_time_ms' => $responseTimeMs,
                'response_status' => $status,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('MetricsTracker::trackApiRequest failed', ['error' => $e->getMessage()]);
        }
    }

    public function trackFeatureUse(string $featureName, ?int $userId, ?array $metadata = null): void
    {
        try {
            DB::table('analytics_events')->insert([
                'event_type' => 'feature_use',
                'event_name' => $featureName,
                'user_id' => $userId,
                'metadata' => $metadata ? json_encode($metadata) : null,
                'created_at' => now(),
            ]);
        } catch (\Throwable $e) {
            Log::debug('MetricsTracker::trackFeatureUse failed', ['error' => $e->getMessage()]);
        }
    }
}
