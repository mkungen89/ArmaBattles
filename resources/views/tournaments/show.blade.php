@extends('layouts.app')
@section('title', $tournament->name)
@section('content')
    <!-- Header -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden mb-6">
        @if($tournament->banner_url)
            <div class="h-48 sm:h-64 bg-cover bg-center relative" style="background-image: url('{{ $tournament->banner_url }}')">
                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 to-transparent"></div>
            </div>
        @endif
        <div class="p-6 {{ $tournament->banner_url ? '-mt-16 relative' : '' }}">
            <div class="flex flex-col sm:flex-row sm:items-end sm:justify-between gap-4">
                <div>
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <span class="px-3 py-1 text-sm rounded-full {{ $tournament->status_badge }}">
                            {{ $tournament->status_text }}
                        </span>
                        <span class="text-sm text-gray-400">{{ $tournament->format_text }}</span>
                        @if($tournament->is_featured)
                            <span class="px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">Featured</span>
                        @endif
                        @if($tournament->prize_pool)
                            <span class="px-3 py-1 text-sm rounded-full bg-amber-500/20 text-amber-400 border border-amber-500/30 font-medium">
                                <svg class="w-4 h-4 inline -mt-0.5 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                {{ $tournament->prize_pool }}
                            </span>
                        @endif
                    </div>
                    <h1 class="text-3xl font-bold text-white">{{ $tournament->name }}</h1>
                </div>
                <div class="flex gap-2">
                    @if($tournament->stream_url)
                        <a href="{{ $tournament->stream_url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition inline-flex items-center gap-2">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8 5v14l11-7z"/>
                            </svg>
                            Watch Live
                        </a>
                    @endif
                    @if(in_array($tournament->format, ['round_robin', 'swiss']))
                        <a href="{{ route('tournaments.standings', $tournament) }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                            Standings
                        </a>
                    @endif
                    @if($tournament->matches()->exists())
                        <a href="{{ route('tournaments.bracket', $tournament) }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">
                            View Bracket
                        </a>
                    @endif
                </div>
            </div>
        </div>
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Description -->
            @if($tournament->description)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">About the tournament</h2>
                    <div class="prose prose-invert max-w-none text-gray-300">
                        {!! nl2br(e($tournament->description)) !!}
                    </div>
                </div>
            @endif
            <!-- Rules -->
            @if($tournament->rules)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Rules</h2>
                    <div class="prose prose-invert max-w-none text-gray-300">
                        {!! nl2br(e($tournament->rules)) !!}
                    </div>
                </div>
            @endif
            <!-- Upcoming Matches -->
            @if($upcomingMatches->count() > 0)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Upcoming matches</h2>
                    <div class="space-y-3">
                        @foreach($upcomingMatches as $match)
                            <a href="{{ route('tournaments.match', [$tournament, $match]) }}" class="block bg-gray-700/50 rounded-lg p-4 hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <span class="text-white font-medium">{{ $match->team1?->name ?? 'TBD' }}</span>
                                        <span class="text-gray-500">vs</span>
                                        <span class="text-white font-medium">{{ $match->team2?->name ?? 'TBD' }}</span>
                                    </div>
                                    <div class="text-sm text-gray-400">
                                        {{ $match->round_label }}
                                    </div>
                                </div>
                                @if($match->scheduled_at)
                                    <div class="text-sm text-gray-500 mt-2">
                                        {{ $match->scheduled_at->format('d M Y H:i') }}
                                    </div>
                                @endif
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            <!-- Recent Results -->
            @if($recentMatches->count() > 0)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Recent results</h2>
                    <div class="space-y-3">
                        @foreach($recentMatches as $match)
                            <a href="{{ route('tournaments.match', [$tournament, $match]) }}" class="block bg-gray-700/50 rounded-lg p-4 hover:bg-gray-700 transition">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-4">
                                        <span class="font-medium {{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-white' }}">
                                            {{ $match->team1?->name ?? 'TBD' }}
                                        </span>
                                        <span class="text-gray-400 font-mono">{{ $match->score_display }}</span>
                                        <span class="font-medium {{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-white' }}">
                                            {{ $match->team2?->name ?? 'TBD' }}
                                        </span>
                                    </div>
                                    <div class="text-sm text-gray-400">
                                        {{ $match->round_label }}
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
            <!-- Teams -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">
                    Participating platoons ({{ $tournament->approvedTeams->count() }}/{{ $tournament->max_teams }})
                </h2>
                @if($tournament->approvedTeams->count() > 0)
                    <div class="grid sm:grid-cols-2 gap-3">
                        @foreach($tournament->approvedTeams as $team)
                            <a href="{{ route('teams.show', $team) }}" class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 hover:bg-gray-700 transition">
                                @if($team->avatar_url)
                                    <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-10 h-10 rounded-lg object-cover">
                                @else
                                    <div class="w-10 h-10 rounded-lg bg-gray-600 flex items-center justify-center text-gray-400 font-bold">
                                        {{ strtoupper(substr($team->tag, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-white font-medium">{{ $team->name }}</div>
                                    <div class="text-xs text-gray-400">[{{ $team->tag }}]</div>
                                </div>
                                @if($team->pivot->seed)
                                    <span class="ml-auto text-xs text-gray-500">#{{ $team->pivot->seed }}</span>
                                @endif
                            </a>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400">No platoons have registered yet.</p>
                @endif
            </div>
        </div>
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Info Card -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-gray-400">Format</dt>
                        <dd class="text-white">{{ $tournament->format_text }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Platoon size</dt>
                        <dd class="text-white">{{ $tournament->team_size }} players</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Max platoons</dt>
                        <dd class="text-white">{{ $tournament->max_teams }}</dd>
                    </div>
                    @if($tournament->registration_starts_at)
                        <div>
                            <dt class="text-sm text-gray-400">Registration opens</dt>
                            <dd class="text-white">{{ $tournament->registration_starts_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($tournament->registration_ends_at)
                        <div>
                            <dt class="text-sm text-gray-400">Registration closes</dt>
                            <dd class="text-white">{{ $tournament->registration_ends_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($tournament->starts_at)
                        <div>
                            <dt class="text-sm text-gray-400">Tournament starts</dt>
                            <dd class="text-white">{{ $tournament->starts_at->format('d M Y H:i') }}</dd>
                        </div>
                    @endif
                    @if($tournament->server)
                        <div>
                            <dt class="text-sm text-gray-400">Server</dt>
                            <dd>
                                @if($tournament->server->battlemetrics_id)
                                    <a href="{{ route('servers.show', $tournament->server->battlemetrics_id) }}" class="text-green-400 hover:text-green-300">
                                        {{ $tournament->server->name }}
                                    </a>
                                @else
                                    <span class="text-white">{{ $tournament->server->name }}</span>
                                @endif
                            </dd>
                        </div>
                    @endif
                    @if($tournament->prize_pool)
                        <div>
                            <dt class="text-sm text-gray-400">Prize Pool</dt>
                            <dd class="text-amber-400 font-medium">{{ $tournament->prize_pool }}</dd>
                        </div>
                    @endif
                </dl>
            </div>
            <!-- Registration Card -->
            @auth
                @php
                    $userTeam = auth()->user()->activeTeam;
                    $registration = $userTeam ? $tournament->registrations()->where('team_id', $userTeam->id)->first() : null;
                @endphp
                @if($tournament->isRegistrationOpen() && $userTeam)
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Registration</h2>
                        @if($registration)
                            <div class="mb-4">
                                <span class="px-3 py-1 text-sm rounded-full {{ $registration->status_badge }}">
                                    {{ $registration->status_text }}
                                </span>
                            </div>
                            @if($registration->status === 'rejected' && $registration->rejection_reason)
                                <p class="text-red-400 text-sm mb-4">{{ $registration->rejection_reason }}</p>
                            @endif
                            @if(in_array($registration->status, ['pending', 'approved']))
                                <form action="{{ route('teams.withdraw', [$userTeam, $tournament]) }}" method="POST">
                                    @csrf
                                    <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition" onclick="return confirm('Are you sure you want to withdraw?')">
                                        Withdraw registration
                                    </button>
                                </form>
                            @endif
                        @elseif($tournament->canTeamRegister($userTeam))
                            <form action="{{ route('teams.register', $userTeam) }}" method="POST">
                                @csrf
                                <input type="hidden" name="tournament_id" value="{{ $tournament->id }}">
                                <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium">
                                    Register {{ $userTeam->name }}
                                </button>
                            </form>
                            @if($tournament->require_approval)
                                <p class="text-sm text-gray-400 mt-2">Registration requires admin approval.</p>
                            @endif
                        @else
                            <p class="text-gray-400 text-sm">
                                @if($userTeam->activeMembers()->count() < $tournament->team_size)
                                    Your platoon needs at least {{ $tournament->team_size }} members to register.
                                @else
                                    Cannot register platoon for this tournament.
                                @endif
                            </p>
                        @endif
                    </div>
                @elseif(!$userTeam && $tournament->isRegistrationOpen())
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Registration</h2>
                        <p class="text-gray-400 text-sm mb-4">You must be a member of a platoon to register.</p>
                        <a href="{{ route('teams.create') }}" class="block w-full px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition text-center font-medium">
                            Create a platoon
                        </a>
                    </div>
                @endif
            @else
                @if($tournament->isRegistrationOpen())
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        <h2 class="text-lg font-semibold text-white mb-4">Registration</h2>
                        <p class="text-gray-400 text-sm mb-4">Log in to register your platoon.</p>
                        <a href="{{ route('auth.steam') }}" class="block w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition text-center">
                            Login with Steam
                        </a>
                    </div>
                @endif
            @endauth
            <!-- Winner Card -->
            @if($tournament->winner)
                <div class="bg-gradient-to-br from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-yellow-400 mb-4 flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Winner
                    </h2>
                    <a href="{{ route('teams.show', $tournament->winner) }}" class="flex items-center gap-3 hover:opacity-80 transition">
                        @if($tournament->winner->logo_url)
                            <img src="{{ $tournament->winner->logo_url }}" alt="{{ $tournament->winner->name }}" class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-yellow-500/20 flex items-center justify-center text-yellow-400 font-bold">
                                {{ strtoupper(substr($tournament->winner->tag, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <div class="text-white font-semibold">{{ $tournament->winner->name }}</div>
                            <div class="text-sm text-yellow-400/70">[{{ $tournament->winner->tag }}]</div>
                        </div>
                    </a>
                </div>
            @endif
        </div>
    </div>

@endsection
