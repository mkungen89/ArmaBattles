<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Server;
use App\Models\Tournament;
use App\Models\TournamentMatch;
use App\Models\TournamentRegistration;
use App\Services\TournamentBracketService;
use Illuminate\Http\Request;

class TournamentAdminController extends Controller
{
    public function __construct(
        protected TournamentBracketService $bracketService
    ) {}

    public function index(Request $request)
    {
        $query = Tournament::withCount(['approvedTeams', 'matches'])
            ->with('winner');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        $tournaments = $query->orderByDesc('created_at')->paginate(20);

        return view('admin.tournaments.index', compact('tournaments'));
    }

    public function create()
    {
        $servers = Server::where('is_visible', true)->orderBy('name')->get();

        return view('admin.tournaments.create', compact('servers'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'banner_url' => 'nullable|url',
            'format' => 'required|in:single_elimination,double_elimination,round_robin,swiss',
            'max_teams' => 'required|integer|min:4|max:128',
            'min_teams' => 'required|integer|min:2',
            'team_size' => 'required|integer|min:1|max:32',
            'swiss_rounds' => 'nullable|integer|min:3|max:10',
            'registration_starts_at' => 'required|date',
            'registration_ends_at' => 'required|date|after:registration_starts_at',
            'starts_at' => 'required|date|after:registration_ends_at',
            'server_id' => 'nullable|exists:servers,id',
            'require_approval' => 'boolean',
            'is_featured' => 'boolean',
            'prize_pool' => 'nullable|string|max:255',
            'stream_url' => 'nullable|url|max:255',
        ]);

        $tournament = Tournament::create([
            ...$validated,
            'created_by' => auth()->id(),
            'status' => 'draft',
            'require_approval' => $request->boolean('require_approval', true),
            'is_featured' => $request->boolean('is_featured', false),
        ]);

        return redirect()->route('admin.tournaments.show', $tournament)
            ->with('success', 'Tournament created!');
    }

    public function show(Tournament $tournament)
    {
        $tournament->load([
            'registrations' => fn ($q) => $q->with(['team.captain', 'team.activeMembers', 'approver']),  // Added: activeMembers and approver
            'matches' => fn ($q) => $q->with(['team1.captain', 'team2.captain', 'winner'])->orderBy('round')->orderBy('match_number'),  // Added: team captains
            'winner.captain',  // Added: winner team's captain
            'server',
            'creator',
        ]);

        $stats = [
            'total_registrations' => $tournament->registrations()->count(),
            'approved_teams' => $tournament->approvedTeams()->count(),
            'pending_teams' => $tournament->pendingTeams()->count(),
            'total_matches' => $tournament->matches()->count(),
            'completed_matches' => $tournament->matches()->where('status', 'completed')->count(),
        ];

        return view('admin.tournaments.show', compact('tournament', 'stats'));
    }

    public function edit(Tournament $tournament)
    {
        $servers = Server::where('is_visible', true)->orderBy('name')->get();

        return view('admin.tournaments.edit', compact('tournament', 'servers'));
    }

