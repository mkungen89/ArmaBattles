<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class AnticheatAdminController extends Controller
{
    public function index(Request $request)
    {
        // Latest stats snapshot per server
        $latestStats = DB::table('anticheat_stats')
            ->whereIn('id', function ($query) {
                $query->selectRaw('MAX(id)')
                    ->from('anticheat_stats')
                    ->groupBy('server_id');
            })
            ->get();

        // Summary counts
        $stats = [
            'total_events' => DB::table('anticheat_events')->count(),
            'enforcement_actions' => DB::table('anticheat_events')->where('event_type', 'ENFORCEMENT_ACTION')->count(),
            'enforcement_skipped' => DB::table('anticheat_events')->where('event_type', 'ENFORCEMENT_SKIPPED')->count(),
            'events_today' => DB::table('anticheat_events')->whereDate('event_time', today())->count(),
            'total_stats_snapshots' => DB::table('anticheat_stats')->count(),
        ];

        // Recent events
        $recentEvents = DB::table('anticheat_events')
            ->orderByDesc('event_time')
            ->limit(10)
            ->get();

        return view('admin.anticheat.index', compact('stats', 'latestStats', 'recentEvents'));
    }

    public function events(Request $request)
    {
        $query = DB::table('anticheat_events');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('player_name', 'like', "%{$search}%")
                    ->orWhere('reason', 'like', "%{$search}%")
                    ->orWhere('raw', 'like', "%{$search}%");
            });
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_time', '<=', $request->date_to);
        }

        $events = $query->orderByDesc('event_time')->paginate(50);

        $serverIds = DB::table('anticheat_events')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.anticheat.events', compact('events', 'serverIds'));
    }

    public function statsHistory(Request $request)
    {
        $query = DB::table('anticheat_stats');

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('event_time', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('event_time', '<=', $request->date_to);
        }

        $statsHistory = $query->orderByDesc('event_time')->paginate(50);

        $serverIds = DB::table('anticheat_stats')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.anticheat.stats-history', compact('statsHistory', 'serverIds'));
    }
}
