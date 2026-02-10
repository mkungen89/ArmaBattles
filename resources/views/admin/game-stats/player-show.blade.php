@extends('admin.layout')

@section('title', ($player->player_name ?? 'Unknown') . ' - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.game-stats.players') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">{{ $player->player_name ?? 'Unknown Player' }}</h1>
    </div>

    {{-- Player Overview --}}
    @php
        $playtimeSeconds = $player->playtime_seconds ?? 0;
        $hours = floor($playtimeSeconds / 3600);
        $minutes = floor(($playtimeSeconds % 3600) / 60);
        $formattedPlaytime = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
        $kdRatio = ($player->deaths ?? 0) > 0 ? round(($player->kills ?? 0) / $player->deaths, 2) : ($player->kills ?? 0);
        $lastSeen = $player->last_seen_at ? \Carbon\Carbon::parse($player->last_seen_at) : null;
    @endphp
    <div class="glass-card rounded-xl p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6">
            <div>
                <p class="text-sm text-gray-400">UUID</p>
                <p class="text-white font-mono text-xs break-all">{{ $player->player_uuid ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Playtime</p>
                <p class="text-white font-medium">{{ $formattedPlaytime }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Kills</p>
                <p class="text-green-400 font-bold text-xl">{{ number_format($player->kills ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Deaths</p>
                <p class="text-red-400 font-bold text-xl">{{ number_format($player->deaths ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">K/D Ratio</p>
                <p class="{{ $kdRatio >= 1 ? 'text-green-400' : 'text-red-400' }} font-bold text-xl">{{ number_format($kdRatio, 2) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Headshots</p>
                <p class="text-yellow-400 font-bold text-xl">{{ number_format($player->headshots ?? 0) }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-6 gap-6 mt-6 pt-6 border-t border-white/5">
            <div>
                <p class="text-sm text-gray-400">Team Kills</p>
                <p class="text-orange-400 font-medium">{{ number_format($player->team_kills ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Friendly Fire Dealt</p>
                <p class="text-orange-400 font-medium">{{ number_format($friendlyFireDealt) }}</p>
            </div>
            @if($friendlyFireReceived > 0)
            <div>
                <p class="text-sm text-gray-400">Friendly Fire Received</p>
                <p class="text-orange-300 font-medium">{{ number_format($friendlyFireReceived) }}</p>
            </div>
            @endif
            <div>
                <p class="text-sm text-gray-400">Distance Traveled</p>
                <p class="text-white font-medium">{{ number_format($player->total_distance ?? 0, 1) }}m</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Heals Given</p>
                <p class="text-blue-400 font-medium">{{ number_format($player->heals_given ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Server</p>
                <p class="text-white font-medium">Server #{{ $player->server_id ?? 'N/A' }}</p>
            </div>
        </div>

        <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-5 gap-6 mt-6 pt-6 border-t border-white/5">
            <div>
                <p class="text-sm text-gray-400">Total XP</p>
                <p class="text-cyan-400 font-bold text-xl">{{ number_format($player->xp_total ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Shots Fired</p>
                <p class="text-white font-medium">{{ number_format($player->shots_fired ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Grenades Thrown</p>
                <p class="text-white font-medium">{{ number_format($player->grenades_thrown ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Bases Captured</p>
                <p class="text-purple-400 font-medium">{{ number_format($player->bases_captured ?? 0) }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Vehicles Destroyed</p>
                <p class="text-red-400 font-medium">{{ number_format($player->vehicles_destroyed ?? 0) }}</p>
            </div>
        </div>

        <div class="mt-4 pt-4 border-t border-white/5 flex gap-6 text-sm text-gray-400">
            <span>Last seen: {{ $lastSeen ? $lastSeen->format('M j, Y H:i') : 'Unknown' }}</span>
        </div>
    </div>

    {{-- Kills by Victim Type --}}
    @if($killsByVictimType->count() > 0)
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Kills by Type</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            @foreach($killsByVictimType as $killType)
            <div class="bg-white/3 rounded-lg p-4 text-center">
                <p class="text-2xl font-bold {{ $killType->victim_type === 'AI' ? 'text-yellow-400' : 'text-red-400' }}">
                    {{ number_format($killType->total) }}
                </p>
                <p class="text-sm text-gray-400 uppercase">{{ $killType->victim_type }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Top Weapons --}}
    @if($topWeapons->count() > 0)
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Top Weapons</h2>
        <div class="space-y-3">
            @foreach($topWeapons as $weapon)
            @php
                $maxKills = $topWeapons->first()->total ?? 1;
                $percentage = ($weapon->total / $maxKills) * 100;
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-20 h-[60px] flex items-center justify-center flex-shrink-0">
                    @if(isset($weaponImages[$weapon->weapon_name]))
                    <img src="{{ Storage::url($weaponImages[$weapon->weapon_name]) }}" alt="{{ $weapon->weapon_name }}" class="max-h-[60px] max-w-20 object-contain">
                    @else
                    <div class="w-6 h-6 bg-white/5 rounded flex items-center justify-center">
                        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                    </div>
                    @endif
                </div>
                <div class="w-32 text-sm text-gray-300 truncate">{{ $weapon->weapon_name }}</div>
                <div class="flex-1 bg-white/5 rounded-full h-4">
                    <div class="bg-green-500 h-4 rounded-full" style="width: {{ $percentage }}%"></div>
                </div>
                <div class="w-12 text-right text-sm text-gray-400">{{ $weapon->total }}</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Hit Zone Distribution --}}
    @if($hitZonesDealt->count() > 0 || $hitZonesReceived->count() > 0)
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Hit Zone Distribution</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Damage Dealt --}}
            <div>
                <h3 class="text-sm font-medium text-gray-400 mb-3 uppercase tracking-wide">Damage Dealt</h3>
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
                        <div class="flex-1 bg-white/5 rounded-full h-4">
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
                <h3 class="text-sm font-medium text-gray-400 mb-3 uppercase tracking-wide">Damage Received</h3>
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
                        <div class="flex-1 bg-white/5 rounded-full h-4">
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
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">XP Breakdown</h2>
        <div class="space-y-2">
            @php
                $xpMax = $xpByType->max('total_xp') ?: 1;
                $xpTotal = $xpByType->sum('total_xp') ?: 1;
            @endphp
            @foreach($xpByType as $xp)
            @php
                $pct = round(($xp->total_xp / $xpTotal) * 100, 1);
                $barWidth = ($xp->total_xp / $xpMax) * 100;
            @endphp
            <div class="flex items-center gap-3">
                <div class="w-28 text-sm text-gray-300 truncate">{{ $xp->reward_type ?? 'Unknown' }}</div>
                <div class="flex-1 bg-white/5 rounded-full h-4">
                    <div class="bg-cyan-500 h-4 rounded-full" style="width: {{ $barWidth }}%"></div>
                </div>
                <div class="w-32 text-right text-sm text-gray-400">{{ number_format($xp->total_xp) }} XP ({{ $pct }}%)</div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Recent Kill Events --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Recent Kills</h2>
            </div>
            <table class="w-full">
                <thead class="bg-white/3">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Victim</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Weapon</th>
                        <th class="px-4 py-2 text-right text-xs font-medium text-gray-400 uppercase">Distance</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentKillEvents as $kill)
                    @php
                        $killedAt = $kill->killed_at ? \Carbon\Carbon::parse($kill->killed_at) : null;
                    @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-2 text-sm text-gray-400">
                            {{ $killedAt ? $killedAt->diffForHumans() : '-' }}
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-2">
                                @if($kill->victim_type === 'AI')
                                <span class="px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded">AI</span>
                                @endif
                                <span class="text-red-400 text-sm">{{ $kill->victim_name ?? 'Unknown' }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <div class="flex items-center gap-1.5">
                                @if(isset($weaponImages[$kill->weapon_name]))
                                <img src="{{ Storage::url($weaponImages[$kill->weapon_name]) }}" alt="{{ $kill->weapon_name }}" class="h-4 w-auto object-contain flex-shrink-0">
                                @endif
                                <span class="text-xs text-gray-300 truncate">{{ $kill->weapon_name }}</span>
                                @if($kill->is_headshot)
                                <span class="text-yellow-400 text-xs">HS</span>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-2 text-right text-sm text-gray-400">
                            {{ $kill->kill_distance ? number_format($kill->kill_distance, 0) . 'm' : '-' }}
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="4" class="px-4 py-4 text-center text-gray-500">No kill events</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>

        {{-- Recent Connections --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-4 py-3 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Recent Connections</h2>
            </div>
            <table class="w-full">
                <thead class="bg-white/3">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                        <th class="px-4 py-2 text-center text-xs font-medium text-gray-400 uppercase">Event</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase">Server</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @forelse($recentSessions as $session)
                    @php
                        $occurredAt = $session->occurred_at ? \Carbon\Carbon::parse($session->occurred_at) : null;
                    @endphp
                    <tr class="hover:bg-white/5">
                        <td class="px-4 py-2 text-sm text-gray-400">
                            {{ $occurredAt ? $occurredAt->diffForHumans() : '-' }}
                        </td>
                        <td class="px-4 py-2 text-center">
                            @if($session->event_type === 'connect')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-green-500/20 text-green-400 text-xs rounded-full">
                                Connected
                            </span>
                            @elseif($session->event_type === 'disconnect')
                            <span class="inline-flex items-center gap-1 px-2 py-0.5 bg-red-500/20 text-red-400 text-xs rounded-full">
                                Disconnected
                            </span>
                            @else
                            <span class="text-gray-500 text-sm">{{ $session->event_type ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-2 text-sm text-gray-400">Server #{{ $session->server_id }}</td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="3" class="px-4 py-4 text-center text-gray-500">No connection history</td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
</div>
@endsection
