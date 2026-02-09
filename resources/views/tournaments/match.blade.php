@extends('layouts.app')
@section('title', ($match->team1?->name ?? 'TBD') . ' vs ' . ($match->team2?->name ?? 'TBD'))
@section('content')
    <!-- Breadcrumb -->
    <div class="mb-6">
        <a href="{{ route('tournaments.show', $tournament) }}" class="text-gray-400 hover:text-white transition text-sm">
            &larr; {{ $tournament->name }}
        </a>
    </div>
    <!-- Match Header -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
        <div class="text-center mb-4">
            <span class="px-3 py-1 text-sm rounded-full {{ $match->status_badge }}">
                {{ $match->status_text }}
            </span>
            <span class="text-gray-500 mx-2">|</span>
            <span class="text-gray-400">{{ $match->round_label }}</span>
            <span class="text-gray-500 mx-2">|</span>
            <span class="text-gray-400">{{ $match->match_type_text }}</span>
        </div>
        <div class="flex items-center justify-center gap-8">
            <!-- Team 1 -->
            <div class="flex-1 text-center">
                @if($match->team1)
                    <a href="{{ route('teams.show', $match->team1) }}" class="block hover:opacity-80 transition">
                        @if($match->team1->logo_url)
                            <img src="{{ $match->team1->logo_url }}" alt="{{ $match->team1->name }}" class="w-20 h-20 mx-auto rounded-xl object-cover mb-3">
                        @else
                            <div class="w-20 h-20 mx-auto rounded-xl bg-gray-700 flex items-center justify-center text-2xl font-bold text-gray-400 mb-3">
                                {{ strtoupper(substr($match->team1->tag, 0, 2)) }}
                            </div>
                        @endif
                        <h2 class="text-xl font-bold {{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-white' }}">
                            {{ $match->team1->name }}
                        </h2>
                        <p class="text-gray-400 text-sm">[{{ $match->team1->tag }}]</p>
                    </a>
                @else
                    <div class="w-20 h-20 mx-auto rounded-xl bg-gray-700/50 flex items-center justify-center text-gray-500 mb-3">
                        ?
                    </div>
                    <h2 class="text-xl font-bold text-gray-500">TBD</h2>
                @endif
            </div>
            <!-- Score -->
            <div class="text-center">
                <div class="text-4xl font-bold font-mono">
                    <span class="{{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-white' }}">{{ $match->team1_score ?? '-' }}</span>
                    <span class="text-gray-500 mx-2">:</span>
                    <span class="{{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-white' }}">{{ $match->team2_score ?? '-' }}</span>
                </div>
                @if($match->scheduled_at)
                    <p class="text-gray-400 text-sm mt-2">
                        {{ $match->scheduled_at->format('d M Y H:i') }}
                    </p>
                @endif
            </div>
            <!-- Team 2 -->
            <div class="flex-1 text-center">
                @if($match->team2)
                    <a href="{{ route('teams.show', $match->team2) }}" class="block hover:opacity-80 transition">
                        @if($match->team2->logo_url)
                            <img src="{{ $match->team2->logo_url }}" alt="{{ $match->team2->name }}" class="w-20 h-20 mx-auto rounded-xl object-cover mb-3">
                        @else
                            <div class="w-20 h-20 mx-auto rounded-xl bg-gray-700 flex items-center justify-center text-2xl font-bold text-gray-400 mb-3">
                                {{ strtoupper(substr($match->team2->tag, 0, 2)) }}
                            </div>
                        @endif
                        <h2 class="text-xl font-bold {{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-white' }}">
                            {{ $match->team2->name }}
                        </h2>
                        <p class="text-gray-400 text-sm">[{{ $match->team2->tag }}]</p>
                    </a>
                @else
                    <div class="w-20 h-20 mx-auto rounded-xl bg-gray-700/50 flex items-center justify-center text-gray-500 mb-3">
                        ?
                    </div>
                    <h2 class="text-xl font-bold text-gray-500">TBD</h2>
                @endif
            </div>
        </div>
        @if($match->winner)
            <div class="text-center mt-6 pt-6 border-t border-gray-700">
                <span class="text-green-400 font-semibold">
                    Winner: {{ $match->winner->name }}
                </span>
            </div>
        @endif
        {{-- Check-in Section --}}
        @if($match->status !== 'completed' && $match->status !== 'cancelled' && $match->team1 && $match->team2)
            <div class="mt-6 pt-6 border-t border-gray-700">
                <h3 class="text-sm font-medium text-gray-400 mb-4 text-center">Check-in Status</h3>
                <div class="flex items-center justify-center gap-8">
                    {{-- Team 1 Check-in --}}
                    <div class="text-center">
                        <span class="text-sm {{ $match->team1_checked_in ? 'text-green-400' : 'text-gray-500' }}">
                            {{ $match->team1->tag }}
                        </span>
                        @if($match->team1_checked_in)
                            <span class="ml-2 text-green-400">&#10003;</span>
                        @else
                            <span class="ml-2 text-gray-500">&#10007;</span>
                        @endif
                    </div>
                    {{-- Team 2 Check-in --}}
                    <div class="text-center">
                        <span class="text-sm {{ $match->team2_checked_in ? 'text-green-400' : 'text-gray-500' }}">
                            {{ $match->team2->tag }}
                        </span>
                        @if($match->team2_checked_in)
                            <span class="ml-2 text-green-400">&#10003;</span>
                        @else
                            <span class="ml-2 text-gray-500">&#10007;</span>
                        @endif
                    </div>
                </div>
                {{-- Check-in Button for participating teams --}}
                @auth
                    @php
                        $userTeam = auth()->user()->activeTeam;
                        $isParticipant = $userTeam && ($userTeam->id === $match->team1_id || $userTeam->id === $match->team2_id);
                        $canCheckIn = $isParticipant && $match->canCheckIn() && !$match->hasTeamCheckedIn($userTeam);
                        $alreadyCheckedIn = $isParticipant && $match->hasTeamCheckedIn($userTeam);
                    @endphp
                    @if($isParticipant)
                        <div class="mt-4 text-center">
                            @if($match->canCheckIn())
                                @if($alreadyCheckedIn)
                                    <span class="px-4 py-2 bg-green-600/20 text-green-400 rounded-lg">
                                        Your platoon is checked in
                                    </span>
                                @elseif($userTeam->isUserCaptainOrOfficer(auth()->user()))
                                    <form action="{{ route('matches.check-in', $match) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium">
                                            Check In
                                        </button>
                                    </form>
                                @else
                                    <span class="text-gray-400 text-sm">Only platoon leaders can check in</span>
                                @endif
                            @elseif($match->check_in_opens_at && now()->lt($match->check_in_opens_at))
                                <span class="text-gray-400 text-sm">
                                    Check-in opens {{ $match->check_in_opens_at->diffForHumans() }}
                                </span>
                            @elseif($match->check_in_closes_at && now()->gt($match->check_in_closes_at))
                                <span class="text-gray-400 text-sm">Check-in has closed</span>
                            @else
                                <span class="text-gray-400 text-sm">Check-in not available</span>
                            @endif
                        </div>
                    @endif
                @endauth
            </div>
        @endif
    </div>
    <!-- Games (for best of X) -->
    @if($match->games->count() > 0)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
            <h3 class="text-lg font-semibold text-white mb-4">Games</h3>
            <div class="space-y-3">
                @foreach($match->games as $game)
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="text-sm text-gray-400">Game {{ $game->game_number }}</div>
                            @if($game->map)
                                <div class="text-sm text-gray-400">{{ $game->map }}</div>
                            @endif
                        </div>
                        <div class="flex items-center justify-center gap-8 mt-2">
                            <span class="{{ $game->winner_id === $match->team1_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                {{ $game->team1_score ?? '-' }}
                            </span>
                            <span class="text-gray-500">-</span>
                            <span class="{{ $game->winner_id === $match->team2_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                {{ $game->team2_score ?? '-' }}
                            </span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif
    <!-- Team Rosters -->
    <div class="grid md:grid-cols-2 gap-6">
        @if($match->team1)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">{{ $match->team1->name }} Roster</h3>
                <div class="space-y-2">
                    @foreach($match->team1->activeMembers as $member)
                        <div class="flex items-center gap-3">
                            <img src="{{ $member->avatar_display }}" alt="{{ $member->name }}" class="w-8 h-8 rounded-full">
                            <span class="text-white">{{ $member->name }}</span>
                            @if($member->pivot->role !== 'member')
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-700 text-gray-400">
                                    {{ ucfirst($member->pivot->role) }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
        @if($match->team2)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">{{ $match->team2->name }} Roster</h3>
                <div class="space-y-2">
                    @foreach($match->team2->activeMembers as $member)
                        <div class="flex items-center gap-3">
                            <img src="{{ $member->avatar_display }}" alt="{{ $member->name }}" class="w-8 h-8 rounded-full">
                            <span class="text-white">{{ $member->name }}</span>
                            @if($member->pivot->role !== 'member')
                                <span class="text-xs px-2 py-0.5 rounded bg-gray-700 text-gray-400">
                                    {{ ucfirst($member->pivot->role) }}
                                </span>
                            @endif
                        </div>
                    @endforeach
                </div>
            </div>
        @endif
    </div>
    @if($match->notes && auth()->user()?->isAdmin())
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mt-6">
            <h3 class="text-lg font-semibold text-white mb-2">Admin Notes</h3>
            <p class="text-gray-400">{{ $match->notes }}</p>
        </div>
    @endif
@endsection
