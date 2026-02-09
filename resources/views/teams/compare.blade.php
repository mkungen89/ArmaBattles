@extends('layouts.app')

@section('title', 'Compare Platoons')

@section('content')
<div class="space-y-6">
    <h1 class="text-3xl font-bold text-white">Compare Platoons</h1>

    {{-- Team Selectors --}}
    <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
        @foreach(['t1' => $team1, 't2' => $team2] as $key => $team)
        <div>
            <label class="block text-sm font-medium text-gray-400 mb-2">Platoon {{ $key === 't1' ? '1' : '2' }}</label>
            <form method="GET" action="{{ route('teams.compare') }}">
                @if($key === 't1' && $t2)<input type="hidden" name="t2" value="{{ $t2 }}">@endif
                @if($key === 't2' && $t1)<input type="hidden" name="t1" value="{{ $t1 }}">@endif
                <select name="{{ $key }}" onchange="this.form.submit()"
                        class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white focus:outline-none focus:border-green-500">
                    <option value="">Select a platoon...</option>
                    @foreach($teams as $t)
                    <option value="{{ $t->id }}" {{ ${$key} == $t->id ? 'selected' : '' }}>
                        [{{ $t->tag }}] {{ $t->name }}
                    </option>
                    @endforeach
                </select>
            </form>
        </div>
        @endforeach
    </div>

    @if($team1 && $team2 && $stats1 && $stats2)
    @php
        $comparisons = [
            ['label' => 'Total Kills', 'k' => 'total_kills', 'color' => 'green'],
            ['label' => 'Total Deaths', 'k' => 'total_deaths', 'color' => 'red'],
            ['label' => 'Total Headshots', 'k' => 'total_headshots', 'color' => 'amber'],
            ['label' => 'Avg K/D', 'k' => 'avg_kd', 'color' => 'yellow', 'decimal' => 2],
            ['label' => 'Total Playtime (h)', 'k' => 'total_playtime_hours', 'color' => 'blue', 'decimal' => 1],
            ['label' => 'Avg Kills/Member', 'k' => 'avg_kills', 'color' => 'green', 'decimal' => 1],
        ];
    @endphp

    <div class="grid grid-cols-3 gap-4 items-center">
        <div class="text-center">
            <div class="flex items-center justify-center gap-2">
                @if($team1->avatar_url)
                <img src="{{ $team1->avatar_url }}" alt="{{ $team1->name }}" class="w-10 h-10 rounded-lg object-cover">
                @endif
                <p class="text-xl font-bold text-white">[{{ $team1->tag }}] {{ $team1->name }}</p>
            </div>
            <p class="text-sm text-gray-400">{{ $stats1['member_count'] }} members</p>
        </div>
        <div class="text-center text-gray-500 text-lg font-bold">VS</div>
        <div class="text-center">
            <div class="flex items-center justify-center gap-2">
                @if($team2->avatar_url)
                <img src="{{ $team2->avatar_url }}" alt="{{ $team2->name }}" class="w-10 h-10 rounded-lg object-cover">
                @endif
                <p class="text-xl font-bold text-white">[{{ $team2->tag }}] {{ $team2->name }}</p>
            </div>
            <p class="text-sm text-gray-400">{{ $stats2['member_count'] }} members</p>
        </div>
    </div>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 space-y-4">
        @foreach($comparisons as $c)
        @php
            $v1 = $stats1[$c['k']] ?? 0;
            $v2 = $stats2[$c['k']] ?? 0;
            $max = max($v1, $v2, 1);
            $p1Pct = ($v1 / $max) * 100;
            $p2Pct = ($v2 / $max) * 100;
            $dec = $c['decimal'] ?? 0;
        @endphp
        <div>
            <div class="text-center text-xs text-gray-500 uppercase mb-1">{{ $c['label'] }}</div>
            <div class="grid grid-cols-[1fr_auto_1fr] gap-3 items-center">
                <div class="flex items-center justify-end gap-2">
                    <span class="text-sm font-bold {{ $v1 >= $v2 ? 'text-green-400' : 'text-gray-400' }}">{{ number_format($v1, $dec) }}</span>
                    <div class="w-full max-w-[200px] bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all {{ $v1 >= $v2 ? 'bg-green-500' : 'bg-gray-600' }}" style="width: {{ $p1Pct }}%; margin-left: auto;"></div>
                    </div>
                </div>
                <div class="w-8 text-center">
                    @if($v1 > $v2)<span class="text-green-400 text-xs font-bold">&larr;</span>
                    @elseif($v2 > $v1)<span class="text-green-400 text-xs font-bold">&rarr;</span>
                    @else<span class="text-gray-500 text-xs">=</span>@endif
                </div>
                <div class="flex items-center gap-2">
                    <div class="w-full max-w-[200px] bg-gray-700 rounded-full h-3">
                        <div class="h-3 rounded-full transition-all {{ $v2 >= $v1 ? 'bg-green-500' : 'bg-gray-600' }}" style="width: {{ $p2Pct }}%;"></div>
                    </div>
                    <span class="text-sm font-bold {{ $v2 >= $v1 ? 'text-green-400' : 'text-gray-400' }}">{{ number_format($v2, $dec) }}</span>
                </div>
            </div>
        </div>
        @endforeach
    </div>
    @elseif($t1 || $t2)
    <div class="text-center text-gray-400 py-8">Select both platoons to compare.</div>
    @else
    <div class="text-center text-gray-400 py-8">Select two platoons to compare their aggregated combat statistics.</div>
    @endif
</div>
@endsection
