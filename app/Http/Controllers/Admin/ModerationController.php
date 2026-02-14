<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\ModeratorNote;
use App\Models\PlayerWarning;
use App\Models\User;
use App\Services\ModerationService;
use App\Traits\LogsAdminActions;
use Carbon\Carbon;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\DB;

class ModerationController extends Controller
{
    use LogsAdminActions;

    protected ModerationService $moderationService;

    public function __construct(ModerationService $moderationService)
    {
        $this->moderationService = $moderationService;
    }

    /**
     * Show moderation dashboard
     */
    public function index()
    {
        $queue = $this->moderationService->getModerationQueue();
        $recentWarnings = PlayerWarning::with(['user', 'moderator'])
            ->orderBy('created_at', 'desc')
            ->limit(10)
            ->get();

        return view('admin.moderation.index', [
            'queue' => $queue,
            'recentWarnings' => $recentWarnings,
        ]);
    }

    /**
     * Show player warnings
     */
    public function warnings(Request $request)
    {
        $query = PlayerWarning::with(['user', 'moderator']);

        if ($request->filled('severity')) {
            $query->severity($request->severity);
        }

        if ($request->has('active_only')) {
            $query->active();
        }

        $warnings = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.moderation.warnings', ['warnings' => $warnings]);
    }

    /**
     * Issue a warning
     */
    public function issueWarning(Request $request, User $user)
    {
        $validated = $request->validate([
            'warning_type' => 'required|in:spam,toxicity,cheating_accusation,inappropriate_behavior',
            'reason' => 'required|string|max:1000',
            'severity' => 'required|in:low,medium,high,critical',
            'evidence' => 'nullable|string',
            'expires_in_days' => 'nullable|integer|min:1|max:365',
        ]);

        $expiresAt = $validated['expires_in_days'] ?? null
            ? now()->addDays($validated['expires_in_days'])
            : null;

        $warning = $this->moderationService->warnPlayer(
            $user,
            Auth::user(),
            $validated['warning_type'],
            $validated['reason'],
            $validated['severity'],
            $validated['evidence'] ?? null,
            $expiresAt
        );

        $this->logAction('moderation.warning-issued', 'PlayerWarning', $warning->id, [
            'user_id' => $user->id,
            'warning_type' => $validated['warning_type'],
            'severity' => $validated['severity'],
        ]);

        return redirect()->back()
            ->with('success', "Warning issued to {$user->name}.");
    }

    /**
     * Show moderator notes
     */
    public function notes(Request $request)
    {
        $query = ModeratorNote::with(['user', 'moderator']);

        if ($request->filled('category')) {
            $query->category($request->category);
        }

        if ($request->has('flagged_only')) {
            $query->flagged();
        }

        $notes = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.moderation.notes', ['notes' => $notes]);
    }

    /**
     * Add a moderator note
     */
    public function addNote(Request $request, User $user)
    {
        $validated = $request->validate([
            'note' => 'required|string|max:2000',
            'category' => 'required|in:positive,negative,neutral,watchlist',
            'is_flagged' => 'boolean',
        ]);

        $note = ModeratorNote::create([
            'user_id' => $user->id,
            'moderator_id' => Auth::id(),
            'note' => $validated['note'],
            'category' => $validated['category'],
            'is_flagged' => $validated['is_flagged'] ?? false,
        ]);

        $this->logAction('moderation.note-added', 'ModeratorNote', $note->id, [
            'user_id' => $user->id,
            'category' => $validated['category'],
        ]);

        return redirect()->back()
            ->with('success', 'Moderator note added.');
    }

    /**
     * Show flagged chat messages
     */
    public function flaggedChat(Request $request)
    {
        $query = DB::table('chat_events')->where('is_flagged', true);

        if ($request->has('reviewed')) {
            $query->whereNotNull('reviewed_at');
        } else {
            $query->whereNull('reviewed_at');
        }

        $messages = $query->orderBy('created_at', 'desc')->paginate(20);

        return view('admin.moderation.flagged-chat', ['messages' => $messages]);
    }

    /**
     * Review a flagged chat message
     */
    public function reviewChat(Request $request, int $chatId)
    {
        $validated = $request->validate([
            'action' => 'required|in:dismiss,warn,ban',
        ]);

        DB::table('chat_events')
            ->where('id', $chatId)
            ->update([
                'reviewed_at' => now(),
                'reviewed_by' => Auth::id(),
            ]);

        $this->logAction('moderation.chat-reviewed', 'ChatEvent', $chatId, [
            'action' => $validated['action'],
        ]);

        return redirect()->back()
            ->with('success', 'Chat message reviewed.');
    }

    /**
     * Mass ban import
     */
    public function importBans(Request $request)
    {
        $validated = $request->validate([
            'source' => 'required|string|max:255',
            'ban_list' => 'required|string',
        ]);

        $lines = explode("\n", $validated['ban_list']);
        $imported = 0;

        foreach ($lines as $line) {
            $line = trim($line);
            if (empty($line)) continue;

            // Format: steam_id:reason or player_uuid:reason
            $parts = explode(':', $line, 2);
            if (count($parts) < 1) continue;

            $identifier = $parts[0];
            $reason = $parts[1] ?? "Imported from {$validated['source']}";

            // Try to find user by steam_id or player_uuid
            $user = User::where('steam_id', $identifier)
                ->orWhere('player_uuid', $identifier)
                ->first();

            if ($user && !$user->is_banned) {
                $user->ban($reason, null, 'permanent', Auth::user());
                $imported++;
            }
        }

        $this->logAction('moderation.mass-ban-import', null, null, [
            'source' => $validated['source'],
            'count' => $imported,
        ]);

        return redirect()->back()
            ->with('success', "Imported {$imported} ban(s) from {$validated['source']}.");
    }
}
