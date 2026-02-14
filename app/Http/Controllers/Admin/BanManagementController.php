<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\BanAppeal;
use App\Models\BanHistory;
use App\Models\User;
use App\Services\BanService;
use App\Traits\LogsAdminActions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class BanManagementController extends Controller
{
    use LogsAdminActions;

    protected BanService $banService;

    public function __construct(BanService $banService)
    {
        $this->banService = $banService;
    }

    /**
     * Show ban management dashboard
     */
    public function index(Request $request)
    {
        // Get pending ban appeals count
        $pendingAppeals = BanAppeal::where('status', 'pending')->count();

        // Get currently banned users count
        $bannedUsers = User::where('is_banned', true)->count();

        // Get temp banned users count
        $tempBannedUsers = User::where('is_banned', true)
            ->whereNotNull('banned_until')
            ->where('banned_until', '>', now())
            ->count();

        // Get recent ban history
        $recentBans = BanHistory::with(['user', 'admin'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.bans.index', [
            'pendingAppeals' => $pendingAppeals,
            'bannedUsers' => $bannedUsers,
            'tempBannedUsers' => $tempBannedUsers,
            'recentBans' => $recentBans,
        ]);
    }

    /**
     * Show all banned users
     */
    public function bannedUsers(Request $request)
    {
        $query = User::where('is_banned', true);

        // Filter by ban type
        if ($request->has('type')) {
            match ($request->type) {
                'permanent' => $query->whereNull('banned_until'),
                'temporary' => $query->whereNotNull('banned_until')->where('banned_until', '>', now()),
                'expired' => $query->whereNotNull('banned_until')->where('banned_until', '<=', now()),
                default => null,
            };
        }

        // Search
        if ($request->has('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('steam_id', 'like', "%{$search}%")
                    ->orWhere('player_uuid', 'like', "%{$search}%");
            });
        }

        $users = $query->orderBy('banned_at', 'desc')->paginate(20);

        return view('admin.bans.users', [
            'users' => $users,
        ]);
    }

    /**
     * Show ban appeals
     */
    public function appeals(Request $request)
    {
        $query = BanAppeal::with(['user', 'reviewer']);

        // Filter by status
        if ($request->has('status')) {
            $query->where('status', $request->status);
        } else {
            // Default to pending
            $query->where('status', 'pending');
        }

        $appeals = $query->orderBy('created_at', 'asc')->paginate(20);

        return view('admin.bans.appeals', [
            'appeals' => $appeals,
        ]);
    }

    /**
     * Show a specific ban appeal
     */
    public function showAppeal(BanAppeal $appeal)
    {
        $appeal->load(['user', 'reviewer']);

        return view('admin.bans.appeal-detail', [
            'appeal' => $appeal,
        ]);
    }

    /**
     * Approve a ban appeal
     */
    public function approveAppeal(Request $request, BanAppeal $appeal)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $appeal->update([
            'status' => 'approved',
            'reviewed_by' => Auth::id(),
            'admin_response' => $validated['admin_response'],
            'reviewed_at' => now(),
        ]);

        // Unban the user
        $this->banService->unban($appeal->user, Auth::user());

        $this->logAction('ban-appeal.approved', 'BanAppeal', $appeal->id, [
            'user_id' => $appeal->user_id,
            'response' => $validated['admin_response'],
        ]);

        return redirect()->route('admin.bans.appeals')
            ->with('success', "Ban appeal approved and user {$appeal->user->name} has been unbanned.");
    }

    /**
     * Reject a ban appeal
     */
    public function rejectAppeal(Request $request, BanAppeal $appeal)
    {
        $validated = $request->validate([
            'admin_response' => 'required|string|max:1000',
        ]);

        $appeal->update([
            'status' => 'rejected',
            'reviewed_by' => Auth::id(),
            'admin_response' => $validated['admin_response'],
            'reviewed_at' => now(),
        ]);

        $this->logAction('ban-appeal.rejected', 'BanAppeal', $appeal->id, [
            'user_id' => $appeal->user_id,
            'response' => $validated['admin_response'],
        ]);

        return redirect()->route('admin.bans.appeals')
            ->with('success', "Ban appeal for {$appeal->user->name} has been rejected.");
    }

    /**
     * Show ban history for a user
     */
    public function userHistory(User $user)
    {
        $history = $user->banHistory()
            ->with('admin')
            ->orderBy('created_at', 'desc')
            ->get();

        return view('admin.bans.user-history', [
            'user' => $user,
            'history' => $history,
        ]);
    }

    /**
     * Ban a user (admin action)
     */
    public function banUser(Request $request, User $user)
    {
        $validated = $request->validate([
            'ban_type' => 'required|in:permanent,temporary',
            'reason' => 'required|string|max:1000',
            'banned_until' => 'required_if:ban_type,temporary|nullable|date|after:now',
        ]);

        if ($validated['ban_type'] === 'temporary') {
            $this->banService->banTemporarily(
                $user,
                Carbon::parse($validated['banned_until']),
                $validated['reason'],
                Auth::user()
            );
        } else {
            $this->banService->banPermanently(
                $user,
                $validated['reason'],
                Auth::user()
            );
        }

        $this->logAction('user.banned', 'User', $user->id, [
            'ban_type' => $validated['ban_type'],
            'reason' => $validated['reason'],
            'banned_until' => $validated['banned_until'] ?? null,
        ]);

        return redirect()->back()
            ->with('success', "User {$user->name} has been banned.");
    }

    /**
     * Unban a user (admin action)
     */
    public function unbanUser(User $user)
    {
        $this->banService->unban($user, Auth::user());

        $this->logAction('user.unbanned', 'User', $user->id);

        return redirect()->back()
            ->with('success', "User {$user->name} has been unbanned.");
    }

    /**
     * Show hardware ID ban form
     */
    public function hardwareBanForm()
    {
        $bannedHardwareIds = $this->banService->getBannedHardwareIds();

        return view('admin.bans.hardware', [
            'bannedHardwareIds' => $bannedHardwareIds,
        ]);
    }

    /**
     * Ban by hardware ID
     */
    public function banByHardwareId(Request $request)
    {
        $validated = $request->validate([
            'hardware_id' => 'required|string|max:255',
            'reason' => 'required|string|max:1000',
        ]);

        $this->banService->banByHardwareId(
            $validated['hardware_id'],
            $validated['reason'],
            Auth::user()
        );

        $this->logAction('hardware-id.banned', 'BanHistory', null, [
            'hardware_id' => $validated['hardware_id'],
            'reason' => $validated['reason'],
        ]);

        return redirect()->back()
            ->with('success', "Hardware ID {$validated['hardware_id']} has been banned.");
    }

    /**
     * Show IP ban form
     */
    public function ipBanForm()
    {
        $bannedIps = $this->banService->getBannedIpAddresses();

        return view('admin.bans.ip', [
            'bannedIps' => $bannedIps,
        ]);
    }

    /**
     * Ban by IP address
     */
    public function banByIpAddress(Request $request)
    {
        $validated = $request->validate([
            'ip_address' => 'required|ip',
            'reason' => 'required|string|max:1000',
        ]);

        $this->banService->banByIpAddress(
            $validated['ip_address'],
            $validated['reason'],
            Auth::user()
        );

        $this->logAction('ip-address.banned', 'BanHistory', null, [
            'ip_address' => $validated['ip_address'],
            'reason' => $validated['reason'],
        ]);

        return redirect()->back()
            ->with('success', "IP address {$validated['ip_address']} has been banned.");
    }

    /**
     * Show mass ban import form
     */
    public function importForm()
    {
        return view('admin.bans.import');
    }

    /**
     * Import bans from external list
     */
    public function importBans(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'ban_list' => 'required|string',
        ]);

        // Parse the ban list (expected format: type:value:reason per line)
        $lines = explode("\n", $validated['ban_list']);
        $bans = [];

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) {
                continue;
            }

            $parts = explode(':', $line, 3);
            if (count($parts) >= 2) {
                $bans[] = [
                    'type' => $parts[0],
                    'value' => $parts[1],
                    'reason' => $parts[2] ?? "Imported from {$validated['source']}",
                ];
            }
        }

        $imported = $this->banService->importBans($bans, $validated['source'], Auth::user());

        $this->logAction('bans.imported', 'BanHistory', null, [
            'source' => $validated['source'],
            'count' => $imported,
        ]);

        return redirect()->route('admin.bans.index')
            ->with('success', "Imported {$imported} ban(s) from {$validated['source']}.");
    }
}