    public function update(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'description' => 'nullable|string',
            'rules' => 'nullable|string',
            'banner_url' => 'nullable|url',
            'max_teams' => 'required|integer|min:4|max:128',
            'min_teams' => 'required|integer|min:2',
            'team_size' => 'required|integer|min:1|max:32',
            'swiss_rounds' => 'nullable|integer|min:3|max:10',
            'registration_starts_at' => 'required|date',
            'registration_ends_at' => 'required|date|after:registration_starts_at',
            'starts_at' => 'required|date',
            'server_id' => 'nullable|exists:servers,id',
            'require_approval' => 'boolean',
            'is_featured' => 'boolean',
            'prize_pool' => 'nullable|string|max:255',
            'stream_url' => 'nullable|url|max:255',
        ]);

        $tournament->update([
            ...$validated,
            'require_approval' => $request->boolean('require_approval', true),
            'is_featured' => $request->boolean('is_featured', false),
        ]);

        return back()->with('success', 'Tournament updated!');
    }

    public function updateStatus(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'status' => 'required|in:draft,registration_open,registration_closed,in_progress,completed,cancelled',
        ]);

        // Validate status transitions
        $allowedTransitions = [
            'draft' => ['registration_open', 'cancelled'],
            'registration_open' => ['registration_closed', 'cancelled'],
            'registration_closed' => ['in_progress', 'registration_open', 'cancelled'],
            'in_progress' => ['completed', 'cancelled'],
            'completed' => [],
            'cancelled' => ['draft'],
        ];

        if (!in_array($validated['status'], $allowedTransitions[$tournament->status] ?? [])) {
            return back()->with('error', 'Invalid status transition.');
        }

        $tournament->update($validated);

        return back()->with('success', 'Status updated to ' . $tournament->status_text . '!');
    }

    public function registrations(Tournament $tournament)
    {
        $registrations = $tournament->registrations()
            ->with(['team.captain', 'team.activeMembers', 'approver'])
            ->orderByRaw("CASE WHEN status = 'pending' THEN 0 ELSE 1 END")
            ->orderBy('created_at')
            ->get();

        return view('admin.tournaments.registrations', compact('tournament', 'registrations'));
    }

    public function approveRegistration(TournamentRegistration $registration)
    {
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Can only approve pending registrations.');
        }

        $tournament = $registration->tournament;

        if ($tournament->approvedTeams()->count() >= $tournament->max_teams) {
            return back()->with('error', 'Tournament is full.');
        }

        $registration->update([
            'status' => 'approved',
            'approved_by' => auth()->id(),
            'approved_at' => now(),
        ]);

        return back()->with('success', 'Registration approved!');
    }

    public function rejectRegistration(Request $request, TournamentRegistration $registration)
    {
        if ($registration->status !== 'pending') {
            return back()->with('error', 'Can only reject pending registrations.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $registration->update([
            'status' => 'rejected',
            'rejection_reason' => $validated['rejection_reason'],
        ]);

        return back()->with('success', 'Registration rejected.');
    }

    public function updateSeeding(Request $request, Tournament $tournament)
    {
        $validated = $request->validate([
            'seeds' => 'required|array',
            'seeds.*' => 'exists:tournament_registrations,id',
        ]);

        foreach ($validated['seeds'] as $seed => $registrationId) {
            TournamentRegistration::where('id', $registrationId)
                ->where('tournament_id', $tournament->id)
                ->update(['seed' => $seed + 1]);
        }

        return back()->with('success', 'Seeding updated!');
    }

    public function generateBracket(Tournament $tournament)
    {
        if ($tournament->matches()->exists()) {
            return back()->with('error', 'Bracket already generated. Delete existing matches first.');
        }

        if ($tournament->approvedTeams()->count() < $tournament->min_teams) {
            return back()->with('error', 'Not enough platoons registered (minimum ' . $tournament->min_teams . ').');
        }

        $this->bracketService->generateBracket($tournament);

        if ($tournament->status !== 'in_progress') {
            $tournament->update(['status' => 'in_progress']);
        }

        return back()->with('success', 'Bracket generated! Tournament is now in progress.');
    }

    public function resetBracket(Tournament $tournament)
    {
        if ($tournament->status === 'completed') {
            return back()->with('error', 'Cannot reset a completed tournament.');
        }

        $tournament->matches()->delete();
        $tournament->update(['winner_team_id' => null]);

        return back()->with('success', 'Bracket reset. You can now generate a new bracket.');
    }

    public function matches(Tournament $tournament)
    {
        $matches = $tournament->matches()
            ->with(['team1', 'team2', 'winner'])
            ->orderBy('bracket')
            ->orderBy('round')
            ->orderBy('match_number')
            ->get();

        // Group by round for display
        $groupedMatches = $matches->groupBy(function ($match) {
            if ($match->bracket === 'grand_final') {
                return 'Grand Final';
            }
            return $match->round_label;
        });

        return view('admin.tournaments.matches', compact('tournament', 'matches', 'groupedMatches'));
    }

    public function editMatch(TournamentMatch $match)
    {
        $match->load(['team1', 'team2', 'winner', 'tournament', 'games']);

        return view('admin.tournaments.match-edit', compact('match'));
    }

    public function updateMatch(Request $request, TournamentMatch $match)
    {
        $validated = $request->validate([
            'team1_score' => 'nullable|integer|min:0',
            'team2_score' => 'nullable|integer|min:0',
            'winner_id' => 'nullable|exists:teams,id',
            'status' => 'required|in:pending,scheduled,in_progress,completed,disputed,cancelled',
            'scheduled_at' => 'nullable|date',
            'match_type' => 'nullable|in:best_of_1,best_of_3,best_of_5',
            'notes' => 'nullable|string',
        ]);

        // Validate winner is one of the teams
        if ($validated['winner_id'] && !in_array($validated['winner_id'], [$match->team1_id, $match->team2_id])) {
            return back()->with('error', 'Winner must be one of the platoons in the match.');
        }

        $wasCompleted = $match->status === 'completed';
        $isNowCompleted = $validated['status'] === 'completed';

        $match->update([
            ...$validated,
            'completed_at' => $isNowCompleted && !$wasCompleted ? now() : $match->completed_at,
            'started_at' => $validated['status'] === 'in_progress' && !$match->started_at ? now() : $match->started_at,
        ]);

        // If match completed with a winner, advance to next match
        if ($isNowCompleted && !$wasCompleted && $validated['winner_id']) {
            $this->bracketService->advanceWinner($match);

            // For double elimination, also handle loser
            if ($match->loser_goes_to) {
                $this->bracketService->advanceLoser($match);
            }

            // Check if tournament is complete
            $this->bracketService->checkTournamentComplete($match->tournament);
        }

        return back()->with('success', 'Match updated!');
    }

    public function generateNextSwissRound(Tournament $tournament)
    {
        if ($tournament->format !== 'swiss') {
            return back()->with('error', 'Only for Swiss format.');
        }

        $generated = $this->bracketService->generateNextSwissRound($tournament);

        if ($generated) {
            return back()->with('success', 'Next Swiss round generated!');
        }

        return back()->with('error', 'Could not generate next round. Make sure all matches in the current round are completed.');
    }

    public function destroy(Tournament $tournament)
    {
        // Delete in order to avoid FK constraints
        $tournament->matches()->delete();
        $tournament->registrations()->delete();
        $tournament->delete();

        return redirect()->route('admin.tournaments.index')
            ->with('success', 'Tournament deleted.');
    }
}
