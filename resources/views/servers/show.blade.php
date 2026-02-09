@extends('layouts.app')

@section('title', $server->name)

@section('content')
<div class="min-h-screen">
    {{-- Hero Section --}}
    <div class="relative border border-gray-700 rounded-xl overflow-hidden mb-6">
        {{-- Background Image --}}
        <div class="absolute inset-0 z-0">
            <img src="https://wallpapercave.com/wp/wp15024138.webp" alt="Everon" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-r from-gray-900/95 via-gray-900/80 to-gray-900/60"></div>
        </div>

        {{-- Content --}}
        <div class="relative z-10 p-6">
            <div class="flex flex-col lg:flex-row lg:items-start lg:justify-between gap-6">
                {{-- Left: Server Info --}}
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-3">
                        <h1 class="text-2xl lg:text-3xl font-bold text-white">{{ $server->name }}</h1>
                        <x-favorite-button :model="$server" type="server" />
                    </div>

                {{-- Scenario/Map Info --}}
                @if($server->scenario || $server->map)
                <div class="inline-flex items-center bg-black/40 backdrop-blur-sm rounded-lg px-3 py-1.5 mb-4">
                    <svg class="w-4 h-4 mr-2 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 20l-5.447-2.724A1 1 0 013 16.382V5.618a1 1 0 011.447-.894L9 7m0 13l6-3m-6 3V7m6 10l4.553 2.276A1 1 0 0021 18.382V7.618a1 1 0 00-.553-.894L15 4m0 13V4m0 0L9 7"/>
                    </svg>
                    <span class="text-white font-medium">{{ $server->scenario_display_name ?? $server->map }}</span>
                </div>
                @endif

                {{-- Badges Row --}}
                <div class="flex flex-wrap items-center gap-2 mb-4">
                    {{-- Version Badge --}}
                    @if($server->game_version)
                    <span class="px-3 py-1 text-xs font-medium bg-green-500/20 text-green-400 rounded-full border border-green-500/30">
                        v{{ $server->game_version }}
                    </span>
                    @endif

                    {{-- BattlEye Badge --}}
                    @if($server->battleye_enabled)
                    <span class="px-3 py-1 text-xs font-medium bg-green-500/20 text-green-400 rounded-full border border-green-500/30">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 20 20">
                            <path fill-rule="evenodd" d="M16.707 5.293a1 1 0 010 1.414l-8 8a1 1 0 01-1.414 0l-4-4a1 1 0 011.414-1.414L8 12.586l7.293-7.293a1 1 0 011.414 0z" clip-rule="evenodd"/>
                        </svg>
                        BattlEye
                    </span>
                    @endif

                    {{-- Platform Badges --}}
                    @if(in_array('pc', $server->supported_platforms ?? []))
                    <span class="px-3 py-1 text-xs font-medium bg-gray-700 text-gray-300 rounded-full">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/>
                        </svg>
                        PC
                    </span>
                    @endif
                    @if(in_array('xbox', $server->supported_platforms ?? []))
                    <span class="px-3 py-1 text-xs font-medium bg-gray-700 text-gray-300 rounded-full">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M4.102 21.033C6.211 22.881 8.977 24 12 24c3.026 0 5.789-1.119 7.902-2.967 1.877-1.912-4.316-8.709-7.902-11.417-3.582 2.708-9.779 9.505-7.898 11.417zm11.16-14.406c2.5 2.961 7.484 10.313 6.076 12.912C23.012 17.36 24 14.812 24 12c0-3.389-1.393-6.449-3.645-8.645-.146-.144-.293-.284-.441-.42l-.001-.001c-.151-.138-.304-.27-.46-.396a12.012 12.012 0 00-.844-.614c-.075-.051-.148-.104-.224-.153l-.017-.012A11.94 11.94 0 0012.001 0c-.127 0-.252.007-.378.012.178.023.349.066.518.115.055.016.113.026.168.044.055.017.109.038.163.058.13.047.258.1.384.157l.084.039c.038.018.074.04.112.059.096.049.189.102.281.157.09.052.178.107.265.164.039.026.079.05.117.076l.006.004c.057.039.113.08.169.121.226.164.443.34.651.527l.009.008-.001-.001-.001-.001-.003-.002-.007-.006c-2.402 2.152-5.468 6.477-5.468 6.477s-3.066-4.325-5.468-6.478l-.007.006-.003.002-.002.002-.001.001-.001.001.009-.008c.208-.188.426-.363.651-.527.056-.041.112-.082.169-.121l.006-.004c.038-.026.078-.05.117-.076.087-.057.175-.112.265-.164.092-.055.185-.108.281-.157.038-.019.074-.041.112-.059l.084-.039c.126-.057.254-.11.384-.157.054-.02.108-.041.163-.058.055-.018.113-.028.168-.044a3.56 3.56 0 01.518-.115A12.012 12.012 0 0012.001 0c-2.725 0-5.26.91-7.281 2.442l-.017.012c-.076.049-.149.102-.224.153-.295.195-.578.406-.844.614-.156.126-.309.258-.46.396l-.001.001c-.148.136-.295.276-.441.42C.393 5.551-1 8.611-1 12c0 2.812.988 5.36 2.662 7.539-1.408-2.599 3.576-9.951 6.076-12.912 1.038-1.231 2.204-2.363 3.262-2.927 1.058.564 2.224 1.696 3.262 2.927z"/>
                        </svg>
                        Xbox
                    </span>
                    @endif
                    @if(in_array('playstation', $server->supported_platforms ?? []))
                    <span class="px-3 py-1 text-xs font-medium bg-gray-700 text-gray-300 rounded-full">
                        <svg class="w-3 h-3 inline mr-1" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M8.985 2.596v17.548l3.915 1.261V6.688c0-.69.304-1.151.794-.991.636.181.76.814.76 1.505v5.876c2.441 1.193 4.362-.002 4.362-3.153 0-3.237-1.126-4.675-4.438-5.827-1.307-.448-3.728-1.186-5.391-1.502h-.002zm4.656 16.242l6.296-2.275c.715-.258.826-.625.246-.818-.586-.192-1.637-.139-2.357.123l-4.205 1.5v-2.385l.24-.085s1.201-.42 2.913-.615c1.696-.18 3.792.03 5.437.661 1.848.548 2.078 1.346 1.6 2.147-.477.8-1.639 1.261-1.639 1.261l-8.531 3.058v-2.572zm-9.112 2.593L.203 19.755c-.857-.439-.709-1.176.375-1.64l2.695-1.07v2.393l-2.025.769c-.727.277-.839.648-.25.842.59.193 1.64.15 2.364-.125l.166-.062v2.369l-.098.036c-1.258.396-2.485.392-3.502-.026z"/>
                        </svg>
                        PlayStation
                    </span>
                    @endif
                </div>

                {{-- Player Count --}}
                <div class="mb-4">
                    <div class="flex items-center gap-3 mb-2">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <span id="hero-player-count" class="text-xl font-bold text-white">{{ $server->players }} / {{ $server->max_players }}</span>
                    </div>
                    <div class="w-full bg-gray-700 rounded-full h-2">
                        <div id="hero-player-bar" class="bg-green-500 h-2 rounded-full transition-all duration-300" style="width: {{ $server->player_percentage }}%"></div>
                    </div>
                    <p class="text-xs text-gray-500 mt-1"><span id="hero-percentage">{{ $server->player_percentage }}</span>% Full</p>
                </div>

                {{-- Last Updated --}}
                <div class="inline-flex items-center bg-black/40 backdrop-blur-sm rounded-lg px-3 py-1.5">
                    <svg class="w-3 h-3 mr-2 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <span class="text-xs text-gray-300">Last updated: <span id="last-updated" class="text-white">{{ $server->last_updated_at?->diffForHumans() ?? 'Just now' }}</span></span>
                </div>
            </div>

            {{-- Right: Action Buttons --}}
            <div class="flex flex-col gap-3 lg:min-w-[200px]">
                {{-- Status Indicator --}}
                <div class="flex items-center justify-center lg:justify-end gap-2 mb-2">
                    <span id="status-indicator" class="relative flex h-3 w-3">
                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $server->isOnline() ? 'bg-green-500' : 'bg-red-500' }} opacity-75"></span>
                        <span class="relative inline-flex rounded-full h-3 w-3 {{ $server->isOnline() ? 'bg-green-500' : 'bg-red-500' }}"></span>
                    </span>
                    <span id="status-text" class="text-sm font-medium {{ $server->isOnline() ? 'text-green-400' : 'text-red-400' }}">
                        {{ $server->isOnline() ? 'Online' : 'Offline' }}
                    </span>
                </div>

                <button id="copy-info-btn" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                    Copy Info
                </button>

                <button id="share-btn" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.684 13.342C8.886 12.938 9 12.482 9 12c0-.482-.114-.938-.316-1.342m0 2.684a3 3 0 110-2.684m0 2.684l6.632 3.316m-6.632-6l6.632-3.316m0 0a3 3 0 105.367-2.684 3 3 0 00-5.367 2.684zm0 9.316a3 3 0 105.368 2.684 3 3 0 00-5.368-2.684z"/>
                    </svg>
                    Share
                </button>

                <a href="{{ route('servers.heatmap', $server->battlemetrics_id) }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17.657 16.657L13.414 20.9a1.998 1.998 0 01-2.827 0l-4.244-4.243a8 8 0 1111.314 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 11a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    Heatmap
                </a>

                <a href="{{ route('servers.embed', $server) }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 20l4-16m4 4l4 4-4 4M6 16l-4-4 4-4"/>
                    </svg>
                    Embed
                </a>

                <a href="steam://connect/{{ $server->ip }}:{{ $server->port }}" class="flex items-center justify-center gap-2 px-4 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Join Server
                </a>
            </div>
            </div>
        </div>
    </div>

    {{-- Main Content: Two Column Layout --}}
    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6 mb-6 items-start">
        {{-- Left Column: Chart + Sessions + Mods --}}
        <div class="lg:col-span-2 space-y-6">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <h2 class="text-base font-semibold text-white flex items-center gap-2">
                    Player Count Over Time
                    <button id="expand-chart-btn" class="text-gray-400 hover:text-white transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 8V4m0 0h4M4 4l5 5m11-1V4m0 0h-4m4 0l-5 5M4 16v4m0 0h4m-4 0l5-5m11 5l-5-5m5 5v-4m0 4h-4"/>
                        </svg>
                    </button>
                </h2>

                {{-- Time Filter Buttons --}}
                <div class="flex items-center gap-1 bg-gray-700/50 rounded-lg p-1">
                    <button data-range="6h" class="time-filter-btn px-2 py-1 text-xs font-medium rounded-md transition text-gray-400 hover:text-white">6h</button>
                    <button data-range="24h" class="time-filter-btn px-2 py-1 text-xs font-medium rounded-md transition bg-green-500/20 text-green-400">24h</button>
                    <button data-range="72h" class="time-filter-btn px-2 py-1 text-xs font-medium rounded-md transition text-gray-400 hover:text-white">72h</button>
                </div>
            </div>

            {{-- Chart Container --}}
            <div id="chart-container" class="relative h-32">
                <canvas id="playerChart"></canvas>
            </div>

            {{-- Stats Row --}}
            <div class="grid grid-cols-4 gap-2 pt-3 mt-3 border-t border-gray-700">
                <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Average</p>
                    <p id="stat-average" class="text-sm font-bold text-white">{{ $stats['average'] ?? 0 }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Peak</p>
                    <p id="stat-peak" class="text-sm font-bold text-green-400">{{ $stats['peak'] ?? 0 }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Median</p>
                    <p id="stat-median" class="text-sm font-bold text-white">{{ $stats['median'] ?? 0 }}</p>
                </div>
                <div class="text-center">
                    <p class="text-[10px] text-gray-500 uppercase tracking-wider">Lowest</p>
                    <p id="stat-lowest" class="text-sm font-bold text-white">{{ $stats['lowest'] ?? 0 }}</p>
                </div>
            </div>

            {{-- Data Points Info --}}
            <div class="flex items-center justify-center gap-3 mt-2 text-[10px] text-gray-500">
                <span><span id="stat-datapoints">{{ $stats['data_points'] ?? 0 }}</span> data points</span>
                <span class="text-gray-600">|</span>
                <span><span id="stat-restarts">{{ $stats['restarts'] ?? 0 }}</span> restarts</span>
            </div>
        </div>

    {{-- Kill Feed Section --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl" x-data="killFeedWidget()" x-init="init()">
        <div class="flex items-center justify-between p-4 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
                Live Kill Feed
            </h2>
            <div class="flex items-center gap-3">
                <label class="flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="hideAI" class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500 focus:ring-offset-0 w-3.5 h-3.5">
                    Hide AI
                </label>
                <label class="flex items-center gap-1.5 text-xs text-gray-400 cursor-pointer">
                    <input type="checkbox" x-model="headshotsOnly" class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500 focus:ring-offset-0 w-3.5 h-3.5">
                    Headshots
                </label>
                <span class="relative flex h-2 w-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 bg-red-500"></span>
                </span>
            </div>
        </div>
        <div class="divide-y divide-gray-700/50 max-h-80 overflow-y-auto">
            <template x-for="kill in filteredKills" :key="kill.id">
                <div class="px-4 py-2.5 hover:bg-gray-700/30 transition text-sm flex items-center gap-2">
                    {{-- Headshot badge --}}
                    <template x-if="kill.is_headshot">
                        <span class="text-yellow-400 text-xs" title="Headshot">HS</span>
                    </template>
                    {{-- Killer --}}
                    <span class="text-white font-medium truncate max-w-[120px]" x-text="kill.killer_name || 'Unknown'"></span>
                    {{-- Arrow --}}
                    <svg class="w-3.5 h-3.5 text-red-400 shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7l5 5m0 0l-5 5m5-5H6"/>
                    </svg>
                    {{-- Victim --}}
                    <span class="truncate max-w-[120px]" :class="kill.victim_type === 'AI' ? 'text-gray-500' : 'text-white font-medium'" x-text="kill.victim_name || 'Unknown'"></span>
                    <template x-if="kill.victim_type === 'AI'">
                        <span class="text-[10px] text-gray-600">(AI)</span>
                    </template>
                    {{-- Spacer --}}
                    <span class="flex-1"></span>
                    {{-- Weapon --}}
                    <span class="text-xs text-gray-500 truncate max-w-[100px]" x-text="kill.weapon_name || ''"></span>
                    {{-- Distance --}}
                    <template x-if="kill.kill_distance > 0">
                        <span class="text-xs text-gray-600 shrink-0" x-text="Math.round(kill.kill_distance) + 'm'"></span>
                    </template>
                    {{-- Time --}}
                    <span class="text-[10px] text-gray-600 shrink-0" x-text="timeAgo(kill.killed_at)"></span>
                </div>
            </template>
            <div x-show="filteredKills.length === 0" class="p-6 text-center text-gray-500 text-sm">
                No kills to display
            </div>
        </div>
        <div class="px-4 py-2 border-t border-gray-700 text-center">
            <a href="{{ route('kill-feed') }}" class="text-xs text-green-400 hover:text-green-300">View full kill feed &rarr;</a>
        </div>
    </div>

    {{-- Sessions Section --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl">
        <button id="toggle-sessions" class="w-full flex items-center justify-between p-4 text-left">
            <h2 class="text-lg font-semibold text-white">Server Sessions</h2>
            <svg id="sessions-chevron" class="w-5 h-5 text-gray-400 transform transition-transform" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
            </svg>
        </button>

        <div id="sessions-content" class="hidden border-t border-gray-700">
            <div class="p-4 space-y-3">
                @forelse($sessions as $index => $session)
                <div class="bg-gray-700/50 rounded-lg p-4">
                    <div class="flex items-center justify-between mb-3">
                        <div class="flex items-center gap-2">
                            <span class="text-white font-medium">Session {{ $session['session_number'] ?? ($index + 1) }}</span>
                            @if($session['is_current'] ?? false)
                            <span class="px-2 py-0.5 text-xs bg-green-500/20 text-green-400 rounded-full">
                                Current • {{ \Carbon\Carbon::parse($session['started_at'])->diffForHumans(null, true) }}
                            </span>
                            @endif
                        </div>
                    </div>
                    <div class="grid grid-cols-2 md:grid-cols-5 gap-4 text-sm">
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Started</p>
                            <p class="text-white">{{ \Carbon\Carbon::parse($session['started_at'])->format('M j, H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Last Seen</p>
                            <p class="text-white">{{ \Carbon\Carbon::parse($session['last_seen_at'])->format('M j, H:i') }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Avg Players</p>
                            <p class="text-white">{{ $session['average_players'] ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Peak</p>
                            <p class="text-green-400">{{ $session['peak_players'] ?? 0 }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 text-xs uppercase">Snapshots</p>
                            <p class="text-white">{{ $session['snapshots'] ?? 0 }}</p>
                        </div>
                    </div>
                </div>
                @empty
                <p class="text-gray-400 text-center py-4">No session data available</p>
                @endforelse
            </div>
        </div>
    </div>

    {{-- Mods Section --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl">
        <div class="flex flex-col sm:flex-row sm:items-center justify-between p-4 border-b border-gray-700 gap-4">
            <h2 class="text-lg font-semibold text-white">Server Mods ({{ $mods->count() }})</h2>

            <div class="flex items-center gap-2">
                {{-- Sort Dropdown --}}
                <select id="mod-sort" class="bg-gray-700 border border-gray-600 text-white text-sm rounded-lg px-3 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="load_order">By Load Order</option>
                    <option value="name">By Name</option>
                    <option value="updated">By Update Date</option>
                </select>

                {{-- View Toggle --}}
                <div class="flex items-center bg-gray-700 rounded-lg p-1">
                    <button id="view-list" class="view-toggle-btn px-3 py-1.5 text-xs font-medium rounded-md bg-green-500/20 text-green-400">List</button>
                    <button id="view-json" class="view-toggle-btn px-3 py-1.5 text-xs font-medium rounded-md text-gray-400 hover:text-white">JSON</button>
                </div>

                {{-- Copy Button --}}
                <button id="copy-mods-btn" class="p-2 bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-white rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>

                {{-- Download Button --}}
                <a href="{{ route('servers.mods.download', $server->battlemetrics_id) }}" class="flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                    </svg>
                    Download
                </a>
            </div>
        </div>

        {{-- Mods List View --}}
        <div id="mods-list-view">
            {{-- Header --}}
            <div class="hidden sm:flex items-center px-3 py-2 bg-gray-700/50 text-xs text-gray-500 uppercase tracking-wider gap-4">
                <div class="flex-1">Name</div>
                <div class="w-20">Version</div>
                <div class="w-32">Author</div>
                <div class="w-28">Updated</div>
                <div class="w-20"></div>
            </div>
            <div class="divide-y divide-gray-700">
            @forelse($mods as $mod)
            <div class="mod-item flex items-center p-3 hover:bg-gray-700/30 transition gap-4" data-name="{{ strtolower($mod->name) }}" data-updated="{{ $mod->workshop_updated_at?->timestamp ?? 0 }}" data-order="{{ $mod->pivot->load_order }}">
                <div class="flex-1 min-w-0">
                    <span class="text-white font-medium truncate">{{ $mod->name }}</span>
                </div>
                <div class="hidden sm:flex items-center gap-6 text-xs text-gray-400 flex-shrink-0">
                    <div class="w-20">
                        <span class="text-gray-500">v</span>{{ $mod->version ?? 'N/A' }}
                    </div>
                    <div class="w-32 truncate text-green-400">
                        {{ $mod->author ?? 'Unknown' }}
                    </div>
                    <div class="w-28">
                        {{ $mod->workshop_updated_at ? $mod->time_since_update : 'N/A' }}
                    </div>
                </div>
                @if($mod->workshop_url || $mod->workshop_link)
                <a href="{{ $mod->workshop_url ?? $mod->workshop_link }}" target="_blank" rel="noopener" class="flex-shrink-0 px-3 py-1 bg-green-600 hover:bg-green-500 text-white text-xs font-medium rounded transition">
                    Workshop
                </a>
                @endif
            </div>
            @empty
            <p class="text-gray-400 text-center py-8">No mods installed on this server</p>
            @endforelse
            </div>
        </div>

        {{-- Mods JSON View (Hidden by default) --}}
        <div id="mods-json-view" class="hidden p-4">
            <pre class="bg-gray-900 rounded-lg p-4 overflow-x-auto text-xs text-gray-300 font-mono max-h-96"><code id="mods-json-content"></code></pre>
        </div>
    </div>
        </div>

        {{-- Right Column: Server Information --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            {{-- Header --}}
            <div class="flex items-center gap-2 p-4 border-b border-gray-700">
                <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
                <h2 class="text-lg font-semibold text-white">Server Information</h2>
            </div>

            {{-- Tabs --}}
            <div class="flex border-b border-gray-700">
                <button id="tab-connection" class="info-tab flex-1 px-4 py-3 text-sm font-medium text-green-400 border-b-2 border-green-500 bg-gray-800/50 flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                    </svg>
                    Connection
                </button>
                <button id="tab-version" class="info-tab flex-1 px-4 py-3 text-sm font-medium text-gray-400 hover:text-white border-b-2 border-transparent flex items-center justify-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    Version Details
                </button>
            </div>

            {{-- Connection Tab Content --}}
            <div id="content-connection" class="p-4 space-y-4">
                {{-- Host Address --}}
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Host Address</label>
                    <div class="flex items-center justify-between bg-gray-700/50 rounded-lg px-3 py-2">
                        <code class="text-sm text-white font-mono">{{ $server->ip }}:{{ $server->port }}</code>
                        <button onclick="copyToClipboard('{{ $server->ip }}:{{ $server->port }}')" class="text-gray-400 hover:text-green-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Direct Join Code --}}
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Direct Join Code</label>
                    <div class="flex items-center justify-between bg-gray-700/50 rounded-lg px-3 py-2">
                        <code class="text-sm text-white font-mono">{{ $server->direct_join_code ?? 'N/A' }}</code>
                        @if($server->direct_join_code)
                        <button onclick="copyToClipboard('{{ $server->direct_join_code }}')" class="text-gray-400 hover:text-green-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                        @endif
                    </div>
                </div>

                {{-- Server ID --}}
                <div>
                    <label class="block text-xs text-gray-500 uppercase tracking-wider mb-1">Server ID</label>
                    <div class="flex items-center justify-between bg-gray-700/50 rounded-lg px-3 py-2">
                        <code class="text-sm text-white font-mono truncate">{{ $server->battlemetrics_id }}</code>
                        <button onclick="copyToClipboard('{{ $server->battlemetrics_id }}')" class="text-gray-400 hover:text-green-400 transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                            </svg>
                        </button>
                    </div>
                </div>

                {{-- Supported Platforms --}}
                <div class="pt-4 border-t border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                        </svg>
                        <label class="text-xs text-gray-500 uppercase tracking-wider">Supported Platforms</label>
                    </div>
                    <div class="flex items-center gap-4">
                        @if(in_array('pc', $server->supported_platforms ?? []))
                        <div class="flex items-center gap-1.5 text-green-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/>
                            </svg>
                            <span class="text-sm font-medium">PC</span>
                        </div>
                        @endif
                        @if(in_array('xbox', $server->supported_platforms ?? []))
                        <div class="flex items-center gap-1.5 text-green-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M4.102 21.033C6.211 22.881 8.977 24 12 24c3.026 0 5.789-1.119 7.902-2.967 1.877-1.912-4.316-8.709-7.902-11.417-3.582 2.708-9.779 9.505-7.898 11.417zm11.16-14.406c2.5 2.961 7.484 10.313 6.076 12.912C23.012 17.36 24 14.812 24 12c0-3.389-1.393-6.449-3.645-8.645-.146-.144-.293-.284-.441-.42l-.001-.001c-.151-.138-.304-.27-.46-.396a12.012 12.012 0 00-.844-.614c-.075-.051-.148-.104-.224-.153l-.017-.012A11.94 11.94 0 0012.001 0c-.127 0-.252.007-.378.012.178.023.349.066.518.115.055.016.113.026.168.044.055.017.109.038.163.058.13.047.258.1.384.157l.084.039c.038.018.074.04.112.059.096.049.189.102.281.157.09.052.178.107.265.164.039.026.079.05.117.076l.006.004c.057.039.113.08.169.121.226.164.443.34.651.527l.009.008-.001-.001-.001-.001-.003-.002-.007-.006c-2.402 2.152-5.468 6.477-5.468 6.477s-3.066-4.325-5.468-6.478l-.007.006-.003.002-.002.002-.001.001-.001.001.009-.008c.208-.188.426-.363.651-.527.056-.041.112-.082.169-.121l.006-.004c.038-.026.078-.05.117-.076.087-.057.175-.112.265-.164.092-.055.185-.108.281-.157.038-.019.074-.041.112-.059l.084-.039c.126-.057.254-.11.384-.157.054-.02.108-.041.163-.058.055-.018.113-.028.168-.044a3.56 3.56 0 01.518-.115A12.012 12.012 0 0012.001 0c-2.725 0-5.26.91-7.281 2.442l-.017.012c-.076.049-.149.102-.224.153-.295.195-.578.406-.844.614-.156.126-.309.258-.46.396l-.001.001c-.148.136-.295.276-.441.42C.393 5.551-1 8.611-1 12c0 2.812.988 5.36 2.662 7.539-1.408-2.599 3.576-9.951 6.076-12.912 1.038-1.231 2.204-2.363 3.262-2.927 1.058.564 2.224 1.696 3.262 2.927z"/>
                            </svg>
                            <span class="text-sm font-medium">Xbox</span>
                        </div>
                        @endif
                        @if(in_array('playstation', $server->supported_platforms ?? []))
                        <div class="flex items-center gap-1.5 text-green-400">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M8.985 2.596v17.548l3.915 1.261V6.688c0-.69.304-1.151.794-.991.636.181.76.814.76 1.505v5.876c2.441 1.193 4.362-.002 4.362-3.153 0-3.237-1.126-4.675-4.438-5.827-1.307-.448-3.728-1.186-5.391-1.502h-.002zm4.656 16.242l6.296-2.275c.715-.258.826-.625.246-.818-.586-.192-1.637-.139-2.357.123l-4.205 1.5v-2.385l.24-.085s1.201-.42 2.913-.615c1.696-.18 3.792.03 5.437.661 1.848.548 2.078 1.346 1.6 2.147-.477.8-1.639 1.261-1.639 1.261l-8.531 3.058v-2.572zm-9.112 2.593L.203 19.755c-.857-.439-.709-1.176.375-1.64l2.695-1.07v2.393l-2.025.769c-.727.277-.839.648-.25.842.59.193 1.64.15 2.364-.125l.166-.062v2.369l-.098.036c-1.258.396-2.485.392-3.502-.026z"/>
                            </svg>
                            <span class="text-sm font-medium">PlayStation</span>
                        </div>
                        @endif
                    </div>
                </div>

                {{-- Server Status Badges --}}
                <div class="pt-4 border-t border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <label class="text-xs text-gray-500 uppercase tracking-wider">Server Status</label>
                    </div>
                    <div class="flex flex-wrap gap-1.5">
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] rounded {{ $server->is_official ? 'bg-green-500/20 text-green-400' : 'bg-gray-700/50 text-gray-500' }}">
                            <i data-lucide="shield" class="w-3 h-3"></i>Official
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] rounded {{ $server->is_joinable ? 'bg-green-500/20 text-green-400' : 'bg-gray-700/50 text-gray-500' }}">
                            <i data-lucide="log-in" class="w-3 h-3"></i>Joinable
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] rounded {{ $server->is_visible ? 'bg-green-500/20 text-green-400' : 'bg-gray-700/50 text-gray-500' }}">
                            <i data-lucide="eye" class="w-3 h-3"></i>Visible
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] rounded {{ $server->is_password_protected ? 'bg-yellow-500/20 text-yellow-400' : 'bg-gray-700/50 text-gray-500' }}">
                            <i data-lucide="{{ $server->is_password_protected ? 'lock' : 'lock-open' }}" class="w-3 h-3"></i>Protected
                        </span>
                        <span class="inline-flex items-center gap-1 px-2 py-1 text-[10px] rounded {{ $server->battleye_enabled ? 'bg-green-500/20 text-green-400' : 'bg-gray-700/50 text-gray-500' }}">
                            <i data-lucide="shield-check" class="w-3 h-3"></i>BattlEye
                        </span>
                    </div>
                </div>

                {{-- Server Sessions (Restart History) --}}
                <div class="pt-4 border-t border-gray-700">
                    <div class="flex items-center gap-2 mb-3">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        <label class="text-xs text-gray-500 uppercase tracking-wider">Server Sessions (Restart History)</label>
                    </div>

                    @php
                        $currentSession = collect($sessions)->firstWhere('is_current', true);
                    @endphp

                    @if($currentSession)
                    <div class="bg-gray-700/30 rounded-lg p-3 mb-3">
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-white font-medium text-sm">Current Session</span>
                            <span class="px-2 py-0.5 text-xs bg-green-500/20 text-green-400 rounded-full">
                                {{ \Carbon\Carbon::parse($currentSession['started_at'])->diffForHumans(null, true) }}
                            </span>
                        </div>
                        <div class="grid grid-cols-2 gap-4 text-xs mb-3">
                            <div>
                                <p class="text-gray-500 uppercase">Started</p>
                                <p class="text-gray-300">{{ \Carbon\Carbon::parse($currentSession['started_at'])->diffForHumans() }}</p>
                                <p class="text-gray-500">{{ \Carbon\Carbon::parse($currentSession['started_at'])->format('M j, H:i') }}</p>
                            </div>
                            <div>
                                <p class="text-gray-500 uppercase">Last Seen</p>
                                <p class="text-gray-300">{{ \Carbon\Carbon::parse($currentSession['last_seen_at'])->diffForHumans() }}</p>
                                <p class="text-gray-500">{{ \Carbon\Carbon::parse($currentSession['last_seen_at'])->format('M j, H:i') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center justify-between text-xs border-t border-gray-600 pt-2">
                            <div class="text-center">
                                <span class="text-gray-500">Avg Players</span>
                                <span class="text-white ml-1">{{ $currentSession['average_players'] ?? 0 }}</span>
                            </div>
                            <div class="text-center">
                                <span class="text-gray-500">Peak</span>
                                <span class="text-green-400 ml-1">{{ $currentSession['peak_players'] ?? 0 }}</span>
                            </div>
                            <div class="text-center">
                                <span class="text-gray-500">Snapshots</span>
                                <span class="text-white ml-1">{{ $currentSession['snapshots'] ?? 0 }}</span>
                            </div>
                        </div>
                    </div>
                    @else
                    <p class="text-gray-500 text-sm">No active session</p>
                    @endif
                </div>
            </div>

            {{-- Version Details Tab Content (Hidden by default) --}}
            <div id="content-version" class="p-4 space-y-4 hidden">
                <div class="space-y-3">
                    <div class="flex justify-between text-sm py-2 border-b border-gray-700/50">
                        <span class="text-gray-400">Game Version</span>
                        <span class="text-white font-mono">{{ $server->game_version ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-gray-700/50">
                        <span class="text-gray-400">Host Type</span>
                        <span class="text-white">{{ $server->is_official ? 'Official' : 'Community' }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-gray-700/50">
                        <span class="text-gray-400">Scenario</span>
                        <span class="text-white text-right max-w-[60%] truncate">{{ $server->scenario_display_name ?? 'Unknown' }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-gray-700/50">
                        <span class="text-gray-400">Platform Hosted On</span>
                        <span class="text-white">{{ $server->country_flag }} {{ strtoupper($server->country_code ?? 'Unknown') }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-gray-700/50">
                        <span class="text-gray-400">Query Port</span>
                        <span class="text-white font-mono">{{ $server->query_port ?? $server->port }}</span>
                    </div>
                    <div class="flex justify-between text-sm py-2 border-b border-gray-700/50">
                        <span class="text-gray-400">Server ID</span>
                        <span class="text-white font-mono text-xs">{{ $server->battlemetrics_id }}</span>
                    </div>
                    @if($server->rank)
                    <div class="flex justify-between text-sm py-2">
                        <span class="text-gray-400">BattleMetrics Rank</span>
                        <span class="text-green-400 font-medium">#{{ $server->rank }}</span>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>

{{-- Toast Notification --}}
<div id="toast" class="fixed bottom-4 right-4 bg-gray-800 border border-gray-700 text-white px-4 py-3 rounded-lg shadow-xl transform translate-y-20 opacity-0 transition-all duration-300 z-50">
    <span id="toast-message">Copied to clipboard!</span>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    const serverId = '{{ $server->battlemetrics_id }}';
    let currentRange = '24h';
    let chart = null;

    // Initialize
    document.addEventListener('DOMContentLoaded', function() {
        lucide.createIcons();
        initChart(@json($history));
        initTabs();
        initModsView();
        initSessionsToggle();
        initTimeFilters();
        initCopyButtons();
        initModsSorting();
        startAutoRefresh();
    });

    // Chart
    function initChart(data) {
        const ctx = document.getElementById('playerChart').getContext('2d');

        const chartData = data.map(point => ({
            x: new Date(point.attributes?.timestamp || point.timestamp),
            y: point.attributes?.value ?? point.value ?? 0
        }));

        if (chart) chart.destroy();

        chart = new Chart(ctx, {
            type: 'line',
            data: {
                datasets: [{
                    data: chartData,
                    borderColor: '#22c55e',
                    backgroundColor: 'rgba(34, 197, 94, 0.1)',
                    fill: true,
                    tension: 0.3,
                    pointRadius: 0,
                    pointHoverRadius: 6,
                    pointHoverBackgroundColor: '#22c55e',
                    pointHoverBorderColor: '#fff',
                    pointHoverBorderWidth: 2,
                }]
            },
            options: {
                responsive: true,
                maintainAspectRatio: false,
                interaction: {
                    mode: 'index',
                    intersect: false,
                },
                plugins: {
                    legend: { display: false },
                    tooltip: {
                        enabled: true,
                        backgroundColor: '#1f2937',
                        titleColor: '#9ca3af',
                        bodyColor: '#fff',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 12,
                        displayColors: false,
                        callbacks: {
                            title: function(items) {
                                const date = new Date(items[0].parsed.x);
                                return date.toLocaleString();
                            },
                            label: function(item) {
                                const players = item.parsed.y;
                                const maxPlayers = {{ $server->max_players }};
                                const utilization = ((players / maxPlayers) * 100).toFixed(1);
                                return [
                                    `Players: ${players}`,
                                    `Capacity: ${maxPlayers}`,
                                    `Utilization: ${utilization}%`
                                ];
                            }
                        }
                    }
                },
                scales: {
                    x: {
                        type: 'time',
                        time: {
                            displayFormats: {
                                hour: 'HH:mm',
                                day: 'MMM d'
                            }
                        },
                        grid: {
                            color: '#374151',
                            drawBorder: false,
                        },
                        ticks: { color: '#9ca3af' }
                    },
                    y: {
                        beginAtZero: true,
                        max: {{ $server->max_players }},
                        grid: {
                            color: '#374151',
                            drawBorder: false,
                        },
                        ticks: { color: '#9ca3af' }
                    }
                }
            }
        });
    }

    async function loadChartData(range) {
        try {
            const response = await fetch(`/servers/${serverId}/history?range=${range}`);
            const result = await response.json();

            initChart(result.data.map(d => ({ timestamp: d.timestamp, value: d.players })));
            updateStats(result.stats);
        } catch (error) {
            console.error('Error loading chart data:', error);
        }
    }

    function updateStats(stats) {
        document.getElementById('stat-average').textContent = stats.average;
        document.getElementById('stat-peak').textContent = stats.peak;
        document.getElementById('stat-median').textContent = stats.median;
        document.getElementById('stat-lowest').textContent = stats.lowest;
        document.getElementById('stat-datapoints').textContent = stats.data_points;
        document.getElementById('stat-restarts').textContent = stats.restarts;
    }

    // Time filters
    function initTimeFilters() {
        document.querySelectorAll('.time-filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                document.querySelectorAll('.time-filter-btn').forEach(b => {
                    b.classList.remove('bg-green-500/20', 'text-green-400');
                    b.classList.add('text-gray-400');
                });
                this.classList.add('bg-green-500/20', 'text-green-400');
                this.classList.remove('text-gray-400');

                currentRange = this.dataset.range;
                loadChartData(currentRange);
            });
        });
    }

    // Tabs
    function initTabs() {
        document.getElementById('tab-connection').addEventListener('click', function() {
            this.classList.add('text-green-400', 'border-green-500', 'bg-gray-800/50');
            this.classList.remove('text-gray-400', 'border-transparent');
            document.getElementById('tab-version').classList.remove('text-green-400', 'border-green-500', 'bg-gray-800/50');
            document.getElementById('tab-version').classList.add('text-gray-400', 'border-transparent');
            document.getElementById('content-connection').classList.remove('hidden');
            document.getElementById('content-version').classList.add('hidden');
        });

        document.getElementById('tab-version').addEventListener('click', function() {
            this.classList.add('text-green-400', 'border-green-500', 'bg-gray-800/50');
            this.classList.remove('text-gray-400', 'border-transparent');
            document.getElementById('tab-connection').classList.remove('text-green-400', 'border-green-500', 'bg-gray-800/50');
            document.getElementById('tab-connection').classList.add('text-gray-400', 'border-transparent');
            document.getElementById('content-version').classList.remove('hidden');
            document.getElementById('content-connection').classList.add('hidden');
        });
    }

    // Mods view toggle
    function initModsView() {
        const modsData = @json($modsJsonData);

        document.getElementById('mods-json-content').textContent = JSON.stringify(modsData, null, 2);

        document.getElementById('view-list').addEventListener('click', function() {
            this.classList.add('bg-green-500/20', 'text-green-400');
            this.classList.remove('text-gray-400');
            document.getElementById('view-json').classList.remove('bg-green-500/20', 'text-green-400');
            document.getElementById('view-json').classList.add('text-gray-400');
            document.getElementById('mods-list-view').classList.remove('hidden');
            document.getElementById('mods-json-view').classList.add('hidden');
        });

        document.getElementById('view-json').addEventListener('click', function() {
            this.classList.add('bg-green-500/20', 'text-green-400');
            this.classList.remove('text-gray-400');
            document.getElementById('view-list').classList.remove('bg-green-500/20', 'text-green-400');
            document.getElementById('view-list').classList.add('text-gray-400');
            document.getElementById('mods-json-view').classList.remove('hidden');
            document.getElementById('mods-list-view').classList.add('hidden');
        });
    }

    // Sessions toggle
    function initSessionsToggle() {
        document.getElementById('toggle-sessions').addEventListener('click', function() {
            const content = document.getElementById('sessions-content');
            const chevron = document.getElementById('sessions-chevron');
            content.classList.toggle('hidden');
            chevron.classList.toggle('rotate-180');
        });
    }

    // Copy functions
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            showToast('Copied to clipboard!');
        }).catch(err => {
            console.error('Failed to copy:', err);
        });
    }

    function showToast(message) {
        const toast = document.getElementById('toast');
        document.getElementById('toast-message').textContent = message;
        toast.classList.remove('translate-y-20', 'opacity-0');
        setTimeout(() => {
            toast.classList.add('translate-y-20', 'opacity-0');
        }, 2000);
    }

    function initCopyButtons() {
        document.getElementById('copy-info-btn').addEventListener('click', function() {
            const info = `{{ $server->name }}\nIP: {{ $server->ip }}:{{ $server->port }}\nPlayers: {{ $server->players }}/{{ $server->max_players }}`;
            copyToClipboard(info);
        });

        document.getElementById('share-btn').addEventListener('click', function() {
            copyToClipboard(window.location.href);
            showToast('Link copied to clipboard!');
        });

        document.getElementById('copy-mods-btn').addEventListener('click', async function() {
            try {
                const response = await fetch(`/servers/${serverId}/mods/json`);
                const data = await response.json();
                copyToClipboard(JSON.stringify(data, null, 2));
            } catch (error) {
                console.error('Error copying mods:', error);
            }
        });
    }

    // Mods sorting
    function initModsSorting() {
        document.getElementById('mod-sort').addEventListener('change', function() {
            const container = document.getElementById('mods-list-view');
            const items = Array.from(container.querySelectorAll('.mod-item'));

            items.sort((a, b) => {
                switch (this.value) {
                    case 'name':
                        return a.dataset.name.localeCompare(b.dataset.name);
                    case 'updated':
                        return parseInt(b.dataset.updated) - parseInt(a.dataset.updated);
                    default:
                        return parseInt(a.dataset.order) - parseInt(b.dataset.order);
                }
            });

            items.forEach(item => container.appendChild(item));
        });
    }

    // Auto refresh
    function startAutoRefresh() {
        let statusWsConnected = false;
        let statusPollInterval = 60000;

        if (window.Echo) {
            window.Echo.channel('server.{{ $server->id }}')
                .listen('.status.updated', (e) => {
                    if (e.players !== undefined && e.max_players !== undefined) {
                        document.getElementById('hero-player-count').textContent = `${e.players} / ${e.max_players}`;
                        const percentage = Math.round((e.players / e.max_players) * 100);
                        document.getElementById('hero-player-bar').style.width = `${percentage}%`;
                        document.getElementById('hero-percentage').textContent = percentage;
                        document.getElementById('last-updated').textContent = 'Just now';
                    }
                    if (!statusWsConnected) {
                        statusWsConnected = true;
                        statusPollInterval = 120000;
                    }
                });
        }

        setInterval(async () => {
            try {
                const response = await fetch(`/servers/${serverId}/status`);
                const data = await response.json();

                document.getElementById('hero-player-count').textContent = `${data.players} / ${data.maxPlayers}`;
                const percentage = Math.round((data.players / data.maxPlayers) * 100);
                document.getElementById('hero-player-bar').style.width = `${percentage}%`;
                document.getElementById('hero-percentage').textContent = percentage;

                const isOnline = data.status === 'online';
                const statusIndicator = document.getElementById('status-indicator');
                const statusText = document.getElementById('status-text');

                statusIndicator.querySelectorAll('span').forEach(span => {
                    span.classList.remove('bg-green-500', 'bg-red-500');
                    span.classList.add(isOnline ? 'bg-green-500' : 'bg-red-500');
                });

                statusText.textContent = isOnline ? 'Online' : 'Offline';
                statusText.classList.remove('text-green-400', 'text-red-400');
                statusText.classList.add(isOnline ? 'text-green-400' : 'text-red-400');

                document.getElementById('last-updated').textContent = 'Just now';
            } catch (error) {
                console.error('Error refreshing status:', error);
            }
        }, statusPollInterval);
    }
</script>
<script src="https://cdn.jsdelivr.net/npm/chartjs-adapter-date-fns"></script>
<script>
    function killFeedWidget() {
        return {
            kills: @json($recentKills),
            hideAI: false,
            headshotsOnly: false,
            get filteredKills() {
                return this.kills.filter(k => {
                    if (this.hideAI && k.victim_type === 'AI') return false;
                    if (this.headshotsOnly && !k.is_headshot) return false;
                    return true;
                });
            },
            wsConnected: false,
            init() {
                this.pollTimer = setInterval(() => this.refresh(), 12000);
                if (window.Echo) {
                    const serverId = '{{ $server->id }}';
                    window.Echo.channel('server.' + serverId)
                        .listen('.kill.new', (e) => {
                            this.kills.unshift({
                                id: e.id,
                                killer_name: e.killer_name,
                                victim_name: e.victim_name,
                                weapon_name: e.weapon_name,
                                kill_distance: e.distance,
                                is_headshot: e.is_headshot,
                                is_team_kill: e.is_team_kill,
                                is_roadkill: e.is_roadkill,
                                victim_type: e.victim_type,
                                killed_at: e.timestamp,
                            });
                            this.kills = this.kills.slice(0, 50);
                            if (!this.wsConnected) {
                                this.wsConnected = true;
                                clearInterval(this.pollTimer);
                                this.pollTimer = setInterval(() => this.refresh(), 60000);
                            }
                        });
                }
            },
            async refresh() {
                try {
                    const latest = this.kills.length > 0 ? this.kills[0].killed_at : null;
                    const url = latest ? '/api/kill-feed?since=' + encodeURIComponent(latest) : '/api/kill-feed';
                    const res = await fetch(url);
                    const newKills = await res.json();
                    if (newKills.length > 0) {
                        this.kills = [...newKills, ...this.kills].slice(0, 50);
                    }
                } catch(e) {}
            },
            timeAgo(dateStr) {
                const diff = Math.floor((Date.now() - new Date(dateStr).getTime()) / 1000);
                if (diff < 60) return diff + 's';
                if (diff < 3600) return Math.floor(diff / 60) + 'm';
                if (diff < 86400) return Math.floor(diff / 3600) + 'h';
                return Math.floor(diff / 86400) + 'd';
            }
        };
    }
</script>
@endpush
@endsection
