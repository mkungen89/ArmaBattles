@extends('layouts.app')

@section('title', 'Delete Account')

@section('content')
<div class="max-w-2xl mx-auto">
    <div class="glass-card rounded-xl p-8">
        {{-- Warning Header --}}
        <div class="flex items-start gap-4 mb-6">
            <div class="flex-shrink-0">
                <div class="w-12 h-12 rounded-full bg-red-500/20 flex items-center justify-center">
                    <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"></path>
                    </svg>
                </div>
            </div>
            <div>
                <h1 class="text-2xl font-bold text-white mb-2">Delete Account</h1>
                <p class="text-gray-400">This action is permanent and cannot be undone.</p>
            </div>
        </div>

        {{-- Warning Message --}}
        <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-6">
            <div class="flex gap-3">
                <svg class="w-5 h-5 text-red-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4m0 4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-red-300">
                    <strong class="font-semibold">Warning:</strong> Deleting your account will:
                    <ul class="mt-2 space-y-1 ml-4 list-disc">
                        <li>Remove all your personal information (email, Steam ID, etc.)</li>
                        <li>Delete your notifications, favorites, and API tokens</li>
                        <li>Anonymize your profile (shown as "Deleted User")</li>
                        <li>Prevent you from logging in again</li>
                        <li>Keep your game stats for leaderboard integrity</li>
                        <li>Keep your team memberships (shown as deleted user)</li>
                    </ul>
                </div>
            </div>
        </div>

        {{-- Export Data First --}}
        <div class="bg-blue-500/10 border border-blue-500/30 rounded-lg p-4 mb-6">
            <div class="flex items-start gap-3">
                <svg class="w-5 h-5 text-blue-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                </svg>
                <div class="text-sm text-blue-300">
                    <strong class="font-semibold">Before you delete:</strong>
                    <p class="mt-1">We recommend downloading a copy of your data first.</p>
                    <a href="{{ route('profile.export-data') }}" class="inline-flex items-center gap-2 mt-2 px-4 py-2 bg-blue-600/20 hover:bg-blue-600/30 text-blue-300 rounded-lg transition">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"></path>
                        </svg>
                        Download My Data (JSON)
                    </a>
                </div>
            </div>
        </div>

        {{-- Deletion Form --}}
        <form method="POST" action="{{ route('profile.delete-account') }}" class="space-y-6"
              x-data="{ confirmation: '', password: '', canSubmit: false }"
              x-effect="canSubmit = confirmation === 'DELETE MY ACCOUNT' && password.length > 0">
            @csrf
            @method('DELETE')

            {{-- Confirmation Text --}}
            <div>
                <label for="confirmation" class="block text-sm font-medium text-gray-300 mb-2">
                    Type <code class="px-2 py-1 bg-white/5 rounded text-red-400 font-mono">DELETE MY ACCOUNT</code> to confirm
                </label>
                <input type="text"
                       name="confirmation"
                       id="confirmation"
                       x-model="confirmation"
                       required
                       autocomplete="off"
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       placeholder="DELETE MY ACCOUNT">
                @error('confirmation')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Password Confirmation --}}
            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                    Confirm your password
                </label>
                <input type="password"
                       name="password"
                       id="password"
                       x-model="password"
                       required
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-red-500 focus:border-transparent"
                       placeholder="Your password">
                @error('password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Actions --}}
            <div class="flex flex-col sm:flex-row gap-3 pt-4">
                <a href="{{ route('profile.settings') }}"
                   class="flex-1 px-6 py-3 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white font-medium rounded-xl transition text-center">
                    Cancel
                </a>
                <button type="submit"
                        :disabled="!canSubmit"
                        :class="canSubmit ? 'bg-red-600 hover:bg-red-500 cursor-pointer' : 'bg-red-600/50 cursor-not-allowed'"
                        class="flex-1 px-6 py-3 text-white font-semibold rounded-xl transition flex items-center justify-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"></path>
                    </svg>
                    Delete My Account Permanently
                </button>
            </div>
        </form>

        {{-- Contact Support --}}
        <div class="mt-6 pt-6 border-t border-white/10">
            <p class="text-sm text-gray-400 text-center">
                Changed your mind? Need help? <a href="{{ route('contact') }}" class="text-blue-400 hover:text-blue-300">Contact Support</a>
            </p>
        </div>
    </div>
</div>
@endsection
