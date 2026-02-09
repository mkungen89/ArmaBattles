<?php

namespace App\Http\Controllers;

use App\Models\Team;
use Illuminate\Http\Request;

class TeamComparisonController extends Controller
{
    public function index(Request $request)
    {
        $t1 = $request->get('t1');
        $t2 = $request->get('t2');
        $team1 = null;
        $team2 = null;
        $stats1 = null;
        $stats2 = null;

        $teams = Team::where('is_active', true)->orderBy('name')->get(['id', 'name', 'tag', 'avatar_url']);

        if ($t1) {
            $team1 = Team::with('activeMembers')->find($t1);
            if ($team1) {
                $stats1 = $team1->getAggregatedGameStats();
            }
        }

        if ($t2) {
            $team2 = Team::with('activeMembers')->find($t2);
            if ($team2) {
                $stats2 = $team2->getAggregatedGameStats();
            }
        }

        return view('teams.compare', compact('team1', 'team2', 'stats1', 'stats2', 'teams', 't1', 't2'));
    }
}
