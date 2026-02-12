@extends('layouts.app')
@section('title', 'Videos')
@section('content')
<div class="py-12 space-y-6">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-2xl p-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Videos</h1>
                    <p class="text-gray-400">Best moments from our community</p>
                </div>
                @auth
                <a href="{{ route('clips.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                    Submit Video
                </a>
                @endauth
            </div>
        </div>
        {{-- Video of the Week --}}
        @if($clipOfTheWeek)
        <div class="bg-gradient-to-r from-yellow-600/10 to-orange-600/10 border border-yellow-500/30 rounded-2xl p-6">
            <h2 class="text-2xl font-bold text-white mb-4 flex items-center gap-2">
                <svg class="w-6 h-6 text-yellow-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                Video of the Week
            </h2>
            <a href="{{ route('clips.show', $clipOfTheWeek) }}" class="block group hover:opacity-90 transition">
                <div class="bg-gray-900/50 rounded-xl overflow-hidden">
                    {{-- Thumbnail --}}
                    @if($clipOfTheWeek->thumbnail_url)
                    <div class="aspect-video relative overflow-hidden">
                        <img src="{{ $clipOfTheWeek->thumbnail_url }}" alt="{{ $clipOfTheWeek->title }}" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-black/30 group-hover:bg-black/50 transition"></div>
                        {{-- Large play button --}}
                        <div class="absolute inset-0 flex items-center justify-center">
                            <div class="w-20 h-20 rounded-full bg-yellow-500/20 backdrop-blur-sm flex items-center justify-center group-hover:bg-yellow-500/30 group-hover:scale-110 transition-all border-2 border-yellow-400">
                                <svg class="w-10 h-10 text-yellow-400 ml-1" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                                </svg>
                            </div>
                        </div>
                    </div>
                    @endif
                    <div class="p-4">
                        <h3 class="text-xl font-bold text-white mb-2 group-hover:text-yellow-400 transition">{{ $clipOfTheWeek->title }}</h3>
                        @if($clipOfTheWeek->author)
                        <p class="text-sm text-gray-400 mb-2 flex items-center gap-1.5">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                            </svg>
                            {{ $clipOfTheWeek->author }}
                        </p>
                        @endif
                        <div class="flex items-center gap-4 text-sm text-gray-400">
                            <span>Submitted by {{ $clipOfTheWeek->user->name }}</span>
                            <span class="{{ $clipOfTheWeek->platform_color }}">{{ $clipOfTheWeek->platform_name }}</span>
                            <span class="flex items-center gap-1">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                                </svg>
                                {{ $clipOfTheWeek->votes }} votes
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        </div>
        @endif
        {{-- Stats --}}
        <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
            <div class="glass-card rounded-xl p-4">
                <div class="text-3xl font-bold text-white">{{ $stats['total'] }}</div>
                <div class="text-sm text-gray-400">Total Videos</div>
            </div>
            <div class="glass-card border border-blue-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-blue-400">{{ $stats['this_week'] }}</div>
                <div class="text-sm text-gray-400">This Week</div>
            </div>
            <div class="glass-card border border-yellow-500/30 rounded-xl p-4">
                <div class="text-3xl font-bold text-yellow-400">{{ $stats['featured'] }}</div>
                <div class="text-sm text-gray-400">Featured</div>
            </div>
        </div>
        {{-- Filters --}}
        <div class="flex flex-wrap gap-3">
            <a href="{{ route('clips.index', ['sort' => 'popular']) }}" class="px-4 py-2 rounded-xl {{ request('sort', 'popular') === 'popular' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Popular
            </a>
            <a href="{{ route('clips.index', ['sort' => 'recent']) }}" class="px-4 py-2 rounded-xl {{ request('sort') === 'recent' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Recent
            </a>
            <a href="{{ route('clips.index', ['sort' => 'featured']) }}" class="px-4 py-2 rounded-xl {{ request('sort') === 'featured' ? 'bg-yellow-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Featured
            </a>
            <span class="text-gray-500">|</span>
            <a href="{{ route('clips.index', ['platform' => 'youtube']) }}" class="px-4 py-2 rounded-xl {{ request('platform') === 'youtube' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                YouTube
            </a>
            <a href="{{ route('clips.index', ['platform' => 'twitch']) }}" class="px-4 py-2 rounded-xl {{ request('platform') === 'twitch' ? 'bg-green-600 text-white' : 'bg-white/3 text-gray-300 hover:bg-white/5' }}">
                Twitch
            </a>
        </div>
        {{-- Videos Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
            @forelse($clips as $clip)
            <a href="{{ route('clips.show', $clip) }}" class="glass-card hover:border-green-500/50 rounded-xl overflow-hidden transition group">
                <div class="aspect-video bg-gray-900 relative overflow-hidden">
                    @if($clip->thumbnail_url)
                        {{-- Thumbnail --}}
                        <img src="{{ $clip->thumbnail_url }}" alt="{{ $clip->title }}" class="w-full h-full object-cover">
                        {{-- Dark overlay on hover --}}
                        <div class="absolute inset-0 bg-black/40 group-hover:bg-black/60 transition"></div>
                    @else
                        {{-- Fallback background --}}
                        <div class="absolute inset-0 bg-gradient-to-br from-gray-800 to-gray-900"></div>
                    @endif

                    {{-- Play icon overlay --}}
                    <div class="absolute inset-0 flex items-center justify-center">
                        <div class="w-16 h-16 rounded-full bg-white/10 backdrop-blur-sm flex items-center justify-center group-hover:bg-white/20 group-hover:scale-110 transition-all">
                            <svg class="w-8 h-8 text-white ml-1" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M6.3 2.841A1.5 1.5 0 004 4.11V15.89a1.5 1.5 0 002.3 1.269l9.344-5.89a1.5 1.5 0 000-2.538L6.3 2.84z"/>
                            </svg>
                        </div>
                    </div>

                    {{-- Featured badge --}}
                    @if($clip->is_featured)
                    <span class="absolute top-2 right-2 px-2 py-1 bg-yellow-600 text-white text-xs font-bold rounded shadow-lg">FEATURED</span>
                    @endif

                    {{-- Platform badge --}}
                    <span class="absolute top-2 left-2 px-2 py-1 bg-black/60 backdrop-blur-sm {{ $clip->platform_color }} text-xs font-medium rounded">
                        {{ $clip->platform_name }}
                    </span>

                    {{-- Duration overlay (if you want to add duration later) --}}
                    {{-- <span class="absolute bottom-2 right-2 px-2 py-0.5 bg-black/80 text-white text-xs rounded">5:23</span> --}}
                </div>
                <div class="p-4">
                    <h3 class="font-bold text-white mb-2 group-hover:text-green-400 transition line-clamp-2">{{ $clip->title }}</h3>
                    @if($clip->author)
                    <p class="text-xs text-gray-500 mb-2 flex items-center gap-1">
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                        </svg>
                        {{ $clip->author }}
                    </p>
                    @endif
                    <div class="flex items-center justify-between text-sm text-gray-400">
                        <span>Submitted by {{ $clip->user->name }}</span>
                        <span class="flex items-center gap-1">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                            {{ $clip->votes }}
                        </span>
                    </div>
                </div>
            </a>
            @empty
            <div class="col-span-full text-center py-12 text-gray-400">
                <p class="text-lg">No videos found.</p>
            </div>
            @endforelse
        </div>
        {{-- Pagination --}}
        @if($clips->hasPages())
        <div class="mt-6">
            {{ $clips->links() }}
        </div>
        @endif
    </div>

@endsection
