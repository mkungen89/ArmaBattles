<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\DB;

class ActivityFeedController extends Controller
{
    public function recent()
    {
        $kills = DB::table('player_kills')
            ->select(
                DB::raw("'kill' as type"),
                'killer_name as actor',
                'killer_uuid as actor_uuid',
                'victim_name as target',
                'victim_uuid as target_uuid',
                'weapon_name as detail',
                'is_headshot',
                'victim_type',
                'killed_at as occurred_at'
            )
            ->whereNotNull('killed_at')
            ->orderByDesc('killed_at')
            ->limit(20);

        $bases = DB::table('base_events')
            ->select(
                DB::raw("'base_capture' as type"),
                'player_name as actor',
                'player_uuid as actor_uuid',
                DB::raw('NULL as target'),
                DB::raw('NULL as target_uuid'),
                'base_name as detail',
                DB::raw('false as is_headshot'),
                DB::raw("'Player' as victim_type"),
                'created_at as occurred_at'
            )
            ->whereIn('event_type', ['CAPTURED', 'CAPTURE', 'BASE_SEIZED', 'BASE_CAPTURE'])
            ->orderByDesc('created_at')
            ->limit(10);

        $connections = DB::table('connections')
            ->select(
                DB::raw("'connection' as type"),
                'player_name as actor',
                'player_uuid as actor_uuid',
                DB::raw('NULL as target'),
                DB::raw('NULL as target_uuid'),
                'event_type as detail',
                DB::raw('false as is_headshot'),
                DB::raw("'Player' as victim_type"),
                'created_at as occurred_at'
            )
            ->where('event_type', 'connect')
            ->orderByDesc('created_at')
            ->limit(10);

        $events = DB::query()
            ->fromSub(
                $kills->unionAll($bases)->unionAll($connections),
                'events'
            )
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();

        // Collect all unique UUIDs from events
        $uuids = $events->pluck('actor_uuid')
            ->merge($events->pluck('target_uuid'))
            ->filter()
            ->unique()
            ->values();

        // Look up registered users by player_uuid (cached 5 min)
        $userMap = $this->resolveUserProfiles($uuids);

        // Attach profile URLs to events
        $events->transform(function ($event) use ($userMap) {
            $event->actor_profile_url = $userMap[$event->actor_uuid] ?? null;
            $event->target_profile_url = $userMap[$event->target_uuid] ?? null;
            unset($event->actor_uuid, $event->target_uuid);

            return $event;
        });

        return response()->json($events);
    }

    /**
     * Resolve player UUIDs to profile URLs for registered users.
     * Cached for 5 minutes to avoid repeated lookups.
     */
    private function resolveUserProfiles($uuids): array
    {
        if ($uuids->isEmpty()) {
            return [];
        }

        $cacheKey = 'activity_feed_user_map';

        $userMap = Cache::remember($cacheKey, 300, function () {
            return User::whereNotNull('player_uuid')
                ->where('player_uuid', '!=', '')
                ->pluck('id', 'player_uuid')
                ->toArray();
        });

        $result = [];
        foreach ($uuids as $uuid) {
            if (isset($userMap[$uuid])) {
                $result[$uuid] = route('players.show', $userMap[$uuid]);
            }
        }

        return $result;
    }
}
