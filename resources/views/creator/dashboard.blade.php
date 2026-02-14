@extends('layouts.app')
@section('title', 'Creator Dashboard')

@section('content')
<div class="py-12 space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            @if(auth()->user()->isAdmin() && auth()->user()->id !== $creator->user_id)
                <a href="{{ route('admin.creators.show', $creator) }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">
                    Creator Dashboard
                    @if(auth()->user()->isAdmin() && auth()->user()->id !== $creator->user_id)
                        <span class="text-sm text-yellow-400 font-normal">(Admin View)</span>
                    @endif
                </h1>
                <p class="text-gray-400">
                    @if(auth()->user()->id === $creator->user_id)
                        Welcome back, {{ $creator->channel_name }}
                    @else
                        Viewing {{ $creator->channel_name }}'s dashboard
                    @endif
                </p>
            </div>
        </div>
        <div class="flex items-center gap-3">
            @if(auth()->user()->isAdmin())
            <form action="{{ route('admin.creators.check-live', $creator) }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Check Live Status
                </button>
            </form>
            @else
            <form action="{{ route('creator.check-live') }}" method="POST">
                @csrf
                <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition inline-flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                    Check Live Status
                </button>
            </form>
            @endif

            @if(auth()->user()->id === $creator->user_id)
                <a href="{{ route('creator.edit') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">Edit Profile</a>
            @elseif(auth()->user()->isAdmin())
                <a href="{{ route('admin.creators.edit', $creator) }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">Edit Profile</a>
                <a href="{{ route('admin.creators.stats-view', $creator) }}" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition">View Stats</a>
            @endif
        </div>
    </div>

    {{-- Status Banner --}}
    @if(!$stats['is_approved'])
    <div class="bg-gradient-to-r from-orange-600/20 to-orange-500/20 border-2 border-orange-500/50 rounded-2xl p-6">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-orange-500/20 rounded-lg">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
            </div>
            <div>
                <h3 class="text-lg font-bold text-orange-400 mb-1">Pending Approval</h3>
                <p class="text-gray-300">Your creator registration is pending admin approval. You'll be notified once approved!</p>
            </div>
        </div>
    </div>
    @elseif($stats['is_live'])
    <div class="bg-gradient-to-r from-red-600/20 to-red-500/20 border-2 border-red-500/50 rounded-2xl p-6 animate-pulse">
        <div class="flex items-start gap-4">
            <div class="p-3 bg-red-500/20 rounded-lg">
                <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.636 18.364a9 9 0 010-12.728m12.728 0a9 9 0 010 12.728M9.172 14.828a4 4 0 010-5.656m5.656 0a4 4 0 010 5.656"/></svg>
            </div>
            <div class="flex-1">
                <div class="flex items-center gap-3 mb-1">
                    <h3 class="text-lg font-bold text-red-400">🔴 You're Live!</h3>
                    <span class="px-3 py-1 bg-red-500/30 text-red-300 rounded-lg text-sm font-medium">{{ number_format($stats['live_viewers']) }} viewers</span>
                </div>
                <p class="text-gray-300">Keep up the great content!</p>
            </div>
        </div>
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-4 gap-6">
        {{-- Followers --}}
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-blue-500/20 rounded-xl">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                </div>
                <span class="text-xs text-gray-400 uppercase tracking-wide">Followers</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['followers']) }}</div>
            <p class="text-sm text-gray-400">Total followers</p>
        </div>

        {{-- Live Viewers --}}
        <div class="glass-card rounded-2xl p-6 {{ $stats['is_live'] ? 'border-2 border-red-500/50' : '' }}">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-red-500/20 rounded-xl">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                </div>
                <span class="text-xs text-gray-400 uppercase tracking-wide">Viewers</span>
            </div>
            <div class="text-3xl font-bold text-white mb-1">{{ number_format($stats['live_viewers']) }}</div>
            <p class="text-sm {{ $stats['is_live'] ? 'text-red-400' : 'text-gray-400' }}">
                {{ $stats['is_live'] ? 'Currently watching' : 'Offline' }}
            </p>
        </div>

        {{-- Status --}}
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-green-500/20 rounded-xl">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs text-gray-400 uppercase tracking-wide">Status</span>
            </div>
            <div class="space-y-2">
                @if($stats['is_verified'])
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-blue-500 rounded-full"></span>
                        <span class="text-sm text-blue-400">Verified</span>
                    </div>
                @endif
                @if($stats['is_featured'])
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-purple-500 rounded-full"></span>
                        <span class="text-sm text-purple-400">Featured</span>
                    </div>
                @endif
                @if(!$stats['is_verified'] && !$stats['is_featured'])
                    <div class="flex items-center gap-2">
                        <span class="w-2 h-2 bg-gray-500 rounded-full"></span>
                        <span class="text-sm text-gray-400">Active</span>
                    </div>
                @endif
            </div>
        </div>

        {{-- Last Live --}}
        <div class="glass-card rounded-2xl p-6">
            <div class="flex items-center justify-between mb-4">
                <div class="p-3 bg-purple-500/20 rounded-xl">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <span class="text-xs text-gray-400 uppercase tracking-wide">Last Stream</span>
            </div>
            @if($stats['last_live'])
                <div class="text-xl font-bold text-white mb-1">{{ $stats['last_live']->diffForHumans() }}</div>
                <p class="text-sm text-gray-400">{{ $stats['last_live']->format('M d, Y') }}</p>
            @else
                <div class="text-xl font-bold text-gray-400 mb-1">Never</div>
                <p class="text-sm text-gray-400">No streams yet</p>
            @endif
        </div>
    </div>

    {{-- Quick Links --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Profile Management --}}
        <div class="glass-card rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-4">Profile Management</h2>
            <div class="space-y-3">
                <a href="{{ route('creator.edit') }}" class="flex items-center justify-between p-4 bg-white/3 hover:bg-white/5 rounded-xl transition group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-green-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">Edit Profile</p>
                            <p class="text-xs text-gray-400">Update your channel info</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('creator.stats') }}" class="flex items-center justify-between p-4 bg-white/3 hover:bg-white/5 rounded-xl transition group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-blue-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">View Statistics</p>
                            <p class="text-xs text-gray-400">Detailed analytics</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>

                <a href="{{ route('content-creators.show', $creator) }}" class="flex items-center justify-between p-4 bg-white/3 hover:bg-white/5 rounded-xl transition group">
                    <div class="flex items-center gap-3">
                        <div class="p-2 bg-purple-500/20 rounded-lg">
                            <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                        </div>
                        <div>
                            <p class="text-white font-medium">Public Profile</p>
                            <p class="text-xs text-gray-400">See your public page</p>
                        </div>
                    </div>
                    <svg class="w-5 h-5 text-gray-400 group-hover:text-white transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
                </a>
            </div>
        </div>

        {{-- Current Stream Info --}}
        <div class="glass-card rounded-2xl p-6">
            <h2 class="text-xl font-bold text-white mb-4">Stream Information</h2>

            @if($stats['is_live'])
                <div class="space-y-4">
                    <div class="p-4 bg-red-500/10 border border-red-500/30 rounded-xl">
                        <div class="flex items-center gap-2 mb-3">
                            <span class="relative flex h-3 w-3">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                            </span>
                            <span class="text-red-400 font-bold">LIVE NOW</span>
                        </div>

                        @if($creator->live_title)
                        <p class="text-white font-medium mb-2">{{ $creator->live_title }}</p>
                        @endif

                        <div class="flex items-center gap-4 text-sm text-gray-300">
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/></svg>
                                <span>{{ number_format($creator->live_viewers ?? 0) }} viewers</span>
                            </div>
                            @if($creator->live_started_at)
                            <div class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm1-12a1 1 0 10-2 0v4a1 1 0 00.293.707l2.828 2.829a1 1 0 101.415-1.415L11 9.586V6z" clip-rule="evenodd"/></svg>
                                <span>{{ $creator->live_started_at->diffForHumans(null, true) }}</span>
                            </div>
                            @endif
                        </div>
                    </div>

                    <a href="{{ $creator->channel_url }}" target="_blank" class="block w-full px-4 py-3 bg-gradient-to-r from-red-600 to-red-500 hover:from-red-500 hover:to-red-400 text-white rounded-xl transition text-center font-medium">
                        Go to Stream
                    </a>
                </div>
            @else
                <div class="text-center py-8">
                    <div class="w-16 h-16 bg-gray-500/20 rounded-full flex items-center justify-center mx-auto mb-4">
                        <svg class="w-8 h-8 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    </div>
                    <p class="text-gray-400 mb-4">You're not currently streaming</p>
                    <a href="{{ $creator->channel_url }}" target="_blank" class="inline-block px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                        Go to Channel
                    </a>
                </div>
            @endif
        </div>
    </div>

    {{-- Platform Info --}}
    <div class="glass-card rounded-2xl p-6">
        <h2 class="text-xl font-bold text-white mb-4">Platform Details</h2>
        <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
            <div>
                <p class="text-sm text-gray-400 mb-1">Platform</p>
                <span class="inline-flex px-3 py-1 {{ $creator->platform_color }} bg-white/5 rounded-lg font-medium">{{ $creator->platform_name }}</span>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-1">Channel URL</p>
                <a href="{{ $creator->channel_url }}" target="_blank" class="text-green-400 hover:text-green-300 text-sm break-all">{{ $creator->channel_url }}</a>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-1">Registered</p>
                <p class="text-white">{{ $creator->created_at->format('M d, Y') }}</p>
            </div>
        </div>
    </div>
</div>
@endsection
