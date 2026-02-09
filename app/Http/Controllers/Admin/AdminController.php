<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Models\Server;
use App\Models\Mod;
use App\Models\ServerSession;
use App\Models\ServerStatistic;
use App\Models\SiteSetting;
use App\Services\BattleMetricsService;
use App\Services\ReforgerWorkshopService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Cache;
use Illuminate\Support\Facades\Log;

class AdminController extends Controller
{
    use \App\Traits\LogsAdminActions;
    public function dashboard()
    {
        $stats = [
            'total_users' => User::count(),
            'admin_users' => User::where('role', 'admin')->count(),
            'banned_users' => User::where('is_banned', true)->count(),
            'total_servers' => Server::count(),
            'total_sessions' => ServerSession::count(),
            'total_statistics' => ServerStatistic::count(),
        ];

        $recentUsers = User::latest()->limit(10)->get();
        $servers = Server::all();

        return view('admin.dashboard', compact('stats', 'recentUsers', 'servers'));
    }

    public function users(Request $request)
    {
        $query = User::query();

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                  ->orWhere('steam_id', 'like', "%{$search}%");
            });
        }

        if ($request->filled('role')) {
            $query->where('role', $request->role);
        }

        if ($request->filled('banned')) {
            $query->where('is_banned', $request->banned === 'yes');
        }

        $users = $query->latest()->paginate(20);

        return view('admin.users.index', compact('users'));
    }

    public function editUser(User $user)
    {
        return view('admin.users.edit', compact('user'));
    }

    public function updateUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'role' => 'required|in:user,moderator,admin',
        ]);

        $user->update($validated);

        $this->logAction('user.update', 'User', $user->id, $validated);

        return redirect()->route('admin.users')->with('success', "User {$user->name} updated.");
    }

    public function banUser(Request $request, User $user)
    {
        if ($user->id === auth()->id()) {
            return back()->with('error', 'You cannot ban yourself.');
        }

        $user->ban($request->reason);

        $this->logAction('user.ban', 'User', $user->id, ['reason' => $request->reason]);

        return back()->with('success', "User {$user->name} has been banned.");
    }

    public function unbanUser(User $user)
    {
        $user->unban();

        $this->logAction('user.unban', 'User', $user->id);

        return back()->with('success', "User {$user->name} has been unbanned.");
    }

    public function resetTwoFactor(User $user)
    {
        if (! $user->hasTwoFactorEnabled()) {
            return back()->with('error', 'This user does not have 2FA enabled.');
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->logAction('2fa.admin-reset', 'User', $user->id);

        return back()->with('success', "Two-factor authentication has been reset for {$user->name}.");
    }

    public function servers()
    {
        $servers = Server::withCount(['sessions', 'statistics', 'mods'])->get();

        return view('admin.servers.index', compact('servers'));
    }

    public function storeServer(Request $request, BattleMetricsService $battleMetrics, ReforgerWorkshopService $workshop)
    {
        $validated = $request->validate([
            'battlemetrics_id' => 'required|string|max:50',
            'sync_mods' => 'nullable|boolean',
        ]);

        $serverId = $validated['battlemetrics_id'];

        // Check if server already exists
        if (Server::where('battlemetrics_id', $serverId)->exists()) {
            return back()->with('error', 'This server is already being tracked.');
        }

        // Fetch server data from BattleMetrics
        $bmServer = $battleMetrics->getServerWithDetails($serverId);

        if (!$bmServer) {
            return back()->with('error', 'Could not find server on BattleMetrics. Please check the ID.');
        }

        // Create server from BattleMetrics data
        $server = Server::syncFromBattleMetrics($bmServer);

        // Sync mods if requested
        if ($request->boolean('sync_mods')) {
            $mods = $battleMetrics->getServerMods($serverId);

            foreach ($mods as $modData) {
                $mod = Mod::syncFromBattleMetrics($modData);

                if (!$server->mods()->where('mod_id', $mod->id)->exists()) {
                    $server->mods()->attach($mod->id, [
                        'load_order' => $modData['load_order'] ?? 0,
                        'is_required' => true,
                    ]);
                }
            }

            // Sync additional data from Reforger Workshop
            $synced = $workshop->syncServerMods($server);
            Log::info("Synced {$synced} mods for new server {$server->name}");
        }

        return redirect()->route('admin.servers')->with('success', "Server '{$server->name}' added successfully.");
    }

    public function destroyServer(Server $server)
    {
        $serverName = $server->name;

        // Delete related data
        $server->statistics()->delete();
        $server->sessions()->delete();
        $server->mods()->detach();

        // Delete the server
        $server->delete();

        return redirect()->route('admin.servers')->with('success', "Server '{$serverName}' has been removed.");
    }

    public function clearCache()
    {
        Cache::flush();

        return back()->with('success', 'Cache cleared successfully.');
    }

    public function syncMods(Server $server, ReforgerWorkshopService $workshop)
    {
        $synced = $workshop->syncServerMods($server);

        return back()->with('success', "Synced {$synced} mods from Reforger Workshop for {$server->name}.");
    }

    public function settings()
    {
        $groupedSettings = SiteSetting::getAllGrouped();

        return view('admin.settings', compact('groupedSettings'));
    }

    public function updateSettings(Request $request)
    {
        $settings = SiteSetting::all();
        $changed = [];

        foreach ($settings as $setting) {
            $key = $setting->key;

            if ($setting->type === 'boolean') {
                $newValue = $request->has($key) ? '1' : '0';
            } elseif ($setting->type === 'json') {
                $newValue = json_encode($request->input($key, []));
            } else {
                $newValue = $request->input($key, $setting->value);
            }

            if ($setting->value !== $newValue) {
                $changed[$key] = ['old' => $setting->value, 'new' => $newValue];
                $setting->update(['value' => $newValue]);
            }
        }

        SiteSetting::clearCache();

        if (!empty($changed)) {
            $this->logAction('settings.update', null, null, ['changed' => array_keys($changed)]);
        }

        return back()->with('success', 'Settings saved successfully.');
    }

    public function auditLog(Request $request)
    {
        $query = \App\Models\AdminAuditLog::with('user')->latest();

        if ($request->filled('action')) {
            $query->where('action', $request->action);
        }

        if ($request->filled('user_id')) {
            $query->where('user_id', $request->user_id);
        }

        if ($request->filled('date_from')) {
            $query->whereDate('created_at', '>=', $request->date_from);
        }

        if ($request->filled('date_to')) {
            $query->whereDate('created_at', '<=', $request->date_to);
        }

        if ($request->filled('search')) {
            $query->where('action', 'like', '%' . $request->search . '%');
        }

        $logs = $query->paginate(50);
        $actions = \App\Models\AdminAuditLog::select('action')->distinct()->pluck('action');
        $users = User::whereIn('role', ['admin', 'moderator', 'gm'])->orderBy('name')->get();

        if ($request->input('export') === 'csv') {
            return $this->exportAuditLogCsv($query);
        }

        return view('admin.audit-log', compact('logs', 'actions', 'users'));
    }

    protected function exportAuditLogCsv($query)
    {
        $logs = $query->limit(5000)->get();

        $callback = function () use ($logs) {
            $file = fopen('php://output', 'w');
            fputcsv($file, ['Date', 'User', 'Action', 'Target Type', 'Target ID', 'IP Address', 'Metadata']);

            foreach ($logs as $log) {
                fputcsv($file, [
                    $log->created_at->toIso8601String(),
                    $log->user?->name ?? 'System',
                    $log->action,
                    $log->target_type ?? '',
                    $log->target_id ?? '',
                    $log->ip_address ?? '',
                    $log->metadata ? json_encode($log->metadata) : '',
                ]);
            }

            fclose($file);
        };

        return response()->stream($callback, 200, [
            'Content-Type' => 'text/csv',
            'Content-Disposition' => 'attachment; filename="audit-log-' . now()->format('Y-m-d') . '.csv"',
        ]);
    }
}
