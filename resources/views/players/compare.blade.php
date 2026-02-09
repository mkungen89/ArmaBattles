@extends('layouts.app')

@section('title', 'Compare Players')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-white">Compare Players</h1>

    {{-- Player Selectors --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['p1' => $player1, 'p2' => $player2] as $key => $player)
        <div x-data="{
            query: '{{ $player?->player_name ?? '' }}',
            results: [],
            showDropdown: false,
            selectedUuid: '{{ ${$key} ?? '' }}',
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
                this.navigate();
            },
            navigate() {
                const params = new URLSearchParams(window.location.search);
                params.set('{{ $key }}', this.selectedUuid);
                window.location.href = '/players/compare?' + params.toString();
            }
        }" class="relative">
            <label class="block text-sm font-medium text-gray-400 mb-2">Player {{ $key === 'p1' ? '1' : '2' }}</label>
            <input type="text" x-model="query" @input.debounce.300ms="search()" @focus="if(results.length) showDropdown = true"
                   placeholder="Search player name..."
                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-green-500">
            <div x-show="showDropdown" @click.outside="showDropdown = false" x-transition
                 class="absolute z-10 w-full mt-1 bg-gray-800 border border-gray-700 rounded-xl shadow-xl overflow-hidden" style="display:none;">
                <template x-for="p in results" :key="p.uuid">
                    <button @click="select(p)" class="w-full px-4 py-2 text-left hover:bg-gray-700 transition flex items-center justify-between">
                        <span class="text-white" x-text="p.name"></span>
                        <span class="text-xs text-gray-500" x-text="p.kills + ' kills'"></span>
                    </button>
                </template>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Comparison --}}
    @if($player1 && $player2)
    @php
        $stats = [
            ['label' => 'Kills', 'key' => 'kills', 'color' => 'green'],
            ['label' => 'Deaths', 'key' => 'deaths', 'color' => 'red'],
            ['label' => 'K/D Ratio', 'key' => 'kd', 'color' => 'yellow'],
            ['label' => 'Headshots', 'key' => 'headshots', 'color' => 'amber'],
            ['label' => 'Playtime (h)', 'key' => 'playtime', 'color' => 'blue'],
            ['label' => 'Distance (km)', 'key' => 'distance', 'color' => 'purple'],
            ['label' => 'Heals Given', 'key' => 'heals_given', 'color' => 'pink'],
            ['label' => 'Bases Captured', 'key' => 'bases_captured', 'color' => 'amber'],
            ['label' => 'Supplies Delivered', 'key' => 'supplies_delivered', 'color' => 'emerald'],
            ['label' => 'XP Total', 'key' => 'xp_total', 'color' => 'cyan'],
        ];
        $getValue = function($player, $key) {
            return match($key) {
                'kd' => $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : $player->kills,
                'playtime' => round($player->playtime_seconds / 3600, 1),
                'distance' => round($player->total_distance / 1000, 1),
                default => $player->{$key} ?? 0,
            };
        };
    @endphp

    {{-- Header --}}
    <div class="grid grid-cols-3 gap-4 items-center">
        <div class="text-center">
            <p class="text-xl font-bold text-white">{{ $player1->player_name }}</p>
        </div>
        <div class="text-center text-gray-500 text-lg font-bold">VS</div>
        <div class="text-center">
            <p class="text-xl font-bold text-white">{{ $player2->player_name }}</p>
        </div>
    </div>

    {{-- Stat Bars --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 space-y-4">
        @foreach($stats as $stat)
        @php
            $v1 = $getValue($player1, $stat['key']);
            $v2 = $getValue($player2, $stat['key']);
            $max = max($v1, $v2, 1);
            $p1Pct = ($v1 / $max) * 100;
            $p2Pct = ($v2 / $max) * 100;
        @endphp
        <div>
            <div class="text-center text-xs text-gray-500 uppercase mb-1">{{ $stat['label'] }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] gap-3 items-center">
                <div class="flex items-center justify-end gap-2">
                    <span class="text-sm font-bold {{ $v1 >= $v2 ? 'text-green-400' : 'text-gray-400' }}">{{ number_format($v1, $stat['key'] === 'kd' ? 2 : ($stat['key'] === 'distance' || $stat['key'] === 'playtime' ? 1 : 0)) }}</span>
                    <div class="w-full max-w-[200px] bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all {{ $v1 >= $v2 ? 'bg-green-500' : 'bg-gray-600' }}" style="width: {{ $p1Pct }}%; margin-left: auto;"></div>
                    </div>
                </div>
                <div class="w-8 text-center">
                    @if($v1 > $v2)
                    <span class="text-green-400 text-xs font-bold">&larr;</span>
                    @elseif($v2 > $v1)
                    <span class="text-green-400 text-xs font-bold">&rarr;</span>
                    @else
                    <span class="text-gray-500 text-xs">=</span>
                    @endif
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-full max-w-[200px] bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all {{ $v2 >= $v1 ? 'bg-green-500' : 'bg-gray-600' }}" style="width: {{ $p2Pct }}%;"></div>
                    </div>
                    <span class="text-sm font-bold {{ $v2 >= $v1 ? 'text-green-400' : 'text-gray-400' }}">{{ number_format($v2, $stat['key'] === 'kd' ? 2 : ($stat['key'] === 'distance' || $stat['key'] === 'playtime' ? 1 : 0)) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>

    {{-- Top Weapons Comparison --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
        @foreach([[$player1, $p1Weapons], [$player2, $p2Weapons]] as [$player, $weapons])
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-4">{{ $player->player_name }}'s Top Weapons</h3>
            @if($weapons->count() > 0)
            <div class="space-y-2">
                @foreach($weapons as $weapon)
                <div class="flex items-center justify-between bg-gray-700/30 rounded-lg p-3">
                    <span class="text-sm text-gray-300 truncate">{{ $weapon->weapon_name }}</span>
                    <span class="text-sm font-bold text-green-400">{{ number_format($weapon->total) }}</span>
                </div>
                @endforeach
            </div>
            @else
            <p class="text-gray-500 text-sm">No weapon data</p>
            @endif
        </div>
        @endforeach
    </div>
    @elseif($p1 || $p2)
    <div class="text-center text-gray-400 py-8">Select both players to compare.</div>
    @else
    <div class="text-center text-gray-400 py-8">Search and select two players to compare their stats side by side.</div>
    @endif
</div>
@endsection
