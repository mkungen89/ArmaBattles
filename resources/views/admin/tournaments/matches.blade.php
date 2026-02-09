@extends('admin.layout')

@section('title', 'Matches - ' . $tournament->name)

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.tournaments.show', $tournament) }}" class="text-gray-400 hover:text-white transition text-sm">
        &larr; Back to {{ $tournament->name }}
    </a>
</div>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Matches</h1>
    <a href="{{ route('tournaments.bracket', $tournament) }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition" target="_blank">
        View bracket
    </a>
</div>

@if($matches->count() > 0)
    @foreach($groupedMatches as $round => $roundMatches)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">{{ $round }}</h2>

            <div class="space-y-3">
                @foreach($roundMatches as $match)
                    <div class="bg-gray-700/50 rounded-lg p-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-4">
                                <span class="text-xs text-gray-500">Match #{{ $match->match_number }}</span>

                                <div class="flex items-center gap-3">
                                    <span class="{{ $match->winner_id === $match->team1_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                        {{ $match->team1?->name ?? 'TBD' }}
                                    </span>
                                    <span class="text-gray-400 font-mono">{{ $match->score_display }}</span>
                                    <span class="{{ $match->winner_id === $match->team2_id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                        {{ $match->team2?->name ?? 'TBD' }}
                                    </span>
                                </div>

                                <span class="px-2 py-1 text-xs rounded-full {{ $match->status_badge }}">
                                    {{ $match->status_text }}
                                </span>
                            </div>

                            <a href="{{ route('admin.matches.edit', $match) }}" class="px-3 py-1 bg-gray-600 hover:bg-gray-500 text-white rounded text-sm">
                                Edit
                            </a>
                        </div>

                        @if($match->scheduled_at)
                            <div class="text-xs text-gray-500 mt-2">
                                Scheduled: {{ $match->scheduled_at->format('d M Y H:i') }}
                            </div>
                        @endif
                    </div>
                @endforeach
            </div>
        </div>
    @endforeach
@else
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-12 text-center">
        <p class="text-gray-400">No bracket has been generated yet.</p>
        <a href="{{ route('admin.tournaments.show', $tournament) }}" class="text-green-400 hover:text-green-300 mt-2 inline-block">
            Go back to generate bracket
        </a>
    </div>
@endif
@endsection
