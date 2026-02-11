<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;

class CleanOldAnalytics extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'analytics:clean';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Clean up old analytics events older than the configured retention period';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $retentionDays = (int) site_setting('analytics_retention_days', 90);
        $cutoffDate = now()->subDays($retentionDays);

        $deleted = DB::table('analytics_events')
            ->where('created_at', '<', $cutoffDate)
            ->delete();

        $this->info("Deleted {$deleted} analytics events older than {$retentionDays} days.");

        return Command::SUCCESS;
    }
}
