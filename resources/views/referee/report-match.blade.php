@extends('layouts.app')
@section('title', 'Submit Match Report')
@section('content')
    <div class="py-12">
        
            {{-- Header --}}
            <div class="mb-6">
                <a href="{{ route('referee.dashboard') }}"
                   class="inline-flex items-center text-sm text-gray-400 hover:text-white transition mb-4">
                    <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                    </svg>
                    Back to Dashboard
                </a>
                <h1 class="text-3xl font-bold text-white mb-2">Submit Match Report</h1>
                <p class="text-gray-400">{{ $match->tournament->name }} - {{ $match->round_label }}</p>
            </div>
            {{-- Match Info Card --}}
            <div class="glass-card rounded-xl p-6 mb-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-xl font-bold text-white">Match Details</h2>
                    <span class="text-xs font-semibold px-3 py-1 rounded {{ $match->status_badge }}">
                        {{ ucfirst($match->status) }}
                    </span>
                </div>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Team 1</p>
                        <p class="text-lg font-bold text-white">{{ $match->team1->name }}</p>
                        @if($match->team1->tag)
                            <span class="text-sm text-gray-400">[{{ $match->team1->tag }}]</span>
                        @endif
                    </div>
                    <div>
                        <p class="text-sm text-gray-400 mb-1">Team 2</p>
                        <p class="text-lg font-bold text-white">{{ $match->team2->name }}</p>
                        @if($match->team2->tag)
                            <span class="text-sm text-gray-400">[{{ $match->team2->tag }}]</span>
                        @endif
                    </div>
                </div>
                <div class="mt-4 pt-4 border-t border-white/5">
                    <p class="text-sm text-gray-400">
                        Scheduled: {{ $match->scheduled_at->format('F j, Y @ g:i A') }}
                    </p>
                </div>
            </div>
            {{-- Report Form --}}
            <form action="{{ route('referee.match.submit-report', $match->id) }}" method="POST">
                @csrf
                <div class="glass-card rounded-xl p-6 space-y-6">
                    {{-- Winner Selection --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Winning Team *</label>
                        <select name="winning_team_id"
                                class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                required>
                            <option value="">Select Winner</option>
                            <option value="{{ $match->team1_id }}">{{ $match->team1->name }}</option>
                            <option value="{{ $match->team2_id }}">{{ $match->team2->name }}</option>
                        </select>
                        @error('winning_team_id')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Score Inputs --}}
                    <div class="grid grid-cols-2 gap-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $match->team1->name }} Score *
                            </label>
                            <input type="number"
                                   name="team1_score"
                                   min="0"
                                   class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                   required>
                            @error('team1_score')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">
                                {{ $match->team2->name }} Score *
                            </label>
                            <input type="number"
                                   name="team2_score"
                                   min="0"
                                   class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"
                                   required>
                            @error('team2_score')
                                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                            @enderror
                        </div>
                    </div>
                    {{-- Notes --}}
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-2">Match Notes</label>
                        <textarea name="notes"
                                  rows="4"
                                  placeholder="Add any relevant notes about the match (optional)"
                                  class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500 focus:border-blue-500 focus:ring-2 focus:ring-blue-500/20 transition"></textarea>
                        @error('notes')
                            <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    {{-- Incidents Section --}}
                    <div x-data="{ incidents: [] }">
                        <div class="flex items-center justify-between mb-3">
                            <label class="block text-sm font-medium text-gray-300">Incidents / Violations</label>
                            <button type="button"
                                    @click="incidents.push({ type: '', description: '', player_name: '', timestamp: '' })"
                                    class="px-3 py-1 bg-yellow-600/20 hover:bg-yellow-600/30 text-yellow-400 text-sm rounded-lg transition flex items-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                                </svg>
                                Add Incident
                            </button>
                        </div>
                        <template x-if="incidents.length === 0">
                            <p class="text-gray-500 text-sm italic">No incidents reported</p>
                        </template>
                        <div class="space-y-3">
                            <template x-for="(incident, index) in incidents" :key="index">
                                <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-lg p-4">
                                    <div class="flex justify-between items-start mb-3">
                                        <span class="text-sm font-semibold text-yellow-400" x-text="'Incident ' + (index + 1)"></span>
                                        <button type="button"
                                                @click="incidents.splice(index, 1)"
                                                class="text-red-400 hover:text-red-300 transition">
                                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                                            </svg>
                                        </button>
                                    </div>
                                    <div class="grid grid-cols-1 md:grid-cols-2 gap-3">
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Type *</label>
                                            <select x-model="incident.type"
                                                    :name="'incidents[' + index + '][type]'"
                                                    class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition"
                                                    required>
                                                <option value="">Select Type</option>
                                                <option value="rule_violation">Rule Violation</option>
                                                <option value="unsportsmanlike">Unsportsmanlike Conduct</option>
                                                <option value="technical_issue">Technical Issue</option>
                                                <option value="cheating">Cheating Allegation</option>
                                                <option value="other">Other</option>
                                            </select>
                                        </div>
                                        <div>
                                            <label class="block text-xs text-gray-400 mb-1">Player (Optional)</label>
                                            <input type="text"
                                                   x-model="incident.player_name"
                                                   :name="'incidents[' + index + '][player_name]'"
                                                   placeholder="Player name"
                                                   class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm placeholder-gray-500 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition">
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-400 mb-1">Description *</label>
                                            <textarea x-model="incident.description"
                                                      :name="'incidents[' + index + '][description]'"
                                                      rows="2"
                                                      placeholder="Describe what happened"
                                                      class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm placeholder-gray-500 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition"
                                                      required></textarea>
                                        </div>
                                        <div class="md:col-span-2">
                                            <label class="block text-xs text-gray-400 mb-1">Timestamp (Optional)</label>
                                            <input type="text"
                                                   x-model="incident.timestamp"
                                                   :name="'incidents[' + index + '][timestamp]'"
                                                   placeholder="e.g., Round 2, 5:30 remaining"
                                                   class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm placeholder-gray-500 focus:border-yellow-500 focus:ring-2 focus:ring-yellow-500/20 transition">
                                        </div>
                                    </div>
                                </div>
                            </template>
                        </div>
                    </div>
                    {{-- Submit Button --}}
                    <div class="flex justify-end gap-3 pt-4 border-t border-white/5">
                        <a href="{{ route('referee.dashboard') }}"
                           class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                            Cancel
                        </a>
                        <button type="submit"
                                class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                            Submit Report
                        </button>
                    </div>
                </div>
            </form>
        </div>
    
@endsection
