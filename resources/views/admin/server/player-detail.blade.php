@extends('admin.layout')

@section('title', 'Player Detail')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.server.player-history') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">{{ $player['primary_name'] ?? 'Unknown Player' }}</h1>
    </div>

    {{-- Player Info Card --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <div class="grid grid-cols-2 md:grid-cols-4 gap-6">
            <div>
                <p class="text-sm text-gray-400">UUID</p>
                <p class="text-white font-mono text-xs break-all">{{ $player['uuid'] ?? 'N/A' }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400">First Seen</p>
                @php
                    $firstSeen = !empty($player['first_seen']) ? \Carbon\Carbon::parse($player['first_seen']) : null;
                @endphp
                <p class="text-white font-medium" title="{{ $firstSeen ? $firstSeen->format('M j, Y H:i:s') : '' }}">
                    {{ $firstSeen ? $firstSeen->diffForHumans() : 'Unknown' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Last Seen</p>
                @php
                    $lastSeen = !empty($player['last_seen']) ? \Carbon\Carbon::parse($player['last_seen']) : null;
                @endphp
                <p class="text-white font-medium" title="{{ $lastSeen ? $lastSeen->format('M j, Y H:i:s') : '' }}">
                    {{ $lastSeen ? $lastSeen->diffForHumans() : 'Unknown' }}
                </p>
            </div>
            <div>
                <p class="text-sm text-gray-400">Total Connections</p>
                <p class="text-green-400 font-bold text-xl">{{ number_format($player['total_connections'] ?? 0) }}</p>
            </div>
        </div>
    </div>

    {{-- Alternative Names --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Alternative Names</h2>
        @if(!empty($player['alt_names']) && count($player['alt_names']) > 0)
        <div class="flex flex-wrap gap-2">
            @foreach($player['alt_names'] as $altName)
            <span class="inline-flex items-center px-3 py-1 bg-gray-700/50 border border-gray-600 text-gray-300 text-sm rounded-full">
                {{ $altName }}
            </span>
            @endforeach
        </div>
        @else
        <p class="text-sm text-gray-500">No alternative names recorded</p>
        @endif
    </div>

    {{-- Connection Log --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Connection Log</h2>
        </div>

        @if(!empty($player['connections']) && count($player['connections']) > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-gray-700/50">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Event Type</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Platform</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-gray-700">
                    @foreach($player['connections'] as $connection)
                    @php
                        $occurredAt = !empty($connection->occurred_at) ? \Carbon\Carbon::parse($connection->occurred_at) : null;
                    @endphp
                    <tr class="hover:bg-gray-700/30 transition">
                        <td class="px-4 py-3">
                            @if(strtoupper($connection->event_type) === 'CONNECT')
                            <span class="inline-flex items-center px-2 py-0.5 bg-green-500/20 text-green-400 text-xs font-medium rounded-full">
                                CONNECT
                            </span>
                            @elseif(strtoupper($connection->event_type) === 'DISCONNECT')
                            <span class="inline-flex items-center px-2 py-0.5 bg-red-500/20 text-red-400 text-xs font-medium rounded-full">
                                DISCONNECT
                            </span>
                            @else
                            <span class="text-gray-500 text-sm">{{ $connection->event_type ?? '-' }}</span>
                            @endif
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400" title="{{ $occurredAt ? $occurredAt->format('M j, Y H:i:s') : '' }}">
                            {{ $occurredAt ? $occurredAt->diffForHumans() : '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-white">
                            {{ $connection->player_name ?? '-' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">
                            {{ $connection->player_platform ?? '-' }}
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-gray-500">No connection history available</p>
        </div>
        @endif
    </div>

    {{-- Quick Action: Ban this GUID --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Ban this GUID</h2>
        <form method="POST" action="{{ route('admin.server.players.ban-guid') }}" class="flex items-end gap-4 flex-wrap">
            @csrf
            <input type="hidden" name="guid" value="{{ $player['uuid'] ?? '' }}">
            <div>
                <label class="block text-sm text-gray-400 mb-1">GUID</label>
                <code class="inline-block px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-gray-300 font-mono">{{ $player['uuid'] ?? 'N/A' }}</code>
            </div>
            <div>
                <label for="ban-minutes" class="block text-sm text-gray-400 mb-1">Duration (minutes)</label>
                <input type="number" name="minutes" id="ban-minutes" value="0" min="0"
                       class="w-32 px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                <p class="text-xs text-gray-600 mt-1">0 = permanent</p>
            </div>
            <div class="flex-1 min-w-[200px]">
                <label for="ban-reason" class="block text-sm text-gray-400 mb-1">Reason</label>
                <input type="text" name="reason" id="ban-reason" value="Banned by admin" placeholder="Ban reason..."
                       class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
            </div>
            <button type="submit" class="px-6 py-2 bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg text-sm font-medium transition"
                    onclick="return confirm('Are you sure you want to ban this GUID?')">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Ban GUID
            </button>
        </form>
    </div>
</div>
@endsection
