@extends('layouts.app')
@section('title', 'Privacy Policy')
@section('content')
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Privacy Policy</h1>
        <p class="text-gray-400">How we collect, use, and protect your information.</p>
    </div>
    <div class="space-y-8">
        {{-- Overview --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Overview</h2>
            <p class="text-gray-300">
                This privacy policy explains what data we collect when you use our website and game servers, and how that data is handled. We are a community-run project, not a corporation -- we keep things simple and only collect what we need to run the site.
            </p>
        </div>
        {{-- Steam Data --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Steam Authentication</h2>
            <p class="text-gray-300 mb-4">
                When you log in through Steam, we receive the following information from your public Steam profile:
            </p>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Steam ID</strong> -- Your unique Steam identifier, used to identify your account.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Display Name</strong> -- Your public Steam username, shown on your profile.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Avatar</strong> -- Your Steam profile picture, used as your avatar on the site.</span>
                </li>
            </ul>
            <p class="text-gray-400 mt-4 text-sm">
                We do not have access to your Steam password, email address, purchase history, or friends list. Authentication is handled entirely through Steam's official OpenID system.
            </p>
        </div>
        {{-- Game Data --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Game Data Collection</h2>
            <p class="text-gray-300 mb-4">
                When you play on our tracked Arma Reforger servers, we automatically collect gameplay data including:
            </p>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Player kills, deaths, and combat statistics</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Connection times and play sessions</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>XP gains, base captures, and other in-game events</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>In-game chat messages</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Your in-game player name and UUID</span>
                </li>
            </ul>
            <p class="text-gray-400 mt-4 text-sm">
                This data is used for leaderboards, player profiles, anti-cheat monitoring, and server administration. It is collected automatically when you connect to our servers.
            </p>
        </div>
        {{-- Two-Factor Authentication --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Two-Factor Authentication</h2>
            <p class="text-gray-300 mb-4">
                If you choose to enable two-factor authentication (2FA), we store the following additional data:
            </p>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">TOTP Secret</strong> -- An encrypted secret key used to generate time-based one-time passwords. This is stored encrypted at rest and is never exposed in plain text.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Recovery Codes</strong> -- A set of encrypted single-use backup codes for account recovery. These are stored encrypted and each code is deleted after use.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Activation Timestamp</strong> -- When 2FA was enabled on your account.</span>
                </li>
            </ul>
            <p class="text-gray-400 mt-4 text-sm">
                2FA is entirely optional and user-initiated. When you disable 2FA, all related data (secret key, recovery codes, and timestamp) is immediately and permanently deleted from our database. We also log 2FA events (enabled, disabled, recovery codes regenerated) in our audit log for security purposes.
            </p>
        </div>
        {{-- Cookies --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Cookies</h2>
            <p class="text-gray-300 mb-4">
                We use cookies for the following purposes:
            </p>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Session cookies</strong> -- Essential for keeping you logged in and maintaining your session. These are deleted when you close your browser or log out.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">CSRF token</strong> -- A security cookie that protects against cross-site request forgery attacks.</span>
                </li>
            </ul>
            <p class="text-gray-400 mt-4 text-sm">
                We do not use advertising cookies or third-party tracking cookies.
            </p>
        </div>
        {{-- Data Storage --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Data Storage & Security</h2>
            <p class="text-gray-300 mb-4">
                Your data is stored on our servers and backed up regularly. We use standard security practices to protect your information, including:
            </p>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Encrypted connections (HTTPS) for all traffic</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Passwords hashed using industry-standard algorithms</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Sensitive data (such as 2FA secrets and recovery codes) encrypted at rest</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span>Security-relevant actions logged in an audit trail</span>
                </li>
            </ul>
            <p class="text-gray-400 mt-4 text-sm">
                We do not sell, trade, or share your personal data with third parties.
            </p>
        </div>
        {{-- Your Rights --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Your Rights</h2>
            <p class="text-gray-300 mb-4">
                Under applicable privacy laws (including the EU General Data Protection Regulation and US state privacy laws such as the CCPA), you have the following rights:
            </p>
            <ul class="space-y-2 text-gray-300">
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Right to access</strong> -- You can view your personal data through your profile and settings pages at any time.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Right to rectification</strong> -- You can update your linked accounts and profile settings at any time.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Right to erasure</strong> -- You can request deletion of your account and all associated data by contacting an admin on Discord.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Right to restrict processing</strong> -- You can set your profile to private to limit how your data is displayed.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Right to data portability</strong> -- You can request a copy of your personal data by contacting an admin.</span>
                </li>
                <li class="flex items-start gap-3">
                    <span class="text-green-400 mt-1">&#8226;</span>
                    <span><strong class="text-white">Right to opt out of sale</strong> -- We do not sell your personal data. Period.</span>
                </li>
            </ul>
        </div>
        {{-- Data Removal --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Data Removal</h2>
            <p class="text-gray-300">
                If you would like your account and associated data removed, please contact a server admin on Discord. We will delete your account information upon request. Note that anonymized game statistics (such as aggregate server stats) may be retained.
            </p>
        </div>
        {{-- Changes --}}
        <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-green-500 mb-4">Changes to This Policy</h2>
            <p class="text-gray-300">
                We may update this privacy policy from time to time. Any changes will be reflected on this page with an updated date below. Continued use of the site after changes constitutes acceptance of the updated policy.
            </p>
        </div>
    </div>
    {{-- Last Updated --}}
    <div class="text-center mt-8 text-sm text-gray-500">
        Last updated: {{ now()->format('F j, Y') }}
    </div>
@endsection
