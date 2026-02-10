@extends('admin.layout')

@section('admin-title', 'Content Creators')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Content Creators</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Creators</p>
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
                    <p class="text-sm text-gray-400">Verified</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($stats['verified']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Unverified</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ number_format($stats['unverified']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-red-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M9.172 14.828a4 4 0 010-5.656m5.656 0a4 4 0 010 5.656"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Currently Live</p>
                    <p class="text-2xl font-bold text-red-400">{{ number_format($stats['live']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.creators.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by channel or user name..." class="flex-1 min-w-[200px] px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:border-green-500">
            <select name="platform" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Platforms</option>
                <option value="twitch" {{ request('platform') === 'twitch' ? 'selected' : '' }}>Twitch</option>
                <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
                <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                <option value="kick" {{ request('platform') === 'kick' ? 'selected' : '' }}>Kick</option>
            </select>
            <select name="status" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Status</option>
                <option value="verified" {{ request('status') === 'verified' ? 'selected' : '' }}>Verified</option>
                <option value="unverified" {{ request('status') === 'unverified' ? 'selected' : '' }}>Unverified</option>
                <option value="live" {{ request('status') === 'live' ? 'selected' : '' }}>Live Now</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm transition">Filter</button>
            @if(request()->hasAny(['search', 'platform', 'status']))
                <a href="{{ route('admin.creators.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-sm transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Creator</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Platform</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Followers</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($creators as $creator)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3">
                        <div class="text-sm font-medium text-white">{{ $creator->channel_name }}</div>
                        <div class="text-xs text-gray-400 truncate max-w-[200px]">{{ $creator->channel_url }}</div>
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $creator->platform_color }} bg-white/5">
                            {{ $creator->platform_name }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">{{ $creator->user->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3 text-sm text-gray-300">{{ number_format($creator->follower_count) }}</td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-2">
                            @if($creator->is_verified)
                                <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs">Verified</span>
                            @else
                                <span class="px-2 py-0.5 bg-gray-500/20 text-gray-400 rounded-full text-xs">Unverified</span>
                            @endif
                            @if($creator->is_live)
                                <span class="px-2 py-0.5 bg-red-500/20 text-red-400 rounded-full text-xs">LIVE</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            @if($creator->is_verified)
                                <form action="{{ route('admin.creators.unverify', $creator) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded transition">Unverify</button>
                                </form>
                            @else
                                <form action="{{ route('admin.creators.verify', $creator) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-600/20 text-green-400 hover:bg-green-600/40 rounded transition">Verify</button>
                                </form>
                            @endif
                            <form action="{{ route('admin.creators.destroy', $creator) }}" method="POST" class="inline" onsubmit="return confirm('Delete this creator?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No content creators found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $creators->withQueryString()->links() }}</div>
</div>
@endsection
