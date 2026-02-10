<?php

namespace App\Http\Controllers;

use App\Models\Server;
use App\Services\BattleMetricsService;
use Illuminate\Http\Request;

class ServerController extends Controller
{
    public function __construct(
        protected BattleMetricsService $battleMetrics
    ) {}

    public function info()
    {
        $serverId = config('services.battlemetrics.server_id');
        $server = null;

        if ($serverId) {
            $server = $this->battleMetrics->getServer($serverId);
        }

        return view('server.info', [
            'server' => $server,
        ]);
    }

    public function status()
    {
        $serverId = config('services.battlemetrics.server_id');

        if (! $serverId) {
            return response()->json(['error' => 'Server ID not configured'], 500);
        }

        $server = $this->battleMetrics->getServer($serverId);

        if (! $server) {
            return response()->json(['error' => 'Could not fetch server data'], 500);
        }

        $details = $server['attributes']['details'] ?? [];
        $reforger = $details['reforger'] ?? [];

        // Parse supported platforms
        $rawPlatforms = $reforger['supportedGameClientTypes'] ?? $details['platforms'] ?? [];
        $platforms = [];
        foreach ($rawPlatforms as $platform) {
            if (str_contains($platform, 'PC')) {
                $platforms[] = 'pc';
            }
            if (str_contains($platform, 'XBL') || str_contains($platform, 'XBOX')) {
                $platforms[] = 'xbox';
            }
            if (str_contains($platform, 'PSN') || str_contains($platform, 'PLAYSTATION')) {
                $platforms[] = 'playstation';
            }
        }
        if (empty($platforms)) {
            $platforms = ['pc'];
        }

        return response()->json([
            'name' => $server['attributes']['name'] ?? 'Unknown',
            'players' => $server['attributes']['players'] ?? 0,
            'maxPlayers' => $server['attributes']['maxPlayers'] ?? 0,
            'status' => $server['attributes']['status'] ?? 'offline',
            'map' => $details['map'] ?? null,
            'scenario' => Server::resolveScenarioName($reforger['scenarioName'] ?? $details['scenario'] ?? $details['map'] ?? null),
            'platforms' => $platforms,
            'ip' => $server['attributes']['ip'] ?? null,
            'port' => $server['attributes']['port'] ?? null,
        ]);
    }

    public function players()
    {
        $serverId = config('services.battlemetrics.server_id');

        if (! $serverId) {
            return response()->json(['error' => 'Server ID not configured'], 500);
        }

        $players = $this->battleMetrics->getServerPlayers($serverId);

        return response()->json($players);
    }

    public function history(Request $request)
    {
        $serverId = config('services.battlemetrics.server_id');

        if (! $serverId) {
            return response()->json(['error' => 'Server ID not configured'], 500);
        }

        $start = $request->get('start', now()->subDay()->toIso8601String());
        $end = $request->get('end', now()->toIso8601String());

        $history = $this->battleMetrics->getServerHistory($serverId, $start, $end);

        return response()->json($history);
    }
}
