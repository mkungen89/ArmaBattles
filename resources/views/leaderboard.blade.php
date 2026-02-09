@extends('layouts.app')

@section('title', 'Leaderboard')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-white">Leaderboard</h1>

        {{-- Export Buttons --}}
        <div class="flex gap-2">
            <a href="{{ route('export.leaderboard.csv', ['type' => $sort]) }}"
               class="px-4 py-2 bg-green-600/80 hover:bg-green-500 text-white text-sm rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                </svg>
                Export CSV
            </a>
            <a href="{{ route('export.leaderboard.json', ['type' => $sort]) }}"
               class="px-4 py-2 bg-blue-600/80 hover:bg-blue-500 text-white text-sm rounded-lg transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 10v6m0 0l-3-3m3 3l3-3m2 8H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                </svg>
                Export JSON
            </a>
        </div>
    </div>

    {{-- Period Tabs --}}
    <div class="flex flex-wrap gap-2">
        @php
            $periods = ['all' => 'All Time', 'monthly' => 'This Month', 'weekly' => 'This Week'];
        @endphp
        @foreach($periods as $key => $label)
        <a href="{{ route('leaderboard', ['sort' => $sort, 'period' => $key]) }}"
           class="px-4 py-2 rounded-lg text-sm font-medium transition {{ ($period ?? 'all') === $key ? 'bg-green-600 text-white' : 'bg-gray-700/50 text-gray-400 border border-gray-600 hover:text-white' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Sort Filter --}}
    <div class="flex flex-wrap gap-2">
        @php
            $filters = [
                'kills' => 'Kills',
                'deaths' => 'Deaths',
                'headshots' => 'Headshots',
                'playtime_seconds' => 'Playtime',
                'total_distance' => 'Distance',
                'bases_captured' => 'Bases Captured',
                'heals_given' => 'Heals Given',
                'supplies_delivered' => 'Supplies Delivered',
                'xp_total' => 'XP',
            ];
        @endphp
        @foreach($filters as $key => $label)
        <a href="{{ route('leaderboard', ['sort' => $key, 'period' => $period ?? 'all']) }}"
           class="px-3 py-1.5 rounded-lg text-sm font-medium transition {{ $sort === $key ? 'bg-green-500/20 text-green-400 border border-green-500/50' : 'bg-gray-700/50 text-gray-400 border border-gray-600 hover:text-white' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Leaderboard Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase w-16">Rank</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'kills', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'kills' ? 'text-green-400' : 'hover:text-white' }}">Kills</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'deaths', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'deaths' ? 'text-green-400' : 'hover:text-white' }}">Deaths</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">K/D</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'headshots', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'headshots' ? 'text-green-400' : 'hover:text-white' }}">HS</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'playtime_seconds', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'playtime_seconds' ? 'text-green-400' : 'hover:text-white' }}">Playtime</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'total_distance', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'total_distance' ? 'text-green-400' : 'hover:text-white' }}">Distance</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'bases_captured', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'bases_captured' ? 'text-green-400' : 'hover:text-white' }}">Bases</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'heals_given', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'heals_given' ? 'text-green-400' : 'hover:text-white' }}">Heals</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'supplies_delivered', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'supplies_delivered' ? 'text-green-400' : 'hover:text-white' }}">Supplies</a>
                    </th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">
                        <a href="{{ route('leaderboard', ['sort' => 'xp_total', 'period' => $period ?? 'all']) }}" class="{{ $sort === 'xp_total' ? 'text-green-400' : 'hover:text-white' }}">XP</a>
                    </th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($players as $index => $player)
                @php
                    $rank = ($players->currentPage() - 1) * $players->perPage() + $index + 1;
                    $kd = $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : $player->kills;
                    $user = $linkedUsers[$player->player_uuid] ?? null;
                @endphp
                <tr class="{{ $loop->odd ? 'bg-gray-800/30' : 'bg-gray-800/10' }} hover:bg-gray-700/30 transition-colors">
                    <td class="px-4 py-3">
                        @if($rank === 1)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 font-bold text-sm">1</span>
                        @elseif($rank === 2)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400/20 text-gray-300 font-bold text-sm">2</span>
                        @elseif($rank === 3)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-700/20 text-amber-600 font-bold text-sm">3</span>
                        @else
                        <span class="text-gray-500 text-sm pl-2">{{ $rank }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($user)
                                <img src="{{ $user->avatar_display }}" alt="{{ $player->player_name }}" class="w-8 h-8 rounded-full">
                                <a href="{{ route('players.show', $user->id) }}" class="text-white font-medium hover:text-green-400 transition">
                                    {{ $player->player_name }}
                                </a>
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                                <span class="text-white font-medium">{{ $player->player_name }}</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'kills' ? 'text-green-400 font-bold' : 'text-green-400/70' }}">{{ number_format($player->kills) }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'deaths' ? 'text-red-400 font-bold' : 'text-red-400/70' }}">{{ number_format($player->deaths) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-yellow-400 font-medium">{{ number_format($kd, 2) }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'headshots' ? 'text-white font-bold' : 'text-gray-400' }}">{{ number_format($player->headshots) }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'playtime_seconds' ? 'text-white font-bold' : 'text-gray-400' }}">
                        {{ floor($player->playtime_seconds / 3600) }}h {{ floor(($player->playtime_seconds % 3600) / 60) }}m
                    </td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'total_distance' ? 'text-white font-bold' : 'text-gray-400' }}">
                        {{ number_format($player->total_distance / 1000, 1) }}km
                    </td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'bases_captured' ? 'text-white font-bold' : 'text-gray-400' }}">{{ number_format($player->bases_captured) }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'heals_given' ? 'text-white font-bold' : 'text-gray-400' }}">{{ number_format($player->heals_given) }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'supplies_delivered' ? 'text-white font-bold' : 'text-gray-400' }}">{{ number_format($player->supplies_delivered) }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ $sort === 'xp_total' ? 'text-white font-bold' : 'text-gray-400' }}">{{ number_format($player->xp_total) }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="12" class="px-4 py-8 text-center text-gray-400">No player data available yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>

    {{-- Pagination --}}
    @if($players->hasPages())
    <div class="flex justify-center">
        {{ $players->appends(['sort' => $sort, 'period' => $period ?? 'all'])->links() }}
    </div>
    @endif
</div>
@endsection
