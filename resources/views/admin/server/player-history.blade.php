@extends('admin.layout')

@section('title', 'Player History')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.server.dashboard') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Player History</h1>
    </div>

    {{-- Search Form --}}
    <div class="glass-card rounded-xl p-6">
        <form method="GET" action="" class="flex items-end gap-4">
            <div class="flex-1">
                <label for="q" class="block text-sm text-gray-400 mb-1">Search by Name or UUID</label>
                <input type="text" name="q" id="q" value="{{ $query ?? '' }}" placeholder="Enter player name or UUID..."
                       class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
            </div>
            <button type="submit" class="px-6 py-2 bg-green-600/20 border border-green-500/30 hover:bg-green-600/30 text-green-400 rounded-lg text-sm font-medium transition">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                </svg>
                Search
            </button>
        </form>
    </div>

    {{-- Results --}}
    @if(!empty($query))
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-white/5">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider">
                Search Results
                <span class="ml-2 text-sm font-normal text-gray-500">({{ count($results) }} found for "{{ $query }}")</span>
            </h2>
        </div>

        @if(count($results) > 0)
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead class="bg-white/3">
                    <tr>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player Name</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">UUID</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Last Seen</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Connections</th>
                        <th class="px-4 py-2 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Alt Names</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($results as $result)
                    @php
                        $lastSeen = $result->last_seen ? \Carbon\Carbon::parse($result->last_seen) : null;
                    @endphp
                    <tr class="hover:bg-white/3 transition">
                        <td class="px-4 py-3">
                            <a href="{{ route('admin.server.player-detail', $result->player_uuid) }}" class="text-green-400 hover:text-green-300 font-medium transition">
                                {{ $result->primary_name ?? 'Unknown' }}
                            </a>
                        </td>
                        <td class="px-4 py-3">
                            <code class="text-xs text-gray-400 font-mono" title="{{ $result->player_uuid }}">
                                {{ Str::limit($result->player_uuid, 12) }}
                            </code>
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">
                            {{ $lastSeen ? $lastSeen->diffForHumans() : 'Never' }}
                        </td>
                        <td class="px-4 py-3 text-sm text-gray-400">
                            {{ number_format($result->total_connections ?? 0) }}
                        </td>
                        <td class="px-4 py-3">
                            @if(!empty($result->alt_names))
                                @foreach($result->alt_names as $altName)
                                <span class="inline-block px-2 py-0.5 bg-white/3 text-gray-300 text-xs rounded-full mr-1 mb-1">{{ $altName }}</span>
                                @endforeach
                            @else
                                <span class="text-xs text-gray-600">None</span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        @else
        <div class="text-center py-12">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
            </svg>
            <p class="text-gray-500">No players found matching "{{ $query }}"</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Ban by GUID --}}
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Ban by GUID</h2>
        <form method="POST" action="{{ route('admin.server.players.ban-guid') }}">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-4">
                <div>
                    <label for="guid" class="block text-sm text-gray-400 mb-1">GUID</label>
                    <input type="text" name="guid" id="guid" required placeholder="Enter player GUID..."
                           class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50 font-mono">
                </div>
                <div>
                    <label for="minutes" class="block text-sm text-gray-400 mb-1">Duration (minutes)</label>
                    <input type="number" name="minutes" id="minutes" value="0" min="0"
                           class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                    <p class="text-xs text-gray-600 mt-1">0 = permanent ban</p>
                </div>
                <div>
                    <label for="reason" class="block text-sm text-gray-400 mb-1">Reason</label>
                    <input type="text" name="reason" id="reason" value="Banned by admin" placeholder="Ban reason..."
                           class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg text-sm font-medium transition"
                    onclick="return confirm('Are you sure you want to ban this GUID?')">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                </svg>
                Ban GUID
            </button>
        </form>
    </div>
</div>
@endsection
