@extends('layouts.app')

@section('title', 'Level Leaderboard')

@section('content')
<div class="min-h-screen pb-20">
    {{-- Header with gradient --}}
    <div class="relative mb-8 overflow-hidden">
        <div class="absolute inset-0 bg-gradient-to-br from-purple-600/20 via-blue-600/10 to-green-600/20"></div>
        <div class="relative max-w-7xl mx-auto px-4 sm:px-6 lg:px-8 py-12">
            <div class="text-center">
                <div class="inline-flex items-center gap-2 px-4 py-2 rounded-full bg-purple-500/10 border border-purple-500/20 text-purple-400 text-sm font-medium mb-4">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Progression System
                </div>
                <h1 class="text-4xl md:text-5xl font-bold text-white mb-4">Level Leaderboard</h1>
                <p class="text-lg text-gray-400 max-w-2xl mx-auto">
                    Track your progression from Recruit to Legend. Earn XP through gameplay and achievements to climb the ranks!
                </p>
            </div>
        </div>
    </div>

    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        {{-- Stats Overview --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-8">
            <div class="glass-card p-4">
                <div class="text-gray-500 text-sm mb-1">Total Players</div>
                <div class="text-2xl font-bold text-white">{{ number_format($stats['total_players']) }}</div>
            </div>
            <div class="glass-card p-4">
                <div class="text-gray-500 text-sm mb-1">Highest Level</div>
                <div class="text-2xl font-bold text-purple-400">{{ $stats['max_level'] }}</div>
            </div>
            <div class="glass-card p-4">
                <div class="text-gray-500 text-sm mb-1">Average Level</div>
                <div class="text-2xl font-bold text-blue-400">{{ $stats['avg_level'] }}</div>
            </div>
            <div class="glass-card p-4">
                <div class="text-gray-500 text-sm mb-1">Legends</div>
                <div class="text-2xl font-bold text-red-400">{{ number_format($stats['legends_count']) }}</div>
            </div>
        </div>

        {{-- Tier Distribution --}}
        <div class="glass-card p-6 mb-8">
            <h3 class="text-lg font-semibold text-white mb-4">Tier Distribution</h3>
            <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-3">
                @foreach(\App\Services\PlayerLevelService::TIERS as $key => $tier)
                    @php
                        $count = $tierDistribution[$key] ?? 0;
                        $colorClasses = [
                            'gray' => 'bg-gray-500/10 border-gray-500/30 text-gray-400',
                            'green' => 'bg-green-500/10 border-green-500/30 text-green-400',
                            'blue' => 'bg-blue-500/10 border-blue-500/30 text-blue-400',
                            'purple' => 'bg-purple-500/10 border-purple-500/30 text-purple-400',
                            'yellow' => 'bg-yellow-500/10 border-yellow-500/30 text-yellow-400',
                            'red' => 'bg-red-500/10 border-red-500/30 text-red-400',
                        ];
                        $colorClass = $colorClasses[$tier['color']] ?? $colorClasses['gray'];
                    @endphp
                    <div class="border rounded-lg p-3 {{ $colorClass }}">
                        <div class="text-xs font-medium mb-1">{{ $tier['label'] }}</div>
                        <div class="text-lg font-bold">{{ number_format($count) }}</div>
                        <div class="text-xs opacity-70">Lv {{ $tier['min'] }}-{{ $tier['max'] }}</div>
                    </div>
                @endforeach
            </div>
        </div>

        {{-- Leaderboard Table --}}
        <div class="glass-card overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="border-b border-gray-800">
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Rank</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Level</th>
                            <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Tier</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Total XP</th>
                            <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Progress</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-gray-800">
                        @forelse($leaderboard as $player)
                            <tr class="hover:bg-white/5 transition">
                                {{-- Rank --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        @if($player->rank === 1)
                                            <span class="text-xl">🥇</span>
                                        @elseif($player->rank === 2)
                                            <span class="text-xl">🥈</span>
                                        @elseif($player->rank === 3)
                                            <span class="text-xl">🥉</span>
                                        @else
                                            <span class="text-gray-500 font-medium">#{{ $player->rank }}</span>
                                        @endif
                                    </div>
                                </td>

                                {{-- Player --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-3">
                                        @if($player->user)
                                            <img src="{{ $player->user->avatar_display }}" alt="{{ $player->player_name }}" class="w-10 h-10 rounded-lg ring-2 ring-gray-700">
                                        @else
                                            <div class="w-10 h-10 rounded-lg bg-gray-700 flex items-center justify-center">
                                                <svg class="w-6 h-6 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        @endif
                                        <div>
                                            <a href="{{ $player->user ? route('profile.public', $player->user) : '#' }}" class="font-medium text-white hover:text-green-400 transition">
                                                {{ $player->player_name }}
                                            </a>
                                            <div class="text-xs text-gray-500">
                                                {{ number_format($player->kills) }} K / {{ number_format($player->deaths) }} D
                                            </div>
                                        </div>
                                    </div>
                                </td>

                                {{-- Level --}}
                                <td class="px-4 py-4">
                                    <div class="flex items-center gap-2">
                                        <svg class="w-5 h-5 text-{{ $player->tier['color'] }}-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                        </svg>
                                        <span class="text-xl font-bold text-white">{{ $player->level }}</span>
                                    </div>
                                </td>

                                {{-- Tier --}}
                                <td class="px-4 py-4">
                                    @php
                                        $tierColors = [
                                            'gray' => 'bg-gray-500/15 text-gray-400 border-gray-500/30',
                                            'green' => 'bg-green-500/15 text-green-400 border-green-500/30',
                                            'blue' => 'bg-blue-500/15 text-blue-400 border-blue-500/30',
                                            'purple' => 'bg-purple-500/15 text-purple-400 border-purple-500/30',
                                            'yellow' => 'bg-yellow-500/15 text-yellow-400 border-yellow-500/30',
                                            'red' => 'bg-red-500/15 text-red-400 border-red-500/30',
                                        ];
                                        $tierClass = $tierColors[$player->tier['color']] ?? $tierColors['gray'];
                                    @endphp
                                    <span class="inline-flex items-center px-2.5 py-1 rounded-lg text-xs font-semibold border {{ $tierClass }}">
                                        {{ $player->tier['label'] }}
                                    </span>
                                </td>

                                {{-- Total XP --}}
                                <td class="px-4 py-4 text-right">
                                    <div class="text-sm font-medium text-white">{{ number_format($player->level_xp) }}</div>
                                    <div class="text-xs text-gray-500">
                                        <span class="text-purple-400">{{ number_format($player->xp_total) }}</span> game +
                                        <span class="text-yellow-400">{{ number_format($player->achievement_points) }}</span> achievements
                                    </div>
                                </td>

                                {{-- Progress --}}
                                <td class="px-4 py-4 text-right">
                                    @if($player->level < 100)
                                        <div class="w-32 ml-auto">
                                            <div class="flex items-center justify-between text-xs mb-1">
                                                <span class="text-gray-500">{{ number_format($player->progress, 1) }}%</span>
                                            </div>
                                            <div class="h-1.5 bg-gray-800 rounded-full overflow-hidden">
                                                <div class="h-full bg-gradient-to-r from-{{ $player->tier['color'] }}-500 to-{{ $player->tier['color'] }}-400 rounded-full" style="width: {{ $player->progress }}%"></div>
                                            </div>
                                        </div>
                                    @else
                                        <span class="text-xs text-yellow-400 font-semibold">MAX LEVEL</span>
                                    @endif
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="6" class="px-4 py-12 text-center text-gray-500">
                                    No players found
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            {{-- Pagination --}}
            @if($leaderboard->hasPages())
                <div class="px-4 py-4 border-t border-gray-800">
                    {{ $leaderboard->links() }}
                </div>
            @endif
        </div>
    </div>
</div>
@endsection
