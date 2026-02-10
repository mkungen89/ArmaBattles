@extends('admin.layout')

@section('title', 'Platoons')

@section('admin-content')
<h1 class="text-2xl font-bold text-white mb-6">Platoons</h1>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-white">{{ $stats['total'] }}</div>
        <div class="text-sm text-gray-400">Total</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-green-400">{{ $stats['active'] }}</div>
        <div class="text-sm text-gray-400">Active</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-blue-400">{{ $stats['verified'] }}</div>
        <div class="text-sm text-gray-400">Verified</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-red-400">{{ $stats['disbanded'] }}</div>
        <div class="text-sm text-gray-400">Disbanded</div>
    </div>
</div>

<!-- Filters -->
<div class="glass-card rounded-xl p-4 mb-6">
    <form method="GET" class="flex flex-wrap gap-4">
        <div class="flex-1 min-w-[200px]">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search teams..."
                class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
        </div>
        <select name="status" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            <option value="">All statuses</option>
            <option value="active" {{ request('status') === 'active' ? 'selected' : '' }}>Active</option>
            <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
            <option value="disbanded" {{ request('status') === 'disbanded' ? 'selected' : '' }}>Disbanded</option>
        </select>
        <button type="submit" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
            Search
        </button>
        @if(request()->hasAny(['search', 'status']))
            <a href="{{ route('admin.teams.index') }}" class="px-4 py-2 text-gray-400 hover:text-white transition">
                Clear
            </a>
        @endif
    </form>
</div>

<!-- Teams List -->
@if($teams->count() > 0)
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Platoon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Captain</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Members</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Tournaments</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @foreach($teams as $team)
                    <tr class="hover:bg-white/3">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($team->avatar_url)
                                    <img src="{{ $team->avatar_url }}" class="w-8 h-8 rounded object-cover">
                                @else
                                    <div class="w-8 h-8 rounded bg-white/5 flex items-center justify-center text-xs font-bold text-gray-400">
                                        {{ strtoupper(substr($team->tag, 0, 2)) }}
                                    </div>
                                @endif
                                <div>
                                    <div class="text-white font-medium">{{ $team->name }}</div>
                                    <div class="text-xs text-gray-500">[{{ $team->tag }}]</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $team->captain->name }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $team->active_members_count }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $team->registrations_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                <span class="px-2 py-1 text-xs rounded-full {{ $team->status_badge }}">
                                    {{ $team->status_text }}
                                </span>
                                @if($team->is_verified)
                                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                        <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                    </svg>
                                @endif
                            </div>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <div class="flex justify-end gap-2">
                                <a href="{{ route('teams.show', $team) }}" class="text-gray-400 hover:text-white" target="_blank">
                                    View
                                </a>
                                @if($team->is_active)
                                    @if($team->is_verified)
                                        <form action="{{ route('admin.teams.unverify', $team) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-yellow-400 hover:text-yellow-300">Unverify</button>
                                        </form>
                                    @else
                                        <form action="{{ route('admin.teams.verify', $team) }}" method="POST" class="inline">
                                            @csrf
                                            <button type="submit" class="text-green-400 hover:text-green-300">Verify</button>
                                        </form>
                                    @endif
                                    <form action="{{ route('admin.teams.disband', $team) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure?')">
                                        @csrf
                                        <button type="submit" class="text-red-400 hover:text-red-300">Disband</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.teams.restore', $team) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="text-green-400 hover:text-green-300">Restore</button>
                                    </form>
                                @endif
                                <form action="{{ route('admin.teams.destroy', $team) }}" method="POST" class="inline" onsubmit="return confirm('PERMANENTLY delete {{ $team->name }}? This cannot be undone!')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="text-red-400 hover:text-red-300">Delete</button>
                                </form>
                            </div>
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>

    <div class="mt-6">
        {{ $teams->withQueryString()->links() }}
    </div>
@else
    <div class="glass-card rounded-xl p-12 text-center">
        <p class="text-gray-400">No platoons found.</p>
    </div>
@endif
@endsection
