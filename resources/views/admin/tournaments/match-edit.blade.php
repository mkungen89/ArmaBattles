@extends('admin.layout')

@section('title', 'Edit Match')

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.tournaments.matches', $match->tournament) }}" class="text-gray-400 hover:text-white transition text-sm">
        &larr; Back to matches
    </a>
</div>

<div class="max-w-2xl">
    <h1 class="text-2xl font-bold text-white mb-6">Edit Match #{{ $match->match_number }}</h1>

    <!-- Match Preview -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
        <div class="text-center">
            <div class="text-sm text-gray-400 mb-2">{{ $match->round_label }}</div>
            <div class="flex items-center justify-center gap-6 text-xl">
                <span class="{{ $match->winner_id === $match->team1_id ? 'text-green-400' : 'text-white' }}">
                    {{ $match->team1?->name ?? 'TBD' }}
                </span>
                <span class="text-gray-500">vs</span>
                <span class="{{ $match->winner_id === $match->team2_id ? 'text-green-400' : 'text-white' }}">
                    {{ $match->team2?->name ?? 'TBD' }}
                </span>
            </div>
        </div>
    </div>

    <!-- Edit Form -->
    <form action="{{ route('admin.matches.update', $match) }}" method="POST" class="space-y-6">
        @csrf
        @method('PUT')

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Match settings</h2>

            <div class="space-y-4">
                <div>
                    <label for="status" class="block text-sm font-medium text-gray-400 mb-2">Status</label>
                    <select name="status" id="status" required
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="pending" {{ $match->status === 'pending' ? 'selected' : '' }}>Pending</option>
                        <option value="scheduled" {{ $match->status === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                        <option value="in_progress" {{ $match->status === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                        <option value="completed" {{ $match->status === 'completed' ? 'selected' : '' }}>Completed</option>
                        <option value="disputed" {{ $match->status === 'disputed' ? 'selected' : '' }}>Disputed</option>
                        <option value="cancelled" {{ $match->status === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
                    </select>
                </div>

                <div>
                    <label for="match_type" class="block text-sm font-medium text-gray-400 mb-2">Match type</label>
                    <select name="match_type" id="match_type"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="best_of_1" {{ $match->match_type === 'best_of_1' ? 'selected' : '' }}>Best of 1</option>
                        <option value="best_of_3" {{ $match->match_type === 'best_of_3' ? 'selected' : '' }}>Best of 3</option>
                        <option value="best_of_5" {{ $match->match_type === 'best_of_5' ? 'selected' : '' }}>Best of 5</option>
                    </select>
                </div>

                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-400 mb-2">Scheduled time</label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at"
                        value="{{ $match->scheduled_at?->format('Y-m-d\TH:i') }}"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Result</h2>

            <div class="space-y-4">
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="team1_score" class="block text-sm font-medium text-gray-400 mb-2">
                            {{ $match->team1?->name ?? 'Platoon 1' }} score
                        </label>
                        <input type="number" name="team1_score" id="team1_score" min="0"
                            value="{{ old('team1_score', $match->team1_score) }}"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="team2_score" class="block text-sm font-medium text-gray-400 mb-2">
                            {{ $match->team2?->name ?? 'Platoon 2' }} score
                        </label>
                        <input type="number" name="team2_score" id="team2_score" min="0"
                            value="{{ old('team2_score', $match->team2_score) }}"
                            class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                <div>
                    <label for="winner_id" class="block text-sm font-medium text-gray-400 mb-2">Winner</label>
                    <select name="winner_id" id="winner_id"
                        class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">No winner</option>
                        @if($match->team1)
                            <option value="{{ $match->team1_id }}" {{ $match->winner_id === $match->team1_id ? 'selected' : '' }}>
                                {{ $match->team1->name }}
                            </option>
                        @endif
                        @if($match->team2)
                            <option value="{{ $match->team2_id }}" {{ $match->winner_id === $match->team2_id ? 'selected' : '' }}>
                                {{ $match->team2->name }}
                            </option>
                        @endif
                    </select>
                    <p class="text-xs text-gray-500 mt-1">If status is "Completed" with a winner, the winner will automatically advance to the next match.</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Admin notes</h2>

            <textarea name="notes" rows="3"
                class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                placeholder="Internal notes about the match...">{{ old('notes', $match->notes) }}</textarea>
        </div>

        <div class="flex gap-4">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium">
                Save changes
            </button>
            <a href="{{ route('admin.tournaments.matches', $match->tournament) }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                Cancel
            </a>
        </div>
    </form>
</div>
@endsection
