@extends('layouts.app')
@section('title', 'Edit Creator Profile')

@section('content')
<div class="py-12">
    <div class="max-w-3xl mx-auto space-y-6">
        {{-- Header --}}
        <div class="flex items-center gap-4">
            @if(auth()->user()->isAdmin() && auth()->user()->id !== $creator->user_id)
                <a href="{{ route('admin.creators.dashboard', $creator) }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @else
                <a href="{{ route('creator.dashboard') }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
                    <svg class="w-5 h-5 text-white" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
                </a>
            @endif
            <div>
                <h1 class="text-3xl font-bold text-white">Edit Profile</h1>
                <p class="text-gray-400">Update your creator information</p>
            </div>
        </div>

        {{-- Form --}}
        <div class="glass-card rounded-2xl p-8">
            <form action="{{ route('creator.update') }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                {{-- Channel Name --}}
                <div>
                    <label for="channel_name" class="block text-sm font-medium text-white mb-2">
                        Channel Name *
                    </label>
                    <input
                        type="text"
                        name="channel_name"
                        id="channel_name"
                        value="{{ old('channel_name', $creator->channel_name) }}"
                        required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition"
                        placeholder="Your channel name"
                    >
                    @error('channel_name')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Platform --}}
                <div>
                    <label for="platform" class="block text-sm font-medium text-white mb-2">
                        Platform *
                    </label>
                    <div class="relative">
                        <select
                            name="platform"
                            id="platform"
                            required
                            disabled
                            class="w-full px-4 py-3 bg-white/3 border border-white/10 rounded-xl text-gray-400 focus:outline-none appearance-none cursor-not-allowed"
                        >
                            <option value="{{ $creator->platform }}" selected>{{ $creator->platform_name }}</option>
                        </select>
                        <div class="absolute right-3 top-1/2 -translate-y-1/2 pointer-events-none">
                            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/></svg>
                        </div>
                    </div>
                    <p class="mt-2 text-sm text-gray-400">Platform cannot be changed after registration</p>
                </div>

                {{-- Channel URL --}}
                <div>
                    <label for="channel_url" class="block text-sm font-medium text-white mb-2">
                        Channel URL *
                    </label>
                    <input
                        type="url"
                        name="channel_url"
                        id="channel_url"
                        value="{{ old('channel_url', $creator->channel_url) }}"
                        required
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition"
                        placeholder="https://..."
                    >
                    @error('channel_url')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">Full URL to your channel</p>
                </div>

                {{-- Bio --}}
                <div>
                    <label for="bio" class="block text-sm font-medium text-white mb-2">
                        Bio
                    </label>
                    <textarea
                        name="bio"
                        id="bio"
                        rows="5"
                        maxlength="500"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition resize-none"
                        placeholder="Tell viewers about yourself and your content..."
                    >{{ old('bio', $creator->bio) }}</textarea>
                    <div class="flex items-center justify-between mt-2">
                        <p class="text-sm text-gray-400">Describe your content and what makes your channel unique</p>
                        <p class="text-sm text-gray-400"><span x-text="$el.parentElement.parentElement.querySelector('textarea').value.length">{{ strlen($creator->bio ?? '') }}</span>/500</p>
                    </div>
                    @error('bio')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                {{-- Follower Count --}}
                <div>
                    <label for="follower_count" class="block text-sm font-medium text-white mb-2">
                        Follower Count
                    </label>
                    <input
                        type="number"
                        name="follower_count"
                        id="follower_count"
                        value="{{ old('follower_count', $creator->follower_count) }}"
                        min="0"
                        class="w-full px-4 py-3 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition"
                        placeholder="0"
                    >
                    @error('follower_count')
                        <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-2 text-sm text-gray-400">Your current follower count on {{ $creator->platform_name }}</p>
                </div>

                {{-- Actions --}}
                <div class="flex items-center gap-4 pt-6 border-t border-white/10">
                    <button
                        type="submit"
                        class="px-6 py-3 bg-gradient-to-r from-green-600 to-green-500 hover:from-green-500 hover:to-green-400 text-white rounded-xl transition font-medium flex items-center gap-2"
                    >
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                        Save Changes
                    </button>
                    <a
                        href="{{ route('creator.dashboard') }}"
                        class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white rounded-xl transition font-medium"
                    >
                        Cancel
                    </a>
                </div>
            </form>
        </div>

        {{-- Current Info Card --}}
        <div class="glass-card rounded-2xl p-6">
            <h3 class="text-lg font-bold text-white mb-4">Current Information</h3>
            <div class="space-y-3">
                <div class="flex items-center justify-between py-2 border-b border-white/5">
                    <span class="text-gray-400">Status</span>
                    <div class="flex items-center gap-2">
                        @if($creator->is_verified)
                            <span class="px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs">Verified</span>
                        @endif
                        @if($creator->is_featured)
                            <span class="px-2 py-1 bg-purple-500/20 text-purple-400 rounded text-xs">Featured</span>
                        @endif
                        @if(!$creator->is_approved)
                            <span class="px-2 py-1 bg-orange-500/20 text-orange-400 rounded text-xs">Pending Approval</span>
                        @endif
                    </div>
                </div>
                <div class="flex items-center justify-between py-2 border-b border-white/5">
                    <span class="text-gray-400">Registered</span>
                    <span class="text-white">{{ $creator->created_at->format('M d, Y') }}</span>
                </div>
                @if($creator->verified_at)
                <div class="flex items-center justify-between py-2 border-b border-white/5">
                    <span class="text-gray-400">Verified On</span>
                    <span class="text-white">{{ $creator->verified_at->format('M d, Y') }}</span>
                </div>
                @endif
                <div class="flex items-center justify-between py-2">
                    <span class="text-gray-400">Public Profile</span>
                    <a href="{{ route('content-creators.show', $creator) }}" target="_blank" class="text-green-400 hover:text-green-300 text-sm flex items-center gap-1">
                        View Profile
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
                    </a>
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
