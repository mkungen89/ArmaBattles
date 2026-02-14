<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Support\Facades\Route;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        channels: __DIR__.'/../routes/channels.php',
        health: '/up',
        then: function () {
            // API v1 routes
            Route::prefix('api/v1')
                ->middleware('api')
                ->group(base_path('routes/api_v1.php'));
        },
    )
    ->withBroadcasting(
        __DIR__.'/../routes/channels.php',
        ['middleware' => ['web', 'auth']],
    )
    ->withMiddleware(function (Middleware $middleware): void {
        $middleware->validateCsrfTokens(except: [
            'api/*',
            'logout',
        ]);

        $middleware->web(append: [
            \App\Http\Middleware\TrackLastSeen::class,
            \App\Http\Middleware\MaintenanceModeMiddleware::class,
            \App\Http\Middleware\TrackAnalytics::class,
        ]);

        // Remove default throttle:api since we use custom per-token rate limiting (api.rate)
        $middleware->api(remove: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
        ]);

        $middleware->api(append: [
            \App\Http\Middleware\TrackAnalytics::class,
        ]);

        $middleware->alias([
            'admin' => \App\Http\Middleware\AdminMiddleware::class,
            'gm' => \App\Http\Middleware\GMMiddleware::class,
            'referee' => \App\Http\Middleware\RefereeMiddleware::class,
            'api.token' => \App\Http\Middleware\ApiTokenAuth::class,
            'api.rate' => \App\Http\Middleware\ApiRateLimiter::class,
            'api.deprecation' => \App\Http\Middleware\ApiDeprecationWarning::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Configure Sentry error reporting with user context
        $exceptions->reportable(function (Throwable $e) {
            if (app()->bound('sentry')) {
                \Sentry\Laravel\Integration::captureUnhandledException($e);

                // Add user context
                if (auth()->check()) {
                    \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                        $scope->setUser([
                            'id' => auth()->id(),
                            'username' => auth()->user()->name,
                            'email' => auth()->user()->email,
                            'role' => auth()->user()->role,
                        ]);
                    });
                }

                // Add additional context and tags
                \Sentry\configureScope(function (\Sentry\State\Scope $scope): void {
                    // Request context
                    $scope->setContext('request', [
                        'url' => request()->fullUrl(),
                        'method' => request()->method(),
                        'ip' => request()->ip(),
                        'user_agent' => request()->userAgent(),
                    ]);

                    // Add useful tags for filtering
                    $scope->setTag('route', request()->route()?->getName() ?? 'unknown');
                    $scope->setTag('http_method', request()->method());

                    // Add environment info
                    if (app()->runningInConsole()) {
                        $scope->setTag('interface', 'cli');
                    } else {
                        $scope->setTag('interface', 'web');
                    }
                });
            }
        });
    })->create();
