@extends('layouts.app')
@section('title', 'Create Platoon')
@section('content')
    <div class="mb-6">
        <a href="{{ route('teams.index') }}" class="text-gray-400 hover:text-white transition text-sm">
            &larr; Back to platoons
        </a>
    </div>
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h1 class="text-2xl font-bold text-white mb-6">Create new platoon</h1>
        <form action="{{ route('teams.store') }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Platoon name *</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="E.g. Viking Vanguard">
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="tag" class="block text-sm font-medium text-gray-400 mb-2">Tag (short name) *</label>
                <input type="text" name="tag" id="tag" value="{{ old('tag') }}" required maxlength="10"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 uppercase"
                    placeholder="E.g. VIK">
                <p class="mt-1 text-xs text-gray-500">Max 10 characters, letters and numbers only</p>
                @error('tag')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="Describe your platoon...">{{ old('description') }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="logo_url" class="block text-sm font-medium text-gray-400 mb-2">Logo URL</label>
                <input type="url" name="logo_url" id="logo_url" value="{{ old('logo_url') }}"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://example.com/logo.png">
                <p class="mt-1 text-xs text-gray-500">Enter a URL to your platoon logo</p>
                @error('logo_url')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-400 mb-2">Avatar image (optional)</label>
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-600 file:text-white hover:file:bg-gray-500">
                <p class="mt-1 text-xs text-gray-500">Recommended: 200 x 200 px. JPG, PNG or WebP. Max 1 MB. Overrides logo URL if set.</p>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="header" class="block text-sm font-medium text-gray-400 mb-2">Header banner (optional)</label>
                <input type="file" name="header" id="header" accept="image/jpeg,image/png,image/webp"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-gray-600 file:text-white hover:file:bg-gray-500">
                <p class="mt-1 text-xs text-gray-500">JPG, PNG or WebP. Max 2MB. Recommended: 1200x400px.</p>
                @error('header')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="website" class="block text-sm font-medium text-gray-400 mb-2">Website (optional)</label>
                <input type="url" name="website" id="website" value="{{ old('website') }}"
                    class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://example.com">
                @error('website')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="bg-gray-700/50 rounded-lg p-4">
                <h3 class="text-sm font-medium text-white mb-2">Information</h3>
                <ul class="text-sm text-gray-400 space-y-1">
                    <li>You will automatically become platoon captain</li>
                    <li>You can invite other players after the platoon is created</li>
                    <li>Platoons can register for tournaments when they have enough members</li>
                </ul>
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium">
                    Create Platoon
                </button>
                <a href="{{ route('teams.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

@endsection
