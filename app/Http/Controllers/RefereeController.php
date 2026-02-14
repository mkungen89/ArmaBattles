<?php

namespace App\Http\Controllers;

use App\Models\MatchReport;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Services\DiscordWebhookService;
use App\Traits\LogsAdminActions;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;

class RefereeController extends Controller
{
    use LogsAdminActions;

    /**
     * Show referee dashboard
     */
    public function index()
    {
        $user = auth()->user();

        // Get all active tournaments
        $activeTournaments = Tournament::whereIn('status', ['registration_open', 'registration_closed', 'in_progress'])
            ->withCount('matches')
            ->orderBy('starts_at', 'desc')
            ->get();

        // Get matches that need refereeing (scheduled or in progress, not yet reported)
        $upcomingMatches = TournamentMatch::with(['tournament', 'team1', 'team2', 'reports'])
            ->whereIn('status', ['scheduled', 'in_progress'])
            ->whereDoesntHave('reports', function ($query) {
                $query->whereIn('status', ['submitted', 'approved']);
            })
            ->orderBy('scheduled_at', 'asc')
            ->limit(20)
            ->get();

        // Get referee's recent reports
        $myReports = MatchReport::with(['match.tournament', 'match.team1', 'match.team2', 'winningTeam'])
            ->where('referee_id', $user->id)
            ->orderBy('reported_at', 'desc')
            ->limit(10)
            ->get();

        // Get disputed matches that need attention
        $disputedMatches = TournamentMatch::with(['tournament', 'team1', 'team2', 'reports.referee'])
            ->whereHas('reports', function ($query) {
                $query->where('status', 'disputed');
            })
            ->orWhere('status', 'disputed')
            ->orderBy('updated_at', 'desc')
            ->get();

        return view('referee.dashboard', compact('activeTournaments', 'upcomingMatches', 'myReports', 'disputedMatches'));
    }

    /**
     * Show match report form
     */
    public function showReportForm(TournamentMatch $match)
    {
        $match->load(['tournament', 'team1', 'team2', 'reports']);

        return view('referee.report-match', compact('match'));
    }

