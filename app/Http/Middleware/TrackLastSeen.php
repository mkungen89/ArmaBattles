<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Symfony\Component\HttpFoundation\Response;

class TrackLastSeen
{
    public function handle(Request $request, Closure $next): Response
    {
        $user = $request->user();

        if ($user) {
            $cacheKey = 'last_seen:'.$user->id;

            if (! Cache::has($cacheKey)) {
                $user->update(['last_seen_at' => now()]);
                Cache::put($cacheKey, true, 300); // 5 minutes
            }
        }

        return $next($request);
    }
}
