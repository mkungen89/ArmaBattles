<?php

namespace App\Http\Controllers;

use App\Models\Mod;
use App\Models\Server;
use App\Models\Weapon;
use App\Services\BattleMetricsService;
use App\Services\ReforgerWorkshopService;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServerDetailController extends Controller
{
    public function __construct(
        protected BattleMetricsService $battleMetrics,
        protected ReforgerWorkshopService $workshop
    ) {}

    /**
     * Display the server detail page
     */
    public function show(string $serverId)
    {
        $bmServer = $this->battleMetrics->getServerWithDetails($serverId);

        if (! $bmServer) {
            abort(404, 'Server not found');
        }

        $server = Server::syncFromBattleMetrics($bmServer);

        $mods = $this->battleMetrics->getServerMods($serverId);

        foreach ($mods as $modData) {
            $mod = Mod::syncFromBattleMetrics($modData);

            if (! $server->mods()->where('mod_id', $mod->id)->exists()) {
                $server->mods()->attach($mod->id, [
                    'load_order' => $modData['load_order'] ?? 0,
                    'is_required' => true,
                ]);
            }

            // Sync additional data from Reforger Workshop if missing
            if ($mod->workshop_id && (! $mod->author || ! $mod->version)) {
                $this->workshop->syncMod($mod->workshop_id);
            }
        }

        // Get real sessions from database (if any), fallback to BattleMetrics estimation
        $dbSessions = $server->sessions()->orderByDesc('started_at')->limit(5)->get();

        if ($dbSessions->isNotEmpty()) {
            $sessions = $dbSessions->map(function ($session) {
                return [
                    'session_number' => $session->session_number,
                    'started_at' => $session->started_at->toIso8601String(),
                    'last_seen_at' => $session->last_seen_at?->toIso8601String(),
                    'ended_at' => $session->ended_at?->toIso8601String(),
                    'peak_players' => $session->peak_players,
                    'average_players' => $session->average_players,
                    'snapshots' => $session->total_snapshots,
                    'is_current' => $session->is_current,
                ];
            })->toArray();
        } else {
            // Fallback to BattleMetrics estimation
            $sessions = $this->battleMetrics->getServerSessions($serverId, 5);
        }

        $history24h = $this->battleMetrics->getServerHistory(
            $serverId,
            now()->subHours(24)->toIso8601String(),
            now()->toIso8601String(),
            60
        );
        $stats24h = $this->battleMetrics->calculateStats($history24h);

        $server->load('mods');

        $sortedMods = $server->mods->sortBy('pivot.load_order');

        $modsJsonData = $sortedMods->map(function ($mod) {
            return [
                'id' => $mod->workshop_id,
                'name' => $mod->name,
                'version' => $mod->version,
                'author' => $mod->author,
                'workshop_url' => $mod->workshop_link,
            ];
        })->values()->toArray();

        $recentKills = DB::table('player_kills')
            ->orderByDesc('killed_at')
            ->limit(30)
            ->get();

        $weaponImages = Weapon::whereNotNull('image_path')
            ->pluck('image_path', 'name')
            ->toArray();

        return view('servers.show', [
            'server' => $server,
            'battlemetricsData' => $bmServer,
            'mods' => $sortedMods,
            'modsJsonData' => $modsJsonData,
            'sessions' => $sessions,
            'stats' => $stats24h,
            'history' => $history24h,
            'recentKills' => $recentKills,
            'weaponImages' => $weaponImages,
        ]);
    }

    /**
     * Get player history data for the chart (AJAX)
     */
    public function history(string $serverId, Request $request): JsonResponse
    {
        $range = $request->get('range', '24h');

        $hours = match ($range) {
            '6h' => 6,
            '72h' => 72,
            default => 24,
        };

        $resolution = match ($range) {
            '6h' => 30,
            '72h' => 120,
            default => 60,
        };

        $history = $this->battleMetrics->getServerHistory(
            $serverId,
            now()->subHours($hours)->toIso8601String(),
            now()->toIso8601String(),
            $resolution
        );

        $stats = $this->battleMetrics->calculateStats($history);

        $chartData = collect($history)->map(function ($point) {
            return [
                'timestamp' => $point['attributes']['timestamp'] ?? $point['timestamp'],
                'players' => $point['attributes']['value'] ?? $point['value'] ?? 0,
            ];
        })->toArray();

        return response()->json([
            'data' => $chartData,
            'stats' => $stats,
        ]);
    }

    /**
     * Get sessions for the server (AJAX)
     */
    public function sessions(string $serverId): JsonResponse
    {
        $server = Server::where('battlemetrics_id', $serverId)->first();

        if ($server) {
            $dbSessions = $server->sessions()->orderByDesc('started_at')->limit(10)->get();

            if ($dbSessions->isNotEmpty()) {
                $sessions = $dbSessions->map(function ($session) {
                    return [
                        'session_number' => $session->session_number,
                        'started_at' => $session->started_at->toIso8601String(),
                        'last_seen_at' => $session->last_seen_at?->toIso8601String(),
                        'ended_at' => $session->ended_at?->toIso8601String(),
                        'peak_players' => $session->peak_players,
                        'average_players' => $session->average_players,
                        'snapshots' => $session->total_snapshots,
                        'is_current' => $session->is_current,
                    ];
                })->toArray();

                return response()->json($sessions);
            }
        }

        // Fallback to BattleMetrics estimation
        $sessions = $this->battleMetrics->getServerSessions($serverId, 10);

        return response()->json($sessions);
    }

    /**
     * Get mods list as JSON
     */
    public function modsJson(string $serverId): JsonResponse
    {
        $mods = $this->battleMetrics->getServerMods($serverId);

        return response()->json([
            'server_id' => $serverId,
            'mod_count' => count($mods),
            'mods' => $mods,
        ]);
    }

    /**
     * Download mods list file
     */
    public function downloadMods(string $serverId)
    {
        $mods = $this->battleMetrics->getServerMods($serverId);
        $server = $this->battleMetrics->getServer($serverId);
        $serverName = $server['attributes']['name'] ?? 'server';

        $content = "# Mods for: {$serverName}\n";
        $content .= '# Generated: '.now()->toDateTimeString()."\n";
        $content .= '# Total mods: '.count($mods)."\n\n";

        foreach ($mods as $mod) {
            $content .= $mod['name'];
            if ($mod['version']) {
                $content .= " (v{$mod['version']})";
            }
            if ($mod['workshop_url']) {
                $content .= "\n  Workshop: {$mod['workshop_url']}";
            }
            $content .= "\n\n";
        }

        $filename = preg_replace('/[^a-z0-9]+/i', '_', $serverName).'_mods.txt';

        return response($content)
            ->header('Content-Type', 'text/plain')
            ->header('Content-Disposition', "attachment; filename=\"{$filename}\"");
    }

    /**
     * Get current server status (AJAX)
     */
    public function status(string $serverId): JsonResponse
    {
        // Use local database data (updated by server:track command via A2S query)
        // This is more accurate and real-time than BattleMetrics API
        $server = Server::where('battlemetrics_id', $serverId)->first();

        if (! $server) {
            return response()->json(['error' => 'Server not found'], 404);
        }

        return response()->json([
            'name' => $server->name,
            'players' => $server->players ?? 0,
            'maxPlayers' => $server->max_players ?? 128,
            'status' => $server->status ?? 'offline',
            'map' => $server->map,
            'ip' => $server->ip,
            'port' => $server->port,
            'updated_at' => $server->last_updated_at?->toIso8601String() ?? now()->toIso8601String(),
        ]);
    }

    /**
     * Display the kill heatmap page
     */
    public function heatmap(string $serverId)
    {
        $bmServer = $this->battleMetrics->getServer($serverId);

        if (! $bmServer) {
            abort(404, 'Server not found');
        }

        $server = Server::syncFromBattleMetrics($bmServer);

        $killCount = DB::table('player_kills')
            ->where('server_id', $server->id)
            ->whereNotNull('killer_position')
            ->count();

        $user = auth()->user();
        $canViewPlayers = $user && ($user->isAdmin() || $user->role === 'gm' || $user->role === 'moderator');

        return view('servers.heatmap', [
            'server' => $server,
            'serverId' => $serverId,
            'killCount' => $killCount,
            'canViewPlayers' => $canViewPlayers,
        ]);
    }

    /**
     * API: Get online players' last known positions (GM/Admin only)
     */
    public function heatmapPlayers(string $serverId): JsonResponse
    {
        $user = auth()->user();
        if (! $user || ! ($user->isAdmin() || $user->role === 'gm' || $user->role === 'moderator')) {
            return response()->json(['error' => 'Forbidden'], 403);
        }

        $server = Server::where('battlemetrics_id', $serverId)->first();
        if (! $server) {
            return response()->json(['error' => 'Server not found'], 404);
        }

        // Get currently online players (latest event is CONNECT)
        $onlinePlayers = DB::table('connections')
            ->select('player_uuid', 'player_name', 'occurred_at')
            ->whereIn('id', function ($q) use ($server) {
                $q->select(DB::raw('MAX(id)'))
                    ->from('connections')
                    ->where('server_id', $server->id)
                    ->whereNotNull('player_uuid')
                    ->groupBy('player_uuid');
            })
            ->where('event_type', 'CONNECT')
            ->get();

        if ($onlinePlayers->isEmpty()) {
            return response()->json(['players' => []]);
        }

        $uuids = $onlinePlayers->pluck('player_uuid')->toArray();
        $playerMap = $onlinePlayers->keyBy('player_uuid');

        // Find last known position per player from position-bearing tables.
        // Query each table separately, collect in PHP (simpler, avoids complex UNION).
        $candidates = collect();

        // Kills (as killer)
        DB::table('player_kills')
            ->select('killer_uuid as player_uuid', 'killer_position as position', 'killed_at as ts')
            ->where('server_id', $server->id)
            ->whereIn('killer_uuid', $uuids)
            ->whereNotNull('killer_position')
            ->orderByDesc('killed_at')
            ->get()
            ->each(fn ($r) => $candidates->push($r));

        // Deaths (as victim)
        DB::table('player_kills')
            ->select('victim_uuid as player_uuid', 'victim_position as position', 'killed_at as ts')
            ->where('server_id', $server->id)
            ->whereIn('victim_uuid', $uuids)
            ->whereNotNull('victim_position')
            ->orderByDesc('killed_at')
            ->get()
            ->each(fn ($r) => $candidates->push($r));

        // Consciousness events
        DB::table('consciousness_events')
            ->select('player_uuid', 'position', 'occurred_at as ts')
            ->where('server_id', $server->id)
            ->whereIn('player_uuid', $uuids)
            ->whereNotNull('position')
            ->orderByDesc('occurred_at')
            ->get()
            ->each(fn ($r) => $candidates->push($r));

        // Grenades
        DB::table('player_grenades')
            ->select('player_uuid', 'position', 'occurred_at as ts')
            ->where('server_id', $server->id)
            ->whereIn('player_uuid', $uuids)
            ->whereNotNull('position')
            ->orderByDesc('occurred_at')
            ->get()
            ->each(fn ($r) => $candidates->push($r));

        // Building events
        DB::table('building_events')
            ->select('player_uuid', 'position', 'occurred_at as ts')
            ->where('server_id', $server->id)
            ->whereIn('player_uuid', $uuids)
            ->whereNotNull('position')
            ->orderByDesc('occurred_at')
            ->get()
            ->each(fn ($r) => $candidates->push($r));

        // Keep only the most recent position per player
        $latest = $candidates
            ->groupBy('player_uuid')
            ->map(fn ($group) => $group->sortByDesc('ts')->first());

        $result = [];
        foreach ($latest as $uuid => $row) {
            $pos = json_decode($row->position, true);
            if (! $pos || count($pos) < 2) {
                continue;
            }

            $player = $playerMap[$uuid] ?? null;
            $result[] = [
                'uuid' => $uuid,
                'name' => $player->player_name ?? 'Unknown',
                'x' => (float) $pos[0],
                'z' => (float) ($pos[2] ?? $pos[1]),
                'updated' => $row->ts,
            ];
        }

        return response()->json(['players' => $result]);
    }

    /**
     * Debug endpoint to see raw BattleMetrics data
     */
    public function debug(string $serverId): JsonResponse
    {
        $server = $this->battleMetrics->getServer($serverId);

        if (! $server) {
            return response()->json(['error' => 'Server not found'], 404);
        }

        $details = $server['attributes']['details'] ?? [];

        return response()->json([
            'server_id' => $serverId,
            'name' => $server['attributes']['name'] ?? 'Unknown',
            'details_keys' => array_keys($details),
            'details' => $details,
            'mods_from_service' => $this->battleMetrics->getServerMods($serverId),
        ]);
    }
}
