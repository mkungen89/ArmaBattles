<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Cache\RateLimiter;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\Response as SymfonyResponse;

class ApiRateLimiter
{
    /**
     * The rate limiter instance.
     */
    protected RateLimiter $limiter;

    /**
     * Create a new middleware instance.
     */
    public function __construct(RateLimiter $limiter)
    {
        $this->limiter = $limiter;
    }

    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): SymfonyResponse
    {
        // Get the authenticated user's token
        $token = $request->user()?->currentAccessToken();

        if (! $token) {
            // No token, no rate limit (will be blocked by auth:sanctum anyway)
            return $next($request);
        }

        // Determine rate limit based on token abilities
        $limit = $this->getRateLimit($token);
        $decayMinutes = 1; // Per minute

        // Create a unique key for this token
        $key = $this->resolveRequestSignature($request, $token);

        // Check if rate limit is exceeded
        if ($this->limiter->tooManyAttempts($key, $limit)) {
            return $this->buildRateLimitResponse($key, $limit, $decayMinutes);
        }

        // Increment the attempt counter
        $this->limiter->hit($key, $decayMinutes * 60);

        // Process the request
        $response = $next($request);

        // Add rate limit headers to response
        return $this->addHeaders(
            $response,
            $limit,
            $this->calculateRemainingAttempts($key, $limit)
        );
    }

    /**
     * Determine the rate limit for the token based on its abilities.
     */
    protected function getRateLimit($token): int
    {
        // Check abilities array directly (not using can() to avoid wildcard matching)
        $abilities = $token->abilities ?? [];

        // Check if token has 'premium' ability explicitly
        if (in_array('premium', $abilities)) {
            return 300; // 300 requests per minute for premium tokens
        }

        // Check if token has 'high-volume' ability explicitly
        if (in_array('high-volume', $abilities)) {
            return 180; // 180 requests per minute for high-volume tokens
        }

        // Default: standard rate limit (only has '*' ability)
        return 60; // 60 requests per minute for standard tokens
    }

    /**
     * Resolve the request signature for rate limiting.
     */
    protected function resolveRequestSignature(Request $request, $token): string
    {
        // Use token ID as the key for rate limiting
        return 'api_rate_limit:'.$token->id;
    }

    /**
     * Calculate the number of remaining attempts.
     */
    protected function calculateRemainingAttempts(string $key, int $limit): int
    {
        return $this->limiter->remaining($key, $limit);
    }

    /**
     * Build the rate limit exceeded response.
     */
    protected function buildRateLimitResponse(string $key, int $limit, int $decayMinutes): SymfonyResponse
    {
        $retryAfter = $this->limiter->availableIn($key);

        $response = response()->json([
            'error' => 'Too Many Requests',
            'message' => 'Rate limit exceeded. Please try again later.',
            'retry_after' => $retryAfter,
        ], 429);

        return $this->addHeaders(
            $response,
            $limit,
            0,
            $retryAfter
        );
    }

    /**
     * Add rate limit headers to the response.
     */
    protected function addHeaders(
        $response,
        int $limit,
        int $remaining,
        ?int $retryAfter = null
    ): SymfonyResponse {
        $response->headers->add([
            'X-RateLimit-Limit' => $limit,
            'X-RateLimit-Remaining' => max(0, $remaining),
        ]);

        if ($retryAfter !== null) {
            $response->headers->add([
                'Retry-After' => $retryAfter,
                'X-RateLimit-Reset' => now()->addSeconds($retryAfter)->timestamp,
            ]);
        } else {
            $response->headers->add([
                'X-RateLimit-Reset' => now()->addMinute()->timestamp,
            ]);
        }

        return $response;
    }
}
