@extends('admin.layout')

@section('admin-title', 'Discord Rich Presence')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Discord Rich Presence</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-indigo-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-indigo-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Presences</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Enabled</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($stats['enabled']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Active Now</p>
                    <p class="text-2xl font-bold text-blue-400">{{ number_format($stats['active']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.discord.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by user or Discord ID..." class="flex-1 min-w-[200px] px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:border-green-500">
            <select name="activity" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Activities</option>
                <option value="playing" {{ request('activity') === 'playing' ? 'selected' : '' }}>Playing</option>
                <option value="watching_tournament" {{ request('activity') === 'watching_tournament' ? 'selected' : '' }}>Watching</option>
                <option value="browsing" {{ request('activity') === 'browsing' ? 'selected' : '' }}>Browsing</option>
            </select>
            <select name="enabled" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All</option>
                <option value="yes" {{ request('enabled') === 'yes' ? 'selected' : '' }}>Enabled</option>
                <option value="no" {{ request('enabled') === 'no' ? 'selected' : '' }}>Disabled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm transition">Filter</button>
            @if(request()->hasAny(['search', 'activity', 'enabled']))
                <a href="{{ route('admin.discord.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-sm transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Discord ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Activity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Details</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Enabled</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Last Updated</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($presences as $presence)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3 text-sm font-medium text-white">{{ $presence->user->name ?? 'Unknown' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400 font-mono">{{ $presence->discord_user_id ?? '-' }}</td>
                    <td class="px-4 py-3">
                        @if($presence->current_activity)
                            <span class="px-2 py-0.5 rounded-full text-xs {{ match($presence->current_activity) { 'playing' => 'bg-green-500/20 text-green-400', 'watching_tournament' => 'bg-blue-500/20 text-blue-400', 'browsing' => 'bg-gray-500/20 text-gray-400', default => 'bg-gray-500/20 text-gray-400' } }}">
                                {{ ucfirst(str_replace('_', ' ', $presence->current_activity)) }}
                            </span>
                        @else
                            <span class="text-xs text-gray-500">None</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400 truncate max-w-[200px]">{{ $presence->getActivityStatus() }}</td>
                    <td class="px-4 py-3">
                        @if($presence->enabled)
                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs">Yes</span>
                        @else
                            <span class="px-2 py-0.5 bg-gray-500/20 text-gray-400 rounded-full text-xs">No</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $presence->last_updated_at ? $presence->last_updated_at->diffForHumans() : '-' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($presence->enabled)
                                <form action="{{ route('admin.discord.disable', $presence) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded transition">Disable</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.discord.destroy', $presence) }}" method="POST" class="inline" onsubmit="return confirm('Delete this presence record?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No Discord presence records found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $presences->withQueryString()->links() }}</div>
</div>
@endsection
