<?php

namespace App\Http\Controllers;

use Illuminate\Support\Facades\DB;

class ActivityFeedController extends Controller
{
    public function recent()
    {
        $kills = DB::table('player_kills')
            ->select(
                DB::raw("'kill' as type"),
                'killer_name as actor',
                'victim_name as target',
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
                DB::raw('NULL as target'),
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
                DB::raw('NULL as target'),
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

        return response()->json($events);
    }
}
