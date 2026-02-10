@props(['gameStats', 'hitZonesDealt', 'hitZonesReceived'])

@if($gameStats && $gameStats->total_hits > 0)
@php
    $totalHits = $gameStats->total_hits ?: 1;
    $headPct = round(($gameStats->hits_head / $totalHits) * 100, 1);
    $torsoPct = round(($gameStats->hits_torso / $totalHits) * 100, 1);
    $armsPct = round(($gameStats->hits_arms / $totalHits) * 100, 1);
    $legsPct = round(($gameStats->hits_legs / $totalHits) * 100, 1);

    // Intensity: map percentage to opacity for SVG fills
    $headAlpha = max(0.15, min(0.8, $headPct / 100));
    $torsoAlpha = max(0.15, min(0.8, $torsoPct / 100));
    $armsAlpha = max(0.15, min(0.8, $armsPct / 100));
    $legsAlpha = max(0.15, min(0.8, $legsPct / 100));
@endphp

<div class="glass-card p-5 sm:p-6" x-data="{ tab: 'dealt' }">
    <div class="flex items-center justify-between mb-5">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Hit Zones</h3>
        <div class="flex gap-1 bg-white/5 rounded-lg p-0.5">
            <button @click="tab = 'dealt'" :class="tab === 'dealt' ? 'bg-white/10 text-white' : 'text-gray-500 hover:text-gray-300'" class="px-3 py-1 text-xs font-medium rounded-md transition">Dealt</button>
            <button @click="tab = 'received'" :class="tab === 'received' ? 'bg-white/10 text-white' : 'text-gray-500 hover:text-gray-300'" class="px-3 py-1 text-xs font-medium rounded-md transition">Received</button>
        </div>
    </div>

    <div class="flex flex-col sm:flex-row items-center gap-6">
        {{-- SVG Silhouette --}}
        <div class="flex-shrink-0 w-32"
            style="--head-fill: rgba(239,68,68,{{ $headAlpha }});
                   --torso-fill: rgba(251,146,60,{{ $torsoAlpha }});
                   --arms-fill: rgba(250,204,21,{{ $armsAlpha }});
                   --legs-fill: rgba(96,165,250,{{ $legsAlpha }});">
            <svg class="hit-zone-svg w-full" viewBox="0 0 120 280" fill="none" xmlns="http://www.w3.org/2000/svg">
                {{-- Head --}}
                <ellipse class="zone zone-head" cx="60" cy="30" rx="20" ry="24" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                {{-- Neck --}}
                <rect class="zone zone-torso" x="52" y="54" width="16" height="12" rx="4" stroke="rgba(255,255,255,0.1)" stroke-width="0.5"/>
                {{-- Torso --}}
                <path class="zone zone-torso" d="M35 66 Q60 62 85 66 L90 140 Q60 145 30 140 Z" stroke="rgba(255,255,255,0.15)" stroke-width="1"/>
                {{-- Left Arm --}}
                <path class="zone zone-arms" d="M35 68 L20 75 L12 130 L22 132 L30 85 L35 78" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
                {{-- Right Arm --}}
                <path class="zone zone-arms" d="M85 68 L100 75 L108 130 L98 132 L90 85 L85 78" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
                {{-- Left Leg --}}
                <path class="zone zone-legs" d="M38 140 L32 220 L26 270 L42 270 L46 220 L50 145" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
                {{-- Right Leg --}}
                <path class="zone zone-legs" d="M82 140 L88 220 L94 270 L78 270 L74 220 L70 145" stroke="rgba(255,255,255,0.12)" stroke-width="1"/>
            </svg>
        </div>

        {{-- Stats breakdown --}}
        <div class="flex-1 w-full space-y-3">
            {{-- Head --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-red-400">Head</span>
                    <span class="text-xs text-gray-400">{{ number_format($gameStats->hits_head) }} <span class="text-gray-600">({{ $headPct }}%)</span></span>
                </div>
                <div class="w-full bg-white/5 rounded-full h-2">
                    <div class="bg-red-500 h-2 rounded-full transition-all" style="width: {{ $headPct }}%"></div>
                </div>
            </div>

            {{-- Torso --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-orange-400">Torso</span>
                    <span class="text-xs text-gray-400">{{ number_format($gameStats->hits_torso) }} <span class="text-gray-600">({{ $torsoPct }}%)</span></span>
                </div>
                <div class="w-full bg-white/5 rounded-full h-2">
                    <div class="bg-orange-500 h-2 rounded-full transition-all" style="width: {{ $torsoPct }}%"></div>
                </div>
            </div>

            {{-- Arms --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-yellow-400">Arms</span>
                    <span class="text-xs text-gray-400">{{ number_format($gameStats->hits_arms) }} <span class="text-gray-600">({{ $armsPct }}%)</span></span>
                </div>
                <div class="w-full bg-white/5 rounded-full h-2">
                    <div class="bg-yellow-500 h-2 rounded-full transition-all" style="width: {{ $armsPct }}%"></div>
                </div>
            </div>

            {{-- Legs --}}
            <div>
                <div class="flex items-center justify-between mb-1">
                    <span class="text-xs font-medium text-blue-400">Legs</span>
                    <span class="text-xs text-gray-400">{{ number_format($gameStats->hits_legs) }} <span class="text-gray-600">({{ $legsPct }}%)</span></span>
                </div>
                <div class="w-full bg-white/5 rounded-full h-2">
                    <div class="bg-blue-500 h-2 rounded-full transition-all" style="width: {{ $legsPct }}%"></div>
                </div>
            </div>

            {{-- Total summary --}}
            <div class="pt-2 border-t border-white/5 flex items-center justify-between text-xs text-gray-500">
                <span>Total Hits: <span class="text-white font-medium">{{ number_format($gameStats->total_hits) }}</span></span>
                <span>Total Dmg: <span class="text-white font-medium">{{ number_format($gameStats->total_damage_dealt, 0) }}</span></span>
            </div>
        </div>
    </div>

    {{-- Detailed Hit Zone Distribution (tabbed) --}}
    @if($hitZonesDealt->count() > 0 || $hitZonesReceived->count() > 0)
    <div class="mt-5 pt-5 border-t border-white/5">
        {{-- Dealt tab --}}
        <div x-show="tab === 'dealt'" x-transition>
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Damage Dealt by Zone</h4>
            @php
                $dealtFiltered = $hitZonesDealt->filter(fn($z) => $z->hit_zone_name && $z->hit_zone_name !== 'SCR_CharacterResilienceHitZone');
                $dealtMax = $dealtFiltered->max('count') ?: 1;
                $dealtTotal = $dealtFiltered->sum('count') ?: 1;
            @endphp
            @if($dealtFiltered->count() > 0)
            <div class="space-y-1.5">
                @foreach($dealtFiltered->sortByDesc('count') as $zone)
                @php $pct = round(($zone->count / $dealtTotal) * 100, 1); $barW = ($zone->count / $dealtMax) * 100; @endphp
                <div class="flex items-center gap-2">
                    <span class="w-24 text-xs text-gray-400 truncate">{{ $zone->hit_zone_name }}</span>
                    <div class="flex-1 bg-white/5 rounded-full h-1.5">
                        <div class="bg-red-500/70 h-1.5 rounded-full" style="width: {{ $barW }}%"></div>
                    </div>
                    <span class="w-16 text-right text-[10px] text-gray-500">{{ $zone->count }} ({{ $pct }}%)</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-600">No hit zone data</p>
            @endif
        </div>

        {{-- Received tab --}}
        <div x-show="tab === 'received'" x-transition x-cloak>
            <h4 class="text-xs font-medium text-gray-500 uppercase tracking-wider mb-3">Damage Received by Zone</h4>
            @php
                $receivedFiltered = $hitZonesReceived->filter(fn($z) => $z->hit_zone_name && $z->hit_zone_name !== 'SCR_CharacterResilienceHitZone');
                $receivedMax = $receivedFiltered->max('count') ?: 1;
                $receivedTotal = $receivedFiltered->sum('count') ?: 1;
            @endphp
            @if($receivedFiltered->count() > 0)
            <div class="space-y-1.5">
                @foreach($receivedFiltered->sortByDesc('count') as $zone)
                @php $pct = round(($zone->count / $receivedTotal) * 100, 1); $barW = ($zone->count / $receivedMax) * 100; @endphp
                <div class="flex items-center gap-2">
                    <span class="w-24 text-xs text-gray-400 truncate">{{ $zone->hit_zone_name }}</span>
                    <div class="flex-1 bg-white/5 rounded-full h-1.5">
                        <div class="bg-blue-500/70 h-1.5 rounded-full" style="width: {{ $barW }}%"></div>
                    </div>
                    <span class="w-16 text-right text-[10px] text-gray-500">{{ $zone->count }} ({{ $pct }}%)</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-xs text-gray-600">No hit zone data</p>
            @endif
        </div>
    </div>
    @endif
</div>
@endif
