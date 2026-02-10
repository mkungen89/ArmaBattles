@extends('admin.layout')

@section('title', 'Kill Events - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Kill Events</h1>
        </div>
        <span class="text-gray-400">{{ $kills->total() }} kills</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.game-stats.kills') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search killer, victim, weapon..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="victim_type" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Victims</option>
                <option value="AI" {{ request('victim_type') === 'AI' ? 'selected' : '' }}>AI Only</option>
                <option value="PLAYER" {{ request('victim_type') === 'PLAYER' ? 'selected' : '' }}>Players Only</option>
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
            @if(request()->hasAny(['search', 'victim_type', 'server_id', 'date_from', 'date_to']))
            <a href="{{ route('admin.game-stats.kills') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Kills Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Killer</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Weapon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Victim</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Distance</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Type</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($kills as $kill)
                @php
                    $occurredAt = $kill->occurred_at ? \Carbon\Carbon::parse($kill->occurred_at) : null;
                    $victimType = $kill->victim_type ?? 'PLAYER';
                @endphp
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3">
                        <div class="text-sm text-white">{{ $occurredAt?->format('M j, Y') ?? 'N/A' }}</div>
                        <div class="text-xs text-gray-400">{{ $occurredAt?->format('H:i:s') ?? '' }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            <span class="text-green-400 font-medium">{{ $kill->killer_name }}</span>
                            @if($kill->killer_faction)
                            <span class="px-1.5 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded">{{ $kill->killer_faction }}</span>
                            @endif
                        </div>
                        @if($kill->killer_uuid)
                        <div class="text-xs text-gray-500">{{ Str::limit($kill->killer_uuid, 20) }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <div class="flex flex-col items-center gap-1">
                            @if(isset($weaponImages[$kill->weapon_name]))
                            <img src="{{ Storage::url($weaponImages[$kill->weapon_name]) }}" alt="{{ $kill->weapon_name }}" class="h-8 w-auto object-contain" title="{{ $kill->weapon_name }}">
                            @endif
                            <div class="px-2 py-1 bg-white/5 text-gray-300 text-xs rounded inline-block">
                                {{ $kill->weapon_name }}
                            </div>
                            @if($kill->weapon_type)
                            <div class="text-xs text-gray-500">{{ $kill->weapon_type }}</div>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        @if($kill->victim_type === 'AI')
                        <span class="px-3 py-1.5 bg-yellow-500/20 text-yellow-400 text-base font-semibold rounded-md">AI</span>
                        @else
                        <div class="flex items-center gap-2">
                            <span class="text-red-400 font-medium">{{ $kill->victim_name ?? 'Unknown' }}</span>
                        </div>
                        @if($kill->victim_uuid)
                        <div class="text-xs text-gray-500">{{ Str::limit($kill->victim_uuid, 20) }}</div>
                        @endif
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center text-gray-400">
                        {{ $kill->kill_distance ? number_format($kill->kill_distance, 1) . 'm' : '-' }}
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($kill->is_team_kill)
                        <span class="px-2 py-1 bg-orange-500/20 text-orange-400 text-xs rounded">TK</span>
                        @else
                        <span class="px-2 py-1 {{ $kill->victim_type === 'AI' ? 'bg-yellow-500/20 text-yellow-400' : 'bg-red-500/20 text-red-400' }} text-xs rounded">
                            {{ $kill->event_type ?? $victimType }}
                        </span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No kill events found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    @if($kills->hasPages())
    <div class="flex justify-center">
        {{ $kills->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
