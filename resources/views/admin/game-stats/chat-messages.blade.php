@extends('admin.layout')

@section('title', 'Chat Messages - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Chat Messages</h1>
        </div>
        <span class="text-gray-400">{{ $messages->total() }} messages</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.game-stats.chat') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search player or message..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="channel" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Channels</option>
                @foreach($channels as $channel)
                <option value="{{ $channel }}" {{ request('channel') == $channel ? 'selected' : '' }}>{{ ucfirst($channel) }}</option>
                @endforeach
            </select>
            <select name="server_id" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'channel', 'server_id']))
            <a href="{{ route('admin.game-stats.chat') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Chat Messages Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Channel</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($messages as $message)
                @php
                    $occurredAt = $message->occurred_at ? \Carbon\Carbon::parse($message->occurred_at) : null;
                    $channelColors = [
                        'global' => 'bg-blue-500/20 text-blue-400',
                        'team' => 'bg-green-500/20 text-green-400',
                        'squad' => 'bg-purple-500/20 text-purple-400',
                        'private' => 'bg-pink-500/20 text-pink-400',
                        'vehicle' => 'bg-orange-500/20 text-orange-400',
                    ];
                    $channelColor = $channelColors[$message->channel] ?? 'bg-gray-500/20 text-gray-400';
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
                        <span class="text-white font-medium">{{ $message->player_name }}</span>
                    </td>
                    <td class="px-4 py-3 text-center">
                        <span class="px-2 py-1 {{ $channelColor }} text-xs rounded capitalize">{{ $message->channel ?? 'unknown' }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <p class="text-gray-300 text-sm max-w-md truncate">{{ $message->message }}</p>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        Server #{{ $message->server_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No chat messages found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($messages->hasPages())
    <div class="flex justify-center">
        {{ $messages->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
