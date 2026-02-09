@extends('admin.layout')

@section('title', 'Server Status - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Server Status History</h1>
        </div>
        <span class="text-gray-400">{{ $statuses->total() }} records</span>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('admin.game-stats.server-status') }}" method="GET" class="flex flex-wrap gap-4">
            <select name="server_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>
                    Server #{{ $serverId }}
                </option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">
                Filter
            </button>
            @if(request()->hasAny(['server_id', 'date_from', 'date_to']))
            <a href="{{ route('admin.game-stats.server-status') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Status Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Players</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">AI Count</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">FPS</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Memory</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Uptime</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($statuses as $status)
                @php
                    $recordedAt = $status->recorded_at ? \Carbon\Carbon::parse($status->recorded_at) : null;
                    $uptimeHours = floor(($status->uptime_seconds ?? 0) / 3600);
                    $uptimeMinutes = floor((($status->uptime_seconds ?? 0) % 3600) / 60);
                @endphp
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-3">
                        @if($recordedAt)
                        <div class="text-sm text-white">{{ $recordedAt->format('M j, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $recordedAt->format('H:i:s') }}</div>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="text-white font-medium">Server #{{ $status->server_id }}</div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex items-center justify-center gap-2">
                            <span class="text-white font-medium">{{ $status->players_online ?? 0 }}</span>
                            <span class="text-gray-500">/</span>
                            <span class="text-gray-400">{{ $status->max_players ?? 64 }}</span>
                        </div>
                        @php
                            $maxPlayers = $status->max_players ?? 64;
                            $percentage = $maxPlayers > 0 ? (($status->players_online ?? 0) / $maxPlayers) * 100 : 0;
                        @endphp
                        <div class="w-full bg-gray-700 rounded-full h-1.5 mt-1">
                            <div class="h-1.5 rounded-full {{ $percentage > 80 ? 'bg-red-500' : ($percentage > 50 ? 'bg-yellow-500' : 'bg-green-500') }}" style="width: {{ $percentage }}%"></div>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-yellow-400">{{ $status->ai_count ?? 0 }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($status->fps)
                        <span class="{{ $status->fps >= 30 ? 'text-green-400' : ($status->fps >= 15 ? 'text-yellow-400' : 'text-red-400') }}">
                            {{ number_format($status->fps, 1) }}
                        </span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($status->memory_mb)
                        <span class="text-gray-300">{{ number_format($status->memory_mb) }} MB</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-300">
                        {{ $uptimeHours }}h {{ $uptimeMinutes }}m
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No server status records found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($statuses->hasPages())
    <div class="flex justify-center">
        {{ $statuses->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
