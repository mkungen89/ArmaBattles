<?php

namespace App\Services;

use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Cache;

class BattleMetricsService
{
    protected string $baseUrl = 'https://api.battlemetrics.com';
    protected ?string $apiToken;

    public function __construct()
    {
        $this->apiToken = config('services.battlemetrics.token');
    }

    public function getServer(string $serverId): ?array
    {
        return Cache::remember("battlemetrics.server.{$serverId}", site_setting('bm_cache_ttl_live', 60), function () use ($serverId) {
            $response = $this->request("/servers/{$serverId}");
            return $response['data'] ?? null;
        });
    }

    public function getServerPlayers(string $serverId): array
    {
        return Cache::remember("battlemetrics.server.{$serverId}.players", site_setting('bm_cache_ttl_live', 60), function () use ($serverId) {
            $response = $this->request("/servers/{$serverId}?include=player");
            return $response['included'] ?? [];
        });
    }

    public function getServerHistory(string $serverId, string $start, string $end, int $resolution = 60): array
    {
        $cacheKey = "battlemetrics.server.{$serverId}.history.{$start}.{$end}";

        return Cache::remember($cacheKey, site_setting('bm_cache_ttl_history', 300), function () use ($serverId, $start, $end, $resolution) {
            $response = $this->request("/servers/{$serverId}/player-count-history", [
                'start' => $start,
                'stop' => $end,
                'resolution' => $resolution,
            ]);
            return $response['data'] ?? [];
        });
    }

    public function searchPlayers(string $search, string $serverId = null): array
    {
        $params = ['filter[search]' => $search];

        if ($serverId) {
            $params['filter[servers]'] = $serverId;
        }

        $response = $this->request('/players', $params);
        return $response['data'] ?? [];
    }

    public function getPlayer(string $playerId): ?array
    {
        return Cache::remember("battlemetrics.player.{$playerId}", 120, function () use ($playerId) {
            $response = $this->request("/players/{$playerId}");
            return $response['data'] ?? null;
        });
    }

    public function getPlayerServerTime(string $playerId, string $serverId): ?array
    {
        $cacheKey = "battlemetrics.player.{$playerId}.server.{$serverId}";

        return Cache::remember($cacheKey, site_setting('bm_cache_ttl_history', 300), function () use ($playerId, $serverId) {
            $response = $this->request("/players/{$playerId}/servers/{$serverId}");
            return $response['data'] ?? null;
        });
    }

    protected function request(string $endpoint, array $params = []): array
    {
        $request = Http::baseUrl($this->baseUrl)
            ->acceptJson();

        if ($this->apiToken) {
            $request->withToken($this->apiToken);
        }

        $response = $request->get($endpoint, $params);

        if ($response->failed()) {
            return [];
        }

        return $response->json();
    }

    public function clearCache(string $serverId = null): void
    {
        if ($serverId) {
            Cache::forget("battlemetrics.server.{$serverId}");
            Cache::forget("battlemetrics.server.{$serverId}.players");
        }
    }

    public function getServerWithDetails(string $serverId): ?array
    {
        return Cache::remember("battlemetrics.server.{$serverId}.details", site_setting('bm_cache_ttl_live', 60), function () use ($serverId) {
            $response = $this->request("/servers/{$serverId}", [
                'include' => 'serverGroup',
            ]);
            return $response['data'] ?? null;
        });
    }

    public function getServerMods(string $serverId): array
    {
        $server = $this->getServer($serverId);

        if (!$server) {
            return [];
        }

        $details = $server['attributes']['details'] ?? [];
        $reforger = $details['reforger'] ?? [];

        // Try multiple possible locations for mods in BattleMetrics API
        $mods = $reforger['mods']
            ?? $details['mods']
            ?? $reforger['activeMods']
            ?? $details['modList']
            ?? $reforger['modList']
            ?? [];

        // If mods is empty, try to get it from the raw attributes
        if (empty($mods)) {
            $attributes = $server['attributes'] ?? [];
            $mods = $attributes['mods'] ?? [];
        }

        \Illuminate\Support\Facades\Log::info('BattleMetrics mods data', [
            'server_id' => $serverId,
            'mods_count' => count($mods),
            'details_keys' => array_keys($details),
            'reforger_keys' => array_keys($reforger),
        ]);

        return collect($mods)->map(function ($mod, $index) {
            if (is_string($mod)) {
                return [
                    'id' => md5($mod),
                    'name' => $mod,
                    'version' => null,
                    'author' => null,
                    'workshop_url' => null,
                    'load_order' => $index,
                ];
            }

            // Handle different mod data structures
            $workshopId = $mod['id'] ?? $mod['workshopId'] ?? $mod['fileId'] ?? $mod['modId'] ?? null;
            $modName = $mod['name'] ?? $mod['modName'] ?? $mod['title'] ?? 'Unknown';

            return [
                'id' => $workshopId ?? md5($modName . $index),
                'name' => $modName,
                'version' => $mod['version'] ?? $mod['modVersion'] ?? null,
                'author' => $mod['author'] ?? $mod['modAuthor'] ?? $mod['creator'] ?? null,
                'workshop_url' => $workshopId
                    ? "https://reforger.armaplatform.com/workshop/" . $workshopId
                    : null,
                'load_order' => $index,
                'updated_at' => $mod['updatedAt'] ?? $mod['updated_at'] ?? null,
            ];
        })->toArray();
    }

