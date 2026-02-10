@props(['xpByType', 'vehicleStats', 'gameStats'])

<div class="space-y-4">
    {{-- XP Breakdown --}}
    @if($xpByType->count() > 0)
    <div class="glass-card p-5 sm:p-6">
        <div class="flex items-center justify-between mb-4">
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider">XP Breakdown</h3>
            @php $totalXp = $xpByType->sum('total_xp'); @endphp
            <span class="text-sm font-bold text-cyan-400">{{ number_format($totalXp) }} XP</span>
        </div>
        @php $xpMax = $xpByType->max('total_xp') ?: 1; $xpTotal = $totalXp ?: 1; @endphp
        <div class="space-y-2">
            @foreach($xpByType as $xp)
            @php
                $pct = round(($xp->total_xp / $xpTotal) * 100, 1);
                $barW = ($xp->total_xp / $xpMax) * 100;
            @endphp
            <div class="flex items-center gap-2">
                <span class="w-24 text-xs text-gray-400 truncate">{{ $xp->reward_type ?? 'Unknown' }}</span>
                <div class="flex-1 bg-white/5 rounded-full h-2">
                    <div class="bg-cyan-500/70 h-2 rounded-full transition-all" style="width: {{ $barW }}%"></div>
                </div>
                <span class="w-20 text-right text-[10px] text-gray-500">{{ number_format($xp->total_xp) }} ({{ $pct }}%)</span>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Vehicle Stats --}}
    @if($gameStats)
    @include('profile._vehicle-stats', ['vehicleStats' => $vehicleStats, 'gameStats' => $gameStats])
    @endif
</div>
