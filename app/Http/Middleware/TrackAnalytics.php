<?php

namespace App\Http\Middleware;

use App\Services\MetricsTracker;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class TrackAnalytics
{
    public function handle(Request $request, Closure $next): Response
    {
        $request->attributes->set('_analytics_start', microtime(true));

        return $next($request);
    }

    public function terminate(Request $request, Response $response): void
    {
        $start = $request->attributes->get('_analytics_start');
        if (! $start) {
            return;
        }

        $responseTimeMs = (int) round((microtime(true) - $start) * 1000);
        $path = '/'.ltrim($request->path(), '/');

        // Determine if this is an API request
        $isApi = str_starts_with($path, '/api/');

        if ($isApi) {
            $this->trackApiRequest($request, $response, $path, $responseTimeMs);
        } else {
            $this->trackPageView($request, $path);
        }
    }

    private function trackPageView(Request $request, string $path): void
    {
        // Only track GET requests for page views
        if (! $request->isMethod('GET')) {
            return;
        }

        // Skip admin AJAX polling endpoints
        if (str_starts_with($path, '/admin/server/api/')) {
            return;
        }

        // Skip AJAX/JSON requests (not real page views)
        if ($request->ajax() || $request->wantsJson()) {
            return;
        }

        $tracker = app(MetricsTracker::class);
        $tracker->trackPageView(
            $path,
            $request->user()?->id,
            $request->ip(),
            $request->userAgent(),
        );
    }

    private function trackApiRequest(Request $request, Response $response, string $path, int $responseTimeMs): void
    {
        $userId = $request->user()?->id;
        $tokenId = null;

        // Get token ID from Sanctum if available
        $user = $request->user();
        if ($user && method_exists($user, 'currentAccessToken') && $user->currentAccessToken()) {
            $token = $user->currentAccessToken();
            $tokenId = $token->id ?? null;
        }

        $tracker = app(MetricsTracker::class);
        $tracker->trackApiRequest(
            $path,
            $userId,
            $tokenId,
            $responseTimeMs,
            $response->getStatusCode(),
            $request->ip(),
        );
    }
}
