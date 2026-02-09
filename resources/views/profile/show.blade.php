@extends('layouts.app')

@section('title', 'My Profile')

@section('content')
<div class="space-y-6">

    {{-- Profile Banner Header --}}
    <div class="relative bg-gradient-to-r from-green-600/20 via-gray-800 to-green-600/20 rounded-2xl border border-gray-700/50 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-b from-transparent to-gray-900/50 pointer-events-none"></div>
        <div class="relative p-6 sm:p-8">
            <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
                {{-- Avatar --}}
                <img src="{{ $user->avatar_display }}"
                     alt="{{ $user->name }}"
                     class="w-28 h-28 rounded-full ring-4 ring-green-500/50 shadow-lg shadow-green-500/20 flex-shrink-0">

                {{-- Info --}}
                <div class="flex-1 text-center sm:text-left">
                    <div class="flex flex-col sm:flex-row items-center sm:items-start gap-3 mb-2">
                        <h1 class="text-2xl sm:text-3xl font-bold text-white">{{ $user->name }}</h1>
                        <span class="px-3 py-1 rounded-full text-sm font-medium
                            @if($user->isAdmin()) bg-red-500/20 text-red-400 border border-red-500/30
                            @elseif($user->isModerator()) bg-yellow-500/20 text-yellow-400 border border-yellow-500/30
                            @else bg-green-500/20 text-green-400 border border-green-500/30
                            @endif">
                            {{ ucfirst($user->role) }}
                        </span>
                        <a href="{{ route('reputation.show', $user) }}" class="px-3 py-1 rounded-full text-sm font-medium {{ $reputation->badge_color }} bg-gray-700/50 border border-gray-600/30 hover:border-gray-500/50 transition flex items-center gap-1">
                            @if($reputation->isTrusted())
                                <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            @elseif($reputation->isFlagged())
                                <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M8.257 3.099c.765-1.36 2.722-1.36 3.486 0l5.58 9.92c.75 1.334-.213 2.98-1.742 2.98H4.42c-1.53 0-2.493-1.646-1.743-2.98l5.58-9.92zM11 13a1 1 0 11-2 0 1 1 0 012 0zm-1-8a1 1 0 00-1 1v3a1 1 0 002 0V6a1 1 0 00-1-1z" clip-rule="evenodd"/>
                                </svg>
                            @else
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                                </svg>
                            @endif
                            {{ $reputation->total_score > 0 ? '+' : '' }}{{ $reputation->total_score }} Rep
                        </a>
                    </div>
                    <p class="text-sm text-gray-400 mb-3">Steam ID: {{ $user->steam_id }}</p>

                    <div class="flex flex-wrap items-center justify-center sm:justify-start gap-4">
                        @if($user->profile_url)
                        <a href="{{ $user->profile_url }}" target="_blank"
                           class="flex items-center gap-2 text-sm text-green-400 hover:text-green-300 transition">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174l2.896-4.143c-.468-.116-.91-.293-1.317-.525L4.5 21.75c-.913-.288-1.772-.684-2.563-1.176l4.707-3.308c-.155-.369-.277-.758-.359-1.162L0 19.293V12C0 5.373 5.373 0 12 0z"/>
                            </svg>
                            Steam Profile
                        </a>
                        @endif
                        @if($user->discord_username)
                        <div class="flex items-center gap-2 text-sm text-indigo-400">
                            <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                            </svg>
                            {{ $user->discord_username }}
                        </div>
                        @endif
                        @include('profile._social-links', ['user' => $user])
                        <span class="text-xs text-gray-500">Member since {{ $user->created_at->format('F Y') }}</span>
                        @if($user->last_seen_at)
                        <span class="text-xs text-gray-500">&middot; Last seen {{ $user->last_seen_at->diffForHumans() }}</span>
                        @endif
                    </div>
                </div>

                {{-- Action Buttons --}}
                <div class="absolute top-4 right-4 sm:relative sm:top-auto sm:right-auto flex gap-2 flex-shrink-0">
                    @if($gameStats)
                    {{-- Export Stats Dropdown --}}
                    <div x-data="{ open: false }" class="relative">
                        <button @click="open = !open" class="px-4 py-2 bg-green-600/80 hover:bg-green-500 text-white rounded-lg transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                            </svg>
                            <span class="hidden sm:inline">Export</span>
                        </button>
                        <div x-show="open" @click.away="open = false" x-cloak class="absolute right-0 mt-2 w-56 bg-gray-800 border border-gray-700 rounded-lg shadow-xl z-50">
                            <a href="{{ route('export.player.stats', $gameStats->player_uuid) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded-t-lg transition">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                                    Stats (CSV)
                                </div>
                            </a>
                            <a href="{{ route('export.player.history', $gameStats->player_uuid) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-gray-700 rounded-b-lg transition">
                                <div class="flex items-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Match History (CSV)
                                </div>
                            </a>
                        </div>
                    </div>
                    @endif

                    {{-- Settings Button --}}
                    <a href="{{ route('profile.settings') }}" class="px-4 py-2 bg-gray-700/80 hover:bg-gray-600 text-white rounded-lg transition flex items-center gap-2">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="hidden sm:inline">Settings</span>
                    </a>
                </div>
            </div>
        </div>
    </div>

    {{-- Achievement Showcase --}}
    @if(isset($showcaseAchievements) && $showcaseAchievements->count() > 0)
    <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-xl p-6">
        <div class="flex items-center gap-3 mb-4">
            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
            </svg>
            <h3 class="text-lg font-semibold text-white">Achievement Showcase</h3>
        </div>
        <div class="grid grid-cols-1 sm:grid-cols-3 gap-4">
            @foreach($showcaseAchievements as $achievement)
            <div class="bg-gray-800/60 border border-green-500/20 rounded-xl p-4 text-center">
                <div class="flex items-center justify-center mb-3">
                    @if($achievement->badge_path)
                        <img src="{{ asset('storage/' . $achievement->badge_path) }}" alt="{{ $achievement->name }}" class="w-16 h-16 object-contain">
                    @else
                        <div class="w-16 h-16 rounded-full flex items-center justify-center" style="background-color: {{ $achievement->color }}20;">
                            <i data-lucide="{{ $achievement->icon }}" class="w-8 h-8" style="color: {{ $achievement->color }};"></i>
                        </div>
                    @endif
                </div>
                <p class="text-sm font-bold text-white mb-1">{{ $achievement->name }}</p>
                <p class="text-xs text-gray-400 line-clamp-2">{{ $achievement->description }}</p>
                <div class="mt-2">
                    <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium" style="background: {{ $achievement->color }}20; color: {{ $achievement->color }};">
                        {{ $achievement->points }} pts
                    </span>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Hero Combat Stats --}}
    @if($gameStats)
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Kills --}}
        <div class="bg-gradient-to-br from-green-500/10 to-gray-800/50 rounded-xl p-5 border border-gray-700/50">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm0 3c1.66 0 3 1.34 3 3s-1.34 3-3 3-3-1.34-3-3 1.34-3 3-3zm0 14.2c-2.5 0-4.71-1.28-6-3.22.03-1.99 4-3.08 6-3.08 1.99 0 5.97 1.09 6 3.08-1.29 1.94-3.5 3.22-6 3.22z"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-black text-green-400 mb-1">{{ number_format($gameStats->kills) }}</p>
            <p class="text-sm text-gray-400">Kills</p>
            @if($killsByVictimType->count() > 0 || ($gameStats->total_roadkills ?? 0) > 0)
            <div class="mt-3 flex flex-wrap gap-2">
                @foreach($killsByVictimType as $killType)
                <span class="px-2 py-0.5 text-xs rounded-md font-medium {{ $killType->victim_type === 'AI' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400' }}">
                    {{ number_format($killType->total) }} {{ $killType->victim_type }}
                </span>
                @endforeach
                @if(($gameStats->total_roadkills ?? 0) > 0)
                <span class="px-2 py-0.5 text-xs rounded-md font-medium bg-violet-500/20 text-violet-400">
                    {{ number_format($gameStats->total_roadkills) }} Roadkills
                </span>
                @endif
            </div>
            @endif
        </div>

        {{-- Deaths --}}
        <div class="bg-gradient-to-br from-red-500/10 to-gray-800/50 rounded-xl p-5 border border-gray-700/50">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M12 2a10 10 0 110 20 10 10 0 010-20z"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-black text-red-400 mb-1">{{ number_format($gameStats->deaths) }}</p>
            <p class="text-sm text-gray-400">Deaths</p>
        </div>

        {{-- K/D Ratio --}}
        <div class="bg-gradient-to-br from-yellow-500/10 to-gray-800/50 rounded-xl p-5 border border-gray-700/50">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-black text-yellow-400 mb-1">
                {{ $gameStats->deaths > 0 ? number_format($gameStats->player_kills_count / $gameStats->deaths, 2) : $gameStats->player_kills_count }}
            </p>
            <p class="text-sm text-gray-400">K/D Ratio</p>
        </div>

        {{-- Headshots --}}
        <div class="bg-gradient-to-br from-amber-500/10 to-gray-800/50 rounded-xl p-5 border border-gray-700/50">
            <div class="flex items-start justify-between mb-3">
                <div class="w-10 h-10 bg-amber-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8c-1.657 0-3 .895-3 2s1.343 2 3 2 3 .895 3 2-1.343 2-3 2m0-8c1.11 0 2.08.402 2.599 1M12 8V7m0 1v8m0 0v1m0-1c-1.11 0-2.08-.402-2.599-1M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
            </div>
            <p class="text-4xl font-black text-amber-400 mb-1">{{ number_format($gameStats->headshots) }}</p>
            <p class="text-sm text-gray-400">Headshots</p>
            @if($gameStats->kills > 0)
            <p class="text-xs text-gray-500 mt-1">{{ number_format(($gameStats->headshots / $gameStats->kills) * 100, 1) }}% of kills</p>
            @endif
        </div>
    </div>
    @endif

    {{-- Two-Column Bottom Layout --}}
    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Main Content (2/3) --}}
        <div class="lg:col-span-2 space-y-6">

            {{-- Secondary Stats: Activity --}}
            @if($gameStats)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">Activity</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Playtime --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            @php
                                $hours = floor($gameStats->playtime_seconds / 3600);
                                $minutes = floor(($gameStats->playtime_seconds % 3600) / 60);
                            @endphp
                            <p class="text-lg font-bold text-blue-400">{{ $hours }}h {{ $minutes }}m</p>
                            <p class="text-xs text-gray-400">Playtime</p>
                        </div>
                    </div>
                    {{-- Distance --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-purple-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-purple-400">{{ number_format($gameStats->total_distance / 1000, 1) }}km</p>
                            <p class="text-xs text-gray-400">Distance</p>
                        </div>
                    </div>
                    {{-- Shots Fired --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-cyan-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-cyan-400">{{ number_format($gameStats->shots_fired) }}</p>
                            <p class="text-xs text-gray-400">Shots Fired</p>
                        </div>
                    </div>
                    {{-- Grenades --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-orange-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 18.657A8 8 0 016.343 7.343S7 9 9 10c0-2 .5-5 2.986-7C14 5 16.09 5.777 17.656 7.343A7.975 7.975 0 0120 13a7.975 7.975 0 01-2.343 5.657z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-orange-400">{{ number_format($gameStats->grenades_thrown) }}</p>
                            <p class="text-xs text-gray-400">Grenades</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Secondary Stats: Support --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">Support</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    {{-- Heals Given --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-pink-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-pink-400">{{ number_format($gameStats->heals_given) }}</p>
                            <p class="text-xs text-gray-400">Heals Given</p>
                        </div>
                    </div>
                    {{-- Heals Received --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-pink-500/10 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-pink-300" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v3m0 0v3m0-3h3m-3 0H9m12 0a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-pink-300">{{ number_format($gameStats->heals_received) }}</p>
                            <p class="text-xs text-gray-400">Heals Received</p>
                        </div>
                    </div>
                    {{-- Bases Captured --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-amber-400">{{ number_format($gameStats->bases_captured) }}</p>
                            <p class="text-xs text-gray-400">Bases Captured</p>
                        </div>
                    </div>
                    {{-- Supplies Delivered --}}
                    <div class="flex items-center gap-3">
                        <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                            <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                            </svg>
                        </div>
                        <div>
                            <p class="text-lg font-bold text-emerald-400">{{ number_format($gameStats->supplies_delivered) }}</p>
                            <p class="text-xs text-gray-400">Supplies</p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- Hit Zone Accuracy --}}
            @if($gameStats->total_hits > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">Hit Zone Accuracy</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                @php
                    $totalHits = $gameStats->total_hits ?: 1;
                    $headPct = round(($gameStats->hits_head / $totalHits) * 100, 1);
                    $torsoPct = round(($gameStats->hits_torso / $totalHits) * 100, 1);
                    $armsPct = round(($gameStats->hits_arms / $totalHits) * 100, 1);
                    $legsPct = round(($gameStats->hits_legs / $totalHits) * 100, 1);
                @endphp
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="text-center">
                        <p class="text-2xl font-bold text-red-400 mb-2">{{ $headPct }}%</p>
                        <div class="w-full bg-gray-600 rounded-full h-3 mb-2">
                            <div class="bg-red-500 h-3 rounded-full transition-all" style="width: {{ $headPct }}%"></div>
                        </div>
                        <p class="text-sm text-red-400 font-medium">Head</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-orange-400 mb-2">{{ $torsoPct }}%</p>
                        <div class="w-full bg-gray-600 rounded-full h-3 mb-2">
                            <div class="bg-orange-500 h-3 rounded-full transition-all" style="width: {{ $torsoPct }}%"></div>
                        </div>
                        <p class="text-sm text-orange-400 font-medium">Torso</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-yellow-400 mb-2">{{ $armsPct }}%</p>
                        <div class="w-full bg-gray-600 rounded-full h-3 mb-2">
                            <div class="bg-yellow-500 h-3 rounded-full transition-all" style="width: {{ $armsPct }}%"></div>
                        </div>
                        <p class="text-sm text-yellow-400 font-medium">Arms</p>
                    </div>
                    <div class="text-center">
                        <p class="text-2xl font-bold text-blue-400 mb-2">{{ $legsPct }}%</p>
                        <div class="w-full bg-gray-600 rounded-full h-3 mb-2">
                            <div class="bg-blue-500 h-3 rounded-full transition-all" style="width: {{ $legsPct }}%"></div>
                        </div>
                        <p class="text-sm text-blue-400 font-medium">Legs</p>
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-gray-700 flex justify-between text-sm text-gray-400">
                    <span>Total Hits: <span class="text-white font-medium">{{ number_format($gameStats->total_hits) }}</span></span>
                    <span>Total Damage: <span class="text-white font-medium">{{ number_format($gameStats->total_damage_dealt, 0) }}</span></span>
                </div>
            </div>
            @endif

            {{-- Friendly Fire --}}
            @if($friendlyFireDealt > 0 || $friendlyFireReceived > 0)
            <div class="grid grid-cols-2 gap-4">
                @if($friendlyFireDealt > 0)
                <div class="p-4 bg-orange-500/10 border border-orange-500/30 rounded-xl text-center">
                    <p class="text-2xl font-bold text-orange-400">{{ number_format($friendlyFireDealt) }}</p>
                    <p class="text-xs text-gray-400">Friendly Fire Dealt</p>
                </div>
                @endif
                @if($friendlyFireReceived > 0)
                <div class="p-4 bg-orange-500/10 border border-orange-500/30 rounded-xl text-center">
                    <p class="text-2xl font-bold text-orange-300">{{ number_format($friendlyFireReceived) }}</p>
                    <p class="text-xs text-gray-400">Friendly Fire Received</p>
                </div>
                @endif
            </div>
            @endif

            @if($gameStats && $gameStats->team_kills > 0)
            <div class="p-3 bg-red-500/10 border border-red-500/30 rounded-lg">
                <p class="text-red-400 text-sm">
                    <span class="font-bold">{{ number_format($gameStats->team_kills) }}</span> team kills
                </p>
            </div>
            @endif

            {{-- Top Weapons --}}
            @if($topWeapons->count() > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">Top Weapons</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
                    @foreach($topWeapons as $index => $weapon)
                    <div class="relative bg-gray-700/30 border border-gray-600/50 rounded-xl p-4 text-center hover:border-gray-500/50 transition">
                        @if($index < 3)
                        <span class="absolute top-2 right-2 w-6 h-6 rounded-full flex items-center justify-center text-xs font-bold
                            {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-400' : ($index === 1 ? 'bg-gray-400/20 text-gray-300' : 'bg-amber-700/20 text-amber-600') }}">
                            #{{ $index + 1 }}
                        </span>
                        @endif
                        <div class="h-16 flex items-center justify-center mb-3">
                            @if(isset($weaponImages[$weapon->weapon_name]))
                            <img src="{{ Storage::url($weaponImages[$weapon->weapon_name]) }}" alt="{{ $weapon->weapon_name }}" class="max-h-16 max-w-full object-contain">
                            @else
                            <div class="w-12 h-12 bg-gray-600/50 rounded-lg flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                                </svg>
                            </div>
                            @endif
                        </div>
                        <p class="text-sm text-gray-300 truncate mb-1">{{ $weapon->weapon_name }}</p>
                        <p class="text-lg font-bold text-green-400">{{ number_format($weapon->total) }} <span class="text-xs text-gray-500 font-normal">kills</span></p>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Hit Zone Distribution --}}
            @if($hitZonesDealt->count() > 0 || $hitZonesReceived->count() > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">Hit Zone Distribution</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
                    {{-- Damage Dealt --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-400 mb-3 uppercase tracking-wide">Damage Dealt</h4>
                        @php
                            $dealtFiltered = $hitZonesDealt->filter(fn($z) => $z->hit_zone_name && $z->hit_zone_name !== 'SCR_CharacterResilienceHitZone');
                            $dealtMax = $dealtFiltered->max('count') ?: 1;
                            $dealtTotal = $dealtFiltered->sum('count') ?: 1;
                        @endphp
                        @if($dealtFiltered->count() > 0)
                        <div class="space-y-2">
                            @foreach($dealtFiltered->sortByDesc('count') as $zone)
                            @php
                                $pct = round(($zone->count / $dealtTotal) * 100, 1);
                                $barWidth = ($zone->count / $dealtMax) * 100;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-28 text-sm text-gray-300 truncate">{{ $zone->hit_zone_name }}</div>
                                <div class="flex-1 bg-gray-700 rounded-full h-4">
                                    <div class="bg-red-500 h-4 rounded-full" style="width: {{ $barWidth }}%"></div>
                                </div>
                                <div class="w-20 text-right text-sm text-gray-400">{{ $zone->count }} ({{ $pct }}%)</div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500">No hit zone data</p>
                        @endif
                    </div>

                    {{-- Damage Received --}}
                    <div>
                        <h4 class="text-sm font-medium text-gray-400 mb-3 uppercase tracking-wide">Damage Received</h4>
                        @php
                            $receivedFiltered = $hitZonesReceived->filter(fn($z) => $z->hit_zone_name && $z->hit_zone_name !== 'SCR_CharacterResilienceHitZone');
                            $receivedMax = $receivedFiltered->max('count') ?: 1;
                            $receivedTotal = $receivedFiltered->sum('count') ?: 1;
                        @endphp
                        @if($receivedFiltered->count() > 0)
                        <div class="space-y-2">
                            @foreach($receivedFiltered->sortByDesc('count') as $zone)
                            @php
                                $pct = round(($zone->count / $receivedTotal) * 100, 1);
                                $barWidth = ($zone->count / $receivedMax) * 100;
                            @endphp
                            <div class="flex items-center gap-3">
                                <div class="w-28 text-sm text-gray-300 truncate">{{ $zone->hit_zone_name }}</div>
                                <div class="flex-1 bg-gray-700 rounded-full h-4">
                                    <div class="bg-blue-500 h-4 rounded-full" style="width: {{ $barWidth }}%"></div>
                                </div>
                                <div class="w-20 text-right text-sm text-gray-400">{{ $zone->count }} ({{ $pct }}%)</div>
                            </div>
                            @endforeach
                        </div>
                        @else
                        <p class="text-sm text-gray-500">No hit zone data</p>
                        @endif
                    </div>
                </div>
            </div>
            @endif

            {{-- XP Breakdown --}}
            @if($xpByType->count() > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">XP Breakdown</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                @php
                    $totalXp = $xpByType->sum('total_xp');
                @endphp
                <p class="text-sm text-gray-400 mb-3">Total XP: <span class="text-cyan-400 font-bold">{{ number_format($totalXp) }}</span></p>
                <div class="space-y-2">
                    @php
                        $xpMax = $xpByType->max('total_xp') ?: 1;
                        $xpTotal = $totalXp ?: 1;
                    @endphp
                    @foreach($xpByType as $xp)
                    @php
                        $pct = round(($xp->total_xp / $xpTotal) * 100, 1);
                        $barWidth = ($xp->total_xp / $xpMax) * 100;
                    @endphp
                    <div class="flex items-center gap-3">
                        <div class="w-28 text-sm text-gray-300 truncate">{{ $xp->reward_type ?? 'Unknown' }}</div>
                        <div class="flex-1 bg-gray-700 rounded-full h-4">
                            <div class="bg-cyan-500 h-4 rounded-full" style="width: {{ $barWidth }}%"></div>
                        </div>
                        <div class="w-32 text-right text-sm text-gray-400">{{ number_format($xp->total_xp) }} XP ({{ $pct }}%)</div>
                    </div>
                    @endforeach
                </div>
            </div>
            @endif

            {{-- Vehicle Stats --}}
            @if($gameStats)
            @include('profile._vehicle-stats', ['vehicleStats' => $vehicleStats])
            @endif

            {{-- Achievements --}}
            @include('profile._achievements')

            {{-- Kill Feed --}}
            @if($recentKillEvents->count() > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
                <div class="px-6 py-4 border-b border-gray-700 flex items-center justify-between">
                    <h3 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Kill Feed
                    </h3>
                    <span class="text-xs text-gray-500">{{ $recentKillEvents->count() }} recent</span>
                </div>
                <div class="max-h-[500px] overflow-y-auto">
                <table class="w-full">
                    <thead class="bg-gray-700/50 sticky top-0">
                        <tr>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Victim</th>
                            <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Weapon</th>
                            <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase">Distance</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-700/50">
                        @foreach($recentKillEvents as $kill)
                        @php
                            $killedAt = $kill->killed_at ? \Carbon\Carbon::parse($kill->killed_at) : null;
                            $distance = $kill->kill_distance ? round($kill->kill_distance) : null;
                        @endphp
                        <tr class="{{ $loop->odd ? 'bg-gray-800/30' : 'bg-gray-800/10' }} hover:bg-gray-700/30 transition-colors">
                            <td class="px-4 py-2 text-sm text-gray-400 whitespace-nowrap">
                                {{ $killedAt ? $killedAt->diffForHumans() : '-' }}
                            </td>
                            <td class="px-4 py-2">
                                @if($kill->victim_type === 'AI')
                                <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs font-semibold rounded">AI</span>
                                @else
                                <span class="text-red-400 text-sm">{{ $kill->victim_name ?? 'Unknown' }}</span>
                                @endif
                            </td>
                            <td class="px-4 py-2">
                                <div class="flex items-center gap-1.5">
                                    @if(isset($weaponImages[$kill->weapon_name]))
                                    <img src="{{ Storage::url($weaponImages[$kill->weapon_name]) }}" alt="{{ $kill->weapon_name }}" class="h-4 w-auto object-contain flex-shrink-0">
                                    @endif
                                    <span class="text-xs text-gray-300 truncate">{{ $kill->weapon_name }}</span>
                                    @if($kill->is_headshot)
                                    <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                    @endif
                                    @if($kill->is_roadkill)
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-violet-500/20 text-violet-400 rounded" title="Roadkill">ROADKILL</span>
                                    @endif
                                </div>
                            </td>
                            <td class="px-4 py-2 text-right text-sm font-medium whitespace-nowrap
                                @if($distance)
                                    {{ $distance < 50 ? 'text-green-400' : ($distance < 200 ? 'text-yellow-400' : 'text-red-400') }}
                                @else
                                    text-gray-400
                                @endif">
                                {{ $distance ? $distance . 'm' : '-' }}
                            </td>
                        </tr>
                        @endforeach
                    </tbody>
                </table>
                </div>
            </div>
            @endif

            @elseif(!$gameStats)
            {{-- No Game Stats State --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-10">
                <div class="text-center">
                    <div class="w-20 h-20 bg-gray-700/50 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-10 h-10 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                    </div>
                    <h3 class="text-xl font-semibold text-white mb-2">No Game Statistics</h3>
                    @if(!$user->hasLinkedArmaId())
                    <p class="text-gray-400 mb-4">Link your Arma Reforger ID in settings to see your stats.</p>
                    <a href="{{ route('profile.settings') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-500 rounded-lg font-medium transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                        </svg>
                        Link Arma ID
                    </a>
                    @else
                    <p class="text-gray-400">Play on our servers to start tracking your stats!</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- Recent Matches --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center gap-3 mb-4">
                    <h3 class="text-lg font-semibold text-white">Recent Matches</h3>
                    <div class="flex-1 h-px bg-gray-700"></div>
                </div>
                @if($recentMatches->count() > 0)
                    <div class="space-y-3">
                        @foreach($recentMatches as $match)
                            <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                                <div class="flex items-center gap-3">
                                    <span class="text-sm {{ $match->winner_id === $team->id ? 'text-green-400' : 'text-red-400' }} font-medium">
                                        {{ $match->winner_id === $team->id ? 'W' : 'L' }}
                                    </span>
                                    <div>
                                        <p class="text-sm text-white">
                                            vs {{ $match->team1_id === $team->id ? $match->team2->name : $match->team1->name }}
                                        </p>
                                        <p class="text-xs text-gray-400">{{ $match->tournament->name }}</p>
                                    </div>
                                </div>
                                <div class="text-right">
                                    <p class="text-sm text-white">
                                        {{ $match->team1_score ?? 0 }} - {{ $match->team2_score ?? 0 }}
                                    </p>
                                    <p class="text-xs text-gray-500">{{ $match->completed_at?->format('M d, Y') }}</p>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @else
                    <p class="text-gray-400 text-sm">No matches played yet.</p>
                @endif
            </div>
        </div>

        {{-- Sidebar (1/3) --}}
        <div class="lg:col-span-1 space-y-6">
            {{-- Tournament Stats --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Tournament Stats</h3>
                <div class="grid grid-cols-2 gap-3">
                    <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['tournaments_played'] }}</p>
                        <p class="text-xs text-gray-400">Tournaments</p>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['matches_played'] }}</p>
                        <p class="text-xs text-gray-400">Matches</p>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-green-400">{{ $stats['wins'] }}</p>
                        <p class="text-xs text-gray-400">Wins</p>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3 text-center">
                        <p class="text-2xl font-bold text-white">{{ $stats['win_rate'] }}%</p>
                        <p class="text-xs text-gray-400">Win Rate</p>
                    </div>
                </div>
            </div>

            {{-- Platoon Card --}}
            @if($team)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">My Platoon</h3>
                    <div class="flex items-center gap-4">
                        @if($team->avatar_url)
                            <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-12 h-12 rounded-lg object-cover">
                        @else
                            <div class="w-12 h-12 rounded-lg bg-gray-700 flex items-center justify-center text-lg font-bold text-gray-400">
                                {{ strtoupper(substr($team->tag, 0, 2)) }}
                            </div>
                        @endif
                        <div>
                            <a href="{{ route('teams.show', $team) }}" class="font-semibold text-white hover:text-green-400 transition">
                                {{ $team->name }}
                            </a>
                            <p class="text-sm text-gray-400">[{{ $team->tag }}]</p>
                        </div>
                    </div>
                    <a href="{{ route('teams.my') }}" class="mt-4 block text-sm text-green-400 hover:text-green-300">
                        Manage Platoon &rarr;
                    </a>
                </div>
            @else
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h3 class="text-lg font-semibold text-white mb-4">My Platoon</h3>
                    <p class="text-gray-400 text-sm mb-4">You are not a member of any platoon.</p>
                    <div class="flex flex-col gap-2">
                        <a href="{{ route('teams.index') }}" class="text-sm text-green-400 hover:text-green-300">
                            Browse Platoons &rarr;
                        </a>
                        <a href="{{ route('teams.create') }}" class="text-sm text-green-400 hover:text-green-300">
                            Create a Platoon &rarr;
                        </a>
                    </div>
                </div>
            @endif

            {{-- Quick Links --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4">Quick Links</h3>
                <div class="space-y-2">
                    <a href="{{ route('tournaments.index') }}" class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 hover:bg-gray-700 transition">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <span class="text-sm text-white">Tournaments</span>
                    </a>
                    <a href="{{ route('teams.index') }}" class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 hover:bg-gray-700 transition">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="text-sm text-white">Platoons</span>
                    </a>
                    <a href="{{ route('servers.show', config('services.battlemetrics.server_id', '0')) }}" class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 hover:bg-gray-700 transition">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                        <span class="text-sm text-white">Server</span>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
