<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Team;
use Illuminate\Http\Request;

class TeamAdminController extends Controller
{
    use \App\Traits\LogsAdminActions;

    public function index(Request $request)
    {
        $query = Team::with('captain')
            ->withCount(['activeMembers', 'registrations']);

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tag', 'like', "%{$search}%");
            });
        }

        if ($request->filled('status')) {
            if ($request->status === 'active') {
                $query->where('is_active', true);
            } elseif ($request->status === 'disbanded') {
                $query->where('is_active', false);
            } elseif ($request->status === 'verified') {
                $query->where('is_verified', true);
            }
        }

        $teams = $query->orderByDesc('created_at')->paginate(20);

        $stats = [
            'total' => Team::count(),
            'active' => Team::where('is_active', true)->count(),
            'verified' => Team::where('is_verified', true)->count(),
            'disbanded' => Team::where('is_active', false)->count(),
        ];

        return view('admin.teams.index', compact('teams', 'stats'));
    }

    public function show(Team $team)
    {
        $team->load([
            'captain',
            'activeMembers',  // activeMembers IS a BelongsToMany to User, no nested .user needed
            'registrations.tournament',
            'invitations' => fn ($q) => $q->with('user', 'inviter')->latest(),
            'applications' => fn ($q) => $q->with('user')->latest(),
        ]);

        return view('admin.teams.show', compact('team'));
    }

    public function verify(Team $team)
    {
        $team->update(['is_verified' => true]);

        $this->logAction('team.verified', 'Team', $team->id, [
            'name' => $team->name,
            'tag' => $team->tag,
        ]);

        return back()->with('success', 'Platoon has been verified.');
    }

    public function unverify(Team $team)
    {
        $team->update(['is_verified' => false]);

        $this->logAction('team.unverified', 'Team', $team->id, [
            'name' => $team->name,
            'tag' => $team->tag,
        ]);

        return back()->with('success', 'Verification removed.');
    }

    public function disband(Team $team)
    {
        // Check for active tournament registrations
        $activeRegistrations = $team->registrations()
            ->whereHas('tournament', fn ($q) => $q->whereIn('status', ['in_progress']))
            ->whereIn('status', ['approved'])
            ->count();

        if ($activeRegistrations > 0) {
            return back()->with('error', 'Cannot disband the platoon while it is participating in active tournaments.');
        }

        // Withdraw from pending tournaments
        $team->registrations()
            ->whereIn('status', ['pending', 'approved'])
            ->update(['status' => 'withdrawn']);

        $team->update([
            'is_active' => false,
            'disbanded_at' => now(),
        ]);

        // Mark all members as left
        \DB::table('team_members')
            ->where('team_id', $team->id)
            ->whereNull('left_at')
            ->update([
                'status' => 'left',
                'left_at' => now(),
            ]);

        $this->logAction('team.disbanded', 'Team', $team->id, [
            'name' => $team->name,
            'tag' => $team->tag,
        ]);

        return back()->with('success', 'Platoon has been disbanded by admin.');
    }

    public function restore(Team $team)
    {
        if ($team->is_active) {
            return back()->with('error', 'Platoon is already active.');
        }

        $team->update([
            'is_active' => true,
            'disbanded_at' => null,
        ]);

        $this->logAction('team.restored', 'Team', $team->id, [
            'name' => $team->name,
            'tag' => $team->tag,
        ]);

        return back()->with('success', 'Platoon has been restored.');
    }

    public function destroy(Team $team)
    {
        // Check for active tournament participation
        $activeRegistrations = $team->registrations()
            ->whereHas('tournament', fn ($q) => $q->whereIn('status', ['in_progress']))
            ->whereIn('status', ['approved'])
            ->count();

        if ($activeRegistrations > 0) {
            return back()->with('error', 'Cannot delete the platoon while it is participating in active tournaments.');
        }

        // Clean up uploaded files
        if ($team->avatar_path) {
            \Storage::disk('s3')->delete($team->avatar_path);
        }
        if ($team->header_image) {
            \Storage::disk('s3')->delete($team->header_image);
        }

        $teamName = $team->name;
        $teamId = $team->id;

        $team->delete();

        $this->logAction('team.deleted', 'Team', $teamId, [
            'name' => $teamName,
            'tag' => $team->tag,
        ]);

        return redirect()->route('admin.teams.index')->with('success', 'Platoon has been permanently deleted.');
    }
}
