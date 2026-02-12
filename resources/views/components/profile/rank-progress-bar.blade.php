@props(['gameStats', 'levelService'])

@php
    $level = $gameStats->level ?? 1;
    $levelXp = $gameStats->level_xp ?? 0;
    $rankInfo = $levelService->getRankInfo($level);
    $era = $levelService->getEraForLevel($level);
    $rank = $levelService->getRankForLevel($level);

    // Progress within current rank (0-100%)
    $rankProgress = $levelService->getProgressInRank($gameStats);

    // Progress to next level (within current rank)
    $levelProgress = $levelService->getProgressToNextLevel($gameStats);
    $xpToNextLevel = $levelService->getXpToNextLevel($gameStats);

    // Era-based gradient colors
    $eraColors = [
        1 => ['from' => '#22c55e', 'to' => '#16a34a', 'glow' => 'rgba(34, 197, 94, 0.5)'], // Green
        2 => ['from' => '#22c55e', 'to' => '#16a34a', 'glow' => 'rgba(34, 197, 94, 0.5)'], // Green
        3 => ['from' => '#3b82f6', 'to' => '#2563eb', 'glow' => 'rgba(59, 130, 246, 0.5)'], // Blue
        4 => ['from' => '#3b82f6', 'to' => '#2563eb', 'glow' => 'rgba(59, 130, 246, 0.5)'], // Blue
        5 => ['from' => '#a855f7', 'to' => '#9333ea', 'glow' => 'rgba(168, 85, 247, 0.5)'], // Purple
        6 => ['from' => '#a855f7', 'to' => '#9333ea', 'glow' => 'rgba(168, 85, 247, 0.5)'], // Purple
        7 => ['from' => '#f97316', 'to' => '#ea580c', 'glow' => 'rgba(249, 115, 22, 0.5)'], // Orange
        8 => ['from' => '#f97316', 'to' => '#ea580c', 'glow' => 'rgba(249, 115, 22, 0.5)'], // Orange
        9 => ['from' => '#ef4444', 'to' => '#dc2626', 'glow' => 'rgba(239, 68, 68, 0.5)'], // Red
        10 => ['from' => '#ef4444', 'to' => '#fbbf24', 'glow' => 'rgba(251, 191, 36, 0.6)'], // Red to Gold
    ];

    $colors = $eraColors[$era] ?? $eraColors[1];

    // Next rank info
    $nextRank = $rank + 1;
    $nextRankInfo = $nextRank <= 50 ? \App\Models\RankLogo::forRank($nextRank) : null;

    // Level within current rank (1-10)
    $levelInRank = $level - ($rankInfo->min_level - 1);
@endphp

