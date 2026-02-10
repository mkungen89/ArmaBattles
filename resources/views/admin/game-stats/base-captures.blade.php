@extends('admin.layout')

@section('title', 'Base Captures - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Base Captures</h1>
        </div>
        <span class="text-gray-400">{{ $captures->total() }} captures</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.game-stats.base-captures') }}" method="GET" class="flex flex-wrap gap-4">
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
            <a href="{{ route('admin.game-stats.base-captures') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Base Captures Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Base Name</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Capture Type</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Capturing Faction</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Lost By</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($captures as $capture)
                @php
                    $occurredAt = $capture->occurred_at ? \Carbon\Carbon::parse($capture->occurred_at) : null;
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
                        <span class="text-white font-medium">{{ $capture->base_name }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $typeColors = [
                                'BASE_SEIZED' => 'bg-green-500/20 text-green-400',
                                'BASE_CAPTURE' => 'bg-yellow-500/20 text-yellow-400',
                            ];
                            $colorClass = $typeColors[$capture->event_type] ?? 'bg-gray-500/20 text-gray-400';
                        @endphp
                        <span class="px-2 py-1 {{ $colorClass }} text-xs rounded">{{ $capture->event_type }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($capture->capturing_faction)
                        <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs rounded">{{ $capture->capturing_faction }}</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($capture->previous_faction)
                        <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded">{{ $capture->previous_faction }}</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        Server #{{ $capture->server_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No base captures found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($captures->hasPages())
    <div class="flex justify-center">
        {{ $captures->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
