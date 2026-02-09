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
