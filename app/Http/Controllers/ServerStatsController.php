<?php

namespace App\Http\Controllers;

use App\Models\Server;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServerStatsController extends Controller
{
    public function show($serverId)
    {
        $server = Server::where('battlemetrics_id', $serverId)->first();

        return view('servers.stats', [
            'serverId' => $serverId,
            'server' => $server,
        ]);
    }

    public function apiData(Request $request, $serverId)
    {
        $range = $request->get('range', '24h');
        $since = match ($range) {
            '6h' => now()->subHours(6),
            '72h' => now()->subHours(72),
            default => now()->subHours(24),
        };

        $server = Server::where('battlemetrics_id', $serverId)->first();
        if (!$server) {
            return response()->json(['error' => 'Server not found'], 404);
        }

        $data = DB::table('server_status')
            ->where('server_id', $server->id)
            ->where('recorded_at', '>=', $since)
            ->orderBy('recorded_at')
            ->get(['players_online', 'max_players', 'fps', 'memory_mb', 'uptime_seconds', 'recorded_at']);

        $labels = $data->pluck('recorded_at')->map(fn($t) => \Carbon\Carbon::parse($t)->format('H:i'));
        $players = $data->pluck('players_online');
        $fps = $data->pluck('fps');

        $playerValues = $players->filter()->values();
        $fpsValues = $fps->filter()->values();

        return response()->json([
            'labels' => $labels,
            'players' => $players,
            'fps' => $fps,
            'summary' => [
                'avg_players' => $playerValues->count() > 0 ? round($playerValues->avg(), 1) : 0,
                'peak_players' => $playerValues->max() ?? 0,
                'avg_fps' => $fpsValues->count() > 0 ? round($fpsValues->avg(), 1) : 0,
                'min_fps' => $fpsValues->min() ?? 0,
                'data_points' => $data->count(),
            ],
        ]);
    }
}
