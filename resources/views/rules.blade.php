@extends('layouts.app')
@section('title', 'Server Rules')
@section('content')
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Server Rules</h1>
        <p class="text-gray-400">Please read and follow these rules to ensure a fair and enjoyable experience for everyone.</p>
    </div>
    {{-- Quick Navigation --}}
    <div class="flex flex-wrap items-center justify-center gap-3 mb-16">
        <a href="#general" class="px-4 py-2 bg-green-500/10 border border-green-500/30 text-green-400 rounded-xl text-sm font-medium hover:bg-green-500/20 transition">General</a>
        <a href="#gameplay" class="px-4 py-2 bg-blue-500/10 border border-blue-500/30 text-blue-400 rounded-xl text-sm font-medium hover:bg-blue-500/20 transition">Gameplay</a>
        <a href="#communication" class="px-4 py-2 bg-purple-500/10 border border-purple-500/30 text-purple-400 rounded-xl text-sm font-medium hover:bg-purple-500/20 transition">Communication</a>
        <a href="#punishments" class="px-4 py-2 bg-red-500/10 border border-red-500/30 text-red-400 rounded-xl text-sm font-medium hover:bg-red-500/20 transition">Punishments</a>
    </div>
    {{-- Rules Sections --}}
    <div class="space-y-8 pt-8">
        {{-- General Rules --}}
        <div id="general" class="glass-card rounded-xl p-6 scroll-mt-24">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">General Rules</h2>
            </div>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 font-bold">1.</span>
                    <span><strong class="text-white">Be respectful</strong> - Treat all players with respect. No harassment, discrimination, or toxic behavior.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 font-bold">2.</span>
                    <span><strong class="text-white">No cheating</strong> - Any form of cheating, hacking, or exploiting bugs is strictly prohibited.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 font-bold">3.</span>
                    <span><strong class="text-white">English in global chat</strong> - Please use English in global chat so everyone can understand.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 font-bold">4.</span>
                    <span><strong class="text-white">No spam</strong> - Avoid spamming chat, voice, or any other communication channels.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 font-bold">5.</span>
                    <span><strong class="text-white">Follow admin instructions</strong> - Always follow the instructions of server admins and moderators.</span>
                </li>
            </ul>
        </div>
        {{-- Gameplay Rules --}}
        <div id="gameplay" class="glass-card rounded-xl p-6 scroll-mt-24">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">Gameplay Rules</h2>
            </div>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-blue-400 font-bold">1.</span>
                    <span><strong class="text-white">Play the objective</strong> - Focus on capturing and defending bases. Work together with your team.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-400 font-bold">2.</span>
                    <span><strong class="text-white">No teamkilling</strong> - Intentional teamkilling will result in a ban. Accidents happen, but apologize.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-400 font-bold">3.</span>
                    <span><strong class="text-white">No base camping</strong> - Do not camp enemy spawn points or main bases.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-400 font-bold">4.</span>
                    <span><strong class="text-white">Use vehicles responsibly</strong> - Don't waste vehicles or use them for personal transport only.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-blue-400 font-bold">5.</span>
                    <span><strong class="text-white">No griefing</strong> - Don't intentionally sabotage your own team's efforts.</span>
                </li>
            </ul>
        </div>
        {{-- Communication Rules --}}
        <div id="communication" class="glass-card rounded-xl p-6 scroll-mt-24">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">Communication Rules</h2>
            </div>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-purple-400 font-bold">1.</span>
                    <span><strong class="text-white">Use appropriate channels</strong> - Use squad chat for squad communication, command chat for leadership.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-purple-400 font-bold">2.</span>
                    <span><strong class="text-white">No music or loud noises</strong> - Keep your microphone clear of background noise and music.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-purple-400 font-bold">3.</span>
                    <span><strong class="text-white">Communicate with your team</strong> - Call out enemy positions and coordinate with squadmates.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-purple-400 font-bold">4.</span>
                    <span><strong class="text-white">No advertising</strong> - Don't advertise other servers, websites, or services.</span>
                </li>
            </ul>
        </div>
        {{-- Punishments --}}
        <div id="punishments" class="glass-card border border-red-500/30 rounded-xl p-6 scroll-mt-24">
            <div class="flex items-center gap-3 mb-4">
                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <h2 class="text-xl font-bold text-white">Punishments</h2>
            </div>
            <p class="text-gray-300 mb-4">Breaking the rules may result in the following actions:</p>
            <div class="grid sm:grid-cols-3 gap-4">
                <div class="bg-white/3 rounded-lg p-4 text-center">
                    <div class="text-yellow-400 font-bold text-lg mb-1">Warning</div>
                    <p class="text-sm text-gray-400">First offense for minor violations</p>
                </div>
                <div class="bg-white/3 rounded-lg p-4 text-center">
                    <div class="text-orange-400 font-bold text-lg mb-1">Kick</div>
                    <p class="text-sm text-gray-400">Repeated violations or moderate offense</p>
                </div>
                <div class="bg-white/3 rounded-lg p-4 text-center">
                    <div class="text-red-400 font-bold text-lg mb-1">Ban</div>
                    <p class="text-sm text-gray-400">Severe violations or repeated offenses</p>
                </div>
            </div>
        </div>
        {{-- Appeal Info --}}
        <div class="glass-card rounded-xl p-6 text-center">
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Need to Appeal a Ban?</h3>
            <p class="text-gray-400 mb-4">If you believe you were banned unfairly, you can submit an appeal through our Discord server.</p>
            <a href="https://discord.gg/TqSGCNyp" target="_blank" rel="noopener" class="inline-flex items-center gap-2 px-6 py-3 bg-indigo-600 hover:bg-indigo-500 text-white font-medium rounded-xl transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
                Join Discord
            </a>
        </div>
    </div>
    {{-- Last Updated --}}
    <div class="text-center mt-8 text-sm text-gray-500">
        Last updated: February 7, 2025
    </div>
@endsection
