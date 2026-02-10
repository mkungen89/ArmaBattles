<?php

namespace App\Jobs;

use App\Models\AdminAuditLog;
use App\Models\ScheduledRestart;
use App\Services\GameServerManager;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ExecuteScheduledRestart implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public int $tries = 1;

    public int $timeout = 600;

    public function __construct(
        protected ScheduledRestart $restart,
    ) {}

    public function handle(): void
    {
        $server = $this->restart->server;
        $manager = (new GameServerManager)->forServer($server);

        $warningMinutes = $this->restart->warning_minutes;
        $warningMessage = $this->restart->warning_message ?? "Server restarting in {$warningMinutes} minute(s)!";

        try {
            // Send initial warning
            if ($warningMinutes > 1) {
                $manager->broadcast($warningMessage);
                sleep(($warningMinutes - 1) * 60);
            }

            // Send final warning
            if ($warningMinutes >= 1) {
                $manager->broadcast('Server restarting in 1 minute!');
                sleep(60);
            }

            // Execute restart
            $manager->restartArma();

            // Discord notification
            if (site_setting('discord_notify_server_restart') && site_setting('discord_webhook_url')) {
                SendDiscordNotification::dispatch(
                    'Server Restart',
                    "**{$server->name}** is restarting (scheduled).",
                    0x3B82F6,
                );
            }

            Log::info("Scheduled restart executed for server {$server->name}");
        } catch (\Exception $e) {
            Log::error("Scheduled restart failed for server {$server->name}: {$e->getMessage()}");
        }

        // Update execution timestamps and schedule next
        $this->restart->update(['last_executed_at' => now()]);
        $this->restart->calculateNextExecution();

        AdminAuditLog::create([
            'user_id' => null,
            'action' => 'server.scheduled-restart.executed',
            'target_type' => 'Server',
            'target_id' => $server->id,
            'metadata' => [
                'schedule_type' => $this->restart->schedule_type,
                'restart_time' => $this->restart->restart_time,
                'server_name' => $server->name,
            ],
            'ip_address' => '127.0.0.1',
        ]);
    }
}
