@extends('layouts.app')
@section('title', 'Practice Matches (Scrims)')
@section('content')
<div class="py-12 space-y-6">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Practice Matches (Scrims)</h1>
                    <p class="text-gray-400">Challenge other teams to practice matches. Stats tracked separately from ranked play.</p>
                </div>
                @if($userTeam && $userTeam->isUserCaptainOrOfficer(auth()->user()))
                <a href="{{ route('scrims.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Challenge Team
                </a>
                @endif
            </div>
        </div>
        @if(!$userTeam)
        {{-- No Team Warning --}}
        <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
            <div class="flex items-start gap-3">
                <svg class="w-6 h-6 text-yellow-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <div class="text-sm text-yellow-300">
                    <p class="font-semibold mb-1">You need to be in a team to participate in scrims.</p>
                    <p>Join an existing team or <a href="{{ route('teams.create') }}" class="underline hover:text-yellow-200">create your own</a>.</p>
                </div>
            </div>
        </div>
        @endif
        {{-- Pending Invitations --}}
        @if($pendingInvitations->isNotEmpty())
        <div class="bg-gray-800/50 backdrop-blur border border-blue-500/30 rounded-xl overflow-hidden">
            <div class="bg-blue-500/10 border-b border-blue-500/30 px-6 py-4">
                <h2 class="text-xl font-bold text-white flex items-center gap-2">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                    Pending Invitations ({{ $pendingInvitations->count() }})
                </h2>
            </div>
            <div class="divide-y divide-gray-700">
                @foreach($pendingInvitations as $invitation)
                <div class="p-6 hover:bg-gray-700/30 transition">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-white">
                                    {{ $invitation->invitingTeam->name }}
                                </h3>
                                <span class="text-gray-400">vs</span>
                                <h3 class="text-lg font-semibold text-white">
                                    {{ $userTeam->name }}
                                </h3>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $invitation->scrimMatch->scheduled_at->format('M j, Y @ g:i A') }}
                                </span>
                                @if($invitation->scrimMatch->map)
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                                    </svg>
                                    {{ $invitation->scrimMatch->map }}
                                </span>
                                @endif
                                <span class="text-gray-500">Expires {{ $invitation->expires_at->diffForHumans() }}</span>
                            </div>
                        </div>
                        <div class="flex gap-2">
                            <form action="{{ route('scrims.invitations.accept', $invitation) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-green-600/20 hover:bg-green-600/30 text-green-400 rounded-lg transition">
                                    Accept
                                </button>
                            </form>
                            <form action="{{ route('scrims.invitations.decline', $invitation) }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition">
                                    Decline
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endif
        {{-- Upcoming Scrims --}}
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl overflow-hidden">
            <div class="bg-gray-900/50 border-b border-gray-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Upcoming Scrims</h2>
            </div>
            <div class="p-6">
                @forelse($upcomingScrims as $scrim)
                <a href="{{ route('scrims.show', $scrim) }}" class="block p-4 bg-gray-900/50 hover:bg-gray-700/30 rounded-lg mb-3 last:mb-0 transition">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-white">{{ $scrim->team1->name }}</h3>
                                <span class="text-gray-400">vs</span>
                                <h3 class="text-lg font-semibold text-white">{{ $scrim->team2->name }}</h3>
                            </div>
                            <div class="flex items-center gap-4 text-sm text-gray-400">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    {{ $scrim->scheduled_at->format('M j, Y @ g:i A') }}
                                </span>
                                @if($scrim->map)
                                <span>{{ $scrim->map }}</span>
                                @endif
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $scrim->status_color }} bg-gray-700/50">
                            {{ $scrim->status_label }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <svg class="w-16 h-16 mx-auto mb-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    <p class="text-lg font-semibold mb-1">No upcoming scrims</p>
                    <p class="text-sm">Challenge another team to schedule a practice match!</p>
                </div>
                @endforelse
            </div>
        </div>
        {{-- Completed Scrims --}}
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl overflow-hidden">
            <div class="bg-gray-900/50 border-b border-gray-700 px-6 py-4">
                <h2 class="text-xl font-bold text-white">Recent Scrims</h2>
            </div>
            <div class="p-6">
                @forelse($completedScrims as $scrim)
                <a href="{{ route('scrims.show', $scrim) }}" class="block p-4 bg-gray-900/50 hover:bg-gray-700/30 rounded-lg mb-3 last:mb-0 transition">
                    <div class="flex items-center justify-between gap-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold {{ $scrim->winner_id === $scrim->team1_id ? 'text-green-400' : 'text-white' }}">
                                    {{ $scrim->team1->name }}
                                </h3>
                                @if($scrim->isCompleted())
                                <span class="text-gray-400">{{ $scrim->team1_score }} - {{ $scrim->team2_score }}</span>
                                @else
                                <span class="text-gray-400">vs</span>
                                @endif
                                <h3 class="text-lg font-semibold {{ $scrim->winner_id === $scrim->team2_id ? 'text-green-400' : 'text-white' }}">
                                    {{ $scrim->team2->name }}
                                </h3>
                            </div>
                            <div class="text-sm text-gray-400">
                                {{ $scrim->completed_at ? $scrim->completed_at->format('M j, Y @ g:i A') : $scrim->scheduled_at->format('M j, Y @ g:i A') }}
                            </div>
                        </div>
                        <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $scrim->status_color }} bg-gray-700/50">
                            {{ $scrim->status_label }}
                        </span>
                    </div>
                </a>
                @empty
                <div class="text-center py-12 text-gray-400">
                    <p>No completed scrims yet.</p>
                </div>
                @endforelse
            </div>
        </div>
    </div>

@endsection