{{-- Military Battlelog Card --}}
<div class="glass-card p-6 relative overflow-hidden" style="border-color: {{ $rankInfo?->color ?? '#22c55e' }};">
    <div class="flex items-start gap-6">
        {{-- Rank Logo --}}
        <div class="flex-shrink-0">
            @if($rankInfo?->logo_url)
                <img src="{{ $rankInfo->logo_url }}" alt="{{ $rankInfo->name }}"
                     class="w-24 h-24 object-contain"
                     style="filter: drop-shadow(0 0 10px {{ $colors['glow'] }});">
            @else
                <div class="w-24 h-24 rounded-full flex items-center justify-center text-white font-bold text-2xl"
                     style="background: linear-gradient(135deg, {{ $colors['from'] }}, {{ $colors['to'] }}); box-shadow: 0 0 20px {{ $colors['glow'] }};">
                    {{ $rank }}
                </div>
            @endif
        </div>

        {{-- Rank Info & Progress --}}
        <div class="flex-1 min-w-0">
            {{-- Rank Name & Level --}}
            <div class="flex items-center justify-between mb-2">
                <div class="flex-1">
                    <div class="flex items-center gap-2">
                        <h3 class="text-2xl font-bold text-white" style="color: {{ $rankInfo?->color ?? '#22c55e' }};">
                            {{ $rankInfo?->name ?? 'Recruit' }}
                        </h3>
                        {{-- Info Tooltip --}}
                        <div class="relative group" x-data="{ tooltipOpen: false }">
                            <button type="button"
                                    @click="tooltipOpen = !tooltipOpen"
                                    @click.outside="tooltipOpen = false"
                                    class="flex items-center justify-center w-5 h-5 rounded-full bg-white/10 hover:bg-white/20 transition cursor-help">
                                <svg class="w-3 h-3 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                            </button>
                            {{-- Tooltip Content --}}
                            <div x-show="tooltipOpen"
                                 x-transition
                                 class="absolute left-0 top-8 z-50 w-80 bg-gray-900 border border-green-500/30 rounded-xl p-4 shadow-xl"
                                 style="display: none;">
                                <div class="flex items-start gap-2 mb-3">
                                    <svg class="w-5 h-5 text-green-500 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                    </svg>
                                    <div>
                                        <h4 class="text-sm font-bold text-white mb-1">Rank & Level System</h4>
                                        <p class="text-xs text-gray-400 leading-relaxed">
                                            You progress through <strong class="text-white">50 military ranks</strong> by earning XP.
                                            Every <strong class="text-green-400">10 levels = 1 rank promotion</strong>.
                                        </p>
                                    </div>
                                </div>
                                <div class="bg-white/5 rounded-lg p-3 mb-3">
                                    <p class="text-xs text-gray-400 mb-2"><strong class="text-white">Your Progress:</strong></p>
                                    <ul class="space-y-1 text-xs">
                                        <li class="flex items-center gap-2">
                                            <span class="text-green-400">●</span>
                                            <span class="text-gray-300"><strong class="text-white">Rank {{ $rank }}</strong> of 50</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="text-blue-400">●</span>
                                            <span class="text-gray-300"><strong class="text-white">Level {{ $level }}</strong> ({{ $levelInRank }}/10 in this rank)</span>
                                        </li>
                                        <li class="flex items-center gap-2">
                                            <span class="text-yellow-400">●</span>
                                            <span class="text-gray-300"><strong class="text-white">{{ 10 - $levelInRank }} levels</strong> until next rank</span>
                                        </li>
                                    </ul>
                                </div>
                                <a href="{{ route('faq') }}#rank-system" class="text-xs text-green-400 hover:text-green-300 underline">
                                    Learn more in FAQ →
                                </a>
                            </div>
                        </div>
                    </div>
                    <p class="text-sm text-gray-400 uppercase tracking-wide">
                        Rank {{ $rank }} of 50 • Era {{ $era }} • Level {{ $level }} ({{ $levelInRank }}/10)
                    </p>
                </div>
                <div class="text-right">
                    <p class="text-xs text-gray-500 uppercase">Total XP</p>
                    <p class="text-lg font-bold text-white">{{ number_format($levelXp) }}</p>
                </div>
            </div>

            {{-- Rank Progress Bar (10 levels per rank) --}}
            <div class="mb-4">
                <div class="flex justify-between items-center mb-2">
                    <span class="text-xs text-gray-400 uppercase tracking-wide font-semibold">Rank Progress</span>
                    <span class="text-xs font-bold" style="color: {{ $rankInfo?->color ?? '#22c55e' }};">{{ round($rankProgress, 1) }}%</span>
                </div>
                <div class="relative h-3 bg-black/40 rounded-full overflow-hidden border border-white/10">
                    <div class="h-full rounded-full transition-all duration-500 relative"
                         style="width: {{ $rankProgress }}%; background: linear-gradient(90deg, {{ $colors['from'] }}, {{ $colors['to'] }}); box-shadow: 0 0 15px {{ $colors['glow'] }}, inset 0 1px 0 rgba(255,255,255,0.3);">
                        {{-- Animated shine sweep --}}
                        <div class="absolute inset-0 bg-gradient-to-r from-transparent via-white/20 to-transparent animate-pulse"></div>
                    </div>
                </div>
            </div>


            {{-- Next Rank Preview --}}
            @if($nextRankInfo && $level < 500)
                <div class="flex items-center gap-2 mt-4 pt-3 border-t border-white/5">
                    <div class="w-8 h-8 rounded-full flex items-center justify-center opacity-50"
                         style="background: linear-gradient(135deg, {{ $nextRankInfo->color }}40, {{ $nextRankInfo->color }}20);">
                        @if($nextRankInfo->logo_url)
                            <img src="{{ $nextRankInfo->logo_url }}" alt="{{ $nextRankInfo->name }}" class="w-6 h-6 object-contain opacity-60">
                        @else
                            <span class="text-xs font-bold text-white/60">{{ $nextRank }}</span>
                        @endif
                    </div>
                    <div class="flex-1">
                        <p class="text-xs text-gray-500">Next Rank</p>
                        <p class="text-sm font-semibold text-gray-400">{{ $nextRankInfo->name }}</p>
                    </div>
                    @php
                        $xpToNextRank = $levelService->getXpToNextRank($gameStats);
                    @endphp
                    <p class="text-xs text-gray-500">{{ number_format($xpToNextRank) }} XP</p>
                </div>
            @elseif($level >= 500)
                <div class="mt-4 pt-3 border-t border-white/5 text-center">
                    <p class="text-sm font-bold text-yellow-400 uppercase tracking-wide" style="text-shadow: 0 0 10px rgba(251, 191, 36, 0.5);">
                        🏆 Maximum Rank Achieved 🏆
                    </p>
                </div>
            @endif
        </div>
    </div>
</div>
