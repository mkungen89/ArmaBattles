<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Schedule;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');

// Track server status every 5 minutes
Schedule::command('server:track')->everyFiveMinutes()->withoutOverlapping();

// Clean up expired team invitations daily
Schedule::call(function () {
    \App\Models\TeamInvitation::where('status', 'pending')
        ->where('expires_at', '<', now())
        ->update(['status' => 'expired']);
})->daily()->name('cleanup:expired-invitations');

// Clean up orphaned favorites (pointing to deleted models)
Schedule::command('favorites:cleanup')
    ->daily()
    ->name('cleanup:orphaned-favorites');

// Clean up old read notifications (older than 90 days)
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('notifications')
        ->whereNotNull('read_at')
        ->where('created_at', '<', now()->subDays(site_setting('notification_retention_days', 90)))
        ->delete();
})->weekly()->name('cleanup:old-notifications');

// Clean up old audit logs (older than 1 year)
Schedule::call(function () {
    \App\Models\AdminAuditLog::where('created_at', '<', now()->subDays(site_setting('audit_log_retention_days', 365)))->delete();
})->monthly()->name('cleanup:old-audit-logs');

// Check and award player achievements hourly
Schedule::command('achievements:check')->hourly();

// Process expired temporary bans every hour
Schedule::command('bans:process-expired')
    ->hourly()
    ->withoutOverlapping()
    ->name('process:expired-bans');

// Process scheduled server restarts
Schedule::call(function () {
    $due = \App\Models\ScheduledRestart::where('is_enabled', true)
        ->where('next_execution_at', '<=', now())
        ->with('server')
        ->get();

    foreach ($due as $restart) {
        \App\Jobs\ExecuteScheduledRestart::dispatch($restart);
    }
})->everyMinute()->name('process:scheduled-restarts');

// Warm leaderboard caches every 4 minutes (slightly before 5min TTL expires)
Schedule::command('leaderboards:warm-cache')
    ->everyFourMinutes()
    ->withoutOverlapping()
    ->name('warm:leaderboard-caches');

// Collect system metrics every 5 minutes
Schedule::command('metrics:collect')
    ->everyFiveMinutes()
    ->withoutOverlapping()
    ->name('collect:system-metrics');

// Clean up old analytics events
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('analytics_events')
        ->where('created_at', '<', now()->subDays(site_setting('analytics_retention_days', 90)))
        ->delete();
})->daily()->name('cleanup:old-analytics-events');

// Clean up old system metrics
Schedule::call(function () {
    \Illuminate\Support\Facades\DB::table('system_metrics')
        ->where('recorded_at', '<', now()->subDays(site_setting('metrics_retention_days', 90)))
        ->delete();
})->daily()->name('cleanup:old-system-metrics');

// Calculate Glicko-2 ratings every 4 hours
Schedule::command('ratings:calculate')
    ->cron('0 */4 * * *')
    ->withoutOverlapping()
    ->name('calculate:ratings');

// Apply rating decay for inactive competitive players daily
Schedule::command('ratings:decay')
    ->daily()
    ->withoutOverlapping()
    ->name('decay:ratings');

// Send match reminders (24h and 1h before matches)
Schedule::command('matches:send-reminders')
    ->everyTenMinutes()
    ->withoutOverlapping()
    ->name('send:match-reminders');

// Check content creators live status every 3 minutes
Schedule::command('creators:check-live')
    ->everyThreeMinutes()
    ->withoutOverlapping()
    ->name('check:creators-live-status');
