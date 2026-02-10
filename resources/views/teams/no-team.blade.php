@extends('layouts.app')
@section('title', 'My Platoon')
@section('content')
    <div class="glass-card rounded-xl p-8 text-center">
        <svg class="w-20 h-20 mx-auto text-gray-600 mb-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
        </svg>
        <h1 class="text-2xl font-bold text-white mb-2">You don't have a platoon</h1>
        <p class="text-gray-400 mb-6">Create your own platoon or accept an invitation to join an existing one.</p>
        <a href="{{ route('teams.create') }}" class="inline-block px-6 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
            Create new platoon
        </a>
    </div>
    @if($pendingInvitations->count() > 0)
        <div class="glass-card rounded-xl p-6 mt-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Pending Invitations</h2>
            <div class="space-y-3">
                @foreach($pendingInvitations as $invitation)
                    <div class="flex items-center justify-between bg-white/3 rounded-lg p-4">
                        <div class="flex items-center gap-4">
                            @if($invitation->team->avatar_url)
                                <img src="{{ $invitation->team->avatar_url }}" alt="{{ $invitation->team->name }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-white/5 flex items-center justify-center text-gray-400 font-bold">
                                    {{ strtoupper(substr($invitation->team->tag, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <div class="text-white font-medium">{{ $invitation->team->name }}</div>
                                <div class="text-sm text-gray-400">
                                    Invited by {{ $invitation->inviter->name }}
                                    <span class="text-gray-500">{{ $invitation->created_at->diffForHumans() }}</span>
                                </div>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('teams.invitations.accept', $invitation) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm">
                                    Accept
                                </button>
                            </form>
                            <form action="{{ route('teams.invitations.decline', $invitation) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm">
                                    Decline
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    @if($pendingApplications->count() > 0)
        <div class="glass-card rounded-xl p-6 mt-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Your Applications</h2>
            <div class="space-y-3">
                @foreach($pendingApplications as $application)
                    <div class="flex items-center justify-between bg-white/3 rounded-lg p-4">
                        <div class="flex items-center gap-4">
                            @if($application->team->avatar_url)
                                <img src="{{ $application->team->avatar_url }}" alt="{{ $application->team->name }}" class="w-12 h-12 rounded-lg object-cover">
                            @else
                                <div class="w-12 h-12 rounded-lg bg-white/5 flex items-center justify-center text-gray-400 font-bold">
                                    {{ strtoupper(substr($application->team->tag, 0, 2)) }}
                                </div>
                            @endif
                            <div>
                                <a href="{{ route('teams.show', $application->team) }}" class="text-white font-medium hover:text-green-400 transition">
                                    {{ $application->team->name }}
                                </a>
                                <div class="text-sm text-gray-400">
                                    Applied {{ $application->created_at->diffForHumans() }}
                                </div>
                            </div>
                        </div>
                        <div class="flex items-center gap-3">
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">Pending</span>
                            <form action="{{ route('teams.applications.cancel', $application) }}" method="POST">
                                @csrf
                                <button type="submit" class="text-sm text-red-400 hover:text-red-300">
                                    Cancel
                                </button>
                            </form>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <div class="text-center mt-6">
        <a href="{{ route('teams.index') }}" class="text-gray-400 hover:text-white transition">
            Explore existing platoons &rarr;
        </a>
    </div>

@endsection
