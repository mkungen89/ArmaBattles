@extends('admin.layout')

@section('title', 'Game Sessions - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Game Sessions</h1>
        </div>
        <span class="text-gray-400">{{ $gameSessions->total() }} sessions</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.game-stats.game-sessions') }}" method="GET" class="flex flex-wrap gap-4">
            <select name="server_id" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['server_id']))
            <a href="{{ route('admin.game-stats.game-sessions') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Game Sessions Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Started</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Game Mode</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Map</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Duration</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Peak Players</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Winner</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($gameSessions as $session)
                @php
                    $startedAt = $session->started_at ? \Carbon\Carbon::parse($session->started_at) : null;
                    $endedAt = $session->ended_at ? \Carbon\Carbon::parse($session->ended_at) : null;
                    $durationSeconds = $session->duration_seconds ?? 0;
                    $hours = floor($durationSeconds / 3600);
                    $minutes = floor(($durationSeconds % 3600) / 60);
                @endphp
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3">
                        @if($startedAt)
                        <div class="text-sm text-white">{{ $startedAt->format('M j, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $startedAt->format('H:i:s') }}</div>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-white font-medium">{{ $session->game_mode }}</span>
                        @if($session->scenario)
                        <div class="text-xs text-gray-500">{{ $session->scenario }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-gray-300">
                        {{ $session->map_name ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($durationSeconds > 0)
                        <span class="text-gray-300">{{ $hours }}h {{ $minutes }}m</span>
                        @elseif(!$endedAt)
                        <span class="inline-flex items-center gap-1 px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded-full">
                            <span class="w-1.5 h-1.5 bg-green-400 rounded-full animate-pulse"></span>
                            In Progress
                        </span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-blue-400 font-medium">{{ $session->peak_players ?? 0 }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($session->winner_faction)
                        <span class="px-2 py-1 bg-yellow-500/20 text-yellow-400 text-xs rounded">{{ $session->winner_faction }}</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        Server #{{ $session->server_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No game sessions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($gameSessions->hasPages())
    <div class="flex justify-center">
        {{ $gameSessions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
