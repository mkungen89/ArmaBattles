@extends('admin.layout')

@section('title', 'Anti-Cheat Stats History - Admin')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.anticheat.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Anti-Cheat Stats History</h1>
        </div>
        <span class="text-gray-400">{{ $statsHistory->total() }} snapshots</span>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('admin.anticheat.stats-history') }}" method="GET" class="flex flex-wrap gap-4">
            <select name="server_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">Filter</button>
            @if(request()->hasAny(['server_id', 'date_from', 'date_to']))
            <a href="{{ route('admin.anticheat.stats-history') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Stats Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Server</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Online</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Active</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Registered</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Potential Cheaters</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Banned</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Potentials List</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($statsHistory as $stat)
                @php
                    $eventTime = $stat->event_time ? \Carbon\Carbon::parse($stat->event_time) : null;
                    $banned = json_decode($stat->banned_players, true) ?? [];
                    $potentials = json_decode($stat->potentials_list, true) ?? [];
                @endphp
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-3">
                        <div class="text-sm text-white">{{ $eventTime?->format('M j, Y') ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $eventTime?->format('H:i:s') ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400">#{{ $stat->server_id }}</td>
                    <td class="px-4 py-3 text-center text-white">{{ $stat->online_players }}</td>
                    <td class="px-4 py-3 text-center text-white">{{ $stat->active_players }}</td>
                    <td class="px-4 py-3 text-center text-white">{{ $stat->registered_players }}</td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-{{ $stat->potential_cheaters > 0 ? 'red' : 'green' }}-400 font-medium">{{ $stat->potential_cheaters }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if(count($banned) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($banned as $name)
                            <span class="px-1.5 py-0.5 bg-red-500/20 text-red-400 text-xs rounded">{{ $name }}</span>
                            @endforeach
                        </div>
                        @else
                        <span class="text-gray-500 text-sm">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @if(count($potentials) > 0)
                        <div class="flex flex-wrap gap-1">
                            @foreach($potentials as $name)
                            <span class="px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded">{{ $name }}</span>
                            @endforeach
                        </div>
                        @else
                        <span class="text-gray-500 text-sm">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="8" class="px-4 py-8 text-center text-gray-500">No anticheat stats recorded yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($statsHistory->hasPages())
    <div class="flex justify-center">
        {{ $statsHistory->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
