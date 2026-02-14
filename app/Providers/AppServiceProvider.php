<?php

namespace App\Providers;

use Illuminate\Cache\RateLimiting\Limit;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\RateLimiter;
use Illuminate\Support\Facades\URL;
use Illuminate\Support\ServiceProvider;

class AppServiceProvider extends ServiceProvider
{
    /**
     * Register any application services.
     */
    public function register(): void
    {
        $this->app->singleton(\App\Services\GameServerManager::class);
        $this->app->singleton(\App\Services\DiscordWebhookService::class);
    }

    /**
     * Bootstrap any application services.
     */
    public function boot(): void
    {
        // Force HTTPS in production
        if ($this->app->environment('production')) {
            URL::forceScheme('https');
        }

        RateLimiter::for('api', function (Request $request) {
            return Limit::perMinute(100)->by($request->user()?->id ?: $request->ip());
        });

        RateLimiter::for('auth', function (Request $request) {
            return Limit::perMinute(10)->by($request->ip());
        });

        // Configure S3 disk for Backblaze B2 (disable ACL)
        \Illuminate\Support\Facades\Storage::extend('s3-b2', function ($app, $config) {
            $client = new \Aws\S3\S3Client([
                'credentials' => [
                    'key' => $config['key'],
                    'secret' => $config['secret'],
                ],
                'region' => $config['region'],
                'version' => 'latest',
                'endpoint' => $config['endpoint'],
                'use_path_style_endpoint' => $config['use_path_style_endpoint'] ?? false,
            ]);

            $adapter = new \League\Flysystem\AwsS3V3\AwsS3V3Adapter(
                $client,
                $config['bucket'],
                '',
                new \App\Support\NoAclVisibilityConverter
            );

            $filesystem = new \League\Flysystem\Filesystem($adapter, $config);

            $driver = new \App\Support\B2FilesystemAdapter(
                $filesystem,
                $adapter,
                $config
            );

            // Set base URL for public file access
            if (isset($config['url'])) {
                $driver->setBaseUrl($config['url']);
            }

            return $driver;
        });
    }
}
