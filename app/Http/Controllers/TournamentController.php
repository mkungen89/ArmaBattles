<?php

namespace App\Http\Controllers;

use App\Models\Tournament;
use App\Models\TournamentMatch;
use Illuminate\Http\Request;

class TournamentController extends Controller
{
    public function index(Request $request)
    {
        $query = Tournament::query()
            ->where('status', '!=', 'draft')
            ->with(['winner', 'server'])
            ->withCount('approvedTeams');

        if ($request->filled('status')) {
            $query->where('status', $request->status);
        }

        if ($request->filled('format')) {
            $query->where('format', $request->format);
        }

        if ($request->boolean('upcoming')) {
            $query->where('starts_at', '>', now())
                ->whereIn('status', ['registration_open', 'registration_closed']);
        }

        if ($request->boolean('past')) {
            $query->where('status', 'completed');
        }

        $tournaments = $query
            ->orderByDesc('is_featured')
            ->orderByDesc('starts_at')
            ->paginate(12);

        return view('tournaments.index', compact('tournaments'));
    }

    public function show(Tournament $tournament)
    {
        if ($tournament->status === 'draft' && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $tournament->load([
            'approvedTeams' => fn ($q) => $q->with(['captain', 'activeMembers.user']),  // Fixed: Eager load user data
            'matches' => fn ($q) => $q->with(['team1.captain', 'team2.captain', 'winner']),  // Added: team captains
            'winner.captain',  // Added: winner team's captain
            'server',
            'creator',
        ]);

        $upcomingMatches = $tournament->matches()
            ->with(['team1.captain', 'team2.captain'])  // Added: team captains
            ->whereIn('status', ['pending', 'scheduled'])
            ->where('team1_id', '!=', null)
            ->where('team2_id', '!=', null)
            ->orderBy('scheduled_at')
            ->orderBy('round')
            ->limit(5)
            ->get();

        $recentMatches = $tournament->matches()
            ->with(['team1.captain', 'team2.captain', 'winner.captain'])  // Added: team captains
            ->where('status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(5)
            ->get();

        return view('tournaments.show', compact('tournament', 'upcomingMatches', 'recentMatches'));
    }

    public function bracket(Tournament $tournament)
    {
        if ($tournament->status === 'draft' && (!auth()->check() || !auth()->user()->isAdmin())) {
            abort(404);
        }

        $matches = $tournament->matches()
            ->with(['team1.captain', 'team2.captain', 'winner.captain'])  // Added: team captains for display
            ->orderBy('bracket')
            ->orderBy('round')
            ->orderBy('match_number')
            ->get();

        // Group matches by bracket and round for visualization
        $brackets = [
            'main' => [],
            'losers' => [],
            'grand_final' => null,
        ];

        foreach ($matches as $match) {
            if ($match->bracket === 'grand_final') {
                $brackets['grand_final'] = $match;
            } elseif ($match->bracket === 'losers') {
                $round = abs($match->round);
                if (!isset($brackets['losers'][$round])) {
                    $brackets['losers'][$round] = [];
                }
                $brackets['losers'][$round][] = $match;
            } else {
                if (!isset($brackets['main'][$match->round])) {
                    $brackets['main'][$match->round] = [];
                }
                $brackets['main'][$match->round][] = $match;
            }
        }

        return view('tournaments.bracket', compact('tournament', 'brackets'));
    }

    public function matchDetails(Tournament $tournament, TournamentMatch $match)
    {
        if ($match->tournament_id !== $tournament->id) {
            abort(404);
        }

        $match->load([
            'team1' => fn ($q) => $q->with(['captain', 'activeMembers.user']),  // Fixed: Eager load user data
            'team2' => fn ($q) => $q->with(['captain', 'activeMembers.user']),  // Fixed: Eager load user data
            'games',
            'winner.captain',  // Added: winner team's captain
            'tournament.server',  // Added: tournament server
        ]);

        return view('tournaments.match', compact('tournament', 'match'));
    }

    public function standings(Tournament $tournament)
    {
        if (!in_array($tournament->format, ['round_robin', 'swiss'])) {
            return redirect()->route('tournaments.bracket', $tournament);
        }

        $standings = app(\App\Services\TournamentBracketService::class)
            ->getSwissStandings($tournament);

        return view('tournaments.standings', compact('tournament', 'standings'));
    }
}
