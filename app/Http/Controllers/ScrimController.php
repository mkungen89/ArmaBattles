<?php

namespace App\Http\Controllers;

use App\Models\ScrimInvitation;
use App\Models\ScrimMatch;
use App\Models\Team;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class ScrimController extends Controller
{
    /**
     * Display scrim listing
     */
    public function index()
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        // Get team's scrims (if user is in a team)
        $upcomingScrims = collect();
        $completedScrims = collect();
        $pendingInvitations = collect();

        if ($userTeam) {
            $upcomingScrims = ScrimMatch::with(['team1', 'team2', 'server'])
                ->where(function ($q) use ($userTeam) {
                    $q->where('team1_id', $userTeam->id)
                        ->orWhere('team2_id', $userTeam->id);
                })
                ->whereIn('status', ['pending', 'scheduled', 'in_progress'])
                ->orderBy('scheduled_at')
                ->get();

            $completedScrims = ScrimMatch::with(['team1', 'team2', 'winner'])
                ->where(function ($q) use ($userTeam) {
                    $q->where('team1_id', $userTeam->id)
                        ->orWhere('team2_id', $userTeam->id);
                })
                ->whereIn('status', ['completed', 'cancelled'])
                ->orderByDesc('completed_at')
                ->limit(10)
                ->get();

            $pendingInvitations = ScrimInvitation::with(['scrimMatch.team1', 'scrimMatch.team2', 'invitingTeam'])
                ->where('invited_team_id', $userTeam->id)
                ->where('status', 'pending')
                ->where('expires_at', '>', now())
                ->orderByDesc('created_at')
                ->get();
        }

        return view('scrims.index', compact('upcomingScrims', 'completedScrims', 'pendingInvitations', 'userTeam'));
    }

    /**
     * Show create scrim form
     */
    public function create()
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam) {
            return redirect()->route('scrims.index')->with('error', 'You must be in a team to create scrims.');
        }

        if (! $userTeam->isUserCaptainOrOfficer($user)) {
            return redirect()->route('scrims.index')->with('error', 'Only team captains and officers can create scrims.');
        }

        // Get all active teams except user's team
        $teams = Team::where('is_active', true)
            ->where('id', '!=', $userTeam->id)
            ->orderBy('name')
            ->get();

        return view('scrims.create', compact('teams', 'userTeam'));
    }

    /**
     * Send scrim invitation
     */
    public function invite(Request $request)
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam) {
            return back()->with('error', 'You must be in a team to send scrim invitations.');
        }

        if (! $userTeam->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only team captains and officers can send scrim invitations.');
        }

        $validated = $request->validate([
            'invited_team_id' => 'required|exists:teams,id',
            'proposed_time' => 'required|date|after:now',
            'message' => 'nullable|string|max:1000',
        ]);

        // Cannot invite yourself
        if ($validated['invited_team_id'] == $userTeam->id) {
            return back()->with('error', 'You cannot invite your own team.');
        }

        // Create invitation (match created on acceptance)
        ScrimInvitation::create([
            'scrim_match_id' => null, // Created when accepted
            'inviting_team_id' => $userTeam->id,
            'invited_team_id' => $validated['invited_team_id'],
            'proposed_time' => $validated['proposed_time'],
            'message' => $validated['message'] ?? null,
            'status' => 'pending',
            'expires_at' => now()->addDays(7),
        ]);

        return redirect()->route('scrims.index')->with('success', 'Scrim invitation sent!');
    }

    /**
     * Store a new scrim challenge
     */
    public function store(Request $request)
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam) {
            return back()->with('error', 'You must be in a team to create scrims.');
        }

        if (! $userTeam->isUserCaptainOrOfficer($user)) {
            return back()->with('error', 'Only team captains and officers can create scrims.');
        }

        $validated = $request->validate([
            'opponent_team_id' => 'required|exists:teams,id',
            'scheduled_at' => 'required|date|after:now',
            'map' => 'nullable|string|max:255',
            'duration_minutes' => 'nullable|integer|min:10|max:180',
            'password' => 'nullable|string|max:255',
            'notes' => 'nullable|string|max:1000',
        ]);

        // Cannot challenge yourself
        if ($validated['opponent_team_id'] == $userTeam->id) {
            return back()->with('error', 'You cannot challenge your own team.');
        }

        DB::transaction(function () use ($validated, $userTeam, $user) {
            // Create scrim match
            $scrim = ScrimMatch::create([
                'team1_id' => $userTeam->id,
                'team2_id' => $validated['opponent_team_id'],
                'created_by' => $user->id,
                'scheduled_at' => $validated['scheduled_at'],
                'status' => 'pending',
                'map' => $validated['map'] ?? null,
                'duration_minutes' => $validated['duration_minutes'] ?? 60,
                'password' => $validated['password'] ?? null,
                'notes' => $validated['notes'] ?? null,
            ]);

            // Create invitation
            ScrimInvitation::create([
                'scrim_match_id' => $scrim->id,
                'inviting_team_id' => $userTeam->id,
                'invited_team_id' => $validated['opponent_team_id'],
                'status' => 'pending',
                'expires_at' => now()->addDays(7),
            ]);
        });

        return redirect()->route('scrims.index')->with('success', 'Scrim challenge sent!');
    }

    /**
     * Show scrim details
     */
    public function show(ScrimMatch $scrim)
    {
        $scrim->load(['team1.activeMembers.user', 'team2.activeMembers.user', 'creator', 'winner', 'server', 'invitation']);

        $user = auth()->user();
        $userTeam = $user->activeTeam;
        $canManage = false;

        if ($userTeam) {
            $canManage = ($scrim->team1_id === $userTeam->id || $scrim->team2_id === $userTeam->id)
                         && $userTeam->isUserCaptainOrOfficer($user);
        }

        return view('scrims.show', compact('scrim', 'userTeam', 'canManage'));
    }

    /**
     * Accept scrim invitation
     */
    public function accept(ScrimInvitation $invitation)
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam || $invitation->invited_team_id !== $userTeam->id) {
            return back()->with('error', 'You cannot accept this invitation.');
        }

        if (! $userTeam->isUserCaptainOrOfficer($user)) {
            return back()->with('error', 'Only team captains and officers can accept scrim invitations.');
        }

        if (! $invitation->canRespond()) {
            abort(403, 'This invitation has expired or already been responded to.');
        }

        DB::transaction(function () use ($invitation, $user) {
            // Create scrim match if it doesn't exist yet
            if (! $invitation->scrim_match_id) {
                $scrimMatch = ScrimMatch::create([
                    'team1_id' => $invitation->inviting_team_id,
                    'team2_id' => $invitation->invited_team_id,
                    'created_by' => $user->id,
                    'scheduled_at' => $invitation->proposed_time ?? now()->addDay(),
                    'status' => 'scheduled',
                ]);

                $invitation->update([
                    'scrim_match_id' => $scrimMatch->id,
                    'status' => 'accepted',
                    'responded_at' => now(),
                ]);
            } else {
                // Match already exists, just update status
                $invitation->update([
                    'status' => 'accepted',
                    'responded_at' => now(),
                ]);

                $invitation->scrimMatch->update([
                    'status' => 'scheduled',
                ]);
            }
        });

        $invitation->refresh();

        return redirect()->route('scrims.show', $invitation->scrimMatch)
            ->with('success', 'Scrim invitation accepted!');
    }

    /**
     * Decline scrim invitation
     */
    public function decline(ScrimInvitation $invitation)
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam || $invitation->invited_team_id !== $userTeam->id) {
            return back()->with('error', 'You cannot decline this invitation.');
        }

        if (! $userTeam->isUserCaptainOrOfficer($user)) {
            return back()->with('error', 'Only team captains and officers can decline scrim invitations.');
        }

        if (! $invitation->canRespond()) {
            abort(403, 'This invitation has expired or already been responded to.');
        }

        DB::transaction(function () use ($invitation) {
            $invitation->update([
                'status' => 'declined',
                'responded_at' => now(),
            ]);

            // Cancel match if it exists
            if ($invitation->scrimMatch) {
                $invitation->scrimMatch->update([
                    'status' => 'cancelled',
                ]);
            }
        });

        return redirect()->route('scrims.index')->with('success', 'Scrim invitation declined.');
    }

    /**
     * Cancel scrim match
     */
    public function cancel(ScrimMatch $scrim)
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam) {
            return back()->with('error', 'You must be in a team to cancel scrims.');
        }

        // Can only cancel if you're team1 or team2 captain/officer
        $canCancel = ($scrim->team1_id === $userTeam->id || $scrim->team2_id === $userTeam->id)
                     && $userTeam->isUserCaptainOrOfficer($user);

        if (! $canCancel) {
            return back()->with('error', 'You cannot cancel this scrim.');
        }

        if ($scrim->isCompleted()) {
            return back()->with('error', 'Cannot cancel a completed scrim.');
        }

        $scrim->update(['status' => 'cancelled']);

        return redirect()->route('scrims.index')->with('success', 'Scrim cancelled.');
    }

    /**
     * Report scrim result
     */
    public function reportResult(Request $request, ScrimMatch $scrim)
    {
        $user = auth()->user();
        $userTeam = $user->activeTeam;

        if (! $userTeam) {
            return back()->with('error', 'You must be in a team to report results.');
        }

        $canReport = ($scrim->team1_id === $userTeam->id || $scrim->team2_id === $userTeam->id)
                     && $userTeam->isUserCaptainOrOfficer($user);

        if (! $canReport) {
            return back()->with('error', 'You cannot report results for this scrim.');
        }

        if (! $scrim->isScheduled() && ! $scrim->isInProgress() && ! $scrim->isCompleted()) {
            return back()->with('error', 'Can only report results for scheduled, in-progress, or completed scrims.');
        }

        $validated = $request->validate([
            'team1_score' => 'required|integer|min:0',
            'team2_score' => 'required|integer|min:0',
        ]);

        $winnerId = null;
        if ($validated['team1_score'] > $validated['team2_score']) {
            $winnerId = $scrim->team1_id;
        } elseif ($validated['team2_score'] > $validated['team1_score']) {
            $winnerId = $scrim->team2_id;
        }

        // Check if this is the first report or if scores match
        if ($scrim->team1_score === null && $scrim->team2_score === null) {
            // First report - save scores and mark as completed
            $scrim->update([
                'status' => 'completed',
                'team1_score' => $validated['team1_score'],
                'team2_score' => $validated['team2_score'],
                'winner_id' => $winnerId,
                'completed_at' => now(),
            ]);
        } elseif ($scrim->team1_score == $validated['team1_score'] && $scrim->team2_score == $validated['team2_score']) {
            // Scores match - keep as completed
            $scrim->update([
                'status' => 'completed',
                'winner_id' => $winnerId,
                'completed_at' => now(),
            ]);
        } else {
            // Scores don't match - revert to in_progress (dispute)
            $scrim->update([
                'status' => 'in_progress',
                'completed_at' => null,
            ]);
            // Keep the first team's reported scores
        }

        return redirect()->route('scrims.show', $scrim)->with('success', 'Scrim result reported!');
    }
}
