@extends('layouts.app')
@section('title', $tournament->name . ' - Standings')
@section('content')
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
        <div>
            <a href="{{ route('tournaments.show', $tournament) }}" class="text-gray-400 hover:text-white transition text-sm mb-2 inline-block">
                &larr; Back to tournament
            </a>
            <h1 class="text-2xl font-bold text-white">{{ $tournament->name }} - Standings</h1>
        </div>
        <span class="px-3 py-1 text-sm rounded-full {{ $tournament->status_badge }} self-start">
            {{ $tournament->status_text }}
        </span>
    </div>
    <!-- Standings Table -->
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Platoon</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">W</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">L</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Points</th>
                    @if($tournament->format === 'swiss')
                        <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Buchholz</th>
                    @endif
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($standings as $index => $team)
                    <tr class="hover:bg-white/3 {{ $index < 3 ? 'bg-green-500/5' : '' }}">
                        <td class="px-4 py-3">
                            <span class="font-mono {{ $index === 0 ? 'text-yellow-400' : ($index === 1 ? 'text-gray-300' : ($index === 2 ? 'text-orange-400' : 'text-gray-400')) }}">
                                {{ $index + 1 }}
                            </span>
                        </td>
                        <td class="px-4 py-3">
                            <a href="{{ route('teams.show', $team) }}" class="flex items-center gap-3 hover:text-green-400 transition">
                                @if($team->avatar_url)
                                    <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-8 h-8 rounded object-cover">
                                @else
                                    <div class="w-8 h-8 rounded bg-white/5 flex items-center justify-center text-xs font-bold text-gray-400">
                                        {{ strtoupper(substr($team->tag, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-white font-medium">{{ $team->name }}</div>
                                    <div class="text-xs text-gray-500">[{{ $team->tag }}]</div>
                                </div>
                            </a>
                        </td>
                        <td class="px-4 py-3 text-center text-green-400 font-mono">{{ $team->wins }}</td>
                        <td class="px-4 py-3 text-center text-red-400 font-mono">{{ $team->losses }}</td>
                        <td class="px-4 py-3 text-center text-white font-bold font-mono">{{ $team->score }}</td>
                        @if($tournament->format === 'swiss')
                            <td class="px-4 py-3 text-center text-gray-400 font-mono">{{ number_format($team->buchholz, 2) }}</td>
                        @endif
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
    @if($tournament->format === 'swiss')
        <div class="mt-4 text-sm text-gray-400">
            <strong>Buchholz:</strong> Tiebreaker based on opponents' total wins. Higher value = stronger opposition.
        </div>
    @endif
@endsection
