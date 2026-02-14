@extends('admin.layout')

@section('admin-title', 'Content Creator Details')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.creators.index') }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">{{ $creator->channel_name }}</h1>
                <p class="text-sm text-gray-400">Creator Details</p>
            </div>
        </div>
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.creators.dashboard', $creator) }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                View Dashboard
            </a>
            <a href="{{ route('admin.creators.stats-view', $creator) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                View Stats
            </a>
            <a href="{{ route('admin.creators.edit', $creator) }}" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition">Edit</a>
            <form action="{{ route('admin.creators.destroy', $creator) }}" method="POST" class="inline" onsubmit="return confirm('Delete this creator?')">
                @csrf
                @method('DELETE')
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition">Delete</button>
            </form>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Approval Status</p>
            @if($creator->is_approved)
                <span class="inline-flex px-2 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Approved</span>
            @else
                <span class="inline-flex px-2 py-1 bg-orange-500/20 text-orange-400 rounded-full text-xs font-medium">Pending</span>
            @endif
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Verification</p>
            @if($creator->is_verified)
                <span class="inline-flex px-2 py-1 bg-blue-500/20 text-blue-400 rounded-full text-xs font-medium">✓ Verified</span>
            @else
                <span class="inline-flex px-2 py-1 bg-gray-500/20 text-gray-400 rounded-full text-xs font-medium">Unverified</span>
            @endif
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Live Status</p>
            @if($creator->is_live)
                <span class="inline-flex px-2 py-1 bg-red-500/20 text-red-400 rounded-full text-xs font-medium animate-pulse">🔴 LIVE</span>
            @else
                <span class="inline-flex px-2 py-1 bg-gray-500/20 text-gray-400 rounded-full text-xs font-medium">Offline</span>
            @endif
        </div>
        <div class="glass-card rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-1">Featured</p>
            @if($creator->is_featured)
                <span class="inline-flex px-2 py-1 bg-purple-500/20 text-purple-400 rounded-full text-xs font-medium">⭐ Featured</span>
            @else
                <span class="inline-flex px-2 py-1 bg-gray-500/20 text-gray-400 rounded-full text-xs font-medium">Not Featured</span>
            @endif
        </div>
    </div>

    {{-- Main Info --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Creator Info --}}
        <div class="glass-card rounded-xl p-6 space-y-4">
            <h2 class="text-lg font-bold text-white mb-4">Creator Information</h2>

            <div>
                <p class="text-sm text-gray-400">User</p>
                <p class="text-white">{{ $creator->user->name }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Platform</p>
                <span class="inline-flex px-2 py-1 {{ $creator->platform_color }} bg-white/5 rounded text-sm font-medium">{{ $creator->platform_name }}</span>
            </div>

            <div>
                <p class="text-sm text-gray-400">Channel URL</p>
                <a href="{{ $creator->channel_url }}" target="_blank" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $creator->channel_url }}</a>
            </div>

            <div>
                <p class="text-sm text-gray-400">Follower Count</p>
                <p class="text-white">{{ number_format($creator->follower_count ?? 0) }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Bio</p>
                <p class="text-white">{{ $creator->bio ?? 'No bio provided' }}</p>
            </div>

            <div>
                <p class="text-sm text-gray-400">Registered</p>
                <p class="text-white">{{ $creator->created_at->format('M d, Y') }} ({{ $creator->created_at->diffForHumans() }})</p>
            </div>

            @if($creator->verified_at)
            <div>
                <p class="text-sm text-gray-400">Verified At</p>
                <p class="text-white">{{ $creator->verified_at->format('M d, Y') }} ({{ $creator->verified_at->diffForHumans() }})</p>
            </div>
            @endif
        </div>

        {{-- Live Info --}}
        <div class="glass-card rounded-xl p-6 space-y-4">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-bold text-white">Live Stream Status</h2>
                <form action="{{ route('admin.creators.check-live', $creator) }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white rounded-lg text-xs transition inline-flex items-center gap-2">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                        Check Now
                    </button>
                </form>
            </div>

            @if($creator->is_live)
                <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-lg">
                    <p class="text-red-400 font-medium mb-2">🔴 Currently Streaming</p>

                    @if($creator->live_platform)
                    <div class="mb-2">
                        <p class="text-xs text-gray-400">Platform</p>
                        <p class="text-white text-sm">{{ ucfirst($creator->live_platform) }}</p>
                    </div>
                    @endif

                    @if($creator->live_title)
                    <div class="mb-2">
                        <p class="text-xs text-gray-400">Stream Title</p>
                        <p class="text-white text-sm">{{ $creator->live_title }}</p>
                    </div>
                    @endif

                    @if($creator->live_viewers !== null)
                    <div class="mb-2">
                        <p class="text-xs text-gray-400">Viewers</p>
                        <p class="text-white text-sm">{{ number_format($creator->live_viewers) }}</p>
                    </div>
                    @endif

                    @if($creator->live_started_at)
                    <div>
                        <p class="text-xs text-gray-400">Started</p>
                        <p class="text-white text-sm">{{ $creator->live_started_at->diffForHumans() }}</p>
                    </div>
                    @endif
                </div>
            @else
                <p class="text-gray-400">Not currently streaming</p>
            @endif

            @if($creator->last_live_at)
            <div>
                <p class="text-sm text-gray-400">Last Live</p>
                <p class="text-white">{{ $creator->last_live_at->diffForHumans() }}</p>
            </div>
            @endif

            @if($creator->live_checked_at)
            <div>
                <p class="text-sm text-gray-400">Last Checked</p>
                <p class="text-white">{{ $creator->live_checked_at->diffForHumans() }}</p>
            </div>
            @endif
        </div>
    </div>

    {{-- Quick Actions --}}
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-lg font-bold text-white mb-4">Quick Actions</h2>
        <div class="flex flex-wrap gap-2">
            @if(!$creator->is_approved)
                <form action="{{ route('admin.creators.approve', $creator) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">Approve Creator</button>
                </form>
                <form action="{{ route('admin.creators.reject', $creator) }}" method="POST" onsubmit="return confirm('Reject and delete this creator?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition">Reject & Delete</button>
                </form>
            @else
                @if($creator->is_verified)
                    <form action="{{ route('admin.creators.unverify', $creator) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-xl transition">Remove Verification</button>
                    </form>
                @else
                    <form action="{{ route('admin.creators.verify', $creator) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition">Verify Creator</button>
                    </form>
                @endif

                <form action="{{ route('admin.creators.toggle-featured', $creator) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl transition">
                        {{ $creator->is_featured ? 'Remove from Featured' : 'Add to Featured' }}
                    </button>
                </form>
            @endif

            <a href="{{ route('content-creators.show', $creator) }}" target="_blank" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-xl transition">View Public Profile</a>
        </div>
    </div>
</div>
@endsection
