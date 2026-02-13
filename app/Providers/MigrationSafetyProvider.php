<?php

namespace App\Providers;

use Illuminate\Support\ServiceProvider;
use Illuminate\Console\Events\CommandStarting;
use Illuminate\Support\Facades\Event;

class MigrationSafetyProvider extends ServiceProvider
{
    /**
     * Register services.
     */
    public function register(): void
    {
        //
    }

    /**
     * Bootstrap services.
     */
    public function boot(): void
    {
        // Only run safety checks in console environment
        if (!$this->app->runningInConsole()) {
            return;
        }

        Event::listen(CommandStarting::class, function ($event) {
            $dangerousCommands = [
                'migrate:fresh',
                'migrate:refresh',
                'db:wipe',
            ];

            if (in_array($event->command, $dangerousCommands)) {
                // Check if in production
                if (app()->environment('production')) {
                    // Check if explicitly allowed
                    if (!env('ALLOW_MIGRATE_FRESH', false) && !in_array('--force', $event->input->getParameterOptions())) {
                        echo "\n";
                        echo "🚨 ═══════════════════════════════════════════════════════════ 🚨\n";
                        echo "   BLOCKED: Dangerous migration command in PRODUCTION!\n";
                        echo "🚨 ═══════════════════════════════════════════════════════════ 🚨\n";
                        echo "\n";
                        echo "  Command blocked: {$event->command}\n";
                        echo "\n";
                        echo "  This command will DELETE ALL DATABASE DATA!\n";
                        echo "\n";
                        echo "  Safe alternatives:\n";
                        echo "  👉 php artisan migrate:fresh-safe (creates backup first)\n";
                        echo "  👉 php artisan migrate (run new migrations only)\n";
                        echo "\n";
                        echo "  To force this command (NOT RECOMMENDED):\n";
                        echo "  1. Set ALLOW_MIGRATE_FRESH=true in .env\n";
                        echo "  2. Or use --force flag\n";
                        echo "\n";
                        echo "🚨 ═══════════════════════════════════════════════════════════ 🚨\n";
                        echo "\n";

                        exit(1);
                    }
                }

                // Log the dangerous command attempt
                \Log::warning('Dangerous migration command attempted', [
                    'command' => $event->command,
                    'environment' => app()->environment(),
                    'user' => get_current_user(),
                    'time' => now(),
                ]);
            }
        });
    }
}
