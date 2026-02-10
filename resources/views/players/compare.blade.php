@extends('layouts.app')

@section('title', 'Compare Players')

@section('content')
<div x-data="playerComparison()" class="space-y-6">
    <h1 class="text-3xl font-bold text-white">Compare Players</h1>

    {{-- Player Selectors --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 gap-4">
        {{-- Slots 1 & 2 always visible --}}
        @foreach(['p1', 'p2'] as $key)
        <div x-data="playerSearch('{{ $key }}', '{{ $players[$key]->player_name ?? '' }}', '{{ $uuids[$key] ?? '' }}')" class="relative">
            <label class="block text-sm font-medium text-gray-400 mb-2">Player {{ substr($key, 1) }}</label>
            <input type="text" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) showDropdown = true"
                   placeholder="Search player name..."
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-green-500">
            <div x-show="showDropdown" @click.outside="showDropdown = false" x-transition
                 class="absolute z-10 w-full mt-1 bg-white/3 border border-white/5 rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto" style="display:none;">
                <template x-for="p in results" :key="p.uuid">
                    <button @click="select(p)" class="w-full px-4 py-2 text-left hover:bg-white/5 transition flex items-center justify-between">
                        <span class="text-white" x-text="p.name"></span>
                        <span class="text-xs text-gray-500" x-text="p.kills + ' kills'"></span>
                    </button>
                </template>
            </div>
        </div>
        @endforeach

        {{-- Slots 3 & 4 toggleable --}}
        @foreach(['p3', 'p4'] as $key)
        <div x-show="showExtraSlots" x-transition x-data="playerSearch('{{ $key }}', '{{ $players[$key]->player_name ?? '' }}', '{{ $uuids[$key] ?? '' }}')" class="relative" style="display:none;">
            <label class="block text-sm font-medium text-gray-400 mb-2 flex items-center justify-between">
                <span>Player {{ substr($key, 1) }}</span>
                <button @click="removePlayer('{{ $key }}')" class="text-xs text-red-400 hover:text-red-300">&times; Remove</button>
            </label>
            <input type="text" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) showDropdown = true"
                   placeholder="Search player name..."
                   class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-green-500">
            <div x-show="showDropdown" @click.outside="showDropdown = false" x-transition
                 class="absolute z-10 w-full mt-1 bg-white/3 border border-white/5 rounded-xl shadow-xl overflow-hidden max-h-60 overflow-y-auto" style="display:none;">
                <template x-for="p in results" :key="p.uuid">
                    <button @click="select(p)" class="w-full px-4 py-2 text-left hover:bg-white/5 transition flex items-center justify-between">
                        <span class="text-white" x-text="p.name"></span>
                        <span class="text-xs text-gray-500" x-text="p.kills + ' kills'"></span>
                    </button>
                </template>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Add/Remove Player Buttons --}}
    <div class="flex gap-3">
        <button x-show="!showExtraSlots" @click="showExtraSlots = true" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-gray-300 rounded-xl text-sm transition" style="display:none;" x-transition>
            + Add More Players
        </button>
        <button x-show="showExtraSlots" @click="hideExtraSlots()" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-gray-300 rounded-xl text-sm transition" style="display:none;" x-transition>
            &minus; Fewer Players
        </button>
    </div>

    @php
        $playerCount = count($players);
        $playerColors = ['p1' => '#22c55e', 'p2' => '#3b82f6', 'p3' => '#f97316', 'p4' => '#a855f7'];
        $colorNames = ['p1' => 'green', 'p2' => 'blue', 'p3' => 'orange', 'p4' => 'purple'];

        $statDefs = [
            ['label' => 'Kills', 'key' => 'kills'],
            ['label' => 'Deaths', 'key' => 'deaths'],
            ['label' => 'K/D Ratio', 'key' => 'kd'],
            ['label' => 'Headshots', 'key' => 'headshots'],
            ['label' => 'Team Kills', 'key' => 'team_kills'],
            ['label' => 'Roadkills', 'key' => 'total_roadkills'],
            ['label' => 'Playtime (h)', 'key' => 'playtime'],
            ['label' => 'Distance (km)', 'key' => 'distance'],
            ['label' => 'Shots Fired', 'key' => 'shots_fired'],
            ['label' => 'Grenades Thrown', 'key' => 'grenades_thrown'],
            ['label' => 'Heals Given', 'key' => 'heals_given'],
            ['label' => 'Bases Captured', 'key' => 'bases_captured'],
            ['label' => 'Supplies Delivered', 'key' => 'supplies_delivered'],
            ['label' => 'XP Total', 'key' => 'xp_total'],
        ];

        $getValue = function($player, $key) {
            return match($key) {
                'kd' => $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : (float) $player->kills,
                'playtime' => round($player->playtime_seconds / 3600, 1),
                'distance' => round($player->total_distance / 1000, 1),
                default => (float) ($player->{$key} ?? 0),
            };
        };

        // Build player data array for JS
        $playerData = [];
        foreach ($players as $key => $player) {
            $playerData[$key] = [
                'name' => $player->player_name,
                'uuid' => $player->player_uuid,
                'kills' => (float) $player->kills,
                'deaths' => (float) $player->deaths,
                'kd' => $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : (float) $player->kills,
                'headshots' => (float) $player->headshots,
                'playtime' => round($player->playtime_seconds / 3600, 1),
                'distance' => round($player->total_distance / 1000, 1),
                'heals_given' => (float) $player->heals_given,
                'xp_total' => (float) $player->xp_total,
            ];
        }

        // Build weapon data for chart
        $allWeaponNames = [];
        foreach ($weapons as $key => $weaponList) {
            foreach ($weaponList as $w) {
                $allWeaponNames[$w->weapon_name] = true;
            }
        }
        $allWeaponNames = array_keys($allWeaponNames);

        $weaponChartData = [];
        foreach ($players as $key => $player) {
            $weaponMap = [];
            if (isset($weapons[$key])) {
                foreach ($weapons[$key] as $w) {
                    $weaponMap[$w->weapon_name] = $w->total;
                }
            }
            $weaponChartData[$key] = [];
            foreach ($allWeaponNames as $wn) {
                $weaponChartData[$key][] = $weaponMap[$wn] ?? 0;
            }
        }

        // Shorten weapon names for chart labels
        $shortWeaponNames = array_map(function($name) {
            $parts = explode('_', $name);
            return end($parts);
        }, $allWeaponNames);
    @endphp

    @if($playerCount >= 2)
    {{-- Player Name Header --}}
    <div class="flex flex-wrap items-center justify-center gap-6">
        @foreach($players as $key => $player)
        <div class="flex items-center gap-2">
            <span class="w-3 h-3 rounded-full inline-block" style="background-color: {{ $playerColors[$key] }};"></span>
            <span class="text-lg font-bold text-white">{{ $player->player_name }}</span>
        </div>
        @if(!$loop->last)
        <span class="text-gray-600 font-bold">VS</span>
        @endif
        @endforeach
    </div>

    {{-- Radar Chart --}}
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Stat Radar</h2>
        <div class="max-w-lg mx-auto">
            <canvas id="radarChart"></canvas>
        </div>
    </div>

    {{-- Stat Bars --}}
    <div class="glass-card rounded-xl p-6 space-y-5">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Stat Comparison</h2>
        @foreach($statDefs as $stat)
        @php
            $values = [];
            foreach ($players as $key => $player) {
                $values[$key] = $getValue($player, $stat['key']);
            }
            $maxVal = max(array_values($values) ?: [1]);
            if ($maxVal == 0) $maxVal = 1;
            $winnerVal = max(array_values($values));
            $decimals = in_array($stat['key'], ['kd']) ? 2 : (in_array($stat['key'], ['distance', 'playtime']) ? 1 : 0);
        @endphp
        <div>
            <div class="text-sm text-gray-400 font-medium mb-2">{{ $stat['label'] }}</div>
            <div class="space-y-1.5">
                @foreach($values as $key => $val)
                @php
                    $pct = ($val / $maxVal) * 100;
                    $isWinner = $val == $winnerVal && $val > 0;
                @endphp
                <div class="flex items-center gap-3">
                    <span class="w-24 text-xs text-gray-400 truncate text-right">{{ $players[$key]->player_name }}</span>
                    <div class="flex-1 bg-white/3 rounded-full h-5 relative">
                        <div class="h-5 rounded-full transition-all flex items-center justify-end pr-2"
                             style="width: {{ max($pct, 2) }}%; background-color: {{ $isWinner ? $playerColors[$key] : $playerColors[$key] . '66' }};">
                            <span class="text-xs font-bold {{ $isWinner ? 'text-white' : 'text-gray-300' }}" style="{{ $pct < 15 ? 'position:absolute;left:calc(' . max($pct, 2) . '% + 8px);' : '' }}">{{ number_format($val, $decimals) }}</span>
                        </div>
                    </div>
                </div>
                @endforeach
            </div>
        </div>
        @endforeach
    </div>

    {{-- Head-to-Head (only when exactly 2 players) --}}
    @if($playerCount === 2)
    @php $pKeys = array_keys($players); @endphp
    <div x-data="headToHead('{{ $players[$pKeys[0]]->player_uuid }}', '{{ $players[$pKeys[1]]->player_uuid }}', '{{ $players[$pKeys[0]]->player_name }}', '{{ $players[$pKeys[1]]->player_name }}')"
         x-init="load()"
         class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Head-to-Head Matchup</h2>

        <div x-show="loading" class="py-4">
            <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                <div class="bg-white/3 rounded-lg p-4">
                    <div class="skeleton skeleton-text-4xl w-16 mx-auto mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 mx-auto"></div>
                </div>
                <div class="flex items-center justify-center">
                    <div class="skeleton skeleton-text-lg w-12"></div>
                </div>
                <div class="bg-white/3 rounded-lg p-4">
                    <div class="skeleton skeleton-text-4xl w-16 mx-auto mb-2"></div>
                    <div class="skeleton skeleton-text w-3/4 mx-auto"></div>
                </div>
            </div>
        </div>

        <template x-if="!loading && data">
            <div class="space-y-6">
                {{-- Kill Counts --}}
                <div class="grid grid-cols-1 sm:grid-cols-3 gap-4 text-center">
                    <div class="bg-white/3 rounded-lg p-4">
                        <div class="text-3xl font-bold" style="color: {{ $playerColors[$pKeys[0]] }};" x-text="data.p1_killed_p2"></div>
                        <div class="text-sm text-gray-400 mt-1"><span class="font-medium text-white">{{ $players[$pKeys[0]]->player_name }}</span> killed <span class="font-medium text-white">{{ $players[$pKeys[1]]->player_name }}</span></div>
                    </div>
                    <div class="flex items-center justify-center">
                        <span class="text-2xl text-gray-600 font-bold">VS</span>
                    </div>
                    <div class="bg-white/3 rounded-lg p-4">
                        <div class="text-3xl font-bold" style="color: {{ $playerColors[$pKeys[1]] }};" x-text="data.p2_killed_p1"></div>
                        <div class="text-sm text-gray-400 mt-1"><span class="font-medium text-white">{{ $players[$pKeys[1]]->player_name }}</span> killed <span class="font-medium text-white">{{ $players[$pKeys[0]]->player_name }}</span></div>
                    </div>
                </div>

                {{-- Top Weapons in Matchup --}}
                <div class="grid grid-cols-1 sm:grid-cols-2 gap-4" x-show="data.p1_top_weapon || data.p2_top_weapon">
                    <div class="bg-white/3 rounded-lg p-3 text-center" x-show="data.p1_top_weapon">
                        <div class="text-xs text-gray-500 uppercase">{{ $players[$pKeys[0]]->player_name }}'s Favorite Weapon</div>
                        <div class="text-sm font-medium text-white mt-1" x-text="data.p1_top_weapon"></div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-3 text-center" x-show="data.p2_top_weapon">
                        <div class="text-xs text-gray-500 uppercase">{{ $players[$pKeys[1]]->player_name }}'s Favorite Weapon</div>
                        <div class="text-sm font-medium text-white mt-1" x-text="data.p2_top_weapon"></div>
                    </div>
                </div>

                {{-- Recent Encounters --}}
                <div x-show="data.recent_encounters && data.recent_encounters.length > 0">
                    <h3 class="text-sm font-semibold text-gray-400 uppercase mb-3">Recent Encounters</h3>
                    <div class="overflow-x-auto">
                        <table class="w-full text-sm">
                            <thead>
                                <tr class="text-gray-500 text-xs uppercase border-b border-white/5">
                                    <th class="pb-2 text-left">Date</th>
                                    <th class="pb-2 text-left">Killer</th>
                                    <th class="pb-2 text-left">Victim</th>
                                    <th class="pb-2 text-left">Weapon</th>
                                    <th class="pb-2 text-right">Distance</th>
                                    <th class="pb-2 text-center">HS</th>
                                </tr>
                            </thead>
                            <tbody>
                                <template x-for="enc in data.recent_encounters" :key="enc.created_at">
                                    <tr class="border-b border-white/5 hover:bg-white/5">
                                        <td class="py-2 text-gray-400" x-text="new Date(enc.created_at).toLocaleDateString()"></td>
                                        <td class="py-2 text-white font-medium" x-text="enc.killer_name"></td>
                                        <td class="py-2 text-gray-300" x-text="enc.victim_name"></td>
                                        <td class="py-2 text-gray-300" x-text="enc.weapon_name"></td>
                                        <td class="py-2 text-gray-400 text-right" x-text="enc.distance ? parseFloat(enc.distance).toFixed(0) + 'm' : '-'"></td>
                                        <td class="py-2 text-center" x-text="enc.is_headshot ? '💀' : ''"></td>
                                    </tr>
                                </template>
                            </tbody>
                        </table>
                    </div>
                </div>

                <div x-show="data.p1_killed_p2 === 0 && data.p2_killed_p1 === 0" class="text-center text-gray-500 text-sm py-2">
                    No encounters found between these players.
                </div>
            </div>
        </template>
    </div>
    @endif

    {{-- Weapon Preference Comparison --}}
    @if(count($allWeaponNames) > 0)
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Weapon Preferences</h2>
        <div class="w-full" style="min-height: {{ max(count($allWeaponNames) * 35, 200) }}px;">
            <canvas id="weaponChart"></canvas>
        </div>
    </div>
    @endif

    {{-- Per-Player Top Weapons Lists --}}
    <div class="grid grid-cols-1 sm:grid-cols-2 {{ $playerCount > 2 ? 'lg:grid-cols-' . min($playerCount, 4) : '' }} gap-4">
        @foreach($players as $key => $player)
        <div class="glass-card rounded-xl p-5">
            <h3 class="text-base font-semibold text-white mb-3 flex items-center gap-2">
                <span class="w-2.5 h-2.5 rounded-full inline-block" style="background-color: {{ $playerColors[$key] }};"></span>
                {{ $player->player_name }}'s Top Weapons
            </h3>
            @if(isset($weapons[$key]) && $weapons[$key]->count() > 0)
            <div class="space-y-1.5">
                @foreach($weapons[$key] as $weapon)
                <div class="flex items-center justify-between bg-white/3 rounded-lg px-3 py-2">
                    <span class="text-sm text-gray-300 truncate">{{ $weapon->weapon_name }}</span>
                    <span class="text-sm font-bold" style="color: {{ $playerColors[$key] }};">{{ number_format($weapon->total) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No weapon data</p>
            @endif
        </div>
        @endforeach
    </div>

    @elseif($playerCount === 1)
    <div class="text-center text-gray-400 py-8">Select at least two players to compare.</div>
    @else
    <div class="text-center text-gray-400 py-8">Search and select two or more players to compare their stats side by side.</div>
    @endif
</div>
@endsection

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>
<script>
    // Player search component
    function playerSearch(key, initialName, initialUuid) {
        return {
            query: initialName,
            results: [],
            showDropdown: false,
            selectedUuid: initialUuid,
            async search() {
                if (this.query.length < 2) { this.results = []; this.showDropdown = false; return; }
                const res = await fetch('/api/players/search?q=' + encodeURIComponent(this.query));
                this.results = await res.json();
                this.showDropdown = this.results.length > 0;
            },
            select(player) {
                this.query = player.name;
                this.selectedUuid = player.uuid;
                this.showDropdown = false;
                this.navigate(key, player.uuid);
            },
            navigate(key, uuid) {
                const params = new URLSearchParams(window.location.search);
                params.set(key, uuid);
                window.location.href = '/players/compare?' + params.toString();
            }
        };
    }

    // Main comparison component
    function playerComparison() {
        return {
            showExtraSlots: !!(new URLSearchParams(window.location.search).get('p3') || new URLSearchParams(window.location.search).get('p4')),
            removePlayer(key) {
                const params = new URLSearchParams(window.location.search);
                params.delete(key);
                // If removing p3 and p4 exists, shift p4 to p3
                if (key === 'p3' && params.has('p4')) {
                    params.set('p3', params.get('p4'));
                    params.delete('p4');
                }
                window.location.href = '/players/compare?' + params.toString();
            },
            hideExtraSlots() {
                const params = new URLSearchParams(window.location.search);
                params.delete('p3');
                params.delete('p4');
                window.location.href = '/players/compare?' + params.toString();
            }
        };
    }

    // Head-to-head component
    function headToHead(uuid1, uuid2, name1, name2) {
        return {
            loading: true,
            data: null,
            async load() {
                try {
                    const res = await fetch('/players/compare/head-to-head?p1=' + encodeURIComponent(uuid1) + '&p2=' + encodeURIComponent(uuid2));
                    this.data = await res.json();
                } catch (e) {
                    this.data = { p1_killed_p2: 0, p2_killed_p1: 0, recent_encounters: [] };
                }
                this.loading = false;
            }
        };
    }

    // Charts initialization
    document.addEventListener('DOMContentLoaded', function() {
        const playerData = @json($playerData);
        const playerColors = { p1: '#22c55e', p2: '#3b82f6', p3: '#f97316', p4: '#a855f7' };
        const playerColorsBg = { p1: 'rgba(34,197,94,0.15)', p2: 'rgba(59,130,246,0.15)', p3: 'rgba(249,115,22,0.15)', p4: 'rgba(168,85,247,0.15)' };
        const keys = Object.keys(playerData);

        if (keys.length < 2) return;

        // Radar chart
        const radarCanvas = document.getElementById('radarChart');
        if (radarCanvas) {
            const radarLabels = ['Kills', 'Deaths', 'K/D', 'Headshots', 'Playtime', 'Distance', 'Heals', 'XP'];
            const radarKeys = ['kills', 'deaths', 'kd', 'headshots', 'playtime', 'distance', 'heals_given', 'xp_total'];

            // Normalize to 0-100
            const maxVals = {};
            radarKeys.forEach(k => {
                maxVals[k] = Math.max(...keys.map(p => playerData[p][k] || 0), 1);
            });

            const datasets = keys.map(k => ({
                label: playerData[k].name,
                data: radarKeys.map(rk => ((playerData[k][rk] || 0) / maxVals[rk]) * 100),
                borderColor: playerColors[k],
                backgroundColor: playerColorsBg[k],
                pointBackgroundColor: playerColors[k],
                pointBorderColor: playerColors[k],
                borderWidth: 2,
                pointRadius: 3,
            }));

            new Chart(radarCanvas, {
                type: 'radar',
                data: { labels: radarLabels, datasets: datasets },
                options: {
                    responsive: true,
                    maintainAspectRatio: true,
                    scales: {
                        r: {
                            beginAtZero: true,
                            max: 100,
                            ticks: { display: false },
                            grid: { color: 'rgba(255,255,255,0.08)' },
                            angleLines: { color: 'rgba(255,255,255,0.08)' },
                            pointLabels: { color: '#9ca3af', font: { size: 12 } }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#d1d5db', usePointStyle: true, pointStyle: 'circle' }
                        },
                        tooltip: {
                            callbacks: {
                                label: function(ctx) {
                                    const key = radarKeys[ctx.dataIndex];
                                    const pKey = keys[ctx.datasetIndex];
                                    const raw = playerData[pKey][key] || 0;
                                    return ctx.dataset.label + ': ' + raw + ' (' + ctx.parsed.r.toFixed(0) + '%)';
                                }
                            }
                        }
                    }
                }
            });
        }

        // Weapon chart
        const weaponCanvas = document.getElementById('weaponChart');
        if (weaponCanvas) {
            const weaponLabels = @json($shortWeaponNames);
            const weaponDatasets = [];

            @foreach($players as $key => $player)
            weaponDatasets.push({
                label: @json($player->player_name),
                data: @json($weaponChartData[$key]),
                backgroundColor: playerColors['{{ $key }}'] + 'cc',
                borderColor: playerColors['{{ $key }}'],
                borderWidth: 1,
                borderRadius: 3,
            });
            @endforeach

            new Chart(weaponCanvas, {
                type: 'bar',
                data: { labels: weaponLabels, datasets: weaponDatasets },
                options: {
                    indexAxis: 'y',
                    responsive: true,
                    maintainAspectRatio: false,
                    scales: {
                        x: {
                            beginAtZero: true,
                            grid: { color: 'rgba(255,255,255,0.05)' },
                            ticks: { color: '#9ca3af' }
                        },
                        y: {
                            grid: { display: false },
                            ticks: { color: '#d1d5db', font: { size: 11 } }
                        }
                    },
                    plugins: {
                        legend: {
                            labels: { color: '#d1d5db', usePointStyle: true, pointStyle: 'circle' }
                        }
                    }
                }
            });
        }
    });
</script>
@endpush
