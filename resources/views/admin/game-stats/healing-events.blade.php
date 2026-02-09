@extends('admin.layout')

@section('title', 'Healing Events - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Healing Events</h1>
        </div>
        <span class="text-gray-400">{{ $healingEvents->total() }} events</span>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('admin.game-stats.healing') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search healer or patient..." class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="server_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'server_id']))
            <a href="{{ route('admin.game-stats.healing') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Healing Events Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Healer</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Patient</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Item</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($healingEvents as $event)
                @php
                    $occurredAt = $event->occurred_at ? \Carbon\Carbon::parse($event->occurred_at) : null;
                @endphp
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-3">
                        @if($occurredAt)
                        <div class="text-sm text-white">{{ $occurredAt->format('M j, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $occurredAt->format('H:i:s') }}</div>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-blue-400 font-medium">{{ $event->healer_name }}</span>
                        @if($event->is_self)
                        <span class="ml-1 px-1.5 py-0.5 bg-purple-500/20 text-purple-400 text-xs rounded">Self</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-green-400">{{ $event->patient_name ?? 'Self' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 bg-gray-700 text-gray-300 text-xs rounded">{{ $event->item ?? '-' }}</span>
                    </td>
                    <td class="px-4 py-3 text-center text-green-400">
                        {{ $event->action ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        Server #{{ $event->server_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No healing events found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($healingEvents->hasPages())
    <div class="flex justify-center">
        {{ $healingEvents->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
