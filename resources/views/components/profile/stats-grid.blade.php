@props(['gameStats', 'killsByVictimType', 'friendlyFireDealt' => 0, 'friendlyFireReceived' => 0])

@if($gameStats)
<div class="space-y-4">
    {{-- Primary Stats (4 big cards) --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        {{-- Kills --}}
        <div class="glass-card glow-green-sm p-5">
            <div class="w-9 h-9 bg-green-500/15 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-green-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><circle cx="12" cy="12" r="10"/><circle cx="12" cy="12" r="3"/></svg>
            </div>
            <p class="text-3xl font-black text-green-400 tracking-tight">{{ number_format($gameStats->kills) }}</p>
            <p class="text-xs text-gray-500 mt-1">Kills</p>
            @if($killsByVictimType->count() > 0 || ($gameStats->total_roadkills ?? 0) > 0)
            <div class="mt-2.5 flex flex-wrap gap-1.5">
                @foreach($killsByVictimType as $killType)
                <span class="px-1.5 py-0.5 text-[10px] rounded font-medium {{ $killType->victim_type === 'AI' ? 'bg-yellow-500/15 text-yellow-400' : 'bg-red-500/15 text-red-400' }}">
                    {{ number_format($killType->total) }} {{ $killType->victim_type }}
                </span>
                @endforeach
                @if(($gameStats->total_roadkills ?? 0) > 0)
                <span class="px-1.5 py-0.5 text-[10px] rounded font-medium bg-violet-500/15 text-violet-400">
                    {{ number_format($gameStats->total_roadkills) }} Roadkill
                </span>
                @endif
            </div>
            @endif
        </div>

        {{-- Deaths --}}
        <div class="glass-card p-5">
            <div class="w-9 h-9 bg-red-500/15 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-red-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M6 18L18 6M6 6l12 12"/></svg>
            </div>
            <p class="text-3xl font-black text-red-400 tracking-tight">{{ number_format($gameStats->deaths) }}</p>
            <p class="text-xs text-gray-500 mt-1">Deaths</p>
        </div>

        {{-- K/D Ratio --}}
        @php $kd = $gameStats->deaths > 0 ? $gameStats->player_kills_count / $gameStats->deaths : $gameStats->player_kills_count; @endphp
        <div class="glass-card p-5">
            <div class="w-9 h-9 bg-yellow-500/15 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-yellow-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <p class="text-3xl font-black text-yellow-400 tracking-tight">{{ number_format($kd, 2) }}</p>
            <p class="text-xs text-gray-500 mt-1">K/D Ratio</p>
        </div>

        {{-- Headshots --}}
        <div class="glass-card p-5">
            <div class="w-9 h-9 bg-amber-500/15 rounded-lg flex items-center justify-center mb-3">
                <svg class="w-4.5 h-4.5 text-amber-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/></svg>
            </div>
            <p class="text-3xl font-black text-amber-400 tracking-tight">{{ number_format($gameStats->headshots) }}</p>
            <p class="text-xs text-gray-500 mt-1">Headshots</p>
            @if($gameStats->kills > 0)
            <p class="text-[10px] text-gray-600 mt-0.5">{{ number_format(($gameStats->headshots / $gameStats->kills) * 100, 1) }}% of kills</p>
            @endif
        </div>
    </div>

    {{-- Secondary Stats (compact row) --}}
    <div class="glass-card p-4">
        <div class="grid grid-cols-2 sm:grid-cols-4 lg:grid-cols-8 gap-3">
            @php $hours = floor($gameStats->playtime_seconds / 3600); $minutes = floor(($gameStats->playtime_seconds % 3600) / 60); @endphp
            <div class="text-center">
                <p class="text-lg font-bold text-blue-400">{{ $hours }}h {{ $minutes }}m</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Playtime</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-purple-400">{{ number_format($gameStats->total_distance / 1000, 1) }}km</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Distance</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-cyan-400">{{ number_format($gameStats->shots_fired) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Shots</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-orange-400">{{ number_format($gameStats->grenades_thrown) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Grenades</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-pink-400">{{ number_format($gameStats->heals_given) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Heals Given</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-amber-400">{{ number_format($gameStats->bases_captured) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Bases</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-emerald-400">{{ number_format($gameStats->supplies_delivered) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Supplies</p>
            </div>
            <div class="text-center">
                <p class="text-lg font-bold text-cyan-300">{{ number_format($gameStats->xp_total) }}</p>
                <p class="text-[10px] text-gray-500 uppercase tracking-wider">Total XP</p>
            </div>
        </div>
    </div>

    {{-- Friendly Fire + Team Kills (only if > 0) --}}
    @if($friendlyFireDealt > 0 || $friendlyFireReceived > 0 || ($gameStats->team_kills ?? 0) > 0)
    <div class="flex flex-wrap gap-3">
        @if($friendlyFireDealt > 0)
        <div class="glass-card px-4 py-2.5 flex items-center gap-2 border-orange-500/20">
            <div class="w-2 h-2 rounded-full bg-orange-500"></div>
            <span class="text-sm text-orange-400 font-bold">{{ number_format($friendlyFireDealt) }}</span>
            <span class="text-xs text-gray-500">FF Dealt</span>
        </div>
        @endif
        @if($friendlyFireReceived > 0)
        <div class="glass-card px-4 py-2.5 flex items-center gap-2 border-orange-500/20">
            <div class="w-2 h-2 rounded-full bg-orange-400"></div>
            <span class="text-sm text-orange-300 font-bold">{{ number_format($friendlyFireReceived) }}</span>
            <span class="text-xs text-gray-500">FF Received</span>
        </div>
        @endif
        @if(($gameStats->team_kills ?? 0) > 0)
        <div class="glass-card px-4 py-2.5 flex items-center gap-2 border-red-500/20">
            <div class="w-2 h-2 rounded-full bg-red-500"></div>
            <span class="text-sm text-red-400 font-bold">{{ number_format($gameStats->team_kills) }}</span>
            <span class="text-xs text-gray-500">Team Kills</span>
        </div>
        @endif
    </div>
    @endif
</div>
@endif
