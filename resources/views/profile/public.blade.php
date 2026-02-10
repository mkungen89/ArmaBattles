@extends('layouts.app')

@section('title', $user->name . "'s Profile")

@section('content')
<div class="space-y-5">

    {{-- Profile Header --}}
    <x-profile.header :user="$user" :reputation="$reputation" :playerRating="$playerRating" :gameStats="$gameStats" :isOwner="$isOwner" />

    {{-- Achievement Showcase --}}
    <x-profile.achievement-showcase :showcaseAchievements="$showcaseAchievements" />

    {{-- Core Stats Grid --}}
    <x-profile.stats-grid :gameStats="$gameStats" :killsByVictimType="$killsByVictimType" :friendlyFireDealt="$friendlyFireDealt" :friendlyFireReceived="$friendlyFireReceived" />

    {{-- Two-Column Layout --}}
    <div class="grid lg:grid-cols-3 gap-5">
        {{-- Main Content (2/3) --}}
        <div class="lg:col-span-2 space-y-5">

            @if($gameStats)
                {{-- Hit Zone --}}
                <x-profile.hit-zone :gameStats="$gameStats" :hitZonesDealt="$hitZonesDealt" :hitZonesReceived="$hitZonesReceived" />

                {{-- Top Weapons --}}
                <x-profile.top-weapons :topWeapons="$topWeapons" :weaponImages="$weaponImages" />

                {{-- Analytics (XP + Vehicles) --}}
                <x-profile.analytics :xpByType="$xpByType" :vehicleStats="$vehicleStats" :gameStats="$gameStats" />
            @else
                {{-- No Game Stats --}}
                <div class="glass-card p-10">
                    <div class="text-center">
                        <div class="w-16 h-16 bg-white/5 rounded-2xl flex items-center justify-center mx-auto mb-4">
                            <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                            </svg>
                        </div>
                        <h3 class="text-lg font-semibold text-white mb-2">No Game Statistics</h3>
                        <p class="text-sm text-gray-400">This player hasn't linked their Arma ID or played on our servers yet.</p>
                    </div>
                </div>
            @endif

            {{-- Achievements --}}
            <x-profile.achievements-list :achievements="$achievements" :playerAchievements="$playerAchievements" :gameStats="$gameStats" />

            {{-- Kill Feed --}}
            <x-profile.kill-feed :recentKillEvents="$recentKillEvents" :weaponImages="$weaponImages" />

        </div>

        {{-- Sidebar (1/3) --}}
        <div class="lg:col-span-1">
            <x-profile.sidebar :user="$user" :team="$team" :stats="$stats" :recentMatches="$recentMatches" :isOwner="$isOwner" />
        </div>
    </div>

</div>
@endsection
