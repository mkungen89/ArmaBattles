<?php

namespace App\Http\Controllers;

use App\Models\TournamentMatch;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class MatchController extends Controller
{
    public function checkIn(TournamentMatch $match)
    {
        $user = Auth::user();
        $team = $user->activeTeam;

        if (! $team) {
            return back()->with('error', 'You must be in a platoon to check in.');
        }

        if (! $team->isUserCaptainOrOfficer($user)) {
            return back()->with('error', 'Only platoon leaders can check in for matches.');
        }

        if ($team->id !== $match->team1_id && $team->id !== $match->team2_id) {
            return back()->with('error', 'Your platoon is not participating in this match.');
        }

        if (! $match->canCheckIn()) {
            return back()->with('error', 'Check-in is not currently open for this match.');
        }

        if ($match->hasTeamCheckedIn($team)) {
            return back()->with('error', 'Your platoon has already checked in.');
        }

        $match->checkInTeam($team, $user);

        return back()->with('success', 'Your platoon has checked in for the match!');
    }

    public function proposeSchedule(Request $request, TournamentMatch $match)
    {
        $user = Auth::user();
        $team = $user->activeTeam;

        if (! $team || ! $team->isUserCaptainOrOfficer($user)) {
            return back()->with('error', 'Only platoon leaders can propose match schedules.');
        }

        if ($team->id !== $match->team1_id && $team->id !== $match->team2_id) {
            return back()->with('error', 'Your platoon is not participating in this match.');
        }

        $validated = $request->validate([
            'scheduled_at' => 'required|date|after:now',
        ]);

        // For now, just set the schedule (in a full implementation, you'd have a proposal/acceptance flow)
        $match->update([
            'scheduled_at' => $validated['scheduled_at'],
            'check_in_opens_at' => \Carbon\Carbon::parse($validated['scheduled_at'])->subMinutes(30),
            'check_in_closes_at' => \Carbon\Carbon::parse($validated['scheduled_at'])->addMinutes(15),
        ]);

        return back()->with('success', 'Match scheduled! Check-in opens 30 minutes before the match.');
    }
}
