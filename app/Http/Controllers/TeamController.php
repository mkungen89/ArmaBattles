<?php

namespace App\Http\Controllers;

use App\Models\Team;
use App\Models\TeamApplication;
use App\Models\TeamInvitation;
use App\Models\Tournament;
use App\Models\User;
use App\Notifications\ApplicationResultNotification;
use App\Notifications\TeamApplicationNotification;
use App\Notifications\TeamInvitationNotification;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;

class TeamController extends Controller
{
    public function index(Request $request)
    {
        $query = Team::where('is_active', true)
            ->with('captain')
            ->withCount('activeMembers');

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function ($q) use ($search) {
                $q->where('name', 'like', "%{$search}%")
                    ->orWhere('tag', 'like', "%{$search}%");
            });
        }

        if ($request->boolean('verified')) {
            $query->where('is_verified', true);
        }

        $teams = $query
            ->orderByDesc('is_verified')
            ->orderByDesc('created_at')
            ->paginate(20);

        return view('teams.index', compact('teams'));
    }

    public function show(Team $team)
    {
        $team->load([
            'captain',
            'activeMembers' => fn ($q) => $q->with('user'),  // Fixed: Eager load user data to avoid N+1
            'tournaments' => fn ($q) => $q->where('tournaments.status', '!=', 'draft')->with('winner')->latest('starts_at'),  // Added: winner
        ]);

        $recentMatches = $team->matchesAsTeam1()
            ->orWhere('team2_id', $team->id)
            ->with(['tournament', 'team1.captain', 'team2.captain', 'winner'])  // Added: team captains
            ->where('tournament_matches.status', 'completed')
            ->orderByDesc('completed_at')
            ->limit(10)
            ->get();

        $combatStats = $team->getAggregatedGameStats();
        $teamRating = $team->getTeamRating();

        return view('teams.show', compact('team', 'recentMatches', 'combatStats', 'teamRating'));
    }

    public function create()
    {
        $user = Auth::user();

        if ($user->hasTeam()) {
            return redirect()->route('teams.my')
                ->with('error', 'You are already a member of a platoon. Leave your current platoon first.');
        }

        return view('teams.create');
    }

    public function store(Request $request)
    {
        $user = Auth::user();

        if ($user->hasTeam()) {
            return back()->with('error', 'You are already a member of a platoon.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams',
            'tag' => 'required|string|max:10|unique:teams|alpha_num',
            'description' => 'nullable|string|max:1000',
            'logo_url' => 'nullable|url',
            'website' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'header' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
        ]);

        $data = collect($validated)->only(['name', 'tag', 'description', 'logo_url', 'website'])->toArray();
        $data['captain_id'] = $user->id;

        if ($request->hasFile('avatar')) {
            $data['avatar_path'] = $request->file('avatar')->store('teams/avatars', 's3');
        }

        if ($request->hasFile('header')) {
            $data['header_image'] = $request->file('header')->store('teams/headers', 's3');
        }

        $team = Team::create($data);

        // Add captain as member
        $team->members()->attach($user->id, [
            'role' => 'captain',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        return redirect()->route('teams.show', $team)
            ->with('success', 'Platoon created successfully!');
    }

    public function myTeam()
    {
        $user = Auth::user();
        $team = $user->activeTeam;

        if (! $team) {
            $pendingInvitations = $user->pendingInvitations()->with('team')->get();
            $pendingApplications = $user->pendingApplications()->with('team')->get();

            return view('teams.no-team', compact('pendingInvitations', 'pendingApplications'));
        }

        $team->load([
            'activeMembers' => fn ($q) => $q->with('user'),  // Fixed: Eager load user data
            'pendingInvitations' => fn ($q) => $q->with(['user', 'inviter']),  // Added: inviter
            'pendingApplications' => fn ($q) => $q->with('user'),
            'registrations' => fn ($q) => $q->with('tournament.winner')->latest(),  // Added: tournament winner
        ]);

        $availableTournaments = Tournament::where('status', 'registration_open')
            ->whereDoesntHave('teams', fn ($q) => $q->where('team_id', $team->id))
            ->with(['server', 'winner'])  // Added: eager load relations
            ->withCount('approvedTeams')  // Added: show how many teams registered
            ->orderBy('registration_ends_at')
            ->get();

        return view('teams.my', compact('team', 'availableTournaments'));
    }

    public function edit(Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can edit the platoon.');
        }

        return view('teams.edit', compact('team'));
    }

    public function update(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can edit the platoon.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255|unique:teams,name,'.$team->id,
            'tag' => 'required|string|max:10|unique:teams,tag,'.$team->id.'|alpha_num',
            'description' => 'nullable|string|max:1000',
            'logo_url' => 'nullable|url',
            'website' => 'nullable|url|max:255',
            'avatar' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:1024',
            'header' => 'nullable|image|mimes:jpg,jpeg,png,webp|max:2048',
            'remove_avatar' => 'nullable|boolean',
            'remove_header' => 'nullable|boolean',
        ]);

        $data = collect($validated)->only(['name', 'tag', 'description', 'logo_url', 'website'])->toArray();

        if ($request->boolean('remove_avatar') && $team->avatar_path) {
            Storage::disk('s3')->delete($team->avatar_path);
            $data['avatar_path'] = null;
        } elseif ($request->hasFile('avatar')) {
            if ($team->avatar_path) {
                Storage::disk('s3')->delete($team->avatar_path);
            }
            $data['avatar_path'] = $request->file('avatar')->store('teams/avatars', 's3');
        }

        if ($request->boolean('remove_header') && $team->header_image) {
            Storage::disk('s3')->delete($team->header_image);
            $data['header_image'] = null;
        } elseif ($request->hasFile('header')) {
            if ($team->header_image) {
                Storage::disk('s3')->delete($team->header_image);
            }
            $data['header_image'] = $request->file('header')->store('teams/headers', 's3');
        }

        $team->update($data);

        return redirect()->route('teams.my')
            ->with('success', 'Platoon updated successfully!');
    }

    public function invite(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can invite members.');
        }

        $validated = $request->validate([
            'steam_id' => 'required|exists:users,steam_id',
        ]);

        $invitee = User::where('steam_id', $validated['steam_id'])->first();

        if ($team->isUserMember($invitee)) {
            return back()->with('error', 'This user is already a member of the platoon.');
        }

        if ($invitee->hasTeam()) {
            return back()->with('error', 'This user is already a member of another platoon.');
        }

        // Check for existing pending invitation
        $existingInvite = TeamInvitation::where('team_id', $team->id)
            ->where('user_id', $invitee->id)
            ->where('status', 'pending')
            ->where('expires_at', '>', now())
            ->first();

        if ($existingInvite) {
            return back()->with('error', 'An invitation has already been sent to this user.');
        }

        TeamInvitation::create([
            'team_id' => $team->id,
            'user_id' => $invitee->id,
            'invited_by' => $user->id,
        ]);

        // Send notification
        $invitee->notify(new TeamInvitationNotification($team, $user));

        return back()->with('success', 'Invitation sent to '.$invitee->name.'!');
    }

    public function acceptInvitation(TeamInvitation $invitation)
    {
        $user = Auth::user();

        if ($invitation->user_id !== $user->id || ! $invitation->isValid()) {
            abort(403);
        }

        if ($user->hasTeam()) {
            return back()->with('error', 'You must leave your current platoon first.');
        }

        $invitation->team->members()->attach($user->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $invitation->update(['status' => 'accepted']);

        return redirect()->route('teams.my')
            ->with('success', 'You have joined '.$invitation->team->name.'!');
    }

    public function declineInvitation(TeamInvitation $invitation)
    {
        $user = Auth::user();

        if ($invitation->user_id !== $user->id) {
            abort(403);
        }

        $invitation->update(['status' => 'declined']);

        return back()->with('success', 'Invitation declined.');
    }

    public function cancelInvitation(Team $team, TeamInvitation $invitation)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user) || $invitation->team_id !== $team->id) {
            abort(403);
        }

        $invitation->update(['status' => 'expired']);

        return back()->with('success', 'Invitation cancelled.');
    }

    public function leaveTeam(Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserMember($user)) {
            abort(403);
        }

        if ($team->captain_id === $user->id) {
            return back()->with('error', 'Captains must transfer leadership or disband the platoon.');
        }

        $team->members()->updateExistingPivot($user->id, [
            'status' => 'left',
            'left_at' => now(),
        ]);

        return redirect()->route('profile')
            ->with('success', 'You have left the platoon.');
    }

    public function kickMember(Team $team, User $member)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can kick members.');
        }

        if (! $team->isUserMember($member)) {
            abort(403, 'User is not a member of this platoon.');
        }

        if ($member->id === $team->captain_id) {
            return back()->with('error', 'You cannot kick the platoon captain.');
        }

        if ($member->id === $user->id) {
            return back()->with('error', 'You cannot kick yourself.');
        }

        $team->members()->updateExistingPivot($member->id, [
            'status' => 'kicked',
            'left_at' => now(),
        ]);

        return back()->with('success', $member->name.' has been kicked from the platoon.');
    }

    public function promoteMember(Team $team, User $member)
    {
        $user = Auth::user();

        if ($team->captain_id !== $user->id) {
            abort(403, 'Only the platoon captain can promote members.');
        }

        if (! $team->isUserMember($member)) {
            abort(403, 'User is not a member of this platoon.');
        }

        $currentRole = $team->members()->where('user_id', $member->id)->first()?->pivot->role;

        if ($currentRole === 'member') {
            $team->members()->updateExistingPivot($member->id, ['role' => 'officer']);

            return back()->with('success', $member->name.' is now an Officer.');
        }

        return back()->with('error', 'Cannot promote this member.');
    }

    public function demoteMember(Team $team, User $member)
    {
        $user = Auth::user();

        if ($team->captain_id !== $user->id) {
            abort(403, 'Only the platoon captain can demote members.');
        }

        if (! $team->isUserMember($member)) {
            abort(403, 'User is not a member of this platoon.');
        }

        $currentRole = $team->members()->where('user_id', $member->id)->first()?->pivot->role;

        if ($currentRole === 'officer') {
            $team->members()->updateExistingPivot($member->id, ['role' => 'member']);

            return back()->with('success', $member->name.' is now a regular member.');
        }

        return back()->with('error', 'Cannot demote this member.');
    }

    public function transferCaptain(Request $request, Team $team)
    {
        $user = Auth::user();

        if ($team->captain_id !== $user->id) {
            abort(403, 'Only the platoon captain can transfer leadership.');
        }

        $validated = $request->validate([
            'new_captain_id' => 'required|exists:users,id',
        ]);

        $newCaptain = User::find($validated['new_captain_id']);

        if (! $team->isUserMember($newCaptain)) {
            return back()->with('error', 'The user must be a member of the platoon.');
        }

        // Update captain
        $team->update(['captain_id' => $newCaptain->id]);

        // Update roles
        $team->members()->updateExistingPivot($user->id, ['role' => 'officer']);
        $team->members()->updateExistingPivot($newCaptain->id, ['role' => 'captain']);

        return back()->with('success', $newCaptain->name.' is now the platoon captain.');
    }

    public function registerForTournament(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can register the platoon for tournaments.');
        }

        $validated = $request->validate([
            'tournament_id' => 'required|exists:tournaments,id',
        ]);

        $tournament = Tournament::findOrFail($validated['tournament_id']);

        if (! $tournament->canTeamRegister($team)) {
            return back()->with('error', 'Cannot register the platoon for this tournament.');
        }

        $tournament->teams()->attach($team->id, [
            'status' => $tournament->require_approval ? 'pending' : 'approved',
        ]);

        $message = $tournament->require_approval
            ? 'Registration submitted! Waiting for approval.'
            : 'The platoon is now registered for the tournament!';

        return back()->with('success', $message);
    }

    public function withdrawFromTournament(Team $team, Tournament $tournament)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403);
        }

        $registration = $team->registrations()
            ->where('tournament_id', $tournament->id)
            ->whereIn('status', ['pending', 'approved'])
            ->first();

        if (! $registration) {
            return back()->with('error', 'No active registration found.');
        }

        if ($tournament->status === 'in_progress') {
            return back()->with('error', 'Cannot withdraw registration after the tournament has started.');
        }

        $registration->update(['status' => 'withdrawn']);

        return back()->with('success', 'Registration has been withdrawn.');
    }

    public function updateSocialLinks(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can edit the platoon.');
        }

        $platforms = ['twitch', 'youtube', 'tiktok', 'kick', 'twitter', 'facebook', 'instagram'];

        $rules = [];
        foreach ($platforms as $platform) {
            $rules[$platform] = ['nullable', 'url', 'max:255'];
        }

        $validated = $request->validate($rules);

        $socialLinks = [];
        foreach ($platforms as $platform) {
            $value = trim($validated[$platform] ?? '');
            if ($value !== '') {
                $socialLinks[$platform] = $value;
            }
        }

        $team->update([
            'social_links' => ! empty($socialLinks) ? $socialLinks : null,
        ]);

        return back()->with('success', 'Social media links updated successfully!');
    }

    public function disband(Team $team)
    {
        $user = Auth::user();

        if ($team->captain_id !== $user->id) {
            abort(403, 'Only the platoon captain can disband the platoon.');
        }

        // Check for active tournament registrations
        $activeRegistrations = $team->registrations()
            ->whereHas('tournament', fn ($q) => $q->whereIn('status', ['registration_open', 'registration_closed', 'in_progress']))
            ->whereIn('status', ['pending', 'approved'])
            ->count();

        if ($activeRegistrations > 0) {
            return back()->with('error', 'Cannot disband the platoon while it has active tournament registrations.');
        }

        // Clean up uploaded files
        if ($team->avatar_path) {
            Storage::disk('s3')->delete($team->avatar_path);
        }
        if ($team->header_image) {
            Storage::disk('s3')->delete($team->header_image);
        }

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

        return redirect()->route('profile')
            ->with('success', 'The platoon has been disbanded.');
    }

    public function destroy(Team $team)
    {
        $user = Auth::user();

        if ($team->captain_id !== $user->id) {
            abort(403, 'Only the platoon captain can delete the platoon.');
        }

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
            Storage::disk('s3')->delete($team->avatar_path);
        }
        if ($team->header_image) {
            Storage::disk('s3')->delete($team->header_image);
        }

        $team->delete();

        return redirect()->route('profile')
            ->with('success', 'The platoon has been permanently deleted.');
    }

    // ==================== Application System ====================

    public function apply(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->is_active) {
            return back()->with('error', 'This platoon is no longer active.');
        }

        if (! $team->is_recruiting) {
            return back()->with('error', 'This platoon is not accepting applications.');
        }

        if ($user->hasTeam()) {
            return back()->with('error', 'You must leave your current platoon first.');
        }

        if ($team->isUserMember($user)) {
            return back()->with('error', 'You are already a member of this platoon.');
        }

        if ($user->hasPendingApplicationTo($team)) {
            return back()->with('error', 'You already have a pending application to this platoon.');
        }

        $validated = $request->validate([
            'message' => 'nullable|string|max:1000',
        ]);

        TeamApplication::create([
            'team_id' => $team->id,
            'user_id' => $user->id,
            'message' => $validated['message'] ?? null,
        ]);

        // Notify platoon leaders
        $leaders = $team->activeMembers()
            ->wherePivotIn('role', ['captain', 'officer'])
            ->get();

        foreach ($leaders as $leader) {
            $leader->notify(new TeamApplicationNotification($team, $user));
        }

        return back()->with('success', 'Application submitted! The platoon leaders will review it.');
    }

    public function acceptApplication(Team $team, TeamApplication $application)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can manage applications.');
        }

        if ($application->team_id !== $team->id) {
            abort(403);
        }

        if (! $application->isPending()) {
            return back()->with('error', 'This application has already been processed.');
        }

        $applicant = $application->user;

        if ($applicant->hasTeam()) {
            $application->update([
                'status' => 'rejected',
                'reviewed_by' => $user->id,
                'reviewed_at' => now(),
                'rejection_reason' => 'User joined another platoon.',
            ]);

            return back()->with('error', 'This user has joined another platoon.');
        }

        // Add to team
        $team->members()->attach($applicant->id, [
            'role' => 'member',
            'status' => 'active',
            'joined_at' => now(),
        ]);

        $application->update([
            'status' => 'accepted',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
        ]);

        // Notify the applicant
        $applicant->notify(new ApplicationResultNotification($team, 'accepted'));

        return back()->with('success', $applicant->name.' has been accepted into the platoon!');
    }

    public function rejectApplication(Request $request, Team $team, TeamApplication $application)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can manage applications.');
        }

        if ($application->team_id !== $team->id) {
            abort(403);
        }

        if (! $application->isPending()) {
            return back()->with('error', 'This application has already been processed.');
        }

        $validated = $request->validate([
            'rejection_reason' => 'nullable|string|max:500',
        ]);

        $application->update([
            'status' => 'rejected',
            'reviewed_by' => $user->id,
            'reviewed_at' => now(),
            'rejection_reason' => $validated['rejection_reason'] ?? null,
        ]);

        // Notify the applicant
        $application->user->notify(new ApplicationResultNotification($team, 'rejected', $validated['rejection_reason'] ?? null));

        return back()->with('success', 'Application rejected.');
    }

    public function cancelApplication(TeamApplication $application)
    {
        $user = Auth::user();

        if ($application->user_id !== $user->id) {
            abort(403);
        }

        if (! $application->isPending()) {
            return back()->with('error', 'This application has already been processed.');
        }

        $application->delete();

        return back()->with('success', 'Application cancelled.');
    }

    public function updateRecruitment(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403, 'Only platoon leaders can change recruitment settings.');
        }

        $validated = $request->validate([
            'is_recruiting' => 'boolean',
            'recruitment_message' => 'nullable|string|max:500',
        ]);

        $team->update([
            'is_recruiting' => $request->boolean('is_recruiting'),
            'recruitment_message' => $validated['recruitment_message'] ?? null,
        ]);

        return back()->with('success', 'Recruitment settings updated.');
    }

    public function searchPlayers(Request $request, Team $team)
    {
        $user = Auth::user();

        if (! $team->isUserCaptainOrOfficer($user)) {
            abort(403);
        }

        $query = $request->get('q', '');

        if (strlen($query) < 2) {
            return response()->json([]);
        }

        $players = User::where(function ($q) use ($query) {
            $q->where('name', 'like', "%{$query}%")
                ->orWhere('steam_id', 'like', "%{$query}%");
        })
            ->whereDoesntHave('teams', fn ($q) => $q->wherePivot('status', 'active'))
            ->whereNotIn('id', $team->pendingInvitations()->pluck('user_id'))
            ->where('is_banned', false)
            ->limit(10)
            ->get(['id', 'name', 'steam_id', 'avatar']);

        return response()->json($players);
    }
}
