@extends('layouts.app')
@section('title', 'Welcome')
@section('content')
<div class="space-y-20">
    <!-- Hero Section -->
    <div class="relative -mt-6 -mx-4 sm:-mx-6 lg:-mx-8 px-4 sm:px-6 lg:px-8 py-24 md:py-32 overflow-hidden">
        <!-- Decorative glow blobs -->
        <div class="absolute top-1/4 left-1/4 w-96 h-96 bg-green-500/20 rounded-full blur-3xl -translate-x-1/2 -translate-y-1/2 pointer-events-none"></div>
        <div class="absolute bottom-1/4 right-1/4 w-80 h-80 bg-emerald-500/15 rounded-full blur-3xl translate-x-1/2 translate-y-1/2 pointer-events-none"></div>
        <!-- Content -->
        <div class="relative z-10 text-center">
            <!-- Eyebrow badge -->
            <span class="inline-flex items-center px-4 py-1.5 bg-green-500/10 border border-green-500/30 rounded-full text-sm text-green-400 mb-6">
                European Arma Reforger Community
            </span>
            <h1 class="text-6xl md:text-7xl lg:text-8xl font-black mb-8 bg-gradient-to-r from-green-400 via-green-500 to-emerald-400 bg-clip-text text-transparent drop-shadow-[0_0_25px_rgba(34,197,94,0.3)]">
                ARMABATTLES
            </h1>
            <!-- Server Status Card -->
            <div id="server-status">
                <div id="server-card" class="bg-gray-800/80 backdrop-blur-md rounded-2xl border border-gray-700/50 overflow-hidden shadow-2xl transition-shadow duration-500">
                    <!-- Server Header -->
                    <div class="relative h-36 sm:h-40 overflow-hidden">
                        <img src="https://wallpapercave.com/wp/wp15024138.webp" alt="Everon" class="w-full h-full object-cover">
                        <div class="absolute inset-0 bg-gradient-to-t from-gray-800 via-gray-800/60 to-transparent"></div>
                        <div class="absolute bottom-0 left-0 right-0 p-4">
                            <div class="flex items-center justify-between">
                                <div class="flex items-center space-x-3">
                                    <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                                        <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                                        </svg>
                                    </div>
                                    <div class="text-left">
                                        <a href="{{ route('servers.show', config('services.battlemetrics.server_id', '0')) }}" id="server-name" class="text-sm font-bold text-white hover:text-green-400 transition-colors line-clamp-1">Loading...</a>
                                        <p id="server-gamemode" class="text-xs text-gray-400">Loading...</p>
                                    </div>
                                </div>
                                <div id="status-indicator" class="flex items-center bg-gray-900/60 backdrop-blur rounded-full px-3 py-1.5">
                                    <span class="relative flex h-2 w-2 mr-2">
                                        <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-gray-400 opacity-75"></span>
                                        <span class="relative inline-flex rounded-full h-2 w-2 bg-gray-400"></span>
                                    </span>
                                    <span class="text-xs font-medium text-gray-400">Loading...</span>
                                </div>
                            </div>
                        </div>
                    </div>
                    <!-- Server Info -->
                    <div id="server-info" class="p-5">
                        <div class="animate-pulse space-y-4">
                            <div class="h-3 bg-gray-700 rounded w-1/3"></div>
                            <div class="h-2 bg-gray-700 rounded w-full"></div>
                            <div class="h-10 bg-gray-700 rounded w-full"></div>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Quick Links --}}
            <div class="mt-8 flex flex-wrap items-center justify-center gap-4">
                @guest
                <a href="{{ route('auth.steam') }}" class="inline-flex items-center space-x-3 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 rounded-xl text-lg font-semibold transition-all duration-300 shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105">
                    <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                        <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174l2.896-4.143c-.468-.116-.91-.293-1.317-.525L4.5 21.75c-.913-.288-1.772-.684-2.563-1.176l4.707-3.308c-.155-.369-.277-.758-.359-1.162L0 19.293V12C0 5.373 5.373 0 12 0zm0 4.5c-4.136 0-7.5 3.364-7.5 7.5 0 .768.115 1.509.328 2.206l3.908-2.745c.493-2.293 2.535-4.011 4.997-4.011 2.795 0 5.067 2.272 5.067 5.067 0 2.462-1.758 4.514-4.089 4.977l-2.725 3.896C9.788 22.285 10.869 22.5 12 22.5c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
                    </svg>
                    <span>Join with Steam</span>
                </a>
                @endguest
                <a href="{{ route('rules') }}" class="inline-flex items-center space-x-2 px-6 py-4 bg-gray-800/80 hover:bg-gray-700 border border-gray-600 rounded-xl font-semibold transition-all duration-300 hover:scale-105">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                    </svg>
                    <span>Server Rules</span>
                </a>
            </div>
        </div>
    </div>
    {{-- Latest News Section --}}
    @if($latestNews->isNotEmpty())
    <div>
        <div class="flex items-center justify-between mb-6">
            <div>
                <h2 class="text-2xl font-bold text-white">Latest News</h2>
                <p class="text-gray-400 text-sm mt-1">Stay up to date with community updates</p>
            </div>
            <a href="{{ route('news.index') }}" class="text-green-400 hover:text-green-300 text-sm font-medium flex items-center gap-1 transition">
                View All
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/></svg>
            </a>
        </div>
        <div class="grid md:grid-cols-3 gap-6">
            @foreach($latestNews as $article)
                <a href="{{ route('news.show', $article) }}" class="group bg-gray-800/60 backdrop-blur border border-gray-700/50 rounded-xl overflow-hidden hover:border-green-500/50 transition-all duration-300 hover:-translate-y-1">
                    <div class="relative h-40 overflow-hidden">
                        @if($article->featured_image_url)
                            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                        @else
                            <div class="w-full h-full bg-gradient-to-br from-green-500/20 to-emerald-500/10 flex items-center justify-center">
                                <svg class="w-10 h-10 text-green-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                </svg>
                            </div>
                        @endif
                        @if($article->is_pinned)
                            <span class="absolute top-2 left-2 px-2 py-0.5 bg-yellow-500/90 text-yellow-900 rounded text-[10px] font-bold uppercase">Pinned</span>
                        @endif
                    </div>
                    <div class="p-4">
                        <h3 class="text-sm font-bold text-white mb-1 line-clamp-2 group-hover:text-green-400 transition-colors">{{ $article->title }}</h3>
                        <div class="flex items-center justify-between text-xs text-gray-500">
                            <span>{{ $article->published_at->diffForHumans() }}</span>
                            <span class="flex items-center gap-1 text-green-400">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                {{ $article->hoorahs_count }}
                            </span>
                        </div>
                    </div>
                </a>
            @endforeach
        </div>
    </div>
    @endif
    <!-- Features Section -->
    <div class="{{ $latestNews->isEmpty() ? '-mt-20' : '' }}">
        <div class="text-center mb-12">
            <h2 class="text-3xl font-bold mb-4">Why Join Us?</h2>
            <div class="w-16 h-1 bg-green-500 mx-auto rounded-full mb-4"></div>
            <p class="text-gray-400">Experience Arma Reforger like never before with our active community and well-maintained servers.</p>
        </div>
        <div class="grid md:grid-cols-3 gap-8">
            <!-- Feature 1: Community -->
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 100)" x-show="show"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="group bg-gradient-to-br from-green-500/5 to-gray-800/90 rounded-2xl p-8 border border-gray-700/50 hover:border-green-500/50 transition-all duration-300 hover:-translate-y-1">
                <div class="w-14 h-14 bg-green-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-green-500/20 transition-colors">
                    <svg class="w-7 h-7 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Active Community</h3>
                <p class="text-gray-400 leading-relaxed">Join hundreds of players in our growing community. Make friends, form squads, and dominate the battlefield together.</p>
            </div>
            <!-- Feature 2: Performance -->
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 250)" x-show="show"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="group bg-gradient-to-br from-blue-500/5 to-gray-800/90 rounded-2xl p-8 border border-gray-700/50 hover:border-blue-500/50 transition-all duration-300 hover:-translate-y-1">
                <div class="w-14 h-14 bg-blue-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-blue-500/20 transition-colors">
                    <svg class="w-7 h-7 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">High Performance</h3>
                <p class="text-gray-400 leading-relaxed">Our servers are optimized for the best gameplay experience with minimal latency and maximum uptime.</p>
            </div>
            <!-- Feature 3: Events -->
            <div x-data="{ show: false }" x-init="setTimeout(() => show = true, 400)" x-show="show"
                 x-transition:enter="transition ease-out duration-500"
                 x-transition:enter-start="opacity-0 translate-y-4"
                 x-transition:enter-end="opacity-100 translate-y-0"
                 class="group bg-gradient-to-br from-purple-500/5 to-gray-800/90 rounded-2xl p-8 border border-gray-700/50 hover:border-purple-500/50 transition-all duration-300 hover:-translate-y-1">
                <div class="w-14 h-14 bg-purple-500/10 rounded-xl flex items-center justify-center mb-6 group-hover:bg-purple-500/20 transition-colors">
                    <svg class="w-7 h-7 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7V3m8 4V3m-9 8h10M5 21h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <h3 class="text-xl font-bold mb-3">Regular Events</h3>
                <p class="text-gray-400 leading-relaxed">Participate in organized operations, tournaments, and special events with prizes and recognition.</p>
            </div>
        </div>
    </div>
    <!-- Live Activity Feed -->
    <div>
        @include('partials._activity-feed')
    </div>
    <!-- Battle Log Section -->
    <div>
        <div class="bg-gradient-to-br from-green-500/5 via-gray-800/80 to-emerald-500/5 border border-gray-700/50 rounded-2xl overflow-hidden">
            <div class="grid md:grid-cols-2 gap-0">
                {{-- Left: Text content --}}
                <div class="p-8 md:p-12 flex flex-col justify-center">
                    <span class="inline-flex items-center self-start px-3 py-1 bg-green-500/10 border border-green-500/30 rounded-full text-xs text-green-400 mb-4 uppercase tracking-wider font-semibold">
                        What makes us different
                    </span>
                    <h2 class="text-3xl md:text-4xl font-bold mb-4 text-white">Your Personal<br><span class="text-green-400">Battle Log</span></h2>
                    <p class="text-gray-400 leading-relaxed mb-6">
                        Every kill, every death, every second on the battlefield is tracked. Our custom stats system gives you a detailed personal profile with weapon breakdowns, hit zone accuracy, distance traveled, XP progress, and more. See how you stack up on the leaderboards.
                    </p>
                    <div class="grid grid-cols-2 gap-3 mb-8">
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Kill & Death tracking
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Weapon statistics
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Hit zone accuracy
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Distance traveled
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            XP & progression
                        </div>
                        <div class="flex items-center gap-2 text-sm text-gray-300">
                            <svg class="w-4 h-4 text-green-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                            Global leaderboards
                        </div>
                    </div>
                    <div class="flex flex-wrap gap-3">
                        <a href="{{ route('leaderboard') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-green-600 hover:bg-green-500 rounded-lg font-semibold text-sm transition-all duration-300 shadow-lg shadow-green-500/20 hover:shadow-green-500/30">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                            View Leaderboard
                        </a>
                        @auth
                        <a href="{{ route('profile') }}" class="inline-flex items-center gap-2 px-5 py-2.5 bg-gray-700 hover:bg-gray-600 border border-gray-600 rounded-lg font-semibold text-sm transition">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                            My Battle Log
                        </a>
                        @endauth
                    </div>
                </div>
                {{-- Right: Visual stats preview --}}
                <div class="p-8 md:p-12 bg-gray-900/40 flex items-center justify-center">
                    <div class="w-full max-w-sm space-y-4">
                        {{-- Mock stat card --}}
                        <div class="bg-gray-800/80 border border-gray-700/50 rounded-xl p-5">
                            <div class="flex items-center gap-3 mb-4">
                                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                                </div>
                                <div>
                                    <p class="text-sm font-semibold text-white">Player Stats</p>
                                    <p class="text-xs text-gray-500">Live tracking example</p>
                                </div>
                            </div>
                            <div class="grid grid-cols-3 gap-3">
                                <div class="text-center p-2 bg-gray-900/50 rounded-lg">
                                    <p class="text-lg font-bold text-green-400">347</p>
                                    <p class="text-[10px] text-gray-500 uppercase">Kills</p>
                                </div>
                                <div class="text-center p-2 bg-gray-900/50 rounded-lg">
                                    <p class="text-lg font-bold text-red-400">189</p>
                                    <p class="text-[10px] text-gray-500 uppercase">Deaths</p>
                                </div>
                                <div class="text-center p-2 bg-gray-900/50 rounded-lg">
                                    <p class="text-lg font-bold text-yellow-400">1.84</p>
                                    <p class="text-[10px] text-gray-500 uppercase">K/D</p>
                                </div>
                            </div>
                        </div>
                        {{-- Mock weapon breakdown --}}
                        <div class="bg-gray-800/80 border border-gray-700/50 rounded-xl p-5">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Top Weapons</p>
                            <div class="space-y-2.5">
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-300">M16A2</span>
                                        <span class="text-green-400 font-medium">142 kills</span>
                                    </div>
                                    <div class="w-full bg-gray-700/50 rounded-full h-1.5">
                                        <div class="bg-green-500 h-1.5 rounded-full" style="width: 75%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-300">AK-74</span>
                                        <span class="text-green-400 font-medium">98 kills</span>
                                    </div>
                                    <div class="w-full bg-gray-700/50 rounded-full h-1.5">
                                        <div class="bg-green-500/70 h-1.5 rounded-full" style="width: 52%"></div>
                                    </div>
                                </div>
                                <div>
                                    <div class="flex items-center justify-between text-sm mb-1">
                                        <span class="text-gray-300">SVD</span>
                                        <span class="text-green-400 font-medium">61 kills</span>
                                    </div>
                                    <div class="w-full bg-gray-700/50 rounded-full h-1.5">
                                        <div class="bg-green-500/50 h-1.5 rounded-full" style="width: 32%"></div>
                                    </div>
                                </div>
                            </div>
                        </div>
                        {{-- Mock hit zone --}}
                        <div class="bg-gray-800/80 border border-gray-700/50 rounded-xl p-5">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-3">Hit Zone Accuracy</p>
                            <div class="flex items-center justify-around">
                                <div class="text-center">
                                    <p class="text-xl font-bold text-red-400">32%</p>
                                    <p class="text-[10px] text-gray-500">Head</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xl font-bold text-yellow-400">45%</p>
                                    <p class="text-[10px] text-gray-500">Torso</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xl font-bold text-gray-400">15%</p>
                                    <p class="text-[10px] text-gray-500">Arms</p>
                                </div>
                                <div class="text-center">
                                    <p class="text-xl font-bold text-gray-500">8%</p>
                                    <p class="text-[10px] text-gray-500">Legs</p>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
    <!-- Stats Section -->
    <div>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 md:gap-6">
            <!-- Max Players -->
            <div class="bg-gray-800/60 backdrop-blur border border-gray-700/50 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-green-500/10 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                </div>
                <div class="text-4xl md:text-5xl font-black text-green-400 mb-1">64</div>
                <div class="text-sm text-gray-400 uppercase tracking-wide">Max Players</div>
            </div>
            <!-- Uptime -->
            <div class="bg-gray-800/60 backdrop-blur border border-gray-700/50 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-blue-500/10 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-4xl md:text-5xl font-black text-green-400 mb-1">24/7</div>
                <div class="text-sm text-gray-400 uppercase tracking-wide">Server Uptime</div>
            </div>
            <!-- Location -->
            <div class="bg-gray-800/60 backdrop-blur border border-gray-700/50 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-purple-500/10 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3.055 11H5a2 2 0 012 2v1a2 2 0 002 2 2 2 0 012 2v2.945M8 3.935V5.5A2.5 2.5 0 0010.5 8h.5a2 2 0 012 2 2 2 0 104 0 2 2 0 012-2h1.064M15 20.488V18a2 2 0 012-2h3.064M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-4xl md:text-5xl font-black text-green-400 mb-1">EU</div>
                <div class="text-sm text-gray-400 uppercase tracking-wide">Server Location</div>
            </div>
            <!-- Gameplay -->
            <div class="bg-gray-800/60 backdrop-blur border border-gray-700/50 rounded-xl p-6 text-center">
                <div class="w-12 h-12 bg-amber-500/10 rounded-lg flex items-center justify-center mx-auto mb-3">
                    <svg class="w-6 h-6 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <div class="text-4xl md:text-5xl font-black text-green-400 mb-1">Vanilla+</div>
                <div class="text-sm text-gray-400 uppercase tracking-wide">Gameplay</div>
            </div>
        </div>
    </div>
    <!-- CTA Section -->
    <div>
        <div class="bg-gradient-to-r from-green-500/10 via-gray-800/80 to-green-500/10 border border-gray-700/50 rounded-2xl p-10 text-center">
            <h2 class="text-2xl md:text-3xl font-bold mb-4">Ready to Join the Battle?</h2>
            <p class="text-gray-400 mb-8 max-w-xl mx-auto">Connect with Steam and become part of our community today. See you on the battlefield!</p>
            @guest
            <a href="{{ route('auth.steam') }}" class="inline-flex items-center space-x-3 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 rounded-xl text-lg font-semibold transition-all duration-300 shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105">
                <svg class="w-6 h-6" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174l2.896-4.143c-.468-.116-.91-.293-1.317-.525L4.5 21.75c-.913-.288-1.772-.684-2.563-1.176l4.707-3.308c-.155-.369-.277-.758-.359-1.162L0 19.293V12C0 5.373 5.373 0 12 0zm0 4.5c-4.136 0-7.5 3.364-7.5 7.5 0 .768.115 1.509.328 2.206l3.908-2.745c.493-2.293 2.535-4.011 4.997-4.011 2.795 0 5.067 2.272 5.067 5.067 0 2.462-1.758 4.514-4.089 4.977l-2.725 3.896C9.788 22.285 10.869 22.5 12 22.5c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
                </svg>
                <span>Login with Steam</span>
            </a>
            @else
            <a href="{{ route('servers.show', config('services.battlemetrics.server_id', '0')) }}" class="inline-flex items-center space-x-3 px-8 py-4 bg-gradient-to-r from-green-600 to-emerald-600 hover:from-green-500 hover:to-emerald-500 rounded-xl text-lg font-semibold transition-all duration-300 shadow-lg shadow-green-500/25 hover:shadow-green-500/40 hover:scale-105">
                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
                <span>View Server</span>
            </a>
            @endguest
        </div>
    </div>