    /**
     * Submit match report
     */
    public function submitReport(Request $request, TournamentMatch $match)
    {
        $validated = $request->validate([
            'winning_team_id' => 'required|exists:teams,id',
            'team1_score' => 'required|integer|min:0',
            'team2_score' => 'required|integer|min:0',
            'notes' => 'nullable|string|max:2000',
            'incidents' => 'nullable|array',
            'incidents.*.type' => 'required_with:incidents|string',
            'incidents.*.description' => 'required_with:incidents|string',
            'incidents.*.player_name' => 'nullable|string',
            'incidents.*.timestamp' => 'nullable|string',
        ]);

        DB::transaction(function () use ($match, $validated) {
            // Create match report
            $report = MatchReport::create([
                'match_id' => $match->id,
                'referee_id' => auth()->id(),
                'winning_team_id' => $validated['winning_team_id'],
                'team1_score' => $validated['team1_score'],
                'team2_score' => $validated['team2_score'],
                'notes' => $validated['notes'] ?? null,
                'incidents' => $validated['incidents'] ?? [],
                'status' => 'submitted',
                'reported_at' => now(),
            ]);

            // Update match with results
            $match->update([
                'winner_id' => $validated['winning_team_id'],
                'team1_score' => $validated['team1_score'],
                'team2_score' => $validated['team2_score'],
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Log referee action
            $this->logAction(
                'referee.match-reported',
                'TournamentMatch',
                $match->id,
                [
                    'report_id' => $report->id,
                    'tournament' => $match->tournament->name,
                    'winner' => $match->winner->name,
                    'score' => "{$validated['team1_score']} - {$validated['team2_score']}",
                    'incidents_count' => count($validated['incidents'] ?? []),
                ]
            );
        });

        // Send Discord notification
        try {
            $discord = app(DiscordWebhookService::class);
            $match->load(['tournament', 'team1', 'team2', 'winner']);

            $discord->sendMatchResult([
                'team1_name' => $match->team1->name,
                'team2_name' => $match->team2->name,
                'team1_score' => $validated['team1_score'],
                'team2_score' => $validated['team2_score'],
                'tournament_name' => $match->tournament->name,
                'round' => $match->round ?? 'Unknown',
            ]);
        } catch (\Exception $e) {
            // Don't fail the request if Discord notification fails
            \Log::warning('Discord match result notification failed', ['error' => $e->getMessage()]);
        }

        return redirect()->route('referee.dashboard')
            ->with('success', 'Match report submitted successfully!');
    }

    /**
     * View specific match report
     */
    public function viewReport(MatchReport $report)
    {
        $report->load(['match.tournament', 'match.team1', 'match.team2', 'referee', 'winningTeam']);

        return view('referee.view-report', compact('report'));
    }

    /**
     * Approve a match report (admin/senior referee only)
     */
    public function approveReport(MatchReport $report)
    {
        if (! auth()->user()->isAdmin()) {
            abort(403, 'Only administrators can approve reports');
        }

        $report->update(['status' => 'approved']);

        $this->logAction(
            'referee.report-approved',
            'MatchReport',
            $report->id,
            [
                'match_id' => $report->match_id,
                'original_referee' => $report->referee->name,
            ]
        );

        return back()->with('success', 'Match report approved!');
    }

    /**
     * Dispute a match report
     */
    public function disputeReport(Request $request, MatchReport $report)
    {
        $validated = $request->validate([
            'dispute_reason' => 'required|string|max:1000',
        ]);

        $report->update([
            'status' => 'disputed',
            'incidents' => array_merge($report->incidents ?? [], [[
                'type' => 'dispute',
                'description' => $validated['dispute_reason'],
                'disputed_by' => auth()->id(),
                'disputed_at' => now()->toISOString(),
            ]]),
        ]);

        $report->match->update(['status' => 'disputed']);

        $this->logAction(
            'referee.report-disputed',
            'MatchReport',
            $report->id,
            [
                'match_id' => $report->match_id,
                'reason' => $validated['dispute_reason'],
            ]
        );

        return back()->with('warning', 'Match report has been disputed. Admin review required.');
    }

    /**
     * Update a match report (only if not approved/finalized)
     */
    public function updateReport(Request $request, MatchReport $report)
    {
        // Cannot edit approved reports
        if ($report->status === 'approved') {
            abort(403, 'Cannot edit an approved match report');
        }

        // Only the original referee can edit their report
        if ($report->referee_id !== auth()->id() && ! auth()->user()->isAdmin()) {
            abort(403, 'You can only edit your own reports');
        }

        $validated = $request->validate([
            'winning_team_id' => 'sometimes|exists:teams,id',
            'team1_score' => 'sometimes|integer|min:0',
            'team2_score' => 'sometimes|integer|min:0',
            'notes' => 'sometimes|nullable|string|max:2000',
        ]);

        $report->update($validated);

        $this->logAction(
            'referee.report-updated',
            'MatchReport',
            $report->id,
            [
                'match_id' => $report->match_id,
                'changes' => array_keys($validated),
            ]
        );

        return back()->with('success', 'Match report updated successfully.');
    }

    /**
     * Forfeit a match due to no-show or withdrawal
     */
    public function forfeitMatch(Request $request, TournamentMatch $match)
    {
        $validated = $request->validate([
            'forfeiting_team_id' => 'required|exists:teams,id',
            'reason' => 'required|string|max:500',
        ]);

        // Determine winner (the team that didn't forfeit)
        $winningTeamId = $validated['forfeiting_team_id'] == $match->team1_id
            ? $match->team2_id
            : $match->team1_id;

        DB::transaction(function () use ($match, $validated, $winningTeamId) {
            // Create forfeit report
            $report = MatchReport::create([
                'match_id' => $match->id,
                'referee_id' => auth()->id(),
                'winning_team_id' => $winningTeamId,
                'team1_score' => 0,
                'team2_score' => 0,
                'notes' => "Forfeit: {$validated['reason']}",
                'incidents' => [[
                    'type' => 'forfeit',
                    'forfeiting_team_id' => $validated['forfeiting_team_id'],
                    'reason' => $validated['reason'],
                    'timestamp' => now()->toISOString(),
                ]],
                'status' => 'approved', // Forfeits are auto-approved
                'reported_at' => now(),
            ]);

            // Update match
            $match->update([
                'winner_id' => $winningTeamId,
                'team1_score' => 0,
                'team2_score' => 0,
                'status' => 'completed',
                'completed_at' => now(),
            ]);

            // Log action
            $this->logAction(
                'referee.match-forfeited',
                'TournamentMatch',
                $match->id,
                [
                    'forfeiting_team_id' => $validated['forfeiting_team_id'],
                    'winning_team_id' => $winningTeamId,
                    'reason' => $validated['reason'],
                ]
            );
        });

        return redirect()->route('referee.dashboard')
            ->with('success', 'Match has been forfeited.');
    }
}
