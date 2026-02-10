@extends('admin.layout')

@section('title', 'Tournaments')

@section('admin-content')
<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Tournaments</h1>
    <a href="{{ route('admin.tournaments.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
        Create Tournament
    </a>
</div>

<!-- Filters -->
<div class="glass-card rounded-xl p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <select name="status" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <option value="">All statuses</option>
            <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
            <option value="registration_open" {{ request('status') === 'registration_open' ? 'selected' : '' }}>Registration Open</option>
            <option value="registration_closed" {{ request('status') === 'registration_closed' ? 'selected' : '' }}>Registration Closed</option>
            <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
            <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
            <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
            Filter
        </button>
        @if(request('status'))
            <a href="{{ route('admin.tournaments.index') }}" class="px-4 py-2 text-gray-400 hover:text-white transition">
                Clear
            </a>
        @endif
    </form>
</div>

<!-- Tournaments List -->
@if($tournaments->count() > 0)
    <div class="space-y-4">
        @foreach($tournaments as $tournament)
            <div class="glass-card rounded-xl p-4">
                <div class="flex items-start justify-between gap-4">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-3 mb-2">
                            <h2 class="text-lg font-semibold text-white truncate">{{ $tournament->name }}</h2>
                            <span class="px-2 py-1 text-xs rounded-full {{ $tournament->status_badge }}">
                                {{ $tournament->status_text }}
                            </span>
                            @if($tournament->is_featured)
                                <span class="px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">Featured</span>
                            @endif
                        </div>
                        <div class="flex flex-wrap gap-4 text-sm text-gray-400">
                            <span>{{ $tournament->format_text }}</span>
                            <span>{{ $tournament->approved_teams_count }}/{{ $tournament->max_teams }} platoons</span>
                            <span>{{ $tournament->matches_count }} matches</span>
                            @if($tournament->starts_at)
                                <span>Start: {{ $tournament->starts_at->format('d M Y') }}</span>
                            @endif
                        </div>
                        @if($tournament->winner)
                            <div class="text-sm text-green-400 mt-2">
                                Winner: {{ $tournament->winner->name }}
                            </div>
                        @endif
                    </div>
                    <div class="flex gap-2">
                        <a href="{{ route('admin.tournaments.show', $tournament) }}" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm">
                            Manage
                        </a>
                        <a href="{{ route('tournaments.show', $tournament) }}" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm" target="_blank">
                            View
                        </a>
                    </div>
                </div>
            </div>
        @endforeach
    </div>

    <div class="mt-6">
        {{ $tournaments->withQueryString()->links() }}
    </div>
@else
    <div class="glass-card rounded-xl p-12 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
        </svg>
        <h3 class="text-lg font-semibold text-white mb-2">No tournaments</h3>
        <p class="text-gray-400 mb-4">Create your first tournament now!</p>
        <a href="{{ route('admin.tournaments.create') }}" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
            Create Tournament
        </a>
    </div>
@endif
@endsection
