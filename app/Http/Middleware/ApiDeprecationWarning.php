<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class ApiDeprecationWarning
{
    /**
     * Handle an incoming request.
     *
     * Add deprecation warning headers to legacy API endpoints.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $response = $next($request);

        // Add deprecation headers
        $response->headers->add([
            'X-API-Deprecated' => 'true',
            'X-API-Deprecation-Date' => '2026-02-08',
            'X-API-Sunset-Date' => '2026-06-01',
            'X-API-Deprecation-Info' => 'This endpoint is deprecated. Please migrate to /api/v1/. See documentation at https://armabattles.se/docs/api',
            'Deprecation' => 'true',
            'Sunset' => 'Sat, 01 Jun 2026 00:00:00 GMT',
        ]);

        // Add Link header to new version
        $currentPath = $request->path(); // e.g. 'api/player-stats'
        $newPath = preg_replace('#^api/#', 'api/v1/', $currentPath);
        $response->headers->add([
            'Link' => '<' . url($newPath) . '>; rel="alternate"; type="application/json"',
        ]);

        return $response;
    }
}
