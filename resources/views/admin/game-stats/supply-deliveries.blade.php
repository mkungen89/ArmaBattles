@extends('admin.layout')

@section('title', 'Supply Deliveries - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Supply Deliveries</h1>
        </div>
        <span class="text-gray-400">{{ $deliveries->total() }} deliveries</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.game-stats.supply-deliveries') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search player..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="server_id" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'server_id']))
            <a href="{{ route('admin.game-stats.supply-deliveries') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Supply Deliveries Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Faction</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Amount</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">XP Awarded</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($deliveries as $delivery)
                @php
                    $occurredAt = $delivery->occurred_at ? \Carbon\Carbon::parse($delivery->occurred_at) : null;
                @endphp
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3">
                        @if($occurredAt)
                        <div class="text-sm text-white">{{ $occurredAt->format('M j, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $occurredAt->format('H:i:s') }}</div>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-white font-medium">{{ $delivery->player_name }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($delivery->player_faction)
                        <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded">{{ $delivery->player_faction }}</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="text-yellow-400 font-medium">{{ $delivery->estimated_amount ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($delivery->xp_awarded)
                        <span class="text-green-400">+{{ $delivery->xp_awarded }} XP</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        Server #{{ $delivery->server_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No supply deliveries found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($deliveries->hasPages())
    <div class="flex justify-center">
        {{ $deliveries->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
