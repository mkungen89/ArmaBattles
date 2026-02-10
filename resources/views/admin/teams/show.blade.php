@extends('admin.layout')

@section('title', $team->name . ' - Platoon Admin')

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.teams.index') }}" class="text-gray-400 hover:text-white transition text-sm">
        &larr; Back to platoons
    </a>
</div>

<div class="flex flex-col sm:flex-row sm:items-center justify-between gap-4 mb-6">
    <div class="flex items-center gap-4">
        @if($team->avatar_url)
            <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-16 h-16 rounded-xl object-cover">
        @else
            <div class="w-16 h-16 rounded-xl bg-white/5 flex items-center justify-center text-xl font-bold text-gray-400">
                {{ strtoupper(substr($team->tag, 0, 2)) }}
            </div>
        @endif
        <div>
            <div class="flex items-center gap-3">
                <h1 class="text-2xl font-bold text-white">{{ $team->name }}</h1>
                <span class="px-2 py-1 text-xs rounded-full {{ $team->status_badge }}">{{ $team->status_text }}</span>
                @if($team->is_verified)
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                @endif
            </div>
            <p class="text-gray-400">[{{ $team->tag }}]</p>
        </div>
    </div>

    <div class="flex flex-wrap gap-2">
        <a href="{{ route('teams.show', $team) }}" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm" target="_blank">
            View public
        </a>
        @if($team->is_active)
            @if($team->is_verified)
                <form action="{{ route('admin.teams.unverify', $team) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-yellow-600 hover:bg-yellow-500 text-white rounded-xl transition text-sm">Unverify</button>
                </form>
            @else
                <form action="{{ route('admin.teams.verify', $team) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm">Verify</button>
                </form>
            @endif
            <form action="{{ route('admin.teams.disband', $team) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to disband this platoon?')">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded-xl transition text-sm">Disband</button>
            </form>
        @else
            <form action="{{ route('admin.teams.restore', $team) }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-3 py-1.5 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm">Restore</button>
            </form>
        @endif
        <form action="{{ route('admin.teams.destroy', $team) }}" method="POST" class="inline" onsubmit="return confirm('PERMANENTLY delete {{ $team->name }}? This cannot be undone!')">
            @csrf
            @method('DELETE')
            <button type="submit" class="px-3 py-1.5 bg-red-900 hover:bg-red-800 text-white rounded-lg transition text-sm">Delete</button>
        </form>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main Content -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Info -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Information</h2>
            <dl class="grid grid-cols-2 gap-4 text-sm">
                <div>
                    <dt class="text-gray-400">Captain</dt>
                    <dd class="text-white">{{ $team->captain->name }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Created</dt>
                    <dd class="text-white">{{ $team->created_at->format('d M Y') }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Recruiting</dt>
                    <dd class="text-white">{{ $team->is_recruiting ? 'Yes' : 'No' }}</dd>
                </div>
                @if($team->website)
                <div>
                    <dt class="text-gray-400">Website</dt>
                    <dd><a href="{{ $team->website }}" class="text-green-400 hover:text-green-300" target="_blank">{{ parse_url($team->website, PHP_URL_HOST) }}</a></dd>
                </div>
                @endif
                @if($team->disbanded_at)
                <div>
                    <dt class="text-gray-400">Disbanded</dt>
                    <dd class="text-red-400">{{ $team->disbanded_at->format('d M Y H:i') }}</dd>
                </div>
                @endif
            </dl>
            @if($team->description)
                <div class="mt-4 pt-4 border-t border-white/5">
                    <p class="text-gray-300 text-sm">{{ $team->description }}</p>
                </div>
            @endif
        </div>

        <!-- Members -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Members ({{ $team->members->where('pivot.status', 'active')->count() }})</h2>
            <div class="space-y-2">
                @foreach($team->members->sortBy(function($m) { return ['captain' => 0, 'officer' => 1, 'member' => 2, 'active' => 3][$m->pivot->role] ?? 4; }) as $member)
                    <div class="flex items-center gap-3 bg-white/3 rounded-lg p-3">
                        <img src="{{ $member->avatar_display }}" alt="{{ $member->name }}" class="w-8 h-8 rounded-full">
                        <div class="flex-1">
                            <span class="text-white text-sm">{{ $member->name }}</span>
                            @if($member->pivot->role === 'captain')
                                <span class="ml-2 px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">Captain</span>
                            @elseif($member->pivot->role === 'officer')
                                <span class="ml-2 px-2 py-0.5 text-xs rounded bg-blue-500/20 text-blue-400">Officer</span>
                            @endif
                        </div>
                        <span class="text-xs px-2 py-0.5 rounded {{ $member->pivot->status === 'active' ? 'bg-green-500/20 text-green-400' : 'bg-gray-500/20 text-gray-400' }}">
                            {{ ucfirst($member->pivot->status) }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>

        <!-- Tournament Registrations -->
        @if($team->registrations->count() > 0)
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Tournament Registrations</h2>
            <div class="space-y-2">
                @foreach($team->registrations as $registration)
                    <div class="flex items-center justify-between bg-white/3 rounded-lg p-3">
                        <div>
                            <a href="{{ route('admin.tournaments.show', $registration->tournament) }}" class="text-white hover:text-green-400 transition text-sm">
                                {{ $registration->tournament->name }}
                            </a>
                        </div>
                        <span class="px-2 py-1 text-xs rounded-full {{ $registration->status_badge }}">
                            {{ $registration->status_text }}
                        </span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Pending Invitations -->
        @if($team->invitations->where('status', 'pending')->count() > 0)
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Pending Invitations</h2>
            <div class="space-y-2">
                @foreach($team->invitations->where('status', 'pending') as $invitation)
                    <div class="flex items-center justify-between bg-white/3 rounded-lg p-3 text-sm">
                        <span class="text-white">{{ $invitation->user->name }}</span>
                        <span class="text-gray-400">by {{ $invitation->inviter->name }}</span>
                    </div>
                @endforeach
            </div>
        </div>
        @endif

        <!-- IDs -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Internal</h2>
            <dl class="space-y-3 text-sm">
                <div>
                    <dt class="text-gray-400">Team ID</dt>
                    <dd class="text-white font-mono">{{ $team->id }}</dd>
                </div>
                <div>
                    <dt class="text-gray-400">Captain ID</dt>
                    <dd class="text-white font-mono">{{ $team->captain_id }}</dd>
                </div>
            </dl>
        </div>
    </div>
</div>
@endsection
