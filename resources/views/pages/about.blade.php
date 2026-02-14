@extends('layouts.app')

@section('title', 'About')

@section('content')
<div class="max-w-5xl mx-auto">
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">About Arma Battles</h1>
        <p class="text-xl text-gray-400">Community-driven Arma Reforger stats and tournaments</p>
    </div>

    {{-- Mission Statement --}}
    <div class="glass-card rounded-xl p-8 mb-8">
        <h2 class="text-2xl font-bold text-green-500 mb-4">Our Mission</h2>
        <p class="text-gray-300 text-lg leading-relaxed">
            Arma Battles is a community platform built by players, for players. We track your in-game performance,
            host competitive tournaments, and bring together the Arma Reforger community through statistics,
            leaderboards, and organized play.
        </p>
    </div>

    {{-- Features Grid --}}
    <div class="grid md:grid-cols-2 gap-6 mb-8">
        {{-- Player Stats --}}
        <div class="glass-card rounded-xl p-6">
            <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Comprehensive Player Stats</h3>
            <p class="text-gray-400">
                Track kills, deaths, accuracy, playtime, XP, distance traveled, and more. View detailed weapon statistics,
                hit zone analysis, and your performance over time.
            </p>
        </div>

        {{-- Tournaments --}}
        <div class="glass-card rounded-xl p-6">
            <div class="w-12 h-12 rounded-full bg-yellow-500/20 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Competitive Tournaments</h3>
            <p class="text-gray-400">
                Participate in organized tournaments with bracket generation, team registration, match scheduling,
                and referee oversight. Compete for glory and bragging rights.
            </p>
        </div>

        {{-- Teams --}}
        <div class="glass-card rounded-xl p-6">
            <div class="w-12 h-12 rounded-full bg-purple-500/20 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Team Management</h3>
            <p class="text-gray-400">
                Create or join teams (platoons), manage rosters, track team statistics, and compete together in tournaments.
                Build your unit's legacy.
            </p>
        </div>

        {{-- Ranked System --}}
        <div class="glass-card rounded-xl p-6">
            <div class="w-12 h-12 rounded-full bg-orange-500/20 flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-orange-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"></path>
                </svg>
            </div>
            <h3 class="text-xl font-semibold text-white mb-2">Competitive Ranking</h3>
            <p class="text-gray-400">
                Opt into competitive ranked mode powered by Glicko-2 rating system. Earn ranks from Bronze to Elite
                through tactical gameplay and objective-based scoring.
            </p>
        </div>
    </div>

    {{-- Technology Stack --}}
    <div class="glass-card rounded-xl p-8 mb-8">
        <h2 class="text-2xl font-bold text-green-500 mb-4">Built With</h2>
        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <h4 class="text-white font-semibold mb-2">Backend</h4>
                <ul class="text-gray-400 space-y-1 text-sm">
                    <li>• Laravel 12 (PHP)</li>
                    <li>• PostgreSQL Database</li>
                    <li>• Laravel Reverb (WebSocket)</li>
                    <li>• Redis Cache & Queue</li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-2">Frontend</h4>
                <ul class="text-gray-400 space-y-1 text-sm">
                    <li>• Tailwind CSS</li>
                    <li>• Alpine.js</li>
                    <li>• Chart.js</li>
                    <li>• Leaflet.js (Maps)</li>
                </ul>
            </div>
            <div>
                <h4 class="text-white font-semibold mb-2">Integrations</h4>
                <ul class="text-gray-400 space-y-1 text-sm">
                    <li>• BattleMetrics API</li>
                    <li>• Steam OpenID</li>
                    <li>• Discord OAuth</li>
                    <li>• Twitch/YouTube APIs</li>
                </ul>
            </div>
        </div>
    </div>

    {{-- Community Stats --}}
    <div class="grid md:grid-cols-4 gap-4 mb-8">
        <div class="glass-card rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-green-400 mb-1">{{ \App\Models\User::count() }}+</div>
            <div class="text-sm text-gray-400">Registered Players</div>
        </div>
        <div class="glass-card rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-blue-400 mb-1">{{ \App\Models\Team::count() }}+</div>
            <div class="text-sm text-gray-400">Active Teams</div>
        </div>
        <div class="glass-card rounded-xl p-6 text-center">
            <div class="text-3xl font-bold text-yellow-400 mb-1">{{ \App\Models\Tournament::count() }}+</div>
            <div class="text-sm text-gray-400">Tournaments Hosted</div>
        </div>
        <div class="glass-card rounded-xl p-6 text-center">
            @php
                $totalKills = \DB::table('player_kills')->count();
            @endphp
            <div class="text-3xl font-bold text-red-400 mb-1">{{ number_format($totalKills) }}</div>
            <div class="text-sm text-gray-400">Kills Tracked</div>
        </div>
    </div>

    {{-- Open Source --}}
    <div class="glass-card rounded-xl p-8 border border-green-500/20 bg-green-500/5">
        <div class="flex items-start gap-4">
            <div class="w-12 h-12 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-xl font-semibold text-white mb-2">Open Source</h3>
                <p class="text-gray-300 mb-4">
                    Arma Battles is open source and community-maintained. We welcome contributions, bug reports,
                    and feature suggestions from the community.
                </p>
                <a href="https://github.com/mkungen89/ArmaBattles" target="_blank" class="inline-flex items-center gap-2 text-green-400 hover:text-green-300 transition">
                    <span>View on GitHub</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                    </svg>
                </a>
            </div>
        </div>
    </div>

    {{-- Links --}}
    <div class="mt-8 text-center">
        <p class="text-gray-400 mb-4">Questions? Get in touch!</p>
        <div class="flex justify-center gap-4">
            <a href="{{ route('contact') }}" class="px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition">
                Contact Us
            </a>
            <a href="{{ route('faq') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white font-medium rounded-xl transition border border-white/10">
                View FAQ
            </a>
        </div>
    </div>
</div>
@endsection
