<?php

namespace App\Console\Commands;

use App\Events\ServerStatusUpdated;
use App\Models\Server;
use App\Models\ServerSession;
use App\Models\ServerStatistic;
use App\Services\A2SQueryService;
use App\Services\BattleMetricsService;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class TrackServerStatus extends Command
{
    protected $signature = 'server:track {--server-id= : BattleMetrics server ID to track}';

    protected $description = 'Track server status and save statistics';

    public function __construct(
        protected A2SQueryService $a2sQuery,
        protected BattleMetricsService $battleMetrics
    ) {
        parent::__construct();
    }

    public function handle(): int
    {
        $serverId = $this->option('server-id') ?? config('services.battlemetrics.server_id');

        if (! $serverId) {
            $this->error('No server ID provided');

            return 1;
        }

        $this->info("Tracking server: {$serverId}");

        // Get server from BattleMetrics first
        $bmData = $this->battleMetrics->getServer($serverId);

        if (! $bmData) {
            $this->error('Could not fetch server data from BattleMetrics');

            return 1;
        }

        // Sync/create server in database
        $server = Server::syncFromBattleMetrics($bmData);

        $this->info("Server: {$server->name}");
        $this->info("IP: {$server->ip}:{$server->port}");

        // Try A2S query for real-time data
        // Arma Reforger typically uses port 17777 for A2S queries
        $queryPort = $server->query_port ?? 17777;
        $a2sData = $this->a2sQuery->queryServerInfo($server->ip, $queryPort);

        // If that fails, try common A2S ports
        if (! $a2sData) {
            $alternativePorts = [17777, $server->port + 1, $server->port, 27015, 27016];
            foreach ($alternativePorts as $port) {
                if ($port === $queryPort) {
                    continue;
                }
                $a2sData = $this->a2sQuery->queryServerInfo($server->ip, $port);
                if ($a2sData) {
                    $this->info("A2S found on port {$port}");
                    break;
                }
            }
        }

        $isOnline = false;
        $playerCount = 0;
        $maxPlayers = $server->max_players;

        if ($a2sData) {
            $this->info('A2S Query successful!');
            $isOnline = true;
            $playerCount = $a2sData['players'] ?? 0;
            $maxPlayers = $a2sData['max_players'] ?? $server->max_players;
            $this->info("Players (A2S): {$playerCount}/{$maxPlayers}");
        } else {
            $this->warn('A2S Query failed, using BattleMetrics data');
            $isOnline = ($bmData['attributes']['status'] ?? 'offline') === 'online';
            $playerCount = $bmData['attributes']['players'] ?? 0;
            $maxPlayers = $bmData['attributes']['maxPlayers'] ?? 128;
            $this->info("Players (BM): {$playerCount}/{$maxPlayers}");
        }

        // Get previous status
        $previousStatus = $server->status;
        $wasOnline = $previousStatus === 'online';
        $newStatus = $isOnline ? 'online' : 'offline';

        // Detect status change (restart/crash)
        if ($wasOnline && ! $isOnline) {
            $this->warn('Server went OFFLINE - ending current session');
            $this->endCurrentSession($server);
        } elseif (! $wasOnline && $isOnline) {
            $this->info('Server came ONLINE - starting new session');
            $this->startNewSession($server);
        }

        // Update server status
        $server->update([
            'status' => $newStatus,
            'players' => $playerCount,
            'max_players' => $maxPlayers,
            'last_updated_at' => now(),
        ]);

        ServerStatusUpdated::dispatch(
            $server->id,
            $playerCount,
            $maxPlayers,
            null,
            $server->name,
        );

        // Save statistic snapshot
        $this->saveStatistic($server, $playerCount, $maxPlayers, $newStatus);

        // Update current session stats
        if ($isOnline) {
            $this->updateCurrentSessionStats($server, $playerCount);
        }

        $this->info("Status: {$newStatus}");
        $this->info('Tracking complete!');

        return 0;
    }

    /**
     * End the current session when server goes offline
     */
    private function endCurrentSession(Server $server): void
    {
        $currentSession = $server->sessions()->where('is_current', true)->first();

        if ($currentSession) {
            $currentSession->update([
                'is_current' => false,
                'ended_at' => now(),
                'last_seen_at' => now(),
            ]);

            Log::info("Ended session #{$currentSession->session_number} for server {$server->name}");
        }
    }

    /**
     * Start a new session when server comes online
     */
    private function startNewSession(Server $server): void
    {
        // End any existing current session first
        $server->sessions()->where('is_current', true)->update([
            'is_current' => false,
            'ended_at' => now(),
        ]);

        // Get next session number
        $lastSession = $server->sessions()->orderByDesc('session_number')->first();
        $sessionNumber = $lastSession ? $lastSession->session_number + 1 : 1;

        // Create new session
        $session = ServerSession::create([
            'server_id' => $server->id,
            'session_number' => $sessionNumber,
            'started_at' => now(),
            'last_seen_at' => now(),
            'is_current' => true,
            'peak_players' => 0,
            'average_players' => 0,
            'total_snapshots' => 0,
        ]);

        // Update server's session start time
        $server->update(['session_started_at' => now()]);

        Log::info("Started new session #{$sessionNumber} for server {$server->name}");
    }

    /**
     * Save a statistic snapshot
     */
    private function saveStatistic(Server $server, int $players, int $maxPlayers, string $status): void
    {
        $currentSession = $server->sessions()->where('is_current', true)->first();

        ServerStatistic::create([
            'server_id' => $server->id,
            'server_session_id' => $currentSession?->id,
            'players' => $players,
            'max_players' => $maxPlayers,
            'status' => $status,
            'recorded_at' => now(),
        ]);
    }

    /**
     * Update current session statistics
     */
    private function updateCurrentSessionStats(Server $server, int $playerCount): void
    {
        $currentSession = $server->sessions()->where('is_current', true)->first();

        if (! $currentSession) {
            // No current session, start one
            $this->startNewSession($server);
            $currentSession = $server->sessions()->where('is_current', true)->first();
        }

        if ($currentSession) {
            // Update peak players
            $newPeak = max($currentSession->peak_players, $playerCount);

            // Calculate new average
            $totalSnapshots = $currentSession->total_snapshots + 1;
            $currentTotal = $currentSession->average_players * $currentSession->total_snapshots;
            $newAverage = ($currentTotal + $playerCount) / $totalSnapshots;

            $currentSession->update([
                'peak_players' => $newPeak,
                'average_players' => round($newAverage, 2),
                'total_snapshots' => $totalSnapshots,
                'last_seen_at' => now(),
            ]);
        }
    }
}
