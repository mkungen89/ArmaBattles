@extends('layouts.app')

@section('title', $tournament->name . ' - Bracket')

@section('content')
<div class="max-w-full mx-auto px-4 sm:px-6 lg:px-8 py-8">
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('tournaments.show', $tournament) }}" class="text-gray-400 hover:text-white transition text-sm mb-2 inline-block">
                &larr; Back to tournament
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $tournament->name }} - Bracket</h1>
        </div>
        <span class="px-3 py-1 text-sm rounded-full {{ $tournament->status_badge }} self-start">
            {{ $tournament->status_text }}
        </span>
    </div>

    @if($tournament->format === 'round_robin' || $tournament->format === 'swiss')
        <!-- Standings-based formats -->
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
            <p class="text-gray-400">
                This is a {{ $tournament->format_text }} format.
                <a href="{{ route('tournaments.standings', $tournament) }}" class="text-green-400 hover:text-green-300">View standings</a>
            </p>
        </div>

        <!-- Match List by Round -->
        @foreach($brackets['main'] as $round => $matches)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
                <h2 class="text-lg font-semibold text-white mb-4">Round {{ $round }}</h2>
                <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($matches as $match)
                        <a href="{{ route('tournaments.match', [$tournament, $match]) }}" class="bg-gray-700/50 rounded-lg p-4 hover:bg-gray-700 transition">
                            <div class="flex items-center justify-between mb-2">
                                <span class="text-xs px-2 py-1 rounded-full {{ $match->status_badge }}">
                                    {{ $match->status_text }}
                                </span>
                                <span class="text-xs text-gray-500">Match #{{ $match->match_number }}</span>
                            </div>
                            <div class="space-y-2">
                                <div class="flex items-center justify-between {{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-white' }}">
                                    <span class="font-medium truncate">{{ $match->team1?->name ?? 'TBD' }}</span>
                                    <span class="font-mono">{{ $match->team1_score ?? '-' }}</span>
                                </div>
                                <div class="flex items-center justify-between {{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-white' }}">
                                    <span class="font-medium truncate">{{ $match->team2?->name ?? 'TBD' }}</span>
                                    <span class="font-mono">{{ $match->team2_score ?? '-' }}</span>
                                </div>
                            </div>
                        </a>
                    @endforeach
                </div>
            </div>
        @endforeach

    @else
        <!-- Elimination Bracket -->
        <div class="overflow-x-auto pb-4">
            <div class="inline-flex gap-8 min-w-max">
                @foreach($brackets['main'] as $round => $matches)
                    <div class="flex flex-col gap-4" style="margin-top: {{ ($round - 1) * 40 }}px;">
                        <h3 class="text-sm font-semibold text-gray-400 text-center mb-2">
                            @if($round === count($brackets['main']))
                                Final
                            @elseif($round === count($brackets['main']) - 1)
                                Semi-Final
                            @elseif($round === count($brackets['main']) - 2)
                                Quarter-Final
                            @else
                                Round {{ $round }}
                            @endif
                        </h3>
                        @foreach($matches as $match)
                            <a href="{{ route('tournaments.match', [$tournament, $match]) }}"
                               class="bg-gray-800 border border-gray-700 rounded-lg w-64 hover:border-green-500/50 transition"
                               style="margin-bottom: {{ pow(2, $round - 1) * 20 - 20 }}px;">
                                <div class="p-3 border-b border-gray-700 {{ $match->winner_id === $match->team1_id ? 'bg-green-500/10' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($match->team1?->logo_url)
                                                <img src="{{ $match->team1->logo_url }}" class="w-6 h-6 rounded object-cover flex-shrink-0">
                                            @endif
                                            <span class="truncate {{ $match->winner_id === $match->team1_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                                {{ $match->team1?->name ?? 'TBD' }}
                                            </span>
                                        </div>
                                        <span class="font-mono text-sm {{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-gray-400' }}">
                                            {{ $match->team1_score ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                                <div class="p-3 {{ $match->winner_id === $match->team2_id ? 'bg-green-500/10' : '' }}">
                                    <div class="flex items-center justify-between">
                                        <div class="flex items-center gap-2 min-w-0">
                                            @if($match->team2?->logo_url)
                                                <img src="{{ $match->team2->logo_url }}" class="w-6 h-6 rounded object-cover flex-shrink-0">
                                            @endif
                                            <span class="truncate {{ $match->winner_id === $match->team2_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                                {{ $match->team2?->name ?? 'TBD' }}
                                            </span>
                                        </div>
                                        <span class="font-mono text-sm {{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-gray-400' }}">
                                            {{ $match->team2_score ?? '-' }}
                                        </span>
                                    </div>
                                </div>
                            </a>
                        @endforeach
                    </div>
                @endforeach

                @if($brackets['grand_final'])
                    <div class="flex flex-col gap-4" style="margin-top: {{ (count($brackets['main']) - 1) * 40 }}px;">
                        <h3 class="text-sm font-semibold text-yellow-400 text-center mb-2">Grand Final</h3>
                        @php $match = $brackets['grand_final']; @endphp
                        <a href="{{ route('tournaments.match', [$tournament, $match]) }}"
                           class="bg-gradient-to-br from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-lg w-64 hover:border-yellow-500/50 transition">
                            <div class="p-3 border-b border-yellow-500/30 {{ $match->winner_id === $match->team1_id ? 'bg-yellow-500/10' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if($match->team1?->logo_url)
                                            <img src="{{ $match->team1->logo_url }}" class="w-6 h-6 rounded object-cover flex-shrink-0">
                                        @endif
                                        <span class="truncate {{ $match->winner_id === $match->team1_id ? 'text-yellow-400 font-semibold' : 'text-white' }}">
                                            {{ $match->team1?->name ?? 'Winners Bracket' }}
                                        </span>
                                    </div>
                                    <span class="font-mono text-sm {{ $match->winner_id === $match->team1_id ? 'text-yellow-400' : 'text-gray-400' }}">
                                        {{ $match->team1_score ?? '-' }}
                                    </span>
                                </div>
                            </div>
                            <div class="p-3 {{ $match->winner_id === $match->team2_id ? 'bg-yellow-500/10' : '' }}">
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-2 min-w-0">
                                        @if($match->team2?->logo_url)
                                            <img src="{{ $match->team2->logo_url }}" class="w-6 h-6 rounded object-cover flex-shrink-0">
                                        @endif
                                        <span class="truncate {{ $match->winner_id === $match->team2_id ? 'text-yellow-400 font-semibold' : 'text-white' }}">
                                            {{ $match->team2?->name ?? 'Losers Bracket' }}
                                        </span>
                                    </div>
                                    <span class="font-mono text-sm {{ $match->winner_id === $match->team2_id ? 'text-yellow-400' : 'text-gray-400' }}">
                                        {{ $match->team2_score ?? '-' }}
                                    </span>
                                </div>
                            </div>
                        </a>
                    </div>
                @endif
            </div>
        </div>

        @if(count($brackets['losers']) > 0)
            <div class="mt-8">
                <h2 class="text-xl font-bold text-white mb-4">Losers Bracket</h2>
                <div class="overflow-x-auto pb-4">
                    <div class="inline-flex gap-8 min-w-max">
                        @foreach($brackets['losers'] as $round => $matches)
                            <div class="flex flex-col gap-4">
                                <h3 class="text-sm font-semibold text-gray-400 text-center mb-2">
                                    Losers Round {{ $round }}
                                </h3>
                                @foreach($matches as $match)
                                    <a href="{{ route('tournaments.match', [$tournament, $match]) }}"
                                       class="bg-gray-800 border border-gray-700 rounded-lg w-64 hover:border-red-500/50 transition">
                                        <div class="p-3 border-b border-gray-700 {{ $match->winner_id === $match->team1_id ? 'bg-green-500/10' : '' }}">
                                            <div class="flex items-center justify-between">
                                                <span class="truncate {{ $match->winner_id === $match->team1_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                                    {{ $match->team1?->name ?? 'TBD' }}
                                                </span>
                                                <span class="font-mono text-sm {{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-gray-400' }}">
                                                    {{ $match->team1_score ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                        <div class="p-3 {{ $match->winner_id === $match->team2_id ? 'bg-green-500/10' : '' }}">
                                            <div class="flex items-center justify-between">
                                                <span class="truncate {{ $match->winner_id === $match->team2_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                                    {{ $match->team2?->name ?? 'TBD' }}
                                                </span>
                                                <span class="font-mono text-sm {{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-gray-400' }}">
                                                    {{ $match->team2_score ?? '-' }}
                                                </span>
                                            </div>
                                        </div>
                                    </a>
                                @endforeach
                            </div>
                        @endforeach
                    </div>
                </div>
            </div>
        @endif
    @endif
</div>
@endsection
