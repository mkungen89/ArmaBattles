@extends('admin.layout')

@section('title', 'Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Game Statistics</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.weapons.index') }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                </svg>
                Weapons
            </a>
            <a href="{{ route('admin.game-stats.api-tokens') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 7a2 2 0 012 2m4 0a6 6 0 01-7.743 5.743L11 17H9v2H7v2H4a1 1 0 01-1-1v-2.586a1 1 0 01.293-.707l5.964-5.964A6 6 0 1121 9z"/>
                </svg>
                API Tokens
            </a>
        </div>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Players</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_players']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Active (24h)</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['active_players_24h']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Kills</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_kills']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Sessions</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_sessions']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.75 17L9 20l-1 1h8l-1-1-.75-3M3 13h18M5 17h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">AI Kills</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['ai_kills']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Player Kills</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['player_kills']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-pink-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Headshots</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_headshots']) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-cyan-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Playtime</p>
                    @php
                        $totalHours = floor($stats['total_playtime'] / 3600);
                    @endphp
                    <p class="text-2xl font-bold text-white">{{ number_format($totalHours) }}h</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <a href="{{ route('admin.game-stats.players') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg group-hover:bg-blue-500/30 transition">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Players</p>
                    <p class="text-sm text-gray-400">All tracked players</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.kills') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-500/20 rounded-lg group-hover:bg-red-500/30 transition">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Kill Events</p>
                    <p class="text-sm text-gray-400">Browse kill feed</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.sessions') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Sessions</p>
                    <p class="text-sm text-gray-400">Connect/disconnect</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.server-status') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-cyan-500/20 rounded-lg group-hover:bg-cyan-500/30 transition">
                    <svg class="w-6 h-6 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Server Status</p>
                    <p class="text-sm text-gray-400">Status history</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.healing') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg group-hover:bg-green-500/30 transition">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4.318 6.318a4.5 4.5 0 000 6.364L12 20.364l7.682-7.682a4.5 4.5 0 00-6.364-6.364L12 7.636l-1.318-1.318a4.5 4.5 0 00-6.364 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Healing</p>
                    <p class="text-sm text-gray-400">Medical events</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.base-captures') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg group-hover:bg-yellow-500/30 transition">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Base Captures</p>
                    <p class="text-sm text-gray-400">Objective events</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.chat') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-pink-500/20 rounded-lg group-hover:bg-pink-500/30 transition">
                    <svg class="w-6 h-6 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Chat</p>
                    <p class="text-sm text-gray-400">Chat messages</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.game-sessions') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-orange-500/20 rounded-lg group-hover:bg-orange-500/30 transition">
                    <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Game Sessions</p>
                    <p class="text-sm text-gray-400">Match history</p>
                </div>
            </div>
        </a>

        <a href="{{ route('admin.game-stats.supply-deliveries') }}" class="bg-gray-800/50 border border-gray-700 hover:border-green-500/50 rounded-xl p-6 transition group">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-amber-500/20 rounded-lg group-hover:bg-amber-500/30 transition">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                </div>
                <div>
                    <p class="text-lg font-semibold text-white">Supply Deliveries</p>
                    <p class="text-sm text-gray-400">Logistics events</p>
                </div>
            </div>
        </a>
    </div>

    {{-- GM-Only Section --}}
    @if(auth()->user()->isGM())
    <div class="mt-6">
        <div class="flex items-center gap-2 mb-4">
            <h2 class="text-lg font-semibold text-white">GM Tools</h2>
            <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs rounded">GM/Admin Only</span>
        </div>
        <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
            <a href="{{ route('gm.sessions') }}" class="bg-gray-800/50 border border-purple-700/50 hover:border-purple-500/50 rounded-xl p-6 transition group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-white">GM Sessions</p>
                        <p class="text-sm text-gray-400">GM enter/exit logs</p>
                    </div>
                </div>
            </a>

            <a href="{{ route('gm.editor-actions') }}" class="bg-gray-800/50 border border-purple-700/50 hover:border-purple-500/50 rounded-xl p-6 transition group">
                <div class="flex items-center gap-4">
                    <div class="p-3 bg-purple-500/20 rounded-lg group-hover:bg-purple-500/30 transition">
                        <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                    </div>
                    <div>
                        <p class="text-lg font-semibold text-white">Editor Actions</p>
                        <p class="text-sm text-gray-400">GM actions log</p>
                    </div>
                </div>
            </a>
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Top Players --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-700">
                <h2 class="text-lg font-semibold text-white">Top Players</h2>
            </div>
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">#</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase">Kills</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase">Deaths</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase">K/D</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($topPlayers as $index => $player)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-4 py-2 text-gray-400">{{ $index + 1 }}</td>
                        <td class="px-4 py-2">
                            <a href="{{ route('admin.game-stats.player', $player->player_uuid) }}" class="text-white hover:text-green-400 transition">
                                {{ $player->player_name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td class="px-4 py-2 text-right text-green-400">{{ number_format($player->kills) }}</td>
                        <td class="px-4 py-2 text-right text-red-400">{{ number_format($player->deaths) }}</td>
                        <td class="px-4 py-2 text-right text-white font-medium">
                            {{ $player->deaths > 0 ? number_format($player->kills / $player->deaths, 2) : $player->kills }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="5" class="px-4 py-8 text-center text-gray-500">No player data yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Sessions --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Recent Sessions</h2>
                <a href="{{ route('admin.game-stats.sessions') }}" class="text-sm text-green-400 hover:text-green-300">View all</a>
            </div>
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-400 uppercase">Platform</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @forelse($recentSessions as $session)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-4 py-2 text-sm text-gray-400">{{ \Carbon\Carbon::parse($session->occurred_at)->diffForHumans() }}</td>
                        <td class="px-4 py-2 text-white">{{ $session->player_name }}</td>
                        <td class="px-4 py-2 text-center">
                            <span class="px-2 py-1 {{ $session->event_type === 'CONNECT' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }} text-xs rounded-full">{{ $session->event_type }}</span>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-8 text-center text-gray-500">No session data yet</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>

    {{-- Recent Kill Events --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-700 flex items-center justify-between">
            <h2 class="text-lg font-semibold text-white">Recent Kill Events</h2>
            <a href="{{ route('admin.game-stats.kills') }}" class="text-sm text-green-400 hover:text-green-300">View all</a>
        </div>
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Killer</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-400 uppercase">Weapon</th>
                    <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Victim</th>
                    <th class="px-4 py-2 text-center text-xs font-medium text-gray-400 uppercase">Distance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($recentKillEvents as $kill)
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-2 text-sm text-gray-400">{{ \Carbon\Carbon::parse($kill->killed_at)->diffForHumans() }}</td>
                    <td class="px-4 py-2">
                        <span class="text-green-400 font-medium">{{ $kill->killer_name }}</span>
                        @if($kill->killer_faction)
                        <span class="text-xs text-gray-500 ml-1">({{ $kill->killer_faction }})</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center">
                        <div class="flex flex-col items-center gap-1">
                            @if(isset($weaponImages[$kill->weapon_name]))
                            <img src="{{ Storage::url($weaponImages[$kill->weapon_name]) }}" alt="{{ $kill->weapon_name }}" class="h-6 w-auto object-contain">
                            @endif
                            <span class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">{{ $kill->weapon_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-2">
                        @if($kill->victim_type === 'AI')
                        <span class="px-3 py-1.5 bg-yellow-500/20 text-yellow-400 text-base font-semibold rounded-md">AI</span>
                        @else
                        <span class="text-red-400">{{ $kill->victim_name ?? 'Unknown' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2 text-center text-gray-400">{{ $kill->kill_distance ? number_format($kill->kill_distance, 0) . 'm' : '-' }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No kill events yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Latest Server Status --}}
    @if($latestServerStatus)
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Latest Server Status</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <div>
                <p class="text-sm text-gray-400">Server ID</p>
                <p class="text-white font-medium">#{{ $latestServerStatus->server_id }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Players</p>
                <p class="text-white font-medium">{{ $latestServerStatus->players_online }}/{{ $latestServerStatus->max_players }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">AI Count</p>
                <p class="text-white font-medium">{{ $latestServerStatus->ai_count }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">FPS</p>
                <p class="text-white font-medium">{{ $latestServerStatus->fps ?? 'N/A' }}</p>
            </div>
        </div>
        <p class="text-xs text-gray-500 mt-4">Last updated: {{ \Carbon\Carbon::parse($latestServerStatus->recorded_at)->diffForHumans() }}</p>
    </div>
    @endif
</div>
@endsection
