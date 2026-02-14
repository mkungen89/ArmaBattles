<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class TestSentry extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'sentry:test';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Test Sentry error tracking by sending a test exception';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        if (! config('sentry.dsn')) {
            $this->error('Sentry DSN is not configured. Please set SENTRY_LARAVEL_DSN in your .env file.');

            return self::FAILURE;
        }

        $this->info('Sending test exception to Sentry...');

        try {
            throw new \Exception('This is a test exception from Sentry:test command');
        } catch (\Exception $e) {
            if (app()->bound('sentry')) {
                \Sentry\captureException($e);
                $this->info('✓ Test exception sent to Sentry successfully!');
                $this->info('Check your Sentry dashboard: https://sentry.io');

                return self::SUCCESS;
            }

            $this->error('Sentry is not properly initialized.');

            return self::FAILURE;
        }
    }
}
