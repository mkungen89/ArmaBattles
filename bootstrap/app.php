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
        ]);

        // Remove default throttle:api since we use custom per-token rate limiting (api.rate)
        $middleware->api(remove: [
            \Illuminate\Routing\Middleware\ThrottleRequests::class.':api',
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
        //
    })->create();
