<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;

class SafeMigrateFresh extends Command
{
    protected $signature = 'migrate:fresh-safe {--seed : Seed the database after migrating} {--force : Force the operation without confirmation}';
    protected $description = 'Run migrate:fresh with safety checks and automatic backup';

    public function handle()
    {
        // Check if in production
        if (app()->environment('production') && !$this->option('force')) {
            $this->error('🚨 PRODUCTION ENVIRONMENT DETECTED! 🚨');
            $this->newLine();

            if (!$this->confirm('You are about to DROP ALL TABLES in PRODUCTION! Are you absolutely sure?', false)) {
                $this->info('Operation cancelled. Thank god! 😅');
                return 0;
            }

            if (!$this->confirm('Really? This will delete ALL data. Type YES to continue:', false)) {
                $this->info('Operation cancelled.');
                return 0;
            }

            $userCount = DB::table('users')->count();
            $this->warn("Current database has {$userCount} users and lots of data.");
            $this->newLine();

            if (!$this->confirm('Last chance! Create backup before proceeding?', true)) {
                $this->info('Operation cancelled.');
                return 0;
            }
        }

        // Create automatic backup
        $this->info('📦 Creating backup...');
        $backupFile = storage_path('backups/pre-migrate-' . date('Y-m-d_His') . '.sql.gz');

        // Create backups directory if it doesn't exist
        if (!is_dir(storage_path('backups'))) {
            mkdir(storage_path('backups'), 0755, true);
        }

        $dbName = config('database.connections.pgsql.database');
        $dbUser = config('database.connections.pgsql.username');
        $dbHost = config('database.connections.pgsql.host', '127.0.0.1');

        exec("PGPASSWORD='" . config('database.connections.pgsql.password') . "' pg_dump -U {$dbUser} -h {$dbHost} {$dbName} | gzip > {$backupFile}", $output, $returnCode);

        if ($returnCode === 0) {
            $this->info("✅ Backup created: {$backupFile}");
        } else {
            $this->error("❌ Backup failed! Aborting operation.");
            return 1;
        }

        // Run migrate:fresh
        $this->warn('🔥 Running migrate:fresh...');
        $exitCode = Artisan::call('migrate:fresh', [
            '--force' => true,
            '--seed' => $this->option('seed'),
        ]);

        $this->info(Artisan::output());

        if ($exitCode === 0) {
            $this->info('✅ Migration completed successfully!');
            $this->info("💾 Backup saved at: {$backupFile}");
        } else {
            $this->error('❌ Migration failed!');
            $this->warn("Restore from backup: {$backupFile}");
        }

        return $exitCode;
    }
}
