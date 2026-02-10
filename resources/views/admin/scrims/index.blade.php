@extends('admin.layout')

@section('admin-title', 'Scrims')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Scrims</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Scrims</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ number_format($stats['pending']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Scheduled</p>
                    <p class="text-2xl font-bold text-blue-400">{{ number_format($stats['scheduled']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Completed</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($stats['completed']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.scrims.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by team name..." class="flex-1 min-w-[200px] px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:border-green-500">
            <select name="status" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="scheduled" {{ request('status') === 'scheduled' ? 'selected' : '' }}>Scheduled</option>
                <option value="in_progress" {{ request('status') === 'in_progress' ? 'selected' : '' }}>In Progress</option>
                <option value="completed" {{ request('status') === 'completed' ? 'selected' : '' }}>Completed</option>
                <option value="cancelled" {{ request('status') === 'cancelled' ? 'selected' : '' }}>Cancelled</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm transition">Filter</button>
            @if(request()->hasAny(['search', 'status']))
                <a href="{{ route('admin.scrims.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-sm transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Teams</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Map</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Scheduled At</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Score</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Created By</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($scrims as $scrim)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3">
                        <div class="text-sm">
                            <span class="text-white font-medium">{{ $scrim->team1->name ?? 'TBD' }}</span>
                            <span class="text-gray-500 mx-1">vs</span>
                            <span class="text-white font-medium">{{ $scrim->team2->name ?? 'TBD' }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">{{ $scrim->map ?? '-' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $scrim->scheduled_at ? $scrim->scheduled_at->format('M d, Y H:i') : '-' }}</td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-0.5 rounded-full text-xs {{ $scrim->status_color }} {{ match($scrim->status) { 'pending' => 'bg-yellow-500/20', 'scheduled' => 'bg-blue-500/20', 'in_progress' => 'bg-green-500/20', 'completed' => 'bg-gray-500/20', 'cancelled' => 'bg-red-500/20', default => 'bg-gray-500/20' } }}">
                            {{ $scrim->status_label }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">
                        @if($scrim->team1_score !== null && $scrim->team2_score !== null)
                            {{ $scrim->team1_score }} - {{ $scrim->team2_score }}
                        @else
                            -
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $scrim->creator->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if(!$scrim->isCompleted() && !$scrim->isCancelled())
                                <form action="{{ route('admin.scrims.cancel', $scrim) }}" method="POST" class="inline" onsubmit="return confirm('Cancel this scrim?')">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded transition">Cancel</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.scrims.destroy', $scrim) }}" method="POST" class="inline" onsubmit="return confirm('Delete this scrim?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No scrims found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $scrims->withQueryString()->links() }}</div>
</div>
@endsection
