@extends('layouts.app')
@section('title', 'Platoons')
@section('content')
    <!-- Header -->
    <div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-8">
        <div>
            <h1 class="text-3xl font-bold text-white">Platoons</h1>
            <p class="text-gray-400 mt-1">Explore our community platoons</p>
        </div>
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
    <!-- Search/Filters -->
    <div class="glass-card rounded-xl p-4 mb-6">
        <form method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search platoons..."
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <label class="flex items-center gap-2 text-gray-400">
                <input type="checkbox" name="verified" value="1" {{ request('verified') ? 'checked' : '' }}
                    class="rounded bg-white/5 border-white/10 text-green-500 focus:ring-green-500">
                Verified only
            </label>
            <button type="submit" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Search
            </button>
            @if(request()->hasAny(['search', 'verified']))
                <a href="{{ route('teams.index') }}" class="px-4 py-2 text-gray-400 hover:text-white transition">
                    Clear
                </a>
            @endif
        </form>
    </div>
    <!-- Platoons Grid -->
    @if($teams->count() > 0)
        <div class="grid md:grid-cols-2 lg:grid-cols-3 gap-6">
            @foreach($teams as $team)
                <a href="{{ route('teams.show', $team) }}" class="glass-card rounded-xl p-6 hover:border-green-500/30 transition group">
                    <div class="flex items-start gap-4">
                        @if($team->avatar_url)
                            <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-16 h-16 rounded-xl object-cover">
                        @else
                            <div class="w-16 h-16 rounded-xl bg-white/5 flex items-center justify-center text-2xl font-bold text-gray-400">
                                {{ strtoupper(substr($team->tag, 0, 2)) }}
                            </div>
                        @endif
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="text-lg font-semibold text-white truncate group-hover:text-green-400 transition">
                                    {{ $team->name }}
                                </h3>
                                @if($team->is_verified)
                                    <svg class="w-5 h-5 text-green-400 flex-shrink-0" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                            <p class="text-gray-400 text-sm mb-2">[{{ $team->tag }}]</p>
                            <div class="flex items-center gap-4 text-sm text-gray-500">
                                <span class="flex items-center gap-1">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                                    </svg>
                                    {{ $team->active_members_count }} members
                                </span>
                            </div>
                        </div>
                    </div>
                    @if($team->description)
                        <p class="text-gray-400 text-sm mt-4 line-clamp-2">{{ $team->description }}</p>
                    @endif
                    <div class="flex items-center gap-2 mt-4 pt-4 border-t border-white/5">
                        <img src="{{ $team->captain->avatar_display }}" alt="{{ $team->captain->name }}" class="w-6 h-6 rounded-full">
                        <span class="text-sm text-gray-400">Captain: {{ $team->captain->name }}</span>
                    </div>
                </a>
            @endforeach
        </div>
        <!-- Pagination -->
        <div class="mt-8">
            {{ $teams->withQueryString()->links() }}
        </div>
    @else
        <div class="glass-card rounded-xl p-12 text-center">
            <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">No platoons found</h3>
            <p class="text-gray-400 mb-4">Be the first to create a platoon!</p>
            @auth
                <a href="{{ route('teams.create') }}" class="inline-block px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                    Create Platoon
                </a>
            @endauth
        </div>
    @endif

@endsection
