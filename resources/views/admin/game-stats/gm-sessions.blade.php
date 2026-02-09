@extends('admin.layout')

@section('title', 'GM Sessions - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ auth()->user()->isAdmin() ? route('admin.game-stats.index') : route('gm.sessions') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">GM Sessions</h1>
            <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs rounded">GM/Admin Only</span>
        </div>
        <span class="text-gray-400">{{ $sessions->total() }} sessions</span>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('gm.sessions') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search player..." class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <select name="event_type" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">All Events</option>
                <option value="GM_ENTER" {{ request('event_type') == 'GM_ENTER' ? 'selected' : '' }}>GM Enter</option>
                <option value="GM_EXIT" {{ request('event_type') == 'GM_EXIT' ? 'selected' : '' }}>GM Exit</option>
            </select>
            <select name="server_id" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-lg transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'event_type', 'server_id']))
            <a href="{{ route('gm.sessions') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- GM Sessions Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Event</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Duration</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($sessions as $session)
                @php
                    $occurredAt = $session->occurred_at ? \Carbon\Carbon::parse($session->occurred_at) : null;
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
                        <span class="text-purple-400 font-medium">{{ $session->player_name }}</span>
                        @if($session->player_uuid)
                        <div class="text-xs text-gray-500 truncate max-w-[150px]">{{ $session->player_uuid }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($session->event_type === 'GM_ENTER')
                        <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs rounded">GM ENTER</span>
                        @else
                        <span class="px-2 py-1 bg-red-500/20 text-red-400 text-xs rounded">GM EXIT</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @if($session->duration)
                        @php
                            $minutes = floor($session->duration / 60);
                            $seconds = $session->duration % 60;
                        @endphp
                        <span class="text-yellow-400">{{ $minutes }}m {{ $seconds }}s</span>
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
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No GM sessions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($sessions->hasPages())
    <div class="flex justify-center">
        {{ $sessions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
