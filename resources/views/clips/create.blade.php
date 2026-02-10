@extends('layouts.app')
@section('title', 'Submit Highlight Clip')
@section('content')
<div class="py-12">

        <div class="mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">Submit Highlight Clip</h1>
            <p class="text-gray-400">Share your best moments with the community</p>
        </div>
        <div class="glass-card rounded-xl p-6">
            <form action="{{ route('clips.store') }}" method="POST" class="space-y-6">
                @csrf
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Title *</label>
                    <input type="text" name="title" required placeholder="Epic 1v5 Clutch" value="{{ old('title') }}" class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2">
                    @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Platform *</label>
                    <select name="platform" required class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2">
                        <option value="">Select platform...</option>
                        <option value="youtube">YouTube</option>
                        <option value="twitch">Twitch</option>
                        <option value="tiktok">TikTok</option>
                        <option value="kick">Kick</option>
                    </select>
                    @error('platform')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Clip URL *</label>
                    <input type="url" name="url" required placeholder="https://youtube.com/watch?v=..." value="{{ old('url') }}" class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2">
                    @error('url')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                    <p class="mt-1 text-xs text-gray-500">Supported: YouTube videos, Twitch clips</p>
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Description</label>
                    <textarea name="description" rows="4" placeholder="Describe what happens in the clip..." class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2">{{ old('description') }}</textarea>
                    @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                </div>
                <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4">
                    <p class="text-sm text-blue-300">
                        <strong>Guidelines:</strong> Submit clips of impressive gameplay, funny moments, or epic plays. Low-effort or spam submissions may be removed.
                    </p>
                </div>
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition">
                        Submit Clip
                    </button>
                    <a href="{{ route('clips.index') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-semibold rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
