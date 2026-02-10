@extends('layouts.app')
@section('title', 'My Platoon')
@section('content')
    <!-- Header -->
    <div class="glass-card p-6 mb-6">
        <div class="flex flex-col sm:flex-row sm:items-center gap-6">
            @if($team->avatar_url)
                <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-20 h-20 rounded-xl object-cover">
            @else
                <div class="w-20 h-20 rounded-xl bg-white/5 flex items-center justify-center text-2xl font-bold text-gray-400">
                    {{ strtoupper(substr($team->tag, 0, 2)) }}
                </div>
            @endif
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <h1 class="text-2xl font-bold text-white">{{ $team->name }}</h1>
                    @if($team->is_verified)
                        <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                        </svg>
                    @endif
                </div>
                <p class="text-gray-400">[{{ $team->tag }}]</p>
            </div>
            <div class="flex gap-2">
                <a href="{{ route('teams.show', $team) }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                    View Profile
                </a>
                @if($team->isUserCaptainOrOfficer(auth()->user()))
                    <a href="{{ route('teams.edit', $team) }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                        Edit
                    </a>
                @endif
            </div>
        </div>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Members -->
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Members ({{ $team->activeMembers->count() }})</h2>
                </div>
                <div class="space-y-3">
                    @foreach($team->activeMembers->sortBy(function($member) {
                        return ['captain' => 0, 'officer' => 1, 'member' => 2][$member->pivot->role] ?? 3;
                    }) as $member)
                        <div class="flex items-center gap-4 bg-white/3 rounded-lg p-3">
                            <img src="{{ $member->avatar_display }}" alt="{{ $member->name }}" class="w-10 h-10 rounded-full">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-medium truncate">{{ $member->name }}</span>
                                    @if($member->pivot->role === 'captain')
                                        <span class="px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">Captain</span>
                                    @elseif($member->pivot->role === 'officer')
                                        <span class="px-2 py-0.5 text-xs rounded bg-blue-500/20 text-blue-400">Officer</span>
                                    @endif
                                </div>
                            </div>
                            @if($team->isUserCaptainOrOfficer(auth()->user()) && $member->id !== auth()->id() && $member->id !== $team->captain_id)
                                <div class="flex gap-2">
                                    @if($team->captain_id === auth()->id())
                                        @if($member->pivot->role === 'member')
                                            <form action="{{ route('teams.members.promote', [$team, $member]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs text-blue-400 hover:text-blue-300">Promote</button>
                                            </form>
                                        @elseif($member->pivot->role === 'officer')
                                            <form action="{{ route('teams.members.demote', [$team, $member]) }}" method="POST">
                                                @csrf
                                                <button type="submit" class="text-xs text-gray-400 hover:text-gray-300">Demote</button>
                                            </form>
                                        @endif
                                    @endif
                                    <form action="{{ route('teams.members.kick', [$team, $member]) }}" method="POST" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        <button type="submit" class="text-xs text-red-400 hover:text-red-300">Kick</button>
                                    </form>
                                </div>
                            @endif
                            @if($member->id === auth()->id() && $member->id !== $team->captain_id)
                                <form action="{{ route('teams.leave', $team) }}" method="POST" onsubmit="return confirm('Are you sure you want to leave the platoon?')">
                                    @csrf
                                    <button type="submit" class="text-xs text-red-400 hover:text-red-300">Leave</button>
                                </form>
                            @endif
                        </div>
                    @endforeach
                </div>
                <!-- Invite Form -->
                @if($team->isUserCaptainOrOfficer(auth()->user()))
                    <div class="mt-6 pt-6 border-t border-white/5">
                        <h3 class="text-sm font-medium text-white mb-3">Invite player</h3>
                        <form action="{{ route('teams.invite', $team) }}" method="POST" class="flex gap-3">
                            @csrf
                            <input type="text" name="steam_id" placeholder="Steam ID" required
                                class="flex-1 bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                                Invite
                            </button>
                        </form>
                    </div>
                    <!-- Pending Invitations -->
                    @if($team->pendingInvitations->count() > 0)
                        <div class="mt-4">
                            <h4 class="text-sm text-gray-400 mb-2">Pending invitations</h4>
                            <div class="space-y-2">
                                @foreach($team->pendingInvitations as $invitation)
                                    <div class="flex items-center justify-between bg-white/3 rounded-lg p-2 text-sm">
                                        <span class="text-white">{{ $invitation->user->name }}</span>
                                        <form action="{{ route('teams.invitations.cancel', [$team, $invitation]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-red-400 hover:text-red-300">Cancel</button>
                                        </form>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                    <!-- Pending Applications -->
                    @if($team->pendingApplications->count() > 0)
                        <div class="mt-6 pt-6 border-t border-white/5">
                            <h3 class="text-sm font-medium text-white mb-3">Pending Applications ({{ $team->pendingApplications->count() }})</h3>
                            <div class="space-y-3">
                                @foreach($team->pendingApplications as $application)
                                    <div class="bg-white/3 rounded-lg p-4">
                                        <div class="flex items-center gap-3 mb-2">
                                            <img src="{{ $application->user->avatar_display }}" alt="{{ $application->user->name }}" class="w-10 h-10 rounded-full">
                                            <div class="flex-1">
                                                <span class="text-white font-medium">{{ $application->user->name }}</span>
                                                <div class="text-xs text-gray-500">Applied {{ $application->created_at->diffForHumans() }}</div>
                                            </div>
                                        </div>
                                        @if($application->message)
                                            <p class="text-sm text-gray-400 mb-3 pl-13">{{ $application->message }}</p>
                                        @endif
                                        <div class="flex gap-2 mt-3">
                                            <form action="{{ route('teams.applications.accept', [$team, $application]) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white rounded text-sm transition">
                                                    Accept
                                                </button>
                                            </form>
                                            <form action="{{ route('teams.applications.reject', [$team, $application]) }}" method="POST" class="flex-1">
                                                @csrf
                                                <button type="submit" class="w-full px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded text-sm transition">
                                                    Reject
                                                </button>
                                            </form>
                                        </div>
                                    </div>
                                @endforeach
                            </div>
                        </div>
                    @endif
                @endif
            </div>
            <!-- Tournament Registrations -->
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Tournament Registrations</h2>
                @if($team->registrations->count() > 0)
                    <div class="space-y-3">
                        @foreach($team->registrations as $registration)
                            <div class="flex items-center justify-between bg-white/3 rounded-lg p-4">
                                <div>
                                    <a href="{{ route('tournaments.show', $registration->tournament) }}" class="text-white font-medium hover:text-green-400 transition">
                                        {{ $registration->tournament->name }}
                                    </a>
                                    <div class="text-sm text-gray-400">
                                        {{ $registration->tournament->starts_at?->format('d M Y') }}
                                    </div>
                                </div>
                                <div class="flex items-center gap-3">
                                    <span class="px-2 py-1 text-xs rounded-full {{ $registration->status_badge }}">
                                        {{ $registration->status_text }}
                                    </span>
                                    @if(in_array($registration->status, ['pending', 'approved']) && $registration->tournament->status !== 'in_progress')
                                        <form action="{{ route('teams.withdraw', [$team, $registration->tournament]) }}" method="POST">
                                            @csrf
                                            <button type="submit" class="text-xs text-red-400 hover:text-red-300" onclick="return confirm('Are you sure?')">
                                                Withdraw
                                            </button>
                                        </form>
                                    @endif
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400">No tournament registrations yet.</p>
                @endif
            </div>
        </div>
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Recruitment Settings -->
            @if($team->isUserCaptainOrOfficer(auth()->user()))
                <div class="glass-card rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Recruitment</h2>
                    <form action="{{ route('teams.recruitment', $team) }}" method="POST" class="space-y-4">
                        @csrf
                        <div class="flex items-center justify-between">
                            <label class="text-sm text-gray-300">Accept applications</label>
                            <label class="relative inline-flex items-center cursor-pointer">
                                <input type="hidden" name="is_recruiting" value="0">
                                <input type="checkbox" name="is_recruiting" value="1" {{ $team->is_recruiting ? 'checked' : '' }}
                                       class="sr-only peer">
                                <div class="w-11 h-6 bg-white/10 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                            </label>
                        </div>
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Recruitment message</label>
                            <textarea name="recruitment_message" rows="3"
                                      class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 text-sm focus:ring-green-500 focus:border-green-500"
                                      placeholder="Optional message for applicants...">{{ $team->recruitment_message }}</textarea>
                        </div>
                        <button type="submit" class="w-full px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm">
                            Save Settings
                        </button>
                    </form>
                </div>
            @endif
            <!-- Register for Tournament -->
            @if($team->isUserCaptainOrOfficer(auth()->user()) && $availableTournaments->count() > 0)
                <div class="glass-card rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Register for tournament</h2>
                    <form action="{{ route('teams.register', $team) }}" method="POST" class="space-y-4">
                        @csrf
                        <select name="tournament_id" required class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                            <option value="">Select tournament...</option>
                            @foreach($availableTournaments as $tournament)
                                <option value="{{ $tournament->id }}">
                                    {{ $tournament->name }} ({{ $tournament->team_size }} players)
                                </option>
                            @endforeach
                        </select>
                        <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
                            Register
                        </button>
                    </form>
                </div>
            @endif
            <!-- Captain Actions -->
            @if($team->captain_id === auth()->id())
                <div class="glass-card rounded-xl p-6">
                    <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Captain Actions</h2>
                    <div class="space-y-3">
                        <!-- Transfer Captain -->
                        @if($team->activeMembers->count() > 1)
                            <form action="{{ route('teams.transfer-captain', $team) }}" method="POST" onsubmit="return confirm('Are you sure you want to transfer leadership?')">
                                @csrf
                                <label class="block text-sm text-gray-400 mb-2">Transfer leadership</label>
                                <select name="new_captain_id" required class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 mb-2">
                                    <option value="">Select new captain...</option>
                                    @foreach($team->activeMembers->where('id', '!=', auth()->id()) as $member)
                                        <option value="{{ $member->id }}">{{ $member->name }}</option>
                                    @endforeach
                                </select>
                                <button type="submit" class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-xl transition text-sm">
                                    Transfer
                                </button>
                            </form>
                        @endif
                        <!-- Disband -->
                        <div class="pt-4 border-t border-white/5">
                            <form action="{{ route('teams.disband', $team) }}" method="POST" onsubmit="return confirm('Are you sure you want to disband the platoon? Members will be removed.')">
                                @csrf
                                <button type="submit" class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-xl transition text-sm">
                                    Disband platoon
                                </button>
                            </form>
                            <p class="text-xs text-gray-500 mt-2">Disbands the platoon but keeps it in the system.</p>
                        </div>
                        <!-- Delete permanently -->
                        <div class="pt-4 border-t border-white/5">
                            <form action="{{ route('teams.destroy', $team) }}" method="POST" onsubmit="return confirm('PERMANENTLY delete {{ $team->name }}? All data, history and tournament records will be lost. This cannot be undone!')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition text-sm">
                                    Delete platoon permanently
                                </button>
                            </form>
                            <p class="text-xs text-gray-500 mt-2">Warning: This will permanently delete the platoon and all its data.</p>
                        </div>
                    </div>
                </div>
            @endif
        </div>
    </div>

@endsection
