<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class GameStatsAdminController extends Controller
{
    public function index()
    {
        $stats = [
            'total_players' => DB::table('player_stats')->count(),
            'total_kills' => DB::table('player_stats')->sum('kills'),
            'total_deaths' => DB::table('player_stats')->sum('deaths'),
            'total_playtime' => DB::table('player_stats')->sum('playtime_seconds'),
            'total_sessions' => DB::table('connections')->count(),
            'total_kill_events' => DB::table('player_kills')->count(),
            'total_status_records' => DB::table('server_status')->count(),
            'active_players_24h' => DB::table('player_stats')->where('last_seen_at', '>=', now()->subDay())->count(),
            'ai_kills' => DB::table('player_kills')->where('victim_type', 'AI')->count(),
            'player_kills' => DB::table('player_kills')->where('victim_type', '!=', 'AI')->orWhereNull('victim_type')->count(),
            'total_headshots' => DB::table('player_stats')->sum('headshots'),
            'total_heals' => DB::table('player_healing_rjs')->count(),
            'total_base_captures' => DB::table('base_events')->count(),
            'total_supply_deliveries' => DB::table('supply_deliveries')->count(),
        ];

        $topPlayers = DB::table('player_stats')
            ->orderByDesc('kills')
            ->limit(10)
            ->get();

        $recentKillEvents = DB::table('player_kills')
            ->orderByDesc('created_at')
            ->limit(10)
            ->get();

        $recentSessions = DB::table('connections')
            ->orderByDesc('occurred_at')
            ->limit(10)
            ->get();

        $latestServerStatus = DB::table('server_status')
            ->orderByDesc('recorded_at')
            ->first();

        // Get weapon images keyed by weapon name
        $weaponImages = DB::table('weapons')
            ->whereNotNull('image_path')
            ->pluck('image_path', 'name');

        return view('admin.game-stats.index', compact(
            'stats',
            'topPlayers',
            'recentKillEvents',
            'recentSessions',
            'latestServerStatus',
            'weaponImages'
        ));
    }

    public function players(Request $request)
    {
        $query = DB::table('player_stats');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('player_name', 'like', "%{$search}%")
                    ->orWhere('player_uuid', 'like', "%{$search}%");
            });
        }

        $sortField = $request->get('sort', 'last_seen_at');
        $sortDirection = $request->get('direction', 'desc');

        $allowedSorts = ['player_name', 'playtime_seconds', 'last_seen_at', 'kills', 'deaths', 'headshots'];
        if (in_array($sortField, $allowedSorts)) {
            $query->orderBy($sortField, $sortDirection);
        }

        $players = $query->paginate(25);

        return view('admin.game-stats.players', compact('players'));
    }

    public function kills(Request $request)
    {
        $query = DB::table('player_kills');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('killer_name', 'like', "%{$search}%")
                    ->orWhere('victim_name', 'like', "%{$search}%")
                    ->orWhere('weapon_name', 'like', "%{$search}%")
                    ->orWhere('killer_uuid', 'like', "%{$search}%");
            });
        }

        if ($request->filled('victim_type')) {
            $query->where('victim_type', $request->victim_type);
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        $kills = $query->orderByDesc('created_at')->paginate(50);

        $serverIds = DB::table('player_kills')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        // Get weapon images keyed by weapon name
        $weaponImages = DB::table('weapons')
            ->whereNotNull('image_path')
            ->pluck('image_path', 'name');

        return view('admin.game-stats.kills', compact('kills', 'serverIds', 'weaponImages'));
    }

    public function sessions(Request $request)
    {
        $query = DB::table('connections');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('player_name', 'like', "%{$search}%");
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('occurred_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('occurred_at', '<=', $request->date_to);
        }

        $sessions = $query->orderByDesc('occurred_at')->paginate(50);

        $serverIds = DB::table('connections')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.sessions', compact('sessions', 'serverIds'));
    }

    public function serverStatus(Request $request)
    {
        $query = DB::table('server_status');

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('recorded_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('recorded_at', '<=', $request->date_to);
        }

        $statuses = $query->orderByDesc('recorded_at')->paginate(50);

        $serverIds = DB::table('server_status')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.server-status', compact('statuses', 'serverIds'));
    }

    public function playerShow($uuid)
    {
        $player = DB::table('player_stats')->where('player_uuid', $uuid)->first();

        if (! $player) {
            abort(404);
        }

        $recentKillEvents = DB::table('player_kills')
            ->where('killer_uuid', $uuid)
            ->orderByDesc('created_at')
            ->limit(20)
            ->get();

        $recentSessions = DB::table('connections')
            ->where('player_uuid', $uuid)
            ->orderByDesc('occurred_at')
            ->limit(20)
            ->get();

        $killsByVictimType = DB::table('player_kills')
            ->where('killer_uuid', $uuid)
            ->selectRaw('COALESCE(victim_type, \'UNKNOWN\') as victim_type, COUNT(*) as total')
            ->groupBy('victim_type')
            ->get();

        $topWeapons = DB::table('player_kills')
            ->where('killer_uuid', $uuid)
            ->selectRaw('weapon_name, COUNT(*) as total')
            ->groupBy('weapon_name')
            ->orderByDesc('total')
            ->limit(5)
            ->get();

        // Hit zones dealt (as attacker)
        $hitZonesDealt = DB::table('damage_events')
            ->where('killer_uuid', $uuid)
            ->selectRaw('hit_zone_name, COUNT(*) as count, SUM(damage_amount) as total_damage')
            ->groupBy('hit_zone_name')
            ->get();

        // Hit zones received (as victim)
        $hitZonesReceived = DB::table('damage_events')
            ->where('victim_uuid', $uuid)
            ->selectRaw('hit_zone_name, COUNT(*) as count, SUM(damage_amount) as total_damage')
            ->groupBy('hit_zone_name')
            ->get();

        // Friendly fire counts
        $friendlyFireDealt = DB::table('damage_events')
            ->where('killer_uuid', $uuid)->where('is_friendly_fire', true)->count();
        $friendlyFireReceived = DB::table('damage_events')
            ->where('victim_uuid', $uuid)->where('is_friendly_fire', true)->count();

        // XP breakdown by type
        $xpByType = DB::table('xp_events')
            ->where('player_uuid', $uuid)
            ->selectRaw('reward_type, COUNT(*) as count, SUM(xp_amount) as total_xp')
            ->groupBy('reward_type')
            ->orderByDesc('total_xp')
            ->get();

        // Get weapon images keyed by weapon name
        $weaponImages = DB::table('weapons')
            ->whereNotNull('image_path')
            ->pluck('image_path', 'name');

        return view('admin.game-stats.player-show', compact(
            'player',
            'recentKillEvents',
            'recentSessions',
            'killsByVictimType',
            'topWeapons',
            'hitZonesDealt',
            'hitZonesReceived',
            'friendlyFireDealt',
            'friendlyFireReceived',
            'xpByType',
            'weaponImages'
        ));
    }

    public function apiTokens()
    {
        $apiUser = User::where('email', 'api@armabattles.se')->first();
        $tokens = $apiUser ? $apiUser->tokens : collect();

        // Add rate limit info to each token
        $tokens = $tokens->map(function ($token) {
            $abilities = json_decode($token->abilities, true) ?? [];

            if (in_array('premium', $abilities)) {
                $token->rate_limit = 300;
                $token->token_type = 'premium';
                $token->badge_color = 'purple';
            } elseif (in_array('high-volume', $abilities)) {
                $token->rate_limit = 180;
                $token->token_type = 'high-volume';
                $token->badge_color = 'blue';
            } else {
                $token->rate_limit = 60;
                $token->token_type = 'standard';
                $token->badge_color = 'gray';
            }

            return $token;
        });

        return view('admin.game-stats.api-tokens', compact('apiUser', 'tokens'));
    }

    public function generateToken(Request $request)
    {
        $validated = $request->validate([
            'token_name' => 'nullable|string|max:255',
            'token_type' => 'required|in:standard,high-volume,premium',
        ]);

        $apiUser = User::firstOrCreate(
            ['email' => 'api@armabattles.se'],
            [
                'name' => 'API Service',
                'password' => bcrypt(bin2hex(random_bytes(32))),
                'role' => 'admin',
            ]
        );

        $tokenName = $validated['token_name'] ?? 'game-server-api-'.now()->timestamp;
        $tokenType = $validated['token_type'] ?? 'standard';

        // Define abilities based on token type
        $abilities = match ($tokenType) {
            'premium' => ['*', 'premium'],          // 300 req/min
            'high-volume' => ['*', 'high-volume'],  // 180 req/min
            default => ['*'],                        // 60 req/min (standard)
        };

        $token = $apiUser->createToken($tokenName, $abilities);

        // Get rate limit for display
        $rateLimit = match ($tokenType) {
            'premium' => 300,
            'high-volume' => 180,
            default => 60,
        };

        return back()
            ->with('new_token', $token->plainTextToken)
            ->with('token_type', $tokenType)
            ->with('rate_limit', $rateLimit)
            ->with('success', "New {$tokenType} API token generated successfully! Rate limit: {$rateLimit} requests/minute");
    }

    public function revokeToken(Request $request, $tokenId)
    {
        $apiUser = User::where('email', 'api@armabattles.se')->first();

        if ($apiUser) {
            $apiUser->tokens()->where('id', $tokenId)->delete();
        }

        return back()->with('success', 'API token revoked.');
    }

    // Additional views for new event types

    public function healingEvents(Request $request)
    {
        $query = DB::table('player_healing_rjs');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('healer_name', 'like', "%{$search}%")
                    ->orWhere('patient_name', 'like', "%{$search}%");
            });
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $healingEvents = $query->orderByDesc('occurred_at')->paginate(50);

        $serverIds = DB::table('player_healing_rjs')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.healing-events', compact('healingEvents', 'serverIds'));
    }

    public function baseCaptures(Request $request)
    {
        $query = DB::table('base_events');

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        $captures = $query->orderByDesc('occurred_at')->paginate(50);

        $serverIds = DB::table('base_events')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.base-captures', compact('captures', 'serverIds'));
    }

    public function chatMessages(Request $request)
    {
        $query = DB::table('chat_events');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('player_name', 'like', "%{$search}%")
                    ->orWhere('message', 'like', "%{$search}%");
            });
        }

        if ($request->filled('channel')) {
            $query->where('channel', $request->channel);
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $messages = $query->orderByDesc('occurred_at')->paginate(50);

        $channels = DB::table('chat_events')
            ->select('channel')
            ->distinct()
            ->orderBy('channel')
            ->pluck('channel');

        $serverIds = DB::table('chat_events')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.chat-messages', compact('messages', 'channels', 'serverIds'));
    }

    public function gameSessions(Request $request)
    {
        $query = DB::table('game_sessions');

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $gameSessions = $query->orderByDesc('started_at')->paginate(50);

        $serverIds = DB::table('game_sessions')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.game-sessions', compact('gameSessions', 'serverIds'));
    }

    public function supplyDeliveries(Request $request)
    {
        $query = DB::table('supply_deliveries');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('player_name', 'like', "%{$search}%");
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        $deliveries = $query->orderByDesc('occurred_at')->paginate(50);

        $serverIds = DB::table('supply_deliveries')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.supply-deliveries', compact('deliveries', 'serverIds'));
    }

    /**
     * GM Sessions - GM/Admin only
     */
    public function gmSessions(Request $request)
    {
        $query = DB::table('gm_sessions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where('player_name', 'like', "%{$search}%");
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('event_type')) {
            $query->where('event_type', $request->event_type);
        }

        $sessions = $query->orderByDesc('occurred_at')->paginate(50);

        $serverIds = DB::table('gm_sessions')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        return view('admin.game-stats.gm-sessions', compact('sessions', 'serverIds'));
    }

    /**
     * Editor Actions (GM Actions) - GM/Admin only
     */
    public function editorActions(Request $request)
    {
        $query = DB::table('editor_actions');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('player_name', 'like', "%{$search}%")
                    ->orWhere('action', 'like', "%{$search}%");
            });
        }

        if ($request->filled('server_id')) {
            $query->where('server_id', $request->server_id);
        }

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        $actions = $query->orderByDesc('occurred_at')->paginate(50);

        $serverIds = DB::table('editor_actions')
            ->select('server_id')
            ->distinct()
            ->orderBy('server_id')
            ->pluck('server_id');

        $actionTypes = DB::table('editor_actions')
            ->select('action')
            ->distinct()
            ->orderBy('action')
            ->pluck('action');

        return view('admin.game-stats.editor-actions', compact('actions', 'serverIds', 'actionTypes'));
    }
}
