@extends('admin.layout')

@section('title', 'Raven Anti-Cheat - Admin')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Raven Anti-Cheat</h1>
        <div class="flex gap-2">
            <a href="{{ route('admin.anticheat.events') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm">View All Events</a>
            <a href="{{ route('admin.anticheat.stats-history') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-sm">Stats History</a>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 lg:grid-cols-5 gap-4">
        <div class="glass-card rounded-xl p-4">
            <div class="text-2xl font-bold text-white">{{ number_format($stats['total_events']) }}</div>
            <div class="text-sm text-gray-400">Total Events</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-2xl font-bold text-red-400">{{ number_format($stats['enforcement_actions']) }}</div>
            <div class="text-sm text-gray-400">Enforcement Actions</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-2xl font-bold text-yellow-400">{{ number_format($stats['enforcement_skipped']) }}</div>
            <div class="text-sm text-gray-400">Enforcement Skipped</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-2xl font-bold text-green-400">{{ number_format($stats['events_today']) }}</div>
            <div class="text-sm text-gray-400">Events Today</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-2xl font-bold text-blue-400">{{ number_format($stats['total_stats_snapshots']) }}</div>
            <div class="text-sm text-gray-400">Stats Snapshots</div>
        </div>
    </div>

    {{-- Live Server Status --}}
    @if($latestStats->count() > 0)
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Latest Server AC Status</h2>
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-4">
            @foreach($latestStats as $stat)
            <div class="bg-white/3 border border-white/10 rounded-lg p-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-white font-medium">Server #{{ $stat->server_id }}</span>
                    <span class="text-xs text-gray-400">{{ \Carbon\Carbon::parse($stat->event_time)->diffForHumans() }}</span>
                </div>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-3 text-sm">
                    <div>
                        <div class="text-gray-400">Online</div>
                        <div class="text-white font-medium">{{ $stat->online_players }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Active</div>
                        <div class="text-white font-medium">{{ $stat->active_players }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Registered</div>
                        <div class="text-white font-medium">{{ $stat->registered_players }}</div>
                    </div>
                    <div>
                        <div class="text-gray-400">Potential Cheaters</div>
                        <div class="text-{{ $stat->potential_cheaters > 0 ? 'red' : 'green' }}-400 font-medium">{{ $stat->potential_cheaters }}</div>
                    </div>
                </div>

                @php
                    $banned = json_decode($stat->banned_players, true) ?? [];
                    $potentials = json_decode($stat->potentials_list, true) ?? [];
                    $topMovement = json_decode($stat->top_movement, true) ?? [];
                @endphp

                @if(count($banned) > 0)
                <div class="mt-3">
                    <span class="text-xs text-gray-400">Banned:</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($banned as $name)
                        <span class="px-2 py-0.5 bg-red-500/20 text-red-400 text-xs rounded">{{ $name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($potentials) > 0)
                <div class="mt-3">
                    <span class="text-xs text-gray-400">Potential Cheaters:</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($potentials as $name)
                        <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs rounded">{{ $name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif

                @if(count($topMovement) > 0)
                <div class="mt-3">
                    <span class="text-xs text-gray-400">Top Movement:</span>
                    <div class="flex flex-wrap gap-1 mt-1">
                        @foreach($topMovement as $name)
                        <span class="px-2 py-0.5 bg-orange-500/20 text-orange-400 text-xs rounded">{{ $name }}</span>
                        @endforeach
                    </div>
                </div>
                @endif
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Recent Events --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/5 flex items-center justify-between">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Recent Events</h2>
            <a href="{{ route('admin.anticheat.events') }}" class="text-sm text-green-400 hover:text-green-300 transition">View All</a>
        </div>
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Reason</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($recentEvents as $event)
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
                        @if($event->is_admin)
                        <span class="ml-1 px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded">Admin</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $event->reason ?? '-' }}</td>
                    <td class="px-4 py-3 text-center text-gray-400">#{{ $event->server_id }}</td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No anticheat events recorded yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>
</div>
@endsection