    /**
     * Get raw server details for debugging
     */
    public function getServerRawDetails(string $serverId): array
    {
        $server = $this->getServer($serverId);
        return $server['attributes']['details'] ?? [];
    }

    public function getServerUptime(string $serverId): array
    {
        return Cache::remember("battlemetrics.server.{$serverId}.uptime", site_setting('bm_cache_ttl_history', 300), function () use ($serverId) {
            $response = $this->request("/servers/{$serverId}/uptime-history", [
                'start' => now()->subDays(7)->toIso8601String(),
                'stop' => now()->toIso8601String(),
            ]);
            return $response['data'] ?? [];
        });
    }

    public function getServerSessions(string $serverId, int $limit = 10): array
    {
        $history = $this->getServerHistory(
            $serverId,
            now()->subDays(3)->toIso8601String(),
            now()->toIso8601String(),
            60
        );

        $sessions = [];
        $currentSession = null;
        $sessionNumber = 1;

        foreach ($history as $point) {
            $players = $point['attributes']['value'] ?? $point['value'] ?? 0;
            $timestamp = $point['attributes']['timestamp'] ?? $point['timestamp'] ?? null;

            if ($players > 0 || ($currentSession && $players === 0)) {
                if (!$currentSession) {
                    $currentSession = [
                        'session_number' => $sessionNumber,
                        'started_at' => $timestamp,
                        'last_seen_at' => $timestamp,
                        'peak_players' => $players,
                        'snapshots' => 1,
                        'total_players' => $players,
                    ];
                } else {
                    $currentSession['last_seen_at'] = $timestamp;
                    $currentSession['peak_players'] = max($currentSession['peak_players'], $players);
                    $currentSession['snapshots']++;
                    $currentSession['total_players'] += $players;

                    if ($players === 0) {
                        $currentSession['average_players'] = $currentSession['snapshots'] > 0
                            ? round($currentSession['total_players'] / $currentSession['snapshots'], 1)
                            : 0;
                        $sessions[] = $currentSession;
                        $currentSession = null;
                        $sessionNumber++;
                    }
                }
            }
        }

        if ($currentSession) {
            $currentSession['is_current'] = true;
            $currentSession['average_players'] = $currentSession['snapshots'] > 0
                ? round($currentSession['total_players'] / $currentSession['snapshots'], 1)
                : 0;
            array_unshift($sessions, $currentSession);
        }

        return array_slice($sessions, 0, $limit);
    }

    public function calculateStats(array $history): array
    {
        if (empty($history)) {
            return [
                'average' => 0,
                'peak' => 0,
                'median' => 0,
                'lowest' => 0,
                'data_points' => 0,
                'restarts' => 0,
            ];
        }

        $values = collect($history)->map(function ($point) {
            return $point['attributes']['value'] ?? $point['value'] ?? 0;
        })->filter(fn($v) => $v !== null)->values()->toArray();

        if (empty($values)) {
            return [
                'average' => 0,
                'peak' => 0,
                'median' => 0,
                'lowest' => 0,
                'data_points' => 0,
                'restarts' => 0,
            ];
        }

        sort($values);
        $count = count($values);
        $median = $count % 2 === 0
            ? ($values[$count / 2 - 1] + $values[$count / 2]) / 2
            : $values[(int) floor($count / 2)];

        $restarts = 0;
        $prevValue = null;
        foreach ($values as $value) {
            if ($prevValue !== null && $prevValue > 0 && $value === 0) {
                $restarts++;
            }
            $prevValue = $value;
        }

        return [
            'average' => round(array_sum($values) / $count, 1),
            'peak' => max($values),
            'median' => round($median, 1),
            'lowest' => min($values),
            'data_points' => $count,
            'restarts' => $restarts,
        ];
    }
}
