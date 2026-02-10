@extends('admin.layout')

@section('title', 'Edit ' . $tournament->name)

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.tournaments.show', $tournament) }}" class="text-gray-400 hover:text-white transition text-sm">
        &larr; Back to tournament
    </a>
</div>

<h1 class="text-2xl font-bold text-white mb-6">Edit tournament</h1>

<form action="{{ route('admin.tournaments.update', $tournament) }}" method="POST" class="space-y-6">
    @csrf
    @method('PUT')

    <div class="grid lg:grid-cols-2 gap-6">
        <!-- Basic Info -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Basic information</h2>

            <div class="space-y-4">
                <div>
                    <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Name *</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $tournament->name) }}" required
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label for="description" class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                    <textarea name="description" id="description" rows="4"
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">{{ old('description', $tournament->description) }}</textarea>
                </div>

                <div>
                    <label for="rules" class="block text-sm font-medium text-gray-400 mb-2">Rules</label>
                    <textarea name="rules" id="rules" rows="4"
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">{{ old('rules', $tournament->rules) }}</textarea>
                </div>

                <div>
                    <label for="banner_url" class="block text-sm font-medium text-gray-400 mb-2">Banner URL</label>
                    <input type="url" name="banner_url" id="banner_url" value="{{ old('banner_url', $tournament->banner_url) }}"
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                </div>

                <div>
                    <label for="prize_pool" class="block text-sm font-medium text-gray-400 mb-2">Prize Pool</label>
                    <input type="text" name="prize_pool" id="prize_pool" value="{{ old('prize_pool', $tournament->prize_pool) }}" placeholder="e.g. $500, Gift cards, etc."
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    @error('prize_pool')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="stream_url" class="block text-sm font-medium text-gray-400 mb-2">Stream URL</label>
                    <input type="url" name="stream_url" id="stream_url" value="{{ old('stream_url', $tournament->stream_url) }}" placeholder="https://twitch.tv/..."
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    @error('stream_url')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>
            </div>
        </div>

        <!-- Settings -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Settings</h2>

            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-400 mb-2">Format</label>
                    <p class="text-white">{{ $tournament->format_text }}</p>
                    <p class="text-xs text-gray-500">Format cannot be changed after creation.</p>
                </div>

                <div class="grid grid-cols-3 gap-4">
                    <div>
                        <label for="max_teams" class="block text-sm font-medium text-gray-400 mb-2">Max platoons *</label>
                        <input type="number" name="max_teams" id="max_teams" value="{{ old('max_teams', $tournament->max_teams) }}" min="4" max="128" required
                            class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="min_teams" class="block text-sm font-medium text-gray-400 mb-2">Min platoons *</label>
                        <input type="number" name="min_teams" id="min_teams" value="{{ old('min_teams', $tournament->min_teams) }}" min="2" required
                            class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                    <div>
                        <label for="team_size" class="block text-sm font-medium text-gray-400 mb-2">Platoon size *</label>
                        <input type="number" name="team_size" id="team_size" value="{{ old('team_size', $tournament->team_size) }}" min="1" max="32" required
                            class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                </div>

                @if($tournament->format === 'swiss')
                    <div>
                        <label for="swiss_rounds" class="block text-sm font-medium text-gray-400 mb-2">Number of Swiss rounds</label>
                        <input type="number" name="swiss_rounds" id="swiss_rounds" value="{{ old('swiss_rounds', $tournament->swiss_rounds) }}" min="3" max="10"
                            class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    </div>
                @endif

                <div>
                    <label for="server_id" class="block text-sm font-medium text-gray-400 mb-2">Server</label>
                    <select name="server_id" id="server_id"
                        class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="">No server</option>
                        @foreach($servers as $server)
                            <option value="{{ $server->id }}" {{ old('server_id', $tournament->server_id) == $server->id ? 'selected' : '' }}>
                                {{ $server->name }}
                            </option>
                        @endforeach
                    </select>
                </div>

                <div class="flex gap-6">
                    <label class="flex items-center gap-2 text-gray-400">
                        <input type="checkbox" name="require_approval" value="1" {{ old('require_approval', $tournament->require_approval) ? 'checked' : '' }}
                            class="rounded bg-white/5 border-white/10 text-green-500 focus:ring-green-500">
                        Require approval
                    </label>
                    <label class="flex items-center gap-2 text-gray-400">
                        <input type="checkbox" name="is_featured" value="1" {{ old('is_featured', $tournament->is_featured) ? 'checked' : '' }}
                            class="rounded bg-white/5 border-white/10 text-green-500 focus:ring-green-500">
                        Featured
                    </label>
                </div>
            </div>
        </div>
    </div>

    <!-- Dates -->
    <div class="glass-card rounded-xl p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Dates</h2>

        <div class="grid md:grid-cols-3 gap-4">
            <div>
                <label for="registration_starts_at" class="block text-sm font-medium text-gray-400 mb-2">Registration opens *</label>
                <input type="datetime-local" name="registration_starts_at" id="registration_starts_at"
                    value="{{ old('registration_starts_at', $tournament->registration_starts_at?->format('Y-m-d\TH:i')) }}" required
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label for="registration_ends_at" class="block text-sm font-medium text-gray-400 mb-2">Registration closes *</label>
                <input type="datetime-local" name="registration_ends_at" id="registration_ends_at"
                    value="{{ old('registration_ends_at', $tournament->registration_ends_at?->format('Y-m-d\TH:i')) }}" required
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label for="starts_at" class="block text-sm font-medium text-gray-400 mb-2">Tournament starts *</label>
                <input type="datetime-local" name="starts_at" id="starts_at"
                    value="{{ old('starts_at', $tournament->starts_at?->format('Y-m-d\TH:i')) }}" required
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
        </div>
    </div>

    <div class="flex gap-4">
        <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
            Save changes
        </button>
        <a href="{{ route('admin.tournaments.show', $tournament) }}" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
            Cancel
        </a>
    </div>
</form>
@endsection
