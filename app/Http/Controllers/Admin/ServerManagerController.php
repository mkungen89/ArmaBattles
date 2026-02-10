<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ScheduledRestart;
use App\Models\Server;
use App\Models\SiteSetting;
use App\Services\GameServerManager;
use App\Services\ModUpdateCheckService;
use App\Services\PlayerHistoryService;
use Illuminate\Http\Client\ConnectionException;
use Illuminate\Http\Client\RequestException;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ServerManagerController extends Controller
{
    use \App\Traits\LogsAdminActions;

    public function __construct(protected GameServerManager $manager) {}

    // ─── Page Routes ─────────────────────────────────────────

    public function dashboard()
    {
        try {
            $status = $this->manager->status();
        } catch (ConnectionException) {
            $status = null;
        } catch (RequestException $e) {
            $status = null;
        }

        return view('admin.server.dashboard', compact('status'));
    }

    public function players()
    {
        return view('admin.server.players');
    }

    public function logs(string $type = 'arma')
    {
        $validTypes = ['arma', 'arma-stdout', 'stats', 'stats-service'];
        if (! in_array($type, $validTypes)) {
            $type = 'arma';
        }

        try {
            $lines = request()->integer('lines', 100);
            $lines = min(max($lines, 50), 500);
            $data = $this->manager->logs($type, $lines);
        } catch (ConnectionException|RequestException) {
            $data = null;
        }

        return view('admin.server.logs', compact('type', 'data'));
    }

    public function mods()
    {
        try {
            $data = $this->manager->getMods();
        } catch (ConnectionException|RequestException) {
            $data = null;
        }

        return view('admin.server.mods', compact('data'));
    }

    public function config()
    {
        try {
            $armaConfig = $this->manager->getArmaConfig();
        } catch (ConnectionException|RequestException) {
            $armaConfig = null;
        }

        try {
            $statsConfig = $this->manager->getStatsConfig();
        } catch (ConnectionException|RequestException) {
            $statsConfig = null;
        }

        return view('admin.server.config', compact('armaConfig', 'statsConfig'));
    }

    // ─── Player History ─────────────────────────────────────

    public function playerHistory(Request $request, PlayerHistoryService $service)
    {
        $results = [];
        $query = $request->input('q', '');

        if ($query) {
            $results = $service->search($query);
        }

        return view('admin.server.player-history', compact('results', 'query'));
    }

    public function playerDetail(string $uuid, PlayerHistoryService $service)
    {
        $player = $service->getPlayerDetail($uuid);

        return view('admin.server.player-detail', compact('player'));
    }

    // ─── Performance Graphs ─────────────────────────────────

    public function performance()
    {
        return view('admin.server.performance');
    }

    public function apiPerformanceData(Request $request)
    {
        $range = $request->input('range', '24h');
        $hours = match ($range) {
            '6h' => 6,
            '72h' => 72,
            default => 24,
        };

        $data = DB::table('server_status')
            ->where('recorded_at', '>=', now()->subHours($hours))
            ->orderBy('recorded_at')
            ->select(['recorded_at', 'fps', 'memory_mb', 'players_online', 'uptime_seconds'])
            ->get();

        $fps = $data->pluck('fps')->filter();
        $memory = $data->pluck('memory_mb')->filter();
        $players = $data->pluck('players_online');

        return response()->json([
            'labels' => $data->pluck('recorded_at')->toArray(),
            'fps' => $data->pluck('fps')->toArray(),
            'memory' => $data->pluck('memory_mb')->toArray(),
            'players' => $data->pluck('players_online')->toArray(),
            'uptime' => $data->map(fn ($r) => round(($r->uptime_seconds ?? 0) / 3600, 2))->toArray(),
            'summary' => [
                'fps' => [
                    'avg' => round($fps->avg(), 1),
                    'min' => round($fps->min(), 1),
                    'max' => round($fps->max(), 1),
                ],
                'memory' => [
                    'avg' => round($memory->avg()),
                    'min' => $memory->min(),
                    'max' => $memory->max(),
                ],
                'players' => [
                    'avg' => round($players->avg(), 1),
                    'min' => $players->min(),
                    'max' => $players->max(),
                ],
            ],
        ]);
    }

    // ─── Scheduled Restarts ─────────────────────────────────

    public function scheduledRestarts()
    {
        $restarts = ScheduledRestart::with('server')->orderBy('next_execution_at')->get();
        $servers = Server::where('is_managed', true)->orWhere('id', 1)->get();

        return view('admin.server.scheduled-restarts', compact('restarts', 'servers'));
    }

    public function storeScheduledRestart(Request $request)
    {
        $validated = $request->validate([
            'server_id' => 'required|exists:servers,id',
            'schedule_type' => 'required|in:daily,weekly,custom',
            'restart_time' => 'nullable|date_format:H:i',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'cron_expression' => 'nullable|string|max:100',
            'warning_minutes' => 'required|integer|min:0|max:30',
            'warning_message' => 'nullable|string|max:255',
        ]);

        $restart = ScheduledRestart::create($validated);
        $restart->calculateNextExecution();

        $this->logAction('server.scheduled-restart.create', 'ScheduledRestart', $restart->id, $validated);

        return back()->with('success', 'Scheduled restart created.');
    }

    public function updateScheduledRestart(Request $request, ScheduledRestart $restart)
    {
        $validated = $request->validate([
            'is_enabled' => 'sometimes|boolean',
            'schedule_type' => 'sometimes|in:daily,weekly,custom',
            'restart_time' => 'nullable|date_format:H:i',
            'days_of_week' => 'nullable|array',
            'days_of_week.*' => 'integer|between:1,7',
            'cron_expression' => 'nullable|string|max:100',
            'warning_minutes' => 'sometimes|integer|min:0|max:30',
            'warning_message' => 'nullable|string|max:255',
        ]);

        $restart->update($validated);
        $restart->calculateNextExecution();

        $this->logAction('server.scheduled-restart.update', 'ScheduledRestart', $restart->id, $validated);

        return back()->with('success', 'Scheduled restart updated.');
    }

    public function destroyScheduledRestart(ScheduledRestart $restart)
    {
        $this->logAction('server.scheduled-restart.delete', 'ScheduledRestart', $restart->id);
        $restart->delete();

        return back()->with('success', 'Scheduled restart deleted.');
    }

    // ─── Quick Messages ─────────────────────────────────────

    public function quickMessages()
    {
        $templates = SiteSetting::get('broadcast_templates', []);

        return view('admin.server.quick-messages', compact('templates'));
    }

    public function saveQuickMessages(Request $request)
    {
        $request->validate([
            'templates' => 'required|array',
            'templates.*.label' => 'required|string|max:100',
            'templates.*.message' => 'required|string|max:500',
        ]);

        SiteSetting::set('broadcast_templates', json_encode($request->templates));

        $this->logAction('server.quick-messages.update');

        return back()->with('success', 'Broadcast templates saved.');
    }

    public function sendQuickMessage(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        try {
            $result = $this->manager->broadcast($request->message);
            $this->logAction('server.broadcast', null, null, ['message' => $request->message, 'source' => 'quick-message']);

            return back()->with('success', 'Message broadcast sent.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to broadcast.');
        }
    }

    // ─── Mod Updates ────────────────────────────────────────

    public function modUpdates(ModUpdateCheckService $service)
    {
        try {
            $server = Server::where('is_managed', true)->first() ?? Server::first();
            $mods = $server ? $service->checkForServer($server) : [];
        } catch (ConnectionException) {
            $mods = [];
            session()->flash('error', 'Could not connect to game server to fetch mod list.');
        } catch (RequestException $e) {
            $mods = [];
            session()->flash('error', $e->response->json('error') ?? 'Failed to fetch mods.');
        }

        return view('admin.server.mod-updates', compact('mods'));
    }

    // ─── Server Comparison ──────────────────────────────────

    public function compare()
    {
        $servers = Server::where('is_managed', true)->get();

        return view('admin.server.compare', compact('servers'));
    }

    // ─── Ban by GUID ────────────────────────────────────────

    public function banPlayerByGuid(Request $request)
    {
        $request->validate([
            'guid' => 'required|string|max:100',
            'minutes' => 'nullable|integer|min:0',
            'reason' => 'nullable|string|max:500',
        ]);

        try {
            $result = $this->manager->banPlayerByGuid(
                $request->guid,
                $request->integer('minutes', 0),
                $request->input('reason', 'Banned by admin')
            );
            $this->logAction('server.player.ban-guid', null, null, [
                'guid' => $request->guid,
                'minutes' => $request->minutes,
                'reason' => $request->reason,
            ]);

            return back()->with('success', 'Player banned by GUID.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to ban player.');
        }
    }

    // ─── Config Actions ──────────────────────────────────────

    public function updateArmaConfig(Request $request)
    {
        try {
            $config = json_decode($request->input('config_json'), true);
            if (! $config) {
                return back()->with('error', 'Invalid JSON configuration.');
            }
            $result = $this->manager->updateArmaConfig($config);
            $this->logAction('server.config.arma.update', null, null);

            return back()->with('success', $result['message'] ?? 'Arma config updated.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to update config.');
        }
    }

    public function updateStatsConfig(Request $request)
    {
        try {
            $config = json_decode($request->input('config_json'), true);
            if (! $config) {
                return back()->with('error', 'Invalid JSON configuration.');
            }
            $result = $this->manager->updateStatsConfig($config);
            $this->logAction('server.config.stats.update', null, null);

            return back()->with('success', $result['message'] ?? 'Stats config updated.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to update config.');
        }
    }

    // ─── Mod Actions ─────────────────────────────────────────

    public function addMod(Request $request)
    {
        $request->validate([
            'mod_id' => 'required|string|max:50',
            'name' => 'required|string|max:255',
            'version' => 'nullable|string|max:50',
        ]);

        try {
            $result = $this->manager->addMod($request->mod_id, $request->name, $request->version);
            $this->logAction('server.mod.add', null, null, ['modId' => $request->mod_id, 'name' => $request->name]);

            return back()->with('success', $result['message'] ?? 'Mod added.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to add mod.');
        }
    }

    public function removeMod(string $modId)
    {
        try {
            $result = $this->manager->removeMod($modId);
            $this->logAction('server.mod.remove', null, null, ['modId' => $modId]);

            return back()->with('success', $result['message'] ?? 'Mod removed.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to remove mod.');
        }
    }

    // ─── Service Actions ─────────────────────────────────────

    public function serviceAction(string $service, string $action)
    {
        $validActions = [
            'arma' => ['restart', 'stop', 'start'],
            'stats' => ['restart'],
        ];

        if (! isset($validActions[$service]) || ! in_array($action, $validActions[$service])) {
            return back()->with('error', 'Invalid service action.');
        }

        try {
            $result = match ("{$service}.{$action}") {
                'arma.restart' => $this->manager->restartArma(),
                'arma.stop' => $this->manager->stopArma(),
                'arma.start' => $this->manager->startArma(),
                'stats.restart' => $this->manager->restartStats(),
            };
            $this->logAction("server.service.{$service}.{$action}", null, null);

            return back()->with('success', $result['message'] ?? ucfirst($action)." {$service} executed.");
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? "Failed to {$action} {$service}.");
        }
    }

    // ─── Server Update ───────────────────────────────────────

    public function startUpdate()
    {
        try {
            $result = $this->manager->startUpdate();
            $this->logAction('server.update.start', null, null);

            return back()->with('success', $result['message'] ?? 'Server update started.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to start update.');
        }
    }

    // ─── Player Actions ──────────────────────────────────────

    public function kickPlayer(Request $request)
    {
        $request->validate(['player_id' => 'required|integer']);

        try {
            $result = $this->manager->kickPlayer($request->player_id, $request->input('reason', 'Kicked by admin'));
            $this->logAction('server.player.kick', null, null, ['playerId' => $request->player_id, 'reason' => $request->reason]);

            return back()->with('success', 'Player kicked.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to kick player.');
        }
    }

    public function banPlayer(Request $request)
    {
        $request->validate(['player_id' => 'required|integer']);

        try {
            $result = $this->manager->banPlayer(
                $request->player_id,
                $request->integer('minutes', 0),
                $request->input('reason', 'Banned by admin')
            );
            $this->logAction('server.player.ban', null, null, [
                'playerId' => $request->player_id,
                'minutes' => $request->minutes,
                'reason' => $request->reason,
            ]);

            return back()->with('success', 'Player banned.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to ban player.');
        }
    }

    public function unbanPlayer(Request $request)
    {
        $request->validate(['ban_index' => 'required']);

        try {
            $result = $this->manager->unbanPlayer($request->ban_index);
            $this->logAction('server.player.unban', null, null, ['banIndex' => $request->ban_index]);

            return back()->with('success', 'Player unbanned.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to unban player.');
        }
    }

    public function broadcast(Request $request)
    {
        $request->validate(['message' => 'required|string|max:500']);

        try {
            $result = $this->manager->broadcast($request->message);
            $this->logAction('server.broadcast', null, null, ['message' => $request->message]);

            return back()->with('success', 'Message broadcast sent.');
        } catch (ConnectionException) {
            return back()->with('error', 'Could not connect to game server.');
        } catch (RequestException $e) {
            return back()->with('error', $e->response->json('error') ?? 'Failed to broadcast.');
        }
    }

    // ─── AJAX API Endpoints ──────────────────────────────────

    public function apiHealth()
    {
        try {
            return response()->json($this->manager->health());
        } catch (ConnectionException) {
            return response()->json(['status' => 'unreachable', 'error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['status' => 'error', 'error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiStatus()
    {
        try {
            return response()->json($this->manager->status());
        } catch (ConnectionException) {
            return response()->json(['error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiUpdateStatus()
    {
        try {
            return response()->json($this->manager->updateStatus());
        } catch (ConnectionException) {
            return response()->json(['error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiAnticheat()
    {
        try {
            return response()->json($this->manager->anticheat());
        } catch (ConnectionException) {
            return response()->json(['error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiPlayers()
    {
        try {
            return response()->json($this->manager->players());
        } catch (ConnectionException) {
            return response()->json(['error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiBans()
    {
        try {
            return response()->json($this->manager->bans());
        } catch (ConnectionException) {
            return response()->json(['error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiLogs(string $type)
    {
        $validTypes = ['arma', 'arma-stdout', 'stats', 'stats-service'];
        if (! in_array($type, $validTypes)) {
            return response()->json(['error' => 'Invalid log type'], 400);
        }

        try {
            $lines = request()->integer('lines', 100);
            $lines = min(max($lines, 50), 500);

            return response()->json($this->manager->logs($type, $lines));
        } catch (ConnectionException) {
            return response()->json(['error' => 'Could not connect to game server'], 503);
        } catch (RequestException $e) {
            return response()->json(['error' => $e->response->json('error') ?? $e->getMessage()], 500);
        }
    }

    public function apiServerCompareData(Request $request)
    {
        $serverIds = $request->input('server_ids', []);
        $servers = Server::whereIn('id', $serverIds)->where('is_managed', true)->get();

        $data = [];
        foreach ($servers as $server) {
            try {
                $mgr = $this->manager->forServer($server);
                $status = $mgr->status();
                $health = $mgr->health();
                $data[] = [
                    'id' => $server->id,
                    'name' => $server->name,
                    'status' => $status,
                    'health' => $health,
                ];
            } catch (ConnectionException|RequestException) {
                $data[] = [
                    'id' => $server->id,
                    'name' => $server->name,
                    'status' => null,
                    'health' => ['status' => 'unreachable'],
                ];
            }
        }

        return response()->json($data);
    }
}