</div>
@push('scripts')
<script>
    async function fetchServerStatus() {
        try {
            const response = await fetch('{{ route("api.server.status") }}');
            const data = await response.json();
            const statusEl = document.getElementById('server-info');
            const indicatorEl = document.getElementById('status-indicator');
            const cardEl = document.getElementById('server-card');
            if (data.error) {
                statusEl.innerHTML = `<p class="text-gray-400 text-center py-4">Server status unavailable</p>`;
                return;
            }
            const isOnline = data.status === 'online';
            const statusColor = isOnline ? 'bg-green-500' : 'bg-red-500';
            const pingColor = isOnline ? 'bg-green-400' : 'bg-red-400';
            const playerPercent = Math.round((data.players / data.maxPlayers) * 100);
            // Add green glow when online
            if (isOnline) {
                cardEl.classList.add('shadow-[0_0_30px_rgba(34,197,94,0.15)]', 'ring-1', 'ring-green-500/20');
            }
            indicatorEl.innerHTML = `
                <span class="relative flex h-2 w-2 mr-2">
                    <span class="animate-ping absolute inline-flex h-full w-full rounded-full ${pingColor} opacity-75"></span>
                    <span class="relative inline-flex rounded-full h-2 w-2 ${statusColor}"></span>
                </span>
                <span class="text-xs font-medium ${isOnline ? 'text-green-400' : 'text-red-400'}">${isOnline ? 'ONLINE' : 'OFFLINE'}</span>
            `;
            // Update server name and gamemode
            document.getElementById('server-name').textContent = data.name || 'Unknown Server';
            document.getElementById('server-gamemode').textContent = data.scenario || 'Unknown';
            // Build platforms HTML
            const platformIcons = {
                pc: '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>',
                xbox: '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.102 21.033C6.211 22.881 8.977 24 12 24c3.026 0 5.789-1.119 7.902-2.967 1.877-1.912-4.316-8.709-7.902-11.417-3.582 2.708-9.779 9.505-7.898 11.417zm11.16-14.406c2.5 2.961 7.484 10.313 6.076 12.912C23.012 17.36 24 14.812 24 12c0-3.389-1.393-6.449-3.645-8.645-.146-.144-.293-.284-.441-.42l-.001-.001c-.151-.138-.304-.27-.46-.396a12.012 12.012 0 00-.844-.614c-.075-.051-.148-.104-.224-.153l-.017-.012A11.94 11.94 0 0012.001 0c-.127 0-.252.007-.378.012.178.023.349.066.518.115.055.016.113.026.168.044.055.017.109.038.163.058.13.047.258.1.384.157l.084.039c.038.018.074.04.112.059.096.049.189.102.281.157.09.052.178.107.265.164.039.026.079.05.117.076l.006.004c.057.039.113.08.169.121.226.164.443.34.651.527l.009.008-.001-.001-.001-.001-.003-.002-.007-.006c-2.402 2.152-5.468 6.477-5.468 6.477s-3.066-4.325-5.468-6.478l-.007.006-.003.002-.002.002-.001.001-.001.001.009-.008c.208-.188.426-.363.651-.527.056-.041.112-.082.169-.121l.006-.004c.038-.026.078-.05.117-.076.087-.057.175-.112.265-.164.092-.055.185-.108.281-.157.038-.019.074-.041.112-.059l.084-.039c.126-.057.254-.11.384-.157.054-.02.108-.041.163-.058.055-.018.113-.028.168-.044a3.56 3.56 0 01.518-.115A12.012 12.012 0 0012.001 0c-2.725 0-5.26.91-7.281 2.442l-.017.012c-.076.049-.149.102-.224.153-.295.195-.578.406-.844.614-.156.126-.309.258-.46.396l-.001.001c-.148.136-.295.276-.441.42C.393 5.551-1 8.611-1 12c0 2.812.988 5.36 2.662 7.539-1.408-2.599 3.576-9.951 6.076-12.912 1.038-1.231 2.204-2.363 3.262-2.927 1.058.564 2.224 1.696 3.262 2.927z"/></svg>',
                playstation: '<svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.985 2.596v17.548l3.915 1.261V6.688c0-.69.304-1.151.794-.991.636.181.76.814.76 1.505v5.876c2.441 1.193 4.362-.002 4.362-3.153 0-3.237-1.126-4.675-4.438-5.827-1.307-.448-3.728-1.186-5.391-1.502h-.002zm4.656 16.242l6.296-2.275c.715-.258.826-.625.246-.818-.586-.192-1.637-.139-2.357.123l-4.205 1.5v-2.385l.24-.085s1.201-.42 2.913-.615c1.696-.18 3.792.03 5.437.661 1.848.548 2.078 1.346 1.6 2.147-.477.8-1.639 1.261-1.639 1.261l-8.531 3.058v-2.572zm-9.112 2.593L.203 19.755c-.857-.439-.709-1.176.375-1.64l2.695-1.07v2.393l-2.025.769c-.727.277-.839.648-.25.842.59.193 1.64.15 2.364-.125l.166-.062v2.369l-.098.036c-1.258.396-2.485.392-3.502-.026z"/></svg>'
            };
            const platformsHtml = (data.platforms || ['pc']).map(p =>
                `<span class="inline-flex items-center gap-1 px-2 py-1 bg-gray-700/50 rounded text-xs text-gray-300">${platformIcons[p] || ''} ${p.toUpperCase()}</span>`
            ).join('');
            statusEl.innerHTML = `
                <div class="space-y-3">
                    <!-- Platforms -->
                    <div class="flex items-center gap-1">${platformsHtml}</div>
                    <!-- Player Count -->
                    <div>
                        <div class="flex items-center justify-between mb-2">
                            <span class="text-sm text-gray-400">Players: <span class="text-white font-semibold">${data.players}/${data.maxPlayers}</span></span>
                            <span class="text-xs text-gray-500">${playerPercent}% full</span>
                        </div>
                        <div class="w-full bg-gray-700/50 rounded-full h-2 overflow-hidden">
                            <div class="bg-gradient-to-r from-green-500 to-emerald-400 h-2 rounded-full transition-all duration-500" style="width: ${playerPercent}%"></div>
                        </div>
                    </div>
                    <!-- Quick Info -->
                    <div class="flex items-center justify-between text-sm">
                        <span class="text-gray-400">
                            <span class="inline-block w-2 h-2 bg-green-500 rounded-full mr-2"></span>
                            ${data.ip}:${data.port}
                        </span>
                        <a href="steam://connect/${data.ip}:${data.port}" class="inline-flex items-center space-x-2 px-4 py-2 bg-green-600 hover:bg-green-500 rounded-lg font-medium transition text-sm">
                            <span>Join Server</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 5l7 7m0 0l-7 7m7-7H3"/>
                            </svg>
                        </a>
                    </div>
                </div>
            `;
        } catch (error) {
            document.getElementById('server-info').innerHTML = `
                <p class="text-gray-400 text-center py-4">Could not fetch server status</p>
            `;
        }
    }
    fetchServerStatus();
    setInterval(fetchServerStatus, 60000);
</script>
@endpush
@endsection
