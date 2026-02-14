@extends('layouts.app')
@section('title', 'Creator Statistics')

@section('content')
<div class="py-12 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if(auth()->user()->isAdmin() && auth()->user()->id !== $creator->user_id)
                <a href="{{ route('admin.creators.dashboard', $creator) }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @else
                <a href="{{ route('creator.dashboard') }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-white">
                    Statistics
                    @if(auth()->user()->isAdmin() && auth()->user()->id !== $creator->user_id)
                        <span class="text-sm text-yellow-400 font-normal">(Admin View)</span>
                    @endif
                </h1>
                <p class="text-gray-400">Detailed analytics for {{ $creator->channel_name }}</p>
            </div>
        </div>
        @if(auth()->user()->isAdmin() && auth()->user()->id !== $creator->user_id)
        <div class="flex items-center gap-2">
            <a href="{{ route('admin.creators.show', $creator) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition">
                Admin View
            </a>
            <a href="{{ route('admin.creators.edit', $creator) }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Edit Profile
            </a>
        </div>
        @endif
    </div>

    {{-- Main Stats Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-400">Total Followers</span>
                <div class="p-2 bg-blue-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20"><path d="M9 6a3 3 0 11-6 0 3 3 0 016 0zM17 6a3 3 0 11-6 0 3 3 0 016 0zM12.93 17c.046-.327.07-.66.07-1a6.97 6.97 0 00-1.5-4.33A5 5 0 0119 16v1h-6.07zM6 11a5 5 0 015 5v1H1v-1a5 5 0 015-5z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ number_format($stats['total_followers']) }}</div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-400">Current Viewers</span>
                <div class="p-2 bg-red-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-red-400" fill="currentColor" viewBox="0 0 20 20"><path d="M10 12a2 2 0 100-4 2 2 0 000 4z"/><path fill-rule="evenodd" d="M.458 10C1.732 5.943 5.522 3 10 3s8.268 2.943 9.542 7c-1.274 4.057-5.064 7-9.542 7S1.732 14.057.458 10zM14 10a4 4 0 11-8 0 4 4 0 018 0z" clip-rule="evenodd"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ number_format($stats['current_viewers']) }}</div>
            @if($stats['is_live'])
                <p class="text-xs text-red-400 mt-1">🔴 LIVE</p>
            @else
                <p class="text-xs text-gray-400 mt-1">Offline</p>
            @endif
        </div>

        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-400">Total Views</span>
                <div class="p-2 bg-purple-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-purple-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M3 3a1 1 0 000 2v8a2 2 0 002 2h2.586l-1.293 1.293a1 1 0 101.414 1.414L10 15.414l2.293 2.293a1 1 0 001.414-1.414L12.414 15H15a2 2 0 002-2V5a1 1 0 100-2H3zm11 4a1 1 0 10-2 0v4a1 1 0 102 0V7zm-3 1a1 1 0 10-2 0v3a1 1 0 102 0V8zM8 9a1 1 0 00-2 0v2a1 1 0 102 0V9z" clip-rule="evenodd"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ number_format($stats['total_views']) }}</div>
        </div>

        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-3">
                <span class="text-sm text-gray-400">Total Streams</span>
                <div class="p-2 bg-green-500/20 rounded-lg">
                    <svg class="w-5 h-5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path d="M2 6a2 2 0 012-2h6a2 2 0 012 2v8a2 2 0 01-2 2H4a2 2 0 01-2-2V6zM14.553 7.106A1 1 0 0014 8v4a1 1 0 00.553.894l2 1A1 1 0 0018 13V7a1 1 0 00-1.447-.894l-2 1z"/></svg>
                </div>
            </div>
            <div class="text-3xl font-bold text-white">{{ number_format($stats['total_streams']) }}</div>
        </div>
    </div>

    {{-- Platform Stats --}}
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-xl font-bold text-white mb-6">Platform Information</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-400 mb-2">Platform</p>
                <span class="inline-flex px-3 py-1.5 {{ $creator->platform_color }} bg-white/5 rounded-lg font-medium text-lg">{{ $platformStats['platform_name'] }}</span>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-2">Verification Status</p>
                @if($platformStats['verified'])
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-blue-500/20 text-blue-400 rounded-lg font-medium">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        Verified
                    </span>
                @else
                    <span class="inline-flex px-3 py-1.5 bg-gray-500/20 text-gray-400 rounded-lg font-medium">Not Verified</span>
                @endif
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-2">Featured Status</p>
                @if($platformStats['featured'])
                    <span class="inline-flex items-center gap-2 px-3 py-1.5 bg-purple-500/20 text-purple-400 rounded-lg font-medium">
                        ⭐ Featured
                    </span>
                @else
                    <span class="inline-flex px-3 py-1.5 bg-gray-500/20 text-gray-400 rounded-lg font-medium">Not Featured</span>
                @endif
            </div>
        </div>
    </div>

    {{-- Live Stream Stats --}}
    @if($stats['is_live'])
    <div class="glass-card rounded-2xl p-6 border-2 border-red-500/30">
        <div class="flex items-center gap-3 mb-6">
            <div class="p-3 bg-red-500/20 rounded-xl">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M9.172 14.828a4 4 0 010-5.656m5.656 0a4 4 0 010 5.656"/></svg>
            </div>
            <div>
                <h2 class="text-xl font-bold text-white">Current Stream</h2>
                <p class="text-sm text-red-400 animate-pulse">🔴 LIVE NOW</p>
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-400 mb-1">Current Viewers</p>
                <p class="text-2xl font-bold text-white">{{ number_format($stats['current_viewers']) }}</p>
            </div>
            @if($stats['live_duration'])
            <div>
                <p class="text-sm text-gray-400 mb-1">Stream Duration</p>
                <p class="text-2xl font-bold text-white">{{ $stats['live_duration'] }}</p>
            </div>
            @endif
            <div>
                <p class="text-sm text-gray-400 mb-1">Platform</p>
                <p class="text-lg font-medium text-white">{{ ucfirst($creator->live_platform ?? $creator->platform) }}</p>
            </div>
        </div>

        @if($creator->live_title)
        <div class="mt-6 p-4 bg-white/5 rounded-lg">
            <p class="text-sm text-gray-400 mb-1">Stream Title</p>
            <p class="text-white font-medium">{{ $creator->live_title }}</p>
        </div>
        @endif
    </div>
    @endif

    {{-- Last Stream Info --}}
    @if($stats['last_live'])
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-xl font-bold text-white mb-4">Last Stream</h2>
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            <div>
                <p class="text-sm text-gray-400 mb-1">Date</p>
                <p class="text-white font-medium">{{ $stats['last_live']->format('F d, Y') }}</p>
                <p class="text-sm text-gray-400">{{ $stats['last_live']->diffForHumans() }}</p>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-1">Time</p>
                <p class="text-white font-medium">{{ $stats['last_live']->format('g:i A') }}</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Growth Placeholder --}}
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-xl font-bold text-white mb-4">Growth & Engagement</h2>
        <div class="text-center py-12">
            <div class="w-16 h-16 bg-blue-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                <svg class="w-8 h-8 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
            </div>
            <p class="text-gray-400">Detailed growth metrics coming soon!</p>
            <p class="text-sm text-gray-500 mt-2">Track your follower growth, viewer trends, and more</p>
        </div>
    </div>
</div>
@endsection
