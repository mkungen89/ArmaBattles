@extends('admin.layout')

@section('admin-title', 'Player Reputation')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Player Reputation</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Players</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Trusted</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($stats['trusted']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Flagged</p>
                    <p class="text-2xl font-bold text-red-400">{{ number_format($stats['flagged']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('admin.reputation.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by player name..." class="flex-1 min-w-[200px] px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:border-green-500">
            <select name="tier" class="px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Tiers</option>
                <option value="trusted" {{ request('tier') === 'trusted' ? 'selected' : '' }}>Trusted (100+)</option>
                <option value="good" {{ request('tier') === 'good' ? 'selected' : '' }}>Good (50-99)</option>
                <option value="neutral" {{ request('tier') === 'neutral' ? 'selected' : '' }}>Neutral (0-49)</option>
                <option value="poor" {{ request('tier') === 'poor' ? 'selected' : '' }}>Poor (-50 to -1)</option>
                <option value="flagged" {{ request('tier') === 'flagged' ? 'selected' : '' }}>Flagged (&lt;-50)</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm transition">Filter</button>
            @if(request()->hasAny(['search', 'tier']))
                <a href="{{ route('admin.reputation.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg text-sm transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Player</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Score</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Tier</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">+/-</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Categories</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($reputations as $rep)
                <tr class="hover:bg-gray-700/30 {{ $rep->isFlagged() ? 'bg-red-500/5' : '' }}">
                    <td class="px-4 py-3 text-sm font-medium text-white">{{ $rep->user->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3">
                        <span class="text-sm font-bold {{ $rep->badge_color }}">{{ $rep->total_score >= 0 ? '+' : '' }}{{ $rep->total_score }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $rep->badge_color }} {{ $rep->isFlagged() ? 'bg-red-500/20' : ($rep->isTrusted() ? 'bg-green-500/20' : 'bg-gray-500/20') }}">
                            {{ $rep->label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm">
                        <span class="text-green-400">+{{ $rep->positive_votes }}</span>
                        <span class="text-gray-500 mx-1">/</span>
                        <span class="text-red-400">-{{ $rep->negative_votes }}</span>
                    </td>
                    <td class="px-4 py-3 text-xs text-gray-400">
                        <span title="Teamwork">TW:{{ $rep->teamwork_count }}</span>
                        <span class="mx-1">|</span>
                        <span title="Leadership">LD:{{ $rep->leadership_count }}</span>
                        <span class="mx-1">|</span>
                        <span title="Sportsmanship">SP:{{ $rep->sportsmanship_count }}</span>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <form action="{{ route('admin.reputation.reset', $rep) }}" method="POST" class="inline" onsubmit="return confirm('Reset this player\'s reputation to zero? All votes will be deleted.')">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-xs bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded transition">Reset</button>
                            </form>
                            <form action="{{ route('admin.reputation.destroy', $rep) }}" method="POST" class="inline" onsubmit="return confirm('Delete this reputation record entirely?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No reputation records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $reputations->withQueryString()->links() }}</div>
</div>
@endsection
