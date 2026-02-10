@extends('layouts.app')
@section('title', 'Profile Settings')
@section('content')
<div class="space-y-5">
    <div class="flex items-center gap-4">
        <a href="{{ route('profile') }}" class="p-2 glass-card hover:bg-white/10 transition rounded-xl">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Profile Settings</h1>
    </div>
    @if(session('success'))
    <div class="bg-green-500/15 border border-green-500/30 text-green-400 px-4 py-3 rounded-xl text-sm">
        {{ session('success') }}
    </div>
    @endif
    @if(session('error'))
    <div class="bg-red-500/15 border border-red-500/30 text-red-400 px-4 py-3 rounded-xl text-sm">
        {{ session('error') }}
    </div>
    @endif

    {{-- Custom Avatar --}}
    <div class="glass-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Avatar</h3>
                <p class="text-gray-500 text-xs mb-4">
                    Upload a custom avatar to replace your Steam profile picture. Remove it to revert to your Steam avatar.
                </p>
                <div class="flex items-center gap-4 mb-4">
                    <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-2xl ring-2 ring-white/10">
                    <div>
                        @if($user->custom_avatar)
                            <span class="px-2 py-0.5 bg-blue-500/15 text-blue-400 text-xs font-medium rounded-lg">Custom avatar</span>
                        @else
                            <span class="px-2 py-0.5 bg-white/5 text-gray-400 text-xs font-medium rounded-lg">Steam avatar</span>
                        @endif
                    </div>
                </div>
                <form action="{{ route('profile.upload-avatar') }}" method="POST" enctype="multipart/form-data" class="mb-3">
                    @csrf
                    <div class="mb-3">
                        <input type="file"
                               name="avatar"
                               accept="image/jpeg,image/png,image/webp"
                               class="block w-full text-sm text-gray-400 file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:text-sm file:font-medium file:bg-blue-600 file:text-white hover:file:bg-blue-500 file:cursor-pointer">
                        @error('avatar')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                        <p class="mt-2 text-[10px] text-gray-600">Recommended: 200 x 200 px. JPG, PNG or WebP. Max 512 KB.</p>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition text-sm font-medium">
                        Upload Avatar
                    </button>
                </form>
                @if($user->custom_avatar)
                <form action="{{ route('profile.remove-avatar') }}" method="POST" class="inline">
                    @csrf
                    @method('DELETE')
                    <button type="submit" class="px-4 py-2 bg-red-600/80 hover:bg-red-500 text-white rounded-xl transition text-sm font-medium"
                            onclick="return confirm('Remove custom avatar and revert to Steam avatar?')">
                        Remove Custom Avatar
                    </button>
                </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Link Arma Reforger ID --}}
    <div class="glass-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-green-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Arma Reforger ID</h3>
                <p class="text-gray-500 text-xs mb-4">
                    Link your Arma Reforger player ID to see your game statistics on your profile.
                </p>
                @if($user->player_uuid)
                    <div class="bg-white/3 rounded-xl p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div>
                                <p class="text-[10px] text-gray-500 uppercase">Currently linked ID</p>
                                <p class="text-white font-mono text-sm mt-1">{{ $user->player_uuid }}</p>
                            </div>
                            <span class="px-2 py-0.5 bg-green-500/15 text-green-400 text-xs font-medium rounded-lg">Linked</span>
                        </div>
                    </div>
                    @if($gameStats)
                    <div class="bg-white/3 rounded-xl p-4 mb-4">
                        <p class="text-[10px] text-gray-500 uppercase mb-2">Quick Stats</p>
                        <div class="flex gap-4 text-sm">
                            <span><span class="text-green-400 font-bold">{{ number_format($gameStats->kills) }}</span> <span class="text-gray-500">kills</span></span>
                            <span><span class="text-red-400 font-bold">{{ number_format($gameStats->deaths) }}</span> <span class="text-gray-500">deaths</span></span>
                            <span><span class="text-yellow-400 font-bold">{{ number_format($gameStats->headshots) }}</span> <span class="text-gray-500">headshots</span></span>
                        </div>
                    </div>
                    @endif
                    <form action="{{ route('profile.unlink-arma-id') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600/80 hover:bg-red-500 text-white rounded-xl transition text-sm font-medium"
                                onclick="return confirm('Are you sure you want to unlink your Arma ID?')">
                            Unlink Arma ID
                        </button>
                    </form>
                @else
                    <form action="{{ route('profile.link-arma-id') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="player_uuid" class="block text-xs font-medium text-gray-400 mb-2">
                                Your Arma Reforger ID
                            </label>
                            <input type="text"
                                   name="player_uuid"
                                   id="player_uuid"
                                   value="{{ old('player_uuid') }}"
                                   placeholder="xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 font-mono text-sm focus:ring-green-500 focus:border-green-500 placeholder-gray-600 @error('player_uuid') border-red-500/50 @enderror">
                            @error('player_uuid')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-[10px] text-gray-600">
                                Format: xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx
                            </p>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm font-medium">
                            Link Arma ID
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Link Discord --}}
    <div class="glass-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-indigo-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Discord</h3>
                <p class="text-gray-500 text-xs mb-4">
                    Link your Discord account so other players can find and contact you.
                </p>
                @if($user->discord_username)
                    <div class="bg-white/3 rounded-xl p-4 mb-4">
                        <div class="flex items-center justify-between">
                            <div class="flex items-center gap-3">
                                <svg class="w-5 h-5 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M20.317 4.37a19.791 19.791 0 0 0-4.885-1.515.074.074 0 0 0-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 0 0-5.487 0 12.64 12.64 0 0 0-.617-1.25.077.077 0 0 0-.079-.037A19.736 19.736 0 0 0 3.677 4.37a.07.07 0 0 0-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 0 0 .031.057 19.9 19.9 0 0 0 5.993 3.03.078.078 0 0 0 .084-.028 14.09 14.09 0 0 0 1.226-1.994.076.076 0 0 0-.041-.106 13.107 13.107 0 0 1-1.872-.892.077.077 0 0 1-.008-.128 10.2 10.2 0 0 0 .372-.292.074.074 0 0 1 .077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 0 1 .078.01c.12.098.246.198.373.292a.077.077 0 0 1-.006.127 12.299 12.299 0 0 1-1.873.892.077.077 0 0 0-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 0 0 .084.028 19.839 19.839 0 0 0 6.002-3.03.077.077 0 0 0 .032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 0 0-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                                </svg>
                                <div>
                                    <p class="text-white font-medium text-sm">{{ $user->discord_username }}</p>
                                    @if($user->discord_id)
                                    <p class="text-[10px] text-gray-600">ID: {{ $user->discord_id }}</p>
                                    @endif
                                </div>
                            </div>
                            <span class="px-2 py-0.5 bg-indigo-500/15 text-indigo-400 text-xs font-medium rounded-lg">Linked</span>
                        </div>
                    </div>
                    <form action="{{ route('profile.unlink-discord') }}" method="POST" class="inline">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600/80 hover:bg-red-500 text-white rounded-xl transition text-sm font-medium"
                                onclick="return confirm('Are you sure you want to unlink your Discord?')">
                            Unlink Discord
                        </button>
                    </form>
                @else
                    <form action="{{ route('profile.link-discord') }}" method="POST">
                        @csrf
                        <div class="mb-4">
                            <label for="discord_username" class="block text-xs font-medium text-gray-400 mb-2">
                                Discord Username
                            </label>
                            <input type="text"
                                   name="discord_username"
                                   id="discord_username"
                                   value="{{ old('discord_username') }}"
                                   placeholder="username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 text-sm focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-600 @error('discord_username') border-red-500/50 @enderror">
                            @error('discord_username')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-[10px] text-gray-600">
                                Your Discord username (without the @)
                            </p>
                        </div>
                        <div class="mb-4">
                            <label for="discord_id" class="block text-xs font-medium text-gray-400 mb-2">
                                Discord ID <span class="text-gray-600">(optional)</span>
                            </label>
                            <input type="text"
                                   name="discord_id"
                                   id="discord_id"
                                   value="{{ old('discord_id') }}"
                                   placeholder="123456789012345678"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-3 font-mono text-sm focus:ring-indigo-500 focus:border-indigo-500 placeholder-gray-600 @error('discord_id') border-red-500/50 @enderror">
                            @error('discord_id')
                            <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                            <p class="mt-2 text-[10px] text-gray-600">
                                Enable Developer Mode in Discord, right-click your name, and select "Copy User ID"
                            </p>
                        </div>
                        <button type="submit" class="px-4 py-2 bg-indigo-600 hover:bg-indigo-500 text-white rounded-xl transition text-sm font-medium">
                            Link Discord
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Social Media Links --}}
    <div class="glass-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-pink-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-pink-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13.828 10.172a4 4 0 00-5.656 0l-4 4a4 4 0 105.656 5.656l1.102-1.101m-.758-4.899a4 4 0 005.656 0l4-4a4 4 0 00-5.656-5.656l-1.1 1.1"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Social Media Links</h3>
                <p class="text-gray-500 text-xs mb-4">
                    Add your social media profiles so other players can find and follow you.
                </p>
                <form action="{{ route('profile.update-social-links') }}" method="POST">
                    @csrf
                    @php $socialLinks = $user->social_links ?? []; @endphp
                    <div class="space-y-3">
                        {{-- Twitch --}}
                        <div>
                            <label for="twitch" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-purple-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>
                                Twitch
                            </label>
                            <input type="url" name="twitch" id="twitch"
                                   value="{{ old('twitch', $socialLinks['twitch'] ?? '') }}"
                                   placeholder="https://twitch.tv/username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-purple-500 focus:border-purple-500 placeholder-gray-600 @error('twitch') border-red-500/50 @enderror">
                            @error('twitch')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- YouTube --}}
                        <div>
                            <label for="youtube" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-red-500" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                                YouTube
                            </label>
                            <input type="url" name="youtube" id="youtube"
                                   value="{{ old('youtube', $socialLinks['youtube'] ?? '') }}"
                                   placeholder="https://youtube.com/@channel"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-red-500 focus:border-red-500 placeholder-gray-600 @error('youtube') border-red-500/50 @enderror">
                            @error('youtube')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- TikTok --}}
                        <div>
                            <label for="tiktok" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-white" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                                TikTok
                            </label>
                            <input type="url" name="tiktok" id="tiktok"
                                   value="{{ old('tiktok', $socialLinks['tiktok'] ?? '') }}"
                                   placeholder="https://tiktok.com/@username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-pink-500 focus:border-pink-500 placeholder-gray-600 @error('tiktok') border-red-500/50 @enderror">
                            @error('tiktok')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Kick --}}
                        <div>
                            <label for="kick" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M1.333 0C.597 0 0 .597 0 1.333v21.334C0 23.403.597 24 1.333 24h21.334c.736 0 1.333-.597 1.333-1.333V1.333C24 .597 23.403 0 22.667 0H1.333zm7.334 4h2.666v5.333L14 6h3.333l-4 4.667L17.667 18h-3.334l-2.666-5.333L10 14v4H7.333V4h1.334z"/></svg>
                                Kick
                            </label>
                            <input type="url" name="kick" id="kick"
                                   value="{{ old('kick', $socialLinks['kick'] ?? '') }}"
                                   placeholder="https://kick.com/username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-green-500 focus:border-green-500 placeholder-gray-600 @error('kick') border-red-500/50 @enderror">
                            @error('kick')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Twitter/X --}}
                        <div>
                            <label for="twitter" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                                X (Twitter)
                            </label>
                            <input type="url" name="twitter" id="twitter"
                                   value="{{ old('twitter', $socialLinks['twitter'] ?? '') }}"
                                   placeholder="https://x.com/username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-gray-500 focus:border-gray-500 placeholder-gray-600 @error('twitter') border-red-500/50 @enderror">
                            @error('twitter')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Facebook --}}
                        <div>
                            <label for="facebook" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-blue-500" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                                Facebook
                            </label>
                            <input type="url" name="facebook" id="facebook"
                                   value="{{ old('facebook', $socialLinks['facebook'] ?? '') }}"
                                   placeholder="https://facebook.com/username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-blue-500 focus:border-blue-500 placeholder-gray-600 @error('facebook') border-red-500/50 @enderror">
                            @error('facebook')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        {{-- Instagram --}}
                        <div>
                            <label for="instagram" class="flex items-center gap-2 text-xs font-medium text-gray-400 mb-1.5">
                                <svg class="w-3.5 h-3.5 text-pink-500" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 100-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 11-2.88 0 1.441 1.441 0 012.88 0z"/></svg>
                                Instagram
                            </label>
                            <input type="url" name="instagram" id="instagram"
                                   value="{{ old('instagram', $socialLinks['instagram'] ?? '') }}"
                                   placeholder="https://instagram.com/username"
                                   class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2.5 text-sm focus:ring-pink-500 focus:border-pink-500 placeholder-gray-600 @error('instagram') border-red-500/50 @enderror">
                            @error('instagram')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    <button type="submit" class="mt-5 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm font-medium">
                        Save Social Links
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- How to find your ID --}}
    <div class="glass-card p-6" x-data="{ platform: 'pc' }">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">How to find your Arma Reforger ID</h3>
        {{-- Platform Tabs --}}
        <div class="flex gap-2 mb-5">
            <button @click="platform = 'pc'"
                    :class="platform === 'pc' ? 'bg-green-500/15 text-green-400 border-green-500/30' : 'bg-white/3 text-gray-400 border-white/6 hover:text-white'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium transition">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M20 18c1.1 0 1.99-.9 1.99-2L22 6c0-1.1-.9-2-2-2H4c-1.1 0-2 .9-2 2v10c0 1.1.9 2 2 2H0v2h24v-2h-4zM4 6h16v10H4V6z"/></svg>
                PC
            </button>
            <button @click="platform = 'xbox'"
                    :class="platform === 'xbox' ? 'bg-green-500/15 text-green-400 border-green-500/30' : 'bg-white/3 text-gray-400 border-white/6 hover:text-white'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium transition">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M4.102 21.033C6.211 22.881 8.977 24 12 24c3.026 0 5.789-1.119 7.902-2.967 1.877-1.912-4.316-8.709-7.902-11.417-3.582 2.708-9.779 9.505-7.898 11.417zm11.16-14.406c2.5 2.961 7.484 10.313 6.076 12.912C23.012 17.36 24 14.812 24 12c0-3.389-1.393-6.449-3.645-8.645a12.012 12.012 0 00-.844-.614A11.94 11.94 0 0012.001 0c2.079 1.344 4.353 4.93 3.261 6.627zM8.738 6.627C7.646 4.93 9.92 1.344 11.999 0A11.94 11.94 0 005.387 2.741a12.012 12.012 0 00-.844.614C2.393 5.551 0 8.611 0 12c0 2.812.988 5.36 2.662 7.539 1.408-2.599 3.576-9.951 6.076-12.912z"/></svg>
                Xbox
            </button>
            <button @click="platform = 'ps'"
                    :class="platform === 'ps' ? 'bg-green-500/15 text-green-400 border-green-500/30' : 'bg-white/3 text-gray-400 border-white/6 hover:text-white'"
                    class="flex items-center gap-2 px-3 py-1.5 rounded-lg border text-xs font-medium transition">
                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M8.985 2.596v17.548l3.915 1.261V6.688c0-.69.304-1.151.794-.991.636.181.76.814.76 1.505v5.876c2.441 1.193 4.362-.002 4.362-3.153 0-3.237-1.126-4.675-4.438-5.827-1.307-.448-3.728-1.186-5.391-1.502h-.002z"/></svg>
                PlayStation
            </button>
        </div>
        {{-- PC Guide --}}
        <div x-show="platform === 'pc'" x-transition>
            <div class="space-y-3 text-xs text-gray-400">
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">1</span>
                    <p>Open Arma Reforger and go to the <strong class="text-white">Main Menu</strong>.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">2</span>
                    <p>Click on your <strong class="text-white">Profile</strong> (your player name in the top-right corner).</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">3</span>
                    <p>Your <strong class="text-white">Identity ID</strong> is displayed as a UUID: <code class="bg-white/5 px-1.5 py-0.5 rounded font-mono text-[10px]">xxxxxxxx-xxxx-xxxx-xxxx-xxxxxxxxxxxx</code></p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">4</span>
                    <p>Click the <strong class="text-white">copy icon</strong> next to the ID, then paste it into the field above.</p>
                </div>
            </div>
            <div class="mt-3 p-3 bg-blue-500/10 border border-blue-500/20 rounded-xl">
                <p class="text-blue-400 text-xs">
                    <strong>Tip:</strong> You can also find your ID in the console log files at <code class="bg-white/5 px-1 rounded text-[10px]">%localappdata%\ArmaReforger\profile</code>.
                </p>
            </div>
        </div>
        {{-- Xbox Guide --}}
        <div x-show="platform === 'xbox'" x-transition>
            <div class="space-y-3 text-xs text-gray-400">
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">1</span>
                    <p>Launch Arma Reforger on your Xbox and navigate to the <strong class="text-white">Main Menu</strong>.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">2</span>
                    <p>Open the <strong class="text-white">Profile</strong> section from the menu.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">3</span>
                    <p>Your <strong class="text-white">Identity ID</strong> (UUID) will be displayed on the profile screen.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">4</span>
                    <p>Use the <strong class="text-white">Xbox App</strong> on your phone: take a screenshot (<strong class="text-white">Xbox button > Y</strong>), then type the ID here.</p>
                </div>
            </div>
            <div class="mt-3 p-3 bg-green-500/10 border border-green-500/20 rounded-xl">
                <p class="text-green-400 text-xs">
                    <strong>Tip:</strong> You can also ask a server admin to look up your UUID from the server logs.
                </p>
            </div>
        </div>
        {{-- PlayStation Guide --}}
        <div x-show="platform === 'ps'" x-transition>
            <div class="space-y-3 text-xs text-gray-400">
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">1</span>
                    <p>Launch Arma Reforger on your PlayStation and go to the <strong class="text-white">Main Menu</strong>.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">2</span>
                    <p>Open the <strong class="text-white">Profile</strong> section from the menu.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">3</span>
                    <p>Your <strong class="text-white">Identity ID</strong> (UUID) will be displayed on the profile screen.</p>
                </div>
                <div class="flex gap-3">
                    <span class="flex-shrink-0 w-5 h-5 bg-green-500/15 text-green-400 rounded-full flex items-center justify-center text-[10px] font-bold">4</span>
                    <p>Take a screenshot (<strong class="text-white">Share button</strong>), use the <strong class="text-white">PlayStation App</strong> to view it and type the ID here.</p>
                </div>
            </div>
            <div class="mt-3 p-3 bg-green-500/10 border border-green-500/20 rounded-xl">
                <p class="text-green-400 text-xs">
                    <strong>Tip:</strong> You can also ask a server admin to look up your UUID from the server logs.
                </p>
            </div>
        </div>
        {{-- What is a UUID --}}
        <div class="mt-5 p-4 bg-white/3 rounded-xl">
            <h4 class="text-xs font-semibold text-white mb-1.5">What is a UUID?</h4>
            <p class="text-xs text-gray-500">
                Your UUID (Universally Unique Identifier) is a unique code assigned to your Arma Reforger profile.
                It looks like: <code class="bg-white/5 px-1.5 py-0.5 rounded font-mono text-[10px] text-green-400">a1b2c3d4-e5f6-7890-abcd-ef1234567890</code>.
                It's how we match your in-game activity to your website profile.
            </p>
        </div>
        <div class="mt-3 p-3 bg-yellow-500/10 border border-yellow-500/20 rounded-xl">
            <p class="text-yellow-400 text-xs">
                <strong>Note:</strong> Make sure to enter your own ID. Entering someone else's ID will show their statistics instead of yours.
            </p>
        </div>
    </div>

    {{-- Account Info --}}
    <div class="glass-card p-6">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Account Information</h3>
        <div class="space-y-2.5 text-sm">
            <div class="flex justify-between items-center py-1.5 border-b border-white/3">
                <span class="text-gray-500 text-xs">Steam Name</span>
                <span class="text-white text-xs font-medium">{{ $user->name }}</span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-white/3">
                <span class="text-gray-500 text-xs">Steam ID</span>
                <span class="text-white font-mono text-xs">{{ $user->steam_id }}</span>
            </div>
            <div class="flex justify-between items-center py-1.5 border-b border-white/3">
                <span class="text-gray-500 text-xs">Role</span>
                <span class="text-white capitalize text-xs font-medium">{{ $user->role }}</span>
            </div>
            <div class="flex justify-between items-center py-1.5">
                <span class="text-gray-500 text-xs">Member since</span>
                <span class="text-white text-xs font-medium">{{ $user->created_at->format('F j, Y') }}</span>
            </div>
        </div>
    </div>

    {{-- Two-Factor Authentication --}}
    <div class="glass-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-yellow-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Two-Factor Authentication</h3>
                @if($user->hasTwoFactorEnabled())
                    <div class="flex items-center gap-2 mb-4 mt-2">
                        <span class="px-2 py-0.5 bg-green-500/15 text-green-400 text-xs font-medium rounded-lg">Enabled</span>
                        <span class="text-gray-500 text-xs">Added {{ $user->two_factor_confirmed_at->diffForHumans() }}</span>
                    </div>
                    <p class="text-gray-500 text-xs mb-4">
                        Your account is protected with two-factor authentication.
                    </p>
                    <div class="flex flex-wrap gap-2">
                        <a href="{{ route('two-factor.recovery-codes') }}" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-xs font-medium border border-white/6">
                            View Recovery Codes
                        </a>
                        <form action="{{ route('two-factor.regenerate-recovery-codes') }}" method="POST" class="inline">
                            @csrf
                            <button type="submit" class="px-3 py-1.5 bg-white/5 hover:bg-white/10 text-white rounded-xl transition text-xs font-medium border border-white/6"
                                    onclick="return confirm('This will invalidate your existing recovery codes. Continue?')">
                                Regenerate Recovery Codes
                            </button>
                        </form>
                    </div>
                    <div class="mt-4 pt-4 border-t border-white/5" x-data="{ showDisable: false }">
                        <button @click="showDisable = !showDisable" class="text-xs text-red-400 hover:text-red-300 transition">
                            Disable Two-Factor Authentication
                        </button>
                        <form x-show="showDisable" x-cloak action="{{ route('two-factor.disable') }}" method="POST" class="mt-3 space-y-3">
                            @csrf
                            @method('DELETE')
                            @if($user->password)
                            <div>
                                <label for="disable_password" class="block text-xs font-medium text-gray-400 mb-1">Confirm Password</label>
                                <input type="password" name="password" id="disable_password" required
                                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-600 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent text-sm">
                                @error('password')
                                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                                @enderror
                            </div>
                            @endif
                            <button type="submit" class="px-4 py-2 bg-red-600/80 hover:bg-red-500 text-white rounded-xl transition text-sm font-medium"
                                    onclick="return confirm('Are you sure you want to disable two-factor authentication?')">
                                Confirm Disable
                            </button>
                        </form>
                    </div>
                @else
                    <p class="text-gray-500 text-xs mb-4 mt-1">
                        Add an extra layer of security to your account. You'll need an authenticator app like Google Authenticator or Authy.
                    </p>
                    <form action="{{ route('two-factor.enable') }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm font-medium">
                            Enable Two-Factor Authentication
                        </button>
                    </form>
                @endif
            </div>
        </div>
    </div>

    {{-- Privacy & Notifications --}}
    <div class="glass-card p-6">
        <div class="flex items-start gap-4">
            <div class="w-10 h-10 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-1">Privacy & Notifications</h3>
                <p class="text-gray-500 text-xs mb-5">
                    Control who can see your profile and manage your notification preferences.
                </p>
                <form action="{{ route('profile.update-settings') }}" method="POST">
                    @csrf
                    {{-- Profile Visibility --}}
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-400 mb-2.5">Profile Visibility</label>
                        <div class="flex gap-3">
                            <label class="flex items-center gap-3 px-4 py-3 bg-white/3 rounded-xl border border-white/6 cursor-pointer hover:border-white/10 transition has-[:checked]:border-green-500/30 has-[:checked]:bg-green-500/5">
                                <input type="radio" name="profile_visibility" value="public"
                                       {{ ($user->profile_visibility ?? 'public') === 'public' ? 'checked' : '' }}
                                       class="text-green-500 focus:ring-green-500 bg-white/5 border-white/20">
                                <div>
                                    <span class="text-white text-xs font-medium">Public</span>
                                    <p class="text-gray-500 text-[10px] mt-0.5">Anyone can view your profile</p>
                                </div>
                            </label>
                            <label class="flex items-center gap-3 px-4 py-3 bg-white/3 rounded-xl border border-white/6 cursor-pointer hover:border-white/10 transition has-[:checked]:border-red-500/30 has-[:checked]:bg-red-500/5">
                                <input type="radio" name="profile_visibility" value="private"
                                       {{ ($user->profile_visibility ?? 'public') === 'private' ? 'checked' : '' }}
                                       class="text-red-500 focus:ring-red-500 bg-white/5 border-white/20">
                                <div>
                                    <span class="text-white text-xs font-medium">Private</span>
                                    <p class="text-gray-500 text-[10px] mt-0.5">Only you can view your profile</p>
                                </div>
                            </label>
                        </div>
                        @error('profile_visibility')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Notification Preferences --}}
                    <div class="mb-5">
                        <label class="block text-xs font-medium text-gray-400 mb-2.5">Notification Preferences</label>
                        <div class="space-y-2">
                            <label class="flex items-center justify-between px-4 py-3 bg-white/3 rounded-xl border border-white/6 cursor-pointer hover:border-white/10 transition">
                                <div>
                                    <span class="text-white text-xs font-medium">Link Arma ID Reminders</span>
                                    <p class="text-gray-500 text-[10px] mt-0.5">Get reminded to link your Arma Reforger ID</p>
                                </div>
                                <input type="checkbox" name="notify_link_arma_id" value="1"
                                       {{ data_get($user->notification_preferences, 'link_arma_id', true) ? 'checked' : '' }}
                                       class="rounded bg-white/5 border-white/20 text-green-500 focus:ring-green-500 focus:ring-offset-0">
                            </label>
                            <label class="flex items-center justify-between px-4 py-3 bg-white/3 rounded-xl border border-white/6 cursor-pointer hover:border-white/10 transition">
                                <div>
                                    <span class="text-white text-xs font-medium">Tournament Updates</span>
                                    <p class="text-gray-500 text-[10px] mt-0.5">Notifications about registrations, matches, and results</p>
                                </div>
                                <input type="checkbox" name="notify_tournament_updates" value="1"
                                       {{ data_get($user->notification_preferences, 'tournament_updates', true) ? 'checked' : '' }}
                                       class="rounded bg-white/5 border-white/20 text-green-500 focus:ring-green-500 focus:ring-offset-0">
                            </label>
                            <label class="flex items-center justify-between px-4 py-3 bg-white/3 rounded-xl border border-white/6 cursor-pointer hover:border-white/10 transition">
                                <div>
                                    <span class="text-white text-xs font-medium">Team Invites</span>
                                    <p class="text-gray-500 text-[10px] mt-0.5">Platoon invitations or application updates</p>
                                </div>
                                <input type="checkbox" name="notify_team_invites" value="1"
                                       {{ data_get($user->notification_preferences, 'team_invites', true) ? 'checked' : '' }}
                                       class="rounded bg-white/5 border-white/20 text-green-500 focus:ring-green-500 focus:ring-offset-0">
                            </label>
                        </div>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm font-medium">
                        Save Settings
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Competitive Mode --}}
    <div class="glass-card p-6">
        <div class="flex items-center gap-3 mb-4">
            <div class="w-10 h-10 rounded-lg bg-purple-500/15 flex items-center justify-center flex-shrink-0">
                <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
            </div>
            <div>
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Competitive Mode</h3>
                <p class="text-[10px] text-gray-500">Opt-in to the Glicko-2 skill rating system</p>
            </div>
        </div>

        @php $myRating = $user->playerRating @endphp

        @if($myRating && $myRating->opted_in_at)
            <div class="bg-purple-500/10 border border-purple-500/20 rounded-xl p-4 mb-4">
                <div class="flex items-center justify-between mb-3">
                    <span class="text-purple-400 font-medium text-sm">Competitive Mode Active</span>
                    @php $tier = \App\Models\PlayerRating::TIERS[$myRating->rank_tier] ?? \App\Models\PlayerRating::TIERS['unranked'] @endphp
                    @if($tier['icon'])
                        <img src="{{ $tier['icon'] }}" alt="{{ $tier['label'] }}" class="w-10 h-10 object-contain" title="{{ $tier['label'] }}">
                    @else
                        <span class="inline-flex items-center px-2 py-0.5 rounded-lg text-xs font-medium border {{ $tier['bg'] }} {{ $tier['color'] }}">
                            {{ $tier['label'] }}
                        </span>
                    @endif
                </div>
                <div class="grid grid-cols-3 gap-3 text-center">
                    <div class="bg-white/3 rounded-lg p-2">
                        <div class="text-white font-bold text-sm">{{ $myRating->is_placed ? number_format($myRating->rating, 0) : 'Placement ' . $myRating->placement_games . '/10' }}</div>
                        <div class="text-gray-500 text-[10px] uppercase">Rating</div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-2">
                        <div class="text-white font-bold text-sm">{{ $myRating->kd_ratio }}</div>
                        <div class="text-gray-500 text-[10px] uppercase">Ranked K/D</div>
                    </div>
                    <div class="bg-white/3 rounded-lg p-2">
                        <div class="text-white font-bold text-sm">{{ number_format($myRating->games_played) }}</div>
                        <div class="text-gray-500 text-[10px] uppercase">Games</div>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between">
                <a href="{{ route('ranked.show', $user) }}" class="text-xs text-purple-400 hover:text-purple-300 transition">View full rating profile &rarr;</a>
                <form action="{{ route('ranked.opt-out') }}" method="POST" onsubmit="return confirm('Disable competitive mode? Your rating will be preserved.')">
                    @csrf
                    <button type="submit" class="px-3 py-1.5 text-xs border border-white/10 text-gray-400 rounded-xl hover:border-red-500/30 hover:text-red-400 transition">
                        Disable Competitive
                    </button>
                </form>
            </div>
        @else
            <p class="text-gray-500 text-xs mb-4">
                Enable competitive mode to track your skill rating. Your PvP kills against other competitive players will affect your Glicko-2 rating. AI kills, team kills, and casual kills are not counted.
            </p>
            @if($user->hasLinkedArmaId())
                <form action="{{ route('ranked.opt-in') }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl transition text-sm font-medium">
                        Enable Competitive Mode
                    </button>
                </form>
            @else
                <p class="text-yellow-400 text-xs">You need to link your Arma Reforger ID above before enabling competitive mode.</p>
            @endif
        @endif
    </div>
</div>
@endsection
