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
                    <span class="px-2 py-1 bg-red-600 text-white text-xs font-bold rounded">LIVE</span>
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
