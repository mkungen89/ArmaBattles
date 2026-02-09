@extends('layouts.app')

@section('title', 'Player Search')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-white">Player Search</h1>
    </div>

    {{-- Search Form --}}
    <form action="{{ route('players.search') }}" method="GET" class="flex gap-3">
        <div class="flex-1 relative">
            <input type="text" name="q" value="{{ $query }}" placeholder="Search by player name or UUID..."
                   class="w-full px-4 py-3 bg-gray-800/50 border border-gray-700 rounded-xl text-white placeholder-gray-500 focus:outline-none focus:border-green-500 focus:ring-1 focus:ring-green-500">
            <svg class="absolute right-4 top-1/2 -translate-y-1/2 w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
        </div>
        <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl font-medium transition">
            Search
        </button>
    </form>

    {{-- Results --}}
    @if(strlen($query) >= 2)
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-gray-700">
            <p class="text-sm text-gray-400">{{ $players->count() }} result{{ $players->count() !== 1 ? 's' : '' }} for "<span class="text-white">{{ $query }}</span>"</p>
        </div>
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Alt Names</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Connections</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Last Seen</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Profile</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                @forelse($players as $player)
                @php $user = $linkedUsers[$player->player_uuid] ?? null; @endphp
                <tr class="{{ $loop->odd ? 'bg-gray-800/30' : 'bg-gray-800/10' }} hover:bg-gray-700/30 transition-colors">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if($user)
                                <img src="{{ $user->avatar_display }}" alt="{{ $player->player_name }}" class="w-8 h-8 rounded-full">
                            @else
                                <div class="w-8 h-8 rounded-full bg-gray-700 flex items-center justify-center">
                                    <svg class="w-4 h-4 text-gray-500" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/>
                                    </svg>
                                </div>
                            @endif
                            <span class="text-white font-medium">{{ $player->player_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400 max-w-xs truncate">
                        {{ $player->alt_names ?? '-' }}
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-400">
                        {{ $player->connection_count }}
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-gray-400">
                        {{ $player->last_seen ? \Carbon\Carbon::parse($player->last_seen)->diffForHumans() : '-' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        @if($user)
                        <a href="{{ route('players.show', $user->id) }}" class="text-green-400 hover:text-green-300 text-sm font-medium">
                            View Profile
                        </a>
                        @else
                        <span class="text-gray-600 text-sm">Not linked</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-400">No players found matching "{{ $query }}".</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
    @elseif(strlen($query) > 0)
    <div class="text-center text-gray-400 py-8">Please enter at least 2 characters to search.</div>
    @endif
</div>
@endsection
