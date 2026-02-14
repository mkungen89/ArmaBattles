@extends('layouts.app')
@section('title', 'Tournaments')
@section('content')
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Tournaments</h1>
            <p class="text-gray-400 mt-1">Compete with your platoon in our tournaments</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('rss.tournaments') }}" target="_blank" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white rounded-xl transition font-medium text-sm flex items-center gap-2" title="Subscribe to RSS feed">
                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24"><path d="M6.18 15.64a2.18 2.18 0 0 1 2.18 2.18C8.36 19 7.38 20 6.18 20C5 20 4 19 4 17.82a2.18 2.18 0 0 1 2.18-2.18M4 4.44A15.56 15.56 0 0 1 19.56 20h-2.83A12.73 12.73 0 0 0 4 7.27V4.44m0 5.66a9.9 9.9 0 0 1 9.9 9.9h-2.83A7.07 7.07 0 0 0 4 12.93V10.1Z"/></svg>
                RSS Feed
            </a>
            @auth
                @if(auth()->user()->hasTeam())
                    <a href="{{ route('teams.my') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
                        My Platoon
                    </a>
                @else
                    <a href="{{ route('teams.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
                        Create Platoon
                    </a>
                @endif
            @endauth
        </div>
    </div>
    <!-- Filters -->
    <div class="glass-card rounded-xl p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div>
                <select name="status" class="bg-white/5 border border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All statuses</option>
                    <option value="registration_open" {{ request('status') === 'registration_open' ? 'selected' : '' }}>Registration Open</option>
                    <option value="registration_closed" {{ request('status') === 'registration_closed' ? 'selected' : '' }}>Registration Closed</option>
                    <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                    <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                </select>
            </div>
            <div>
                <select name="format" class="bg-white/5 border border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All formats</option>
                    <option value="single_elimination" {{ request('format') === 'single_elimination' ? 'selected' : '' }}>Single Elimination</option>
                    <option value="double_elimination" {{ request('format') === 'double_elimination' ? 'selected' : '' }}>Double Elimination</option>
                    <option value="round_robin" {{ request('format') === 'round_robin' ? 'selected' : '' }}>Round Robin</option>
                    <option value="swiss" {{ request('format') === 'swiss' ? 'selected' : '' }}>Swiss</option>
                </select>
            </div>
            <button type="submit" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['status', 'format', 'upcoming', 'past']))
                <a href="{{ route('tournaments.index') }}" class="px-4 py-2 text-gray-400 hover:text-white transition">
                    Clear filters
                </a>
            @endif
        </form>
    </div>
    <!-- Tournament Grid -->
    @if($tournaments->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($tournaments as $tournament)
                <a href="{{ route('tournaments.show', $tournament) }}" class="glass-card rounded-xl overflow-hidden hover:border-green-500/30 transition group">
                    @if($tournament->banner_url)
                        <div class="h-40 bg-cover bg-center" style="background-image: url('{{ $tournament->banner_url }}')"></div>
                    @else
                        <div class="h-40 bg-gradient-to-br from-green-500/20 to-blue-500/20 flex items-center justify-center">
                            <svg class="w-16 h-16 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                            </svg>
                        </div>
                    @endif
                    <div class="p-4">
                        <div class="flex items-start justify-between gap-2 mb-2">
                            <h3 class="text-lg font-semibold text-white group-hover:text-green-400 transition">
                                {{ $tournament->name }}
                            </h3>
                            @if($tournament->is_featured)
                                <span class="px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">Featured</span>
                            @endif
                        </div>
                        <div class="flex items-center gap-2 mb-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $tournament->status_badge }}">
                                {{ $tournament->status_text }}
                            </span>
                            <span class="text-xs text-gray-500">{{ $tournament->format_text }}</span>
                        </div>
                        <div class="space-y-2 text-sm text-gray-400">
                            <div class="flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                                </svg>
                                <span>{{ $tournament->approved_teams_count }}/{{ $tournament->max_teams }} platoons</span>
                            </div>
                            @if($tournament->starts_at)
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                    </svg>
                                    <span>{{ $tournament->starts_at->format('d M Y') }}</span>
                                </div>
                            @endif
                            @if($tournament->winner)
                                <div class="flex items-center gap-2 text-green-400">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                                    </svg>
                                    <span>Winner: {{ $tournament->winner->name }}</span>
                                </div>
                            @endif
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
        <!-- Pagination -->
        <div class="mt-8">
            {{ $tournaments->withQueryString()->links() }}
        </div>
    @else
        <div class="glass-card rounded-xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
            </svg>
            <h3 class="text-lg font-semibold text-white mb-2">No tournaments found</h3>
            <p class="text-gray-400">Check back soon for new tournaments!</p>
        </div>
    @endif

@endsection
