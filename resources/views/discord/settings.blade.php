@extends('layouts.app')
@section('title', 'Discord Rich Presence Settings')
@section('content')
<div class="py-12 space-y-6">
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Discord Rich Presence</h1>
                <p class="text-gray-400">Show your activity on Discord</p>
            </div>
        </div>
        {{-- Info Card --}}
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-6">
            <div class="flex items-start gap-4">
                <svg class="w-12 h-12 text-blue-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189z"/>
                </svg>
                <div>
                    <h3 class="text-lg font-bold text-white mb-2">What is Discord Rich Presence?</h3>
                    <p class="text-sm text-gray-300 mb-3">
                        Discord Rich Presence displays your current activity on your Discord profile. Your friends can see:
                    </p>
                    <ul class="text-sm text-gray-400 space-y-1 list-disc list-inside">
                        <li>"Playing on [Server Name]" when you're in-game</li>
                        <li>"Watching [Tournament Name]" when viewing tournaments</li>
                        <li>"Browsing Community" when exploring the site</li>
                        <li>Player stats embed in your Discord profile</li>
                    </ul>
                </div>
            </div>
        </div>
        {{-- Current Status --}}
        @if($presence && $presence->enabled)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-xl font-bold text-white">Current Status</h2>
                <span class="flex items-center gap-2 px-3 py-1 bg-green-500/20 text-green-400 text-sm font-medium rounded-full">
                    <span class="w-2 h-2 bg-green-400 rounded-full animate-pulse"></span>
                    Active
                </span>
            </div>
            @if($presence->current_activity)
            <div class="bg-gray-900/50 rounded-lg p-4">
                <div class="flex items-start gap-4">
                    <div class="w-16 h-16 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-xl">
                        AR
                    </div>
                    <div class="flex-1">
                        <p class="font-bold text-white mb-1">{{ $presence->getActivityStatus() }}</p>
                        @if($presence->getActivityState())
                        <p class="text-sm text-gray-400">{{ $presence->getActivityState() }}</p>
                        @endif
                        @if($presence->started_at)
                        <p class="text-xs text-gray-500 mt-2">Started {{ $presence->started_at->diffForHumans() }}</p>
                        @endif
                    </div>
                </div>
            </div>
            @else
            <p class="text-gray-400 text-sm">No active presence. Visit a server or tournament page to set your status!</p>
            @endif
            @if($presence->discord_user_id)
            <div class="mt-4 pt-4 border-t border-gray-700">
                <p class="text-sm text-gray-400">
                    <strong class="text-white">Discord ID:</strong> {{ $presence->discord_user_id }}
                </p>
            </div>
            @endif
        </div>
        @endif
        {{-- Settings Form --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-white mb-4">Settings</h2>
            @if($presence && $presence->enabled)
            {{-- Disable Form --}}
            <form action="{{ route('discord.presence.disable') }}" method="POST">
                @csrf
                @method('DELETE')
                <div class="mb-6">
                    <p class="text-gray-300 mb-4">Discord Rich Presence is currently <strong class="text-green-400">enabled</strong>. Your activity will be shown on Discord.</p>
                </div>
                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-lg transition">
                    Disable Discord Presence
                </button>
            </form>
            @else
            {{-- Enable Form --}}
            <form action="{{ route('discord.presence.enable') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Discord User ID (Optional)
                    </label>
                    <input
                        type="text"
                        name="discord_user_id"
                        value="{{ old('discord_user_id', $presence->discord_user_id ?? '') }}"
                        placeholder="123456789012345678"
                        class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2"
                    >
                    <p class="mt-2 text-xs text-gray-500">
                        Your Discord User ID can be found in Discord User Settings → Advanced → Developer Mode (enable) → Right-click your profile → Copy User ID
                    </p>
                    @error('discord_user_id')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
                    <p class="text-sm text-yellow-300">
                        <strong>Note:</strong> Enabling this feature will allow your Discord status to update automatically based on your activity on this website.
                    </p>
                </div>
                <button type="submit" class="px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white font-semibold rounded-lg transition">
                    Enable Discord Presence
                </button>
            </form>
            @endif
        </div>
        {{-- How It Works --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h2 class="text-xl font-bold text-white mb-4">How It Works</h2>
            <div class="space-y-4">
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                        1
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Enable the Feature</h3>
                        <p class="text-sm text-gray-400">Toggle Discord Rich Presence on in your settings.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                        2
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Activity Tracking</h3>
                        <p class="text-sm text-gray-400">Your activity is automatically tracked as you browse servers and tournaments.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                        3
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Discord Integration</h3>
                        <p class="text-sm text-gray-400">Your status updates on Discord in real-time, showing what you're doing.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3">
                    <div class="w-8 h-8 bg-purple-600 rounded-full flex items-center justify-center text-white font-bold flex-shrink-0">
                        4
                    </div>
                    <div>
                        <h3 class="font-bold text-white mb-1">Privacy Control</h3>
                        <p class="text-sm text-gray-400">You can disable it anytime. Your activity is only visible to your Discord friends.</p>
                    </div>
                </div>
            </div>
        </div>
    </div>
</div>
@if(session('success'))
<div x-data="{ show: true }" x-show="show" x-init="setTimeout(() => show = false, 3000)" class="fixed bottom-4 right-4 bg-green-500 text-white px-6 py-3 rounded-lg shadow-lg">
    {{ session('success') }}
</div>
@endif
@endsection
