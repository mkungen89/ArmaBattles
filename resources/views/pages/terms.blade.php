@extends('layouts.app')
@section('title', 'Terms of Service')
@section('content')
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Terms of Service</h1>
        <p class="text-gray-400">By using this website and our game servers, you agree to the following terms.</p>
    </div>
    <div class="space-y-8">
        {{-- Acceptance --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">1. Acceptance of Terms</h2>
            <p class="text-gray-300">
                By accessing this website or connecting to our game servers, you agree to be bound by these terms of service. If you do not agree with any part of these terms, you should not use our services. These terms apply to all users, including visitors, registered members, and tournament participants.
            </p>
        </div>
        {{-- Accounts --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">2. Accounts</h2>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Accounts are created through Steam authentication. You are responsible for any activity that occurs under your account.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>You may only have one account. Creating multiple accounts to evade bans or manipulate stats is prohibited.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>You must not impersonate other players, staff members, or community officials.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>We reserve the right to suspend or terminate accounts that violate these terms.</span>
                </li>
            </ul>
        </div>
        {{-- Fair Play --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">3. Fair Play</h2>
            <p class="text-gray-300 mb-4">
                All players on our servers are expected to play fairly. The following are strictly prohibited:
            </p>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Cheating</strong> -- Using hacks, exploits, aimbots, wallhacks, or any unauthorized third-party software that provides an unfair advantage.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Stat manipulation</strong> -- Artificially inflating stats through farming, boosting, or colluding with other players.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Bug exploitation</strong> -- Knowingly exploiting game bugs or glitches for an advantage instead of reporting them.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Griefing</strong> -- Intentionally ruining the experience for other players, including teamkilling, team sabotage, or toxic behavior.</span>
                </li>
            </ul>
        </div>
        {{-- Server Rules --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">4. Server Rules</h2>
            <p class="text-gray-300">
                In addition to these terms, all players must follow our <a href="{{ route('rules') }}" class="text-green-400 hover:text-green-300 underline">Server Rules</a>. Server-specific rules are enforced by our admin and moderator team. Violation of server rules may result in warnings, kicks, temporary bans, or permanent bans at admin discretion.
            </p>
        </div>
        {{-- Tournaments --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">5. Tournaments & Competitions</h2>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Tournament participants must be registered with a valid platoon and follow all tournament-specific rules.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Match schedules and results are final once confirmed by tournament admins.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Unsportsmanlike conduct during tournaments may result in disqualification and further sanctions.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Teams that fail to check in or show up for scheduled matches may forfeit the match.</span>
                </li>
            </ul>
        </div>
        {{-- Bans & Enforcement --}}
        <div class="glass-card border border-red-500/30 rounded-xl p-6">
            <h2 class="text-xl font-bold text-red-400 mb-4">6. Bans & Enforcement</h2>
            <ul class="space-y-3 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span>Admins may issue warnings, kicks, temporary bans, or permanent bans for rule violations.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span>Our servers use automated anti-cheat systems. Players flagged by anti-cheat may be banned automatically.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span>Ban appeals can be submitted through our Discord server. Appeals are reviewed by the admin team and decisions are final.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-red-400 mt-1">&#8226;</span>
                    <span>Attempting to evade a ban (new accounts, VPNs, etc.) will result in a permanent ban with no appeal.</span>
                </li>
            </ul>
        </div>
        {{-- Content --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">7. User Content</h2>
            <p class="text-gray-300">
                Any content you submit through our services (chat messages, platoon descriptions, etc.) must not contain illegal material, hate speech, explicit content, or personal information of others. We reserve the right to remove any content that violates these guidelines without notice.
            </p>
        </div>
        {{-- Liability --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">8. Limitation of Liability</h2>
            <p class="text-gray-300">
                This is a community-run project provided as-is. We make no guarantees about server uptime, data preservation, or service availability. We are not liable for any loss of data, stats, or progress. We do our best to keep things running smoothly, but stuff happens.
            </p>
        </div>
        {{-- Changes --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">9. Changes to These Terms</h2>
            <p class="text-gray-300">
                We may update these terms at any time. Changes take effect immediately when posted on this page. Continued use of our services after changes constitutes acceptance of the updated terms. We will make reasonable efforts to announce significant changes on Discord.
            </p>
        </div>
    </div>
    {{-- Last Updated --}}
    <div class="text-center mt-8 text-sm text-gray-500">
        Last updated: {{ now()->format('F j, Y') }}
    </div>
@endsection
