@extends('layouts.app')
@section('title', 'FAQ')
@section('content')
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Frequently Asked Questions</h1>
        <p class="text-gray-400">Got questions? We have answers. If you can't find what you're looking for, reach out on Discord.</p>
    </div>
    {{-- Getting Started --}}
    <div class="space-y-6">
        <h2 class="text-2xl font-bold text-green-500">Getting Started</h2>
        <div class="space-y-3" x-data="{ open: null }">
            {{-- Q1 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">What is this community about?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    We are an Arma Reforger gaming community that runs public and competitive servers. We track player stats, host tournaments, and provide a place for players to form platoons and compete together.
                </div>
            </div>
            {{-- Q2 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How do I create an account?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    There are two ways to create an account:
                    <ul class="list-disc list-inside mt-2 space-y-1">
                        <li><strong class="text-white">Email & Password</strong> — Click <strong class="text-white">"Register"</strong> in the top-right corner and fill in your name, email, and password.</li>
                        <li><strong class="text-white">Steam</strong> — Click the <strong class="text-white">Steam icon</strong> to log in with your Steam account. We only receive your public profile information — we never see your Steam password.</li>
                    </ul>
                    Both methods give you full access to the site. If you register with email, you can still link your Steam and Arma Reforger accounts later from your profile settings.
                </div>
            </div>
            {{-- Q3 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">Is it free to join?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Yes, everything is completely free. Our servers, tournaments, and all website features are available at no cost.
                </div>
            </div>
        </div>
    </div>
    {{-- Player Stats & UUID --}}
    <div class="space-y-6 mt-10">
        <h2 class="text-2xl font-bold text-green-500">Player Stats & Linking Your Account</h2>
        <div class="space-y-3" x-data="{ open: null }">
            {{-- Q4 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">What is a Player UUID and why do I need to link it?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Your Player UUID is a unique identifier assigned to you by Arma Reforger. Linking it to your website account lets us display your in-game stats (kills, deaths, playtime, XP, etc.) on your profile. You can link your UUID from the <strong class="text-white">Profile Settings</strong> page after logging in.
                </div>
            </div>
            {{-- Q5 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How do I find my Arma Reforger UUID?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Your UUID can be found in your Arma Reforger profile or by asking a server admin. It is a long string of characters that looks something like <code class="bg-gray-700 px-2 py-0.5 rounded text-green-400 text-sm">a1b2c3d4-e5f6-7890-abcd-ef1234567890</code>. You only need to link it once.
                </div>
            </div>
            {{-- Q6 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How are stats tracked?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Our servers automatically record game events such as kills, deaths, base captures, XP gains, healing, and more. These are aggregated on your profile and used for leaderboards. Stats update in near real-time while you play on our tracked servers.
                </div>
            </div>
        </div>
    </div>
    {{-- Tournaments --}}
    <div class="space-y-6 mt-10">
        <h2 class="text-2xl font-bold text-green-500">Tournaments</h2>
        <div class="space-y-3" x-data="{ open: null }">
            {{-- Q7 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How do I register for a tournament?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    First, you need to be part of a platoon. Your platoon captain can register the team for any open tournament from the tournament page. Registration must be approved by an admin before the bracket is generated.
                </div>
            </div>
            {{-- Q8 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">What tournament formats are available?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    We support several formats: <strong class="text-white">Single Elimination</strong>, <strong class="text-white">Double Elimination</strong>, <strong class="text-white">Round Robin</strong>, and <strong class="text-white">Swiss</strong>. The format is chosen by the tournament organizer when creating the event.
                </div>
            </div>
            {{-- Q9 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How does match check-in work?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Before a scheduled match, both teams need to check in to confirm they are ready. Any team member can check in on behalf of their platoon. If a team fails to check in, the match may be forfeited.
                </div>
            </div>
        </div>
    </div>
    {{-- Platoons --}}
    <div class="space-y-6 mt-10">
        <h2 class="text-2xl font-bold text-green-500">Platoons</h2>
        <div class="space-y-3" x-data="{ open: null }">
            {{-- Q10 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How do I create or join a platoon?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    To <strong class="text-white">create</strong> a platoon, log in and go to the Platoons page, then click "Create Platoon." To <strong class="text-white">join</strong> an existing one, browse the platoon listings and apply to any that are recruiting. You can also be invited directly by a platoon captain.
                </div>
            </div>
            {{-- Q11 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">Can I be in multiple platoons?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    No, each player can only belong to one platoon at a time. If you want to join a different platoon, you will need to leave your current one first.
                </div>
            </div>
            {{-- Q12 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">What roles exist within a platoon?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Platoons have a <strong class="text-white">Captain</strong> who manages the team (inviting, kicking, registering for tournaments), <strong class="text-white">Officers</strong> who can help with some management tasks, and <strong class="text-white">Members</strong> who make up the core roster.
                </div>
            </div>
        </div>
    </div>
    {{-- General / Technical --}}
    <div class="space-y-6 mt-10">
        <h2 class="text-2xl font-bold text-green-500">General</h2>
        <div class="space-y-3" x-data="{ open: null }">
            {{-- Q13 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 1 ? null : 1" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How do I report a player?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 1 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 1" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    If you encounter a player breaking the rules, please report them on our Discord server with evidence (screenshots or video). Server admins can also review player logs through the admin panel.
                </div>
            </div>
            {{-- Q14 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 2 ? null : 2" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">How do I connect to the server?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 2 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 2" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Visit the <a href="{{ route('home') }}" class="text-green-400 hover:text-green-300 underline">Server page</a> to see live server status, player count, and installed mods. You can find the server in the Arma Reforger server browser or connect directly using the server IP shown on the page.
                </div>
            </div>
            {{-- Q15 --}}
            <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl">
                <button @click="open = open === 3 ? null : 3" class="w-full flex items-center justify-between p-6 text-left">
                    <span class="font-semibold text-white">I found a bug on the website. What should I do?</span>
                    <svg class="w-5 h-5 text-green-500 transition-transform duration-200" :class="open === 3 ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </button>
                <div x-show="open === 3" x-collapse x-cloak class="px-6 pb-6 text-gray-300">
                    Please report any website bugs or issues on our Discord. Include what page you were on, what you expected to happen, and what actually happened. Screenshots are always helpful.
                </div>
            </div>
        </div>
    </div>
    {{-- Still Need Help --}}
    <div class="mt-12 bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6 text-center">
        <h3 class="text-lg font-bold text-white mb-2">Still have questions?</h3>
        <p class="text-gray-400">Join our Discord server and ask in the support channel. Our community and staff are happy to help.</p>
    </div>
    {{-- Last Updated --}}
    <div class="text-center mt-8 text-sm text-gray-500">
        Last updated: {{ now()->format('F j, Y') }}
    </div>
@endsection
