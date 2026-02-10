<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class MaintenanceModeMiddleware
{
    public function handle(Request $request, Closure $next): Response
    {
        if (! site_setting('maintenance_mode', false)) {
            return $next($request);
        }

        // Allow admins through
        if ($request->user() && $request->user()->role === 'admin') {
            return $next($request);
        }

        // Allow login and admin routes through
        if ($request->is('login', 'auth/*', 'admin/*', 'api/*', 'logout', 'two-factor-challenge')) {
            return $next($request);
        }

        $message = site_setting('maintenance_message', 'We are performing scheduled maintenance. Please check back soon.');

        return response()->view('maintenance', compact('message'), 503);
    }
}
