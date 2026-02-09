@extends('layouts.app')
@section('title', 'Challenge Team to Scrim')
@section('content')
<div class="py-12">
    
        {{-- Header --}}
        <div class="mb-6">
            <div class="flex items-center gap-4 mb-4">
                <a href="{{ route('scrims.index') }}" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-white">Challenge Team to Scrim</h1>
            </div>
            <p class="text-gray-400">Schedule a practice match with another team.</p>
        </div>
        {{-- Form --}}
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
            <form action="{{ route('scrims.store') }}" method="POST" class="space-y-6">
                @csrf
                {{-- Opponent Team --}}
                <div>
                    <label for="opponent_team_id" class="block text-sm font-medium text-gray-300 mb-2">
                        Opponent Team *
                    </label>
                    <select name="opponent_team_id" id="opponent_team_id" required
                            class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                        <option value="">Select a team...</option>
                        @foreach($teams as $team)
                        <option value="{{ $team->id }}" {{ old('opponent_team_id') == $team->id ? 'selected' : '' }}>
                            {{ $team->name }} @if($team->tag)[{{ $team->tag }}]@endif
                        </option>
                        @endforeach
                    </select>
                    @error('opponent_team_id')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Scheduled Date/Time --}}
                <div>
                    <label for="scheduled_at" class="block text-sm font-medium text-gray-300 mb-2">
                        Scheduled Date & Time *
                    </label>
                    <input type="datetime-local" name="scheduled_at" id="scheduled_at" required
                           value="{{ old('scheduled_at', now()->addDays(1)->format('Y-m-d\TH:i')) }}"
                           min="{{ now()->format('Y-m-d\TH:i') }}"
                           class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('scheduled_at')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">The opponent team has 7 days to accept or decline.</p>
                </div>
                {{-- Map --}}
                <div>
                    <label for="map" class="block text-sm font-medium text-gray-300 mb-2">
                        Map (Optional)
                    </label>
                    <input type="text" name="map" id="map" value="{{ old('map') }}"
                           placeholder="e.g., Everon, Arland..."
                           class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('map')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Duration --}}
                <div>
                    <label for="duration_minutes" class="block text-sm font-medium text-gray-300 mb-2">
                        Duration (minutes)
                    </label>
                    <input type="number" name="duration_minutes" id="duration_minutes"
                           value="{{ old('duration_minutes', 60) }}"
                           min="10" max="180" step="5"
                           class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('duration_minutes')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Suggested match duration (10-180 minutes).</p>
                </div>
                {{-- Password --}}
                <div>
                    <label for="password" class="block text-sm font-medium text-gray-300 mb-2">
                        Server Password (Optional)
                    </label>
                    <input type="text" name="password" id="password" value="{{ old('password') }}"
                           placeholder="Leave blank for public match"
                           class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">
                    @error('password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Optional password for private match server.</p>
                </div>
                {{-- Notes --}}
                <div>
                    <label for="notes" class="block text-sm font-medium text-gray-300 mb-2">
                        Notes (Optional)
                    </label>
                    <textarea name="notes" id="notes" rows="4"
                              placeholder="Any additional details about the scrim..."
                              class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500 focus:border-transparent">{{ old('notes') }}</textarea>
                    @error('notes')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
                {{-- Info Box --}}
                <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4">
                    <div class="flex items-start gap-3">
                        <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        <div class="text-sm text-green-300">
                            <p class="font-semibold mb-1">About Scrims</p>
                            <ul class="list-disc list-inside space-y-1">
                                <li>The opponent team will receive an invitation</li>
                                <li>They have 7 days to accept or decline</li>
                                <li>Scrim stats are tracked separately from ranked matches</li>
                                <li>Both teams can report results after the match</li>
                            </ul>
                        </div>
                    </div>
                </div>
                {{-- Submit --}}
                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-lg transition">
                        Send Challenge
                    </button>
                    <a href="{{ route('scrims.index') }}" class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>
        </div>
    </div>
@endsection
