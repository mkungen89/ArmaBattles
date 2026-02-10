@extends('admin.layout')

@section('title', 'Anti-Cheat Events - Admin')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.anticheat.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Anti-Cheat Events</h1>
        </div>
        <span class="text-gray-400">{{ $events->total() }} events</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.anticheat.events') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search player, reason, raw..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="event_type" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Types</option>
                <option value="ENFORCEMENT_ACTION" {{ request('event_type') === 'ENFORCEMENT_ACTION' ? 'selected' : '' }}>Enforcement Action</option>
                <option value="ENFORCEMENT_SKIPPED" {{ request('event_type') === 'ENFORCEMENT_SKIPPED' ? 'selected' : '' }}>Enforcement Skipped</option>
                <option value="LIFESTATE" {{ request('event_type') === 'LIFESTATE' ? 'selected' : '' }}>Lifestate</option>
                <option value="SPAWN_GRACE" {{ request('event_type') === 'SPAWN_GRACE' ? 'selected' : '' }}>Spawn Grace</option>
                <option value="OTHER" {{ request('event_type') === 'OTHER' ? 'selected' : '' }}>Other</option>
                <option value="UNKNOWN" {{ request('event_type') === 'UNKNOWN' ? 'selected' : '' }}>Unknown</option>
            </select>
            <select name="server_id" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">Filter</button>
            @if(request()->hasAny(['search', 'event_type', 'server_id', 'date_from', 'date_to']))
            <a href="{{ route('admin.anticheat.events') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Events Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Reason</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Raw</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($events as $event)
                @php
                    $eventTime = $event->event_time ? \Carbon\Carbon::parse($event->event_time) : null;
                    $typeColors = [
                        'ENFORCEMENT_ACTION' => 'bg-red-500/20 text-red-400',
                        'ENFORCEMENT_SKIPPED' => 'bg-yellow-500/20 text-yellow-400',
                        'LIFESTATE' => 'bg-blue-500/20 text-blue-400',
                        'SPAWN_GRACE' => 'bg-purple-500/20 text-purple-400',
                        'OTHER' => 'bg-gray-500/20 text-gray-400',
                        'UNKNOWN' => 'bg-gray-500/20 text-gray-400',
                    ];
                    $color = $typeColors[$event->event_type] ?? 'bg-gray-500/20 text-gray-400';
                @endphp
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3">
                        <div class="text-sm text-white">{{ $eventTime?->format('M j, Y') ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $eventTime?->format('H:i:s') ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 {{ $color }} text-xs rounded">{{ $event->event_type }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-white">{{ $event->player_name ?? 'Unknown' }}</span>
                        @if($event->player_id)
                        <div class="text-xs text-gray-500">PID: {{ $event->player_id }}</div>
                        @endif
                        @if($event->is_admin)
                        <span class="ml-1 px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded">Admin</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $event->reason ?? '-' }}</td>
                    <td class="px-4 py-3 text-xs text-gray-500 max-w-xs truncate" title="{{ $event->raw }}">{{ Str::limit($event->raw, 60) }}</td>
                    <td class="px-4 py-3 text-center text-gray-400">#{{ $event->server_id }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No anticheat events found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($events->hasPages())
    <div class="flex justify-center">
        {{ $events->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
