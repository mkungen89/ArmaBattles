@extends('admin.layout')

@section('admin-title', 'Edit Content Creator')

@section('admin-content')
<div class="max-w-3xl mx-auto space-y-6">
    {{-- Header --}}
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.creators.show', $creator) }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
            <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <div>
            <h1 class="text-2xl font-bold text-white">Edit Creator</h1>
            <p class="text-sm text-gray-400">{{ $creator->channel_name }}</p>
        </div>
    </div>

    {{-- Form --}}
    <div class="glass-card rounded-xl p-6">
        <form action="{{ route('admin.creators.update', $creator) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Channel Name --}}
            <div>
                <label for="channel_name" class="block text-sm font-medium text-white mb-2">Channel Name *</label>
                <input type="text" name="channel_name" id="channel_name" value="{{ old('channel_name', $creator->channel_name) }}" required class="w-full px-4 py-2 bg-white/3 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                @error('channel_name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Platform --}}
            <div>
                <label for="platform" class="block text-sm font-medium text-white mb-2">Platform *</label>
                <select name="platform" id="platform" required class="w-full px-4 py-2 bg-white/3 border border-white/10 rounded-lg text-white focus:outline-none focus:border-green-500">
                    <option value="twitch" {{ old('platform', $creator->platform) === 'twitch' ? 'selected' : '' }}>Twitch</option>
                    <option value="youtube" {{ old('platform', $creator->platform) === 'youtube' ? 'selected' : '' }}>YouTube</option>
                    <option value="tiktok" {{ old('platform', $creator->platform) === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                    <option value="kick" {{ old('platform', $creator->platform) === 'kick' ? 'selected' : '' }}>Kick</option>
                </select>
                @error('platform')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Channel URL --}}
            <div>
                <label for="channel_url" class="block text-sm font-medium text-white mb-2">Channel URL *</label>
                <input type="url" name="channel_url" id="channel_url" value="{{ old('channel_url', $creator->channel_url) }}" required class="w-full px-4 py-2 bg-white/3 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                @error('channel_url')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Bio --}}
            <div>
                <label for="bio" class="block text-sm font-medium text-white mb-2">Bio</label>
                <textarea name="bio" id="bio" rows="4" maxlength="500" class="w-full px-4 py-2 bg-white/3 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">{{ old('bio', $creator->bio) }}</textarea>
                <p class="mt-1 text-sm text-gray-400">Max 500 characters</p>
                @error('bio')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Follower Count --}}
            <div>
                <label for="follower_count" class="block text-sm font-medium text-white mb-2">Follower Count</label>
                <input type="number" name="follower_count" id="follower_count" value="{{ old('follower_count', $creator->follower_count) }}" min="0" class="w-full px-4 py-2 bg-white/3 border border-white/10 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500">
                @error('follower_count')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex items-center gap-3 pt-4">
                <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                    Save Changes
                </button>
                <a href="{{ route('admin.creators.show', $creator) }}" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
