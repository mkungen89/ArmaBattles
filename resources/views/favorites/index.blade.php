@extends('layouts.app')

@section('title', 'My Favorites')

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">My Favorites</h1>
    </div>

    {{-- Favorite Players --}}
    @if($players->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
            </svg>
            Players ({{ $players->count() }})
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($players as $player)
            <div class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 border border-gray-600/30">
                <img src="{{ $player->avatar_display }}" alt="{{ $player->name }}" class="w-10 h-10 rounded-full">
                <div class="flex-1 min-w-0">
                    <a href="{{ route('player.profile', $player) }}" class="text-white font-medium hover:text-green-400 transition truncate block">{{ $player->name }}</a>
                    <span class="text-xs text-gray-400">{{ ucfirst($player->role) }}</span>
                </div>
                <form action="{{ route('favorites.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="player">
                    <input type="hidden" name="id" value="{{ $player->id }}">
                    <button type="submit" class="text-yellow-400 hover:text-yellow-300 transition" title="Remove from favorites">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Favorite Teams --}}
    @if($teams->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
            </svg>
            Platoons ({{ $teams->count() }})
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($teams as $team)
            <div class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 border border-gray-600/30">
                @if($team->avatar_url)
                    <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-10 h-10 rounded-lg">
                @else
                    <div class="w-10 h-10 rounded-lg bg-gray-600 flex items-center justify-center text-white font-bold text-sm">{{ $team->tag ?? substr($team->name, 0, 2) }}</div>
                @endif
                <div class="flex-1 min-w-0">
                    <a href="{{ route('teams.show', $team) }}" class="text-white font-medium hover:text-blue-400 transition truncate block">[{{ $team->tag }}] {{ $team->name }}</a>
                    <span class="text-xs text-gray-400">{{ $team->activeMembers->count() ?? 0 }} members</span>
                </div>
                <form action="{{ route('favorites.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="team">
                    <input type="hidden" name="id" value="{{ $team->id }}">
                    <button type="submit" class="text-yellow-400 hover:text-yellow-300 transition" title="Remove from favorites">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Favorite Servers --}}
    @if($servers->isNotEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-6">
        <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
            </svg>
            Servers ({{ $servers->count() }})
        </h2>
        <div class="grid grid-cols-1 sm:grid-cols-2 lg:grid-cols-3 gap-4">
            @foreach($servers as $server)
            <div class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 border border-gray-600/30">
                <div class="w-10 h-10 rounded-lg bg-gray-600 flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div class="flex-1 min-w-0">
                    <a href="{{ route('servers.show', $server) }}" class="text-white font-medium hover:text-purple-400 transition truncate block">{{ $server->name }}</a>
                    <span class="text-xs text-gray-400">{{ $server->scenario_display_name ?? 'Unknown' }}</span>
                </div>
                <form action="{{ route('favorites.toggle') }}" method="POST">
                    @csrf
                    <input type="hidden" name="type" value="server">
                    <input type="hidden" name="id" value="{{ $server->id }}">
                    <button type="submit" class="text-yellow-400 hover:text-yellow-300 transition" title="Remove from favorites">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                    </button>
                </form>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Empty State --}}
    @if($players->isEmpty() && $teams->isEmpty() && $servers->isEmpty())
    <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-12 text-center">
        <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        <h3 class="text-lg font-semibold text-gray-400 mb-2">No favorites yet</h3>
        <p class="text-gray-500">Star players, platoons, or servers to add them to your favorites.</p>
    </div>
    @endif
</div>
@endsection
