@extends('layouts.app')
@section('title', 'Content Creators')
@section('content')
<div class="py-12 space-y-6">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Content Creators</h1>
                    <p class="text-gray-400">Streamers, YouTubers, and content creators from our community</p>
                </div>
                @auth
                @if(!auth()->user()->isContentCreator())
                <a href="{{ route('content-creators.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                    Register as Creator
                </a>
                @endif
                @endauth
            </div>
        </div>
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="glass-card rounded-xl p-4">
                <div class="text-3xl font-bold text-white">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-400">Total Creators</div>
            </div>
            <div class="glass-card border border-green-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-green-400">{{ $stats['live'] }}</div>
                <div class="text-sm text-gray-400">Live Now</div>
            </div>
            <div class="glass-card border border-blue-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-blue-400">{{ $stats['verified'] }}</div>
                <div class="text-sm text-gray-400">Verified</div>
            </div>
        </div>
        {{-- Live Now Section --}}
        @php
            $liveCreators = $creators->filter(fn($c) => $c->is_live);
        @endphp
        @if($liveCreators->count() > 0 && !request('filter') && !request('platform'))
        <div class="bg-gradient-to-r from-red-600/20 to-red-500/20 border-2 border-red-500/50 rounded-2xl p-6">
            <div class="flex items-center gap-3 mb-4">
                <div class="relative flex h-3 w-3">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-red-400 opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-3 w-3 bg-red-500"></span>
                </div>
                <h2 class="text-2xl font-bold text-white">Live Now</h2>
                <span class="px-3 py-1 bg-red-500/30 text-red-300 rounded-lg text-sm font-medium">{{ $liveCreators->count() }} streaming</span>
            </div>

            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($liveCreators->take(6) as $creator)
                <a href="{{ route('content-creators.show', $creator) }}" class="glass-card hover:border-red-500/50 rounded-xl p-4 transition group bg-gradient-to-br from-red-500/5 to-transparent">
                    <div class="flex items-start gap-3 mb-3">
                        <img src="{{ $creator->user->avatar_display }}" alt="{{ $creator->user->name }}" class="w-12 h-12 rounded-full ring-2 ring-red-500/50">
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-2 mb-1">
                                <h3 class="font-bold text-white truncate group-hover:text-red-400 transition text-sm">{{ $creator->channel_name }}</h3>
                                @if($creator->is_verified)
                                <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                                @endif
                            </div>
                            <p class="text-xs {{ $creator->platform_color }}">{{ $creator->platform_name }}</p>
                        </div>
                        <div class="flex items-center gap-1.5 px-2 py-1 bg-red-600 rounded text-white text-xs font-bold">
                            <span class="relative flex h-1.5 w-1.5">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-1.5 w-1.5 bg-white"></span>
                            </span>
                            LIVE
                        </div>
                    </div>
                    @if($creator->live_title)
                    <p class="text-sm text-gray-300 line-clamp-2 mb-2">{{ $creator->live_title }}</p>
                    @endif
                    <div class="flex items-center justify-between text-xs text-gray-400">
                        <div class="flex items-center gap-1">
                            <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                            </svg>
                            <span>{{ number_format($creator->live_viewers ?? 0) }}</span>
                        </div>
                        @if($creator->live_started_at)
                        <span>{{ $creator->live_started_at->diffForHumans(null, true) }}</span>
                        @endif
                    </div>
                </a>
                @endforeach
            </div>

            @if($liveCreators->count() > 6)
            <div class="mt-4 text-center">
                <a href="{{ route('content-creators.index', ['filter' => 'live']) }}" class="inline-flex items-center gap-2 px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition font-medium">
                    View all {{ $liveCreators->count() }} live creators
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </a>
            </div>
            @endif
        </div>
        @endif

        {{-- Filters --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('content-creators.index') }}" class="px-4 py-2 rounded-xl {{ !request('filter') && !request('platform') ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                All
            </a>
            <a href="{{ route('content-creators.index', ['filter' => 'live']) }}" class="px-4 py-2 rounded-xl {{ request('filter') === 'live' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Live Now
            </a>
            <a href="{{ route('content-creators.index', ['filter' => 'verified']) }}" class="px-4 py-2 rounded-xl {{ request('filter') === 'verified' ? 'bg-blue-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Verified
            </a>
            <span class="text-gray-500">|</span>
            <a href="{{ route('content-creators.index', ['platform' => 'twitch']) }}" class="px-4 py-2 rounded-xl {{ request('platform') === 'twitch' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Twitch
            </a>
            <a href="{{ route('content-creators.index', ['platform' => 'youtube']) }}" class="px-4 py-2 rounded-xl {{ request('platform') === 'youtube' ? 'bg-red-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                YouTube
            </a>
            <a href="{{ route('content-creators.index', ['platform' => 'tiktok']) }}" class="px-4 py-2 rounded-xl {{ request('platform') === 'tiktok' ? 'bg-pink-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                TikTok
            </a>
            <a href="{{ route('content-creators.index', ['platform' => 'kick']) }}" class="px-4 py-2 rounded-xl {{ request('platform') === 'kick' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Kick
            </a>
        </div>
        {{-- Creators Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($creators as $creator)
            <a href="{{ route('content-creators.show', $creator) }}" class="glass-card hover:border-green-500/50 rounded-xl p-6 transition group">
                <div class="flex items-start gap-4 mb-4">
                    <img src="{{ $creator->user->avatar_display }}" alt="{{ $creator->user->name }}" class="w-16 h-16 rounded-full">
                    <div class="flex-1 min-w-0">
                        <div class="flex items-center gap-2 mb-1">
                            <h3 class="font-bold text-white truncate group-hover:text-green-400 transition">{{ $creator->channel_name }}</h3>
                            @if($creator->is_verified)
                            <svg class="w-5 h-5 text-blue-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            @endif
                        </div>
                        <p class="text-sm {{ $creator->platform_color }}">{{ $creator->platform_name }}</p>
                    </div>
                    @if($creator->is_live)
                    <div class="relative group/live">
                        <div class="flex items-center gap-2 px-3 py-1.5 bg-gradient-to-r from-red-600 to-red-500 rounded-lg shadow-lg shadow-red-500/50 animate-pulse">
                            <span class="relative flex h-2 w-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-white opacity-75"></span>
                                <span class="relative inline-flex rounded-full h-2 w-2 bg-white"></span>
                            </span>
                            <span class="text-white text-xs font-bold uppercase">Live</span>
                        </div>
                        {{-- Tooltip --}}
                        <div class="absolute right-0 top-full mt-2 w-64 bg-gray-900 border border-gray-700 rounded-lg p-3 shadow-xl opacity-0 invisible group-hover/live:opacity-100 group-hover/live:visible transition-all duration-200 z-10">
                            <div class="text-xs text-gray-400 mb-1">Streaming on {{ ucfirst($creator->live_platform) }}</div>
                            @if($creator->live_title)
                            <div class="text-sm text-white font-medium mb-2">{{ $creator->live_title }}</div>
                            @endif
                            <div class="flex items-center gap-2 text-xs">
                                <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                </svg>
                                <span class="text-gray-300">{{ number_format($creator->live_viewers ?? 0) }} viewers</span>
                            </div>
                            @if($creator->live_started_at)
                            <div class="text-xs text-gray-500 mt-1">Started {{ $creator->live_started_at->diffForHumans() }}</div>
                            @endif
                        </div>
                    </div>
                    @endif
                </div>
                @if($creator->bio)
                <p class="text-sm text-gray-400 line-clamp-2 mb-3">{{ $creator->bio }}</p>
                @endif
                @if($creator->follower_count)
                <div class="text-sm text-gray-500">{{ number_format($creator->follower_count) }} followers</div>
                @endif
            </a>
            @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <p class="text-lg">No content creators found.</p>
            </div>
            @endforelse
        </div>
        {{-- Pagination --}}
        @if($creators->hasPages())
        <div class="mt-6">
            {{ $creators->links() }}
        </div>
        @endif
    </div>

@endsection

@push('scripts')
<script>
    // Auto-refresh live status every 3 minutes (matches scheduled command)
    function updateLiveStatus() {
        // Only refresh if we're on the main view (no filters)
        const urlParams = new URLSearchParams(window.location.search);
        if (urlParams.has('filter') || urlParams.has('platform')) {
            return;
        }

        fetch('{{ route('content-creators.live-status') }}')
            .then(response => response.json())
            .then(data => {
                // Update live count in stats
                const liveCountEl = document.querySelector('.text-green-400');
                if (liveCountEl) {
                    liveCountEl.textContent = data.total;
                }

                // If there are live creators and no Live Now section exists, reload page
                // If there are no live creators and Live Now section exists, reload page
                const liveSection = document.querySelector('.bg-gradient-to-r.from-red-600\\/20');
                const hasLiveCreators = data.count > 0;
                const hasLiveSection = liveSection !== null;

                if (hasLiveCreators !== hasLiveSection) {
                    window.location.reload();
                }
            })
            .catch(error => console.error('Error updating live status:', error));
    }

    // Check every 3 minutes
    setInterval(updateLiveStatus, 3 * 60 * 1000);

    // Initial check after 10 seconds
    setTimeout(updateLiveStatus, 10000);
</script>
@endpush
