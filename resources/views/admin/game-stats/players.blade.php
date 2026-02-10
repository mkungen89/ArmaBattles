@extends('admin.layout')

@section('title', 'Players - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Players</h1>
        </div>
        <span class="text-gray-400">{{ $players->total() }} players tracked</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.game-stats.players') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or UUID..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="sort" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="last_seen_at" {{ request('sort', 'last_seen_at') === 'last_seen_at' ? 'selected' : '' }}>Last Seen</option>
                <option value="player_name" {{ request('sort') === 'player_name' ? 'selected' : '' }}>Name</option>
                <option value="playtime_seconds" {{ request('sort') === 'playtime_seconds' ? 'selected' : '' }}>Playtime</option>
                <option value="kills" {{ request('sort') === 'kills' ? 'selected' : '' }}>Kills</option>
                <option value="deaths" {{ request('sort') === 'deaths' ? 'selected' : '' }}>Deaths</option>
                <option value="headshots" {{ request('sort') === 'headshots' ? 'selected' : '' }}>Headshots</option>
            </select>
            <select name="direction" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="desc" {{ request('direction', 'desc') === 'desc' ? 'selected' : '' }}>Descending</option>
                <option value="asc" {{ request('direction') === 'asc' ? 'selected' : '' }}>Ascending</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'sort', 'direction']))
            <a href="{{ route('admin.game-stats.players') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Players Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Playtime</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Kills</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Deaths</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">K/D</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Headshots</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Last Seen</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($players as $player)
                @php
                    $playtimeSeconds = $player->playtime_seconds ?? 0;
                    $hours = floor($playtimeSeconds / 3600);
                    $minutes = floor(($playtimeSeconds % 3600) / 60);
                    $formattedPlaytime = $hours > 0 ? "{$hours}h {$minutes}m" : "{$minutes}m";
                    $kdRatio = $player->deaths > 0 ? round($player->kills / $player->deaths, 2) : $player->kills;
                    $lastSeen = $player->last_seen_at ? \Carbon\Carbon::parse($player->last_seen_at) : null;
                @endphp
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3">
                        <a href="{{ route('admin.game-stats.player', $player->player_uuid) }}" class="text-white hover:text-green-400 font-medium transition">
                            {{ $player->player_name ?? 'Unknown' }}
                        </a>
                        @if($player->player_uuid)
                        <div class="text-xs text-gray-500">{{ Str::limit($player->player_uuid, 20) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-right text-gray-300">
                        {{ $formattedPlaytime }}
                    </td>
                    <td class="px-4 py-3 text-right text-green-400">{{ number_format($player->kills) }}</td>
                    <td class="px-4 py-3 text-right text-red-400">{{ number_format($player->deaths) }}</td>
                    <td class="px-4 py-3 text-right">
                        <span class="{{ $kdRatio >= 1 ? 'text-green-400' : 'text-red-400' }} font-medium">
                            {{ number_format($kdRatio, 2) }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-right text-yellow-400">{{ number_format($player->headshots) }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        {{ $lastSeen ? $lastSeen->diffForHumans() : 'Never' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No players found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($players->hasPages())
    <div class="flex justify-center">
        {{ $players->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
