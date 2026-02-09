@extends('layouts.app')
@section('title', 'Register as Content Creator')
@section('content')
<div class="py-12">
    
        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Register as Content Creator</h1>
            <p class="text-gray-400">Join our directory of streamers and content creators</p>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <form action="{{ route('content-creators.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Platform *</label>
                    <select name="platform" required class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2">
                        <option value="">Select platform...</option>
                        <option value="twitch">Twitch</option>
                        <option value="youtube">YouTube</option>
                        <option value="tiktok">TikTok</option>
                        <option value="kick">Kick</option>
                    </select>
                    @error('platform')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Channel URL *</label>
                    <input type="url" name="channel_url" required placeholder="https://twitch.tv/yourname" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2">
                    @error('channel_url')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Channel Name</label>
                    <input type="text" name="channel_name" placeholder="Your channel display name" class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2">
                    @error('channel_name')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Bio</label>
                    <textarea name="bio" rows="4" placeholder="Tell us about your content..." class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2"></textarea>
                    @error('bio')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4">
                    <p class="text-sm text-blue-300">
                        <strong>Note:</strong> Your profile will be reviewed by admins for verification. Verified creators get a badge and appear higher in search results.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-purple-600 hover:bg-purple-500 text-white font-semibold rounded-lg transition">
                        Register
                    </button>
                    <a href="{{ route('content-creators.index') }}" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
