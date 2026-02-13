@extends('layouts.app')
@section('title', 'View Match Report')
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
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Match Report</h1>
                        <p class="text-gray-400">{{ $report->match->tournament->name }} - {{ $report->match->round_label }}</p>
                    </div>
                    <span class="text-sm font-semibold px-4 py-2 rounded-lg
                        {{ $report->status === 'submitted' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                        {{ $report->status === 'approved' ? 'bg-green-500/20 text-green-400' : '' }}
                        {{ $report->status === 'disputed' ? 'bg-red-500/20 text-red-400' : '' }}">
                        {{ ucfirst($report->status) }}
                    </span>
                </div>
            </div>
            {{-- Match Result --}}
            <div class="glass-card rounded-xl p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Match Result</h2>
                <div class="flex items-center justify-center gap-6 py-6">
                    <div class="text-center">
                        <p class="text-gray-400 text-sm mb-2">{{ $report->match->team1->name }}</p>
                        <p class="text-5xl font-bold {{ $report->winning_team_id === $report->match->team1_id ? 'text-green-400' : 'text-gray-500' }}">
                            {{ $report->team1_score }}
                        </p>
                    </div>
                    <div class="text-2xl text-gray-500 font-bold">-</div>
                    <div class="text-center">
                        <p class="text-gray-400 text-sm mb-2">{{ $report->match->team2->name }}</p>
                        <p class="text-5xl font-bold {{ $report->winning_team_id === $report->match->team2_id ? 'text-green-400' : 'text-gray-500' }}">
                            {{ $report->team2_score }}
                        </p>
                    </div>
                </div>
                <div class="text-center pt-4 border-t border-white/5">
                    <p class="text-gray-400">Winner</p>
                    <p class="text-2xl font-bold text-green-400 mt-1">{{ $report->winningTeam->name }}</p>
                </div>
            </div>
            {{-- Report Details --}}
            <div class="glass-card rounded-xl p-6 mb-6">
                <h2 class="text-xl font-bold text-white mb-4">Report Details</h2>
                <div class="grid grid-cols-1 md:grid-cols-2 gap-4 mb-4">
                    <div>
                        <p class="text-sm text-gray-400">Reported By</p>
                        <p class="text-white font-semibold">{{ $report->referee->name }}</p>
                    </div>
                    <div>
                        <p class="text-sm text-gray-400">Reported At</p>
                        <p class="text-white font-semibold">{{ $report->reported_at->format('M j, Y @ g:i A') }}</p>
                    </div>
                </div>
                @if($report->notes)
                    <div class="pt-4 border-t border-white/5">
                        <p class="text-sm text-gray-400 mb-2">Notes</p>
                        <p class="text-gray-300">{{ $report->notes }}</p>
                    </div>
                @endif
            </div>
            {{-- Incidents --}}
            @if($report->incidents && count($report->incidents) > 0)
                <div class="bg-yellow-500/5 border border-yellow-500/20 rounded-xl p-6 mb-6">
                    <h2 class="text-xl font-bold text-yellow-400 mb-4">
                        Incidents Reported ({{ count($report->incidents) }})
                    </h2>
                    <div class="space-y-4">
                        @foreach($report->incidents as $index => $incident)
                            <div class="bg-white/3 border border-yellow-500/10 rounded-lg p-4">
                                <div class="flex items-start justify-between mb-2">
                                    <div class="flex items-center gap-2">
                                        <span class="text-sm font-semibold text-yellow-400">
                                            Incident {{ $index + 1 }}
                                        </span>
                                        <span class="text-xs px-2 py-1 bg-yellow-500/10 text-yellow-400 rounded">
                                            {{ ucwords(str_replace('_', ' ', $incident['type'] ?? 'Unknown')) }}
                                        </span>
                                    </div>
                                    @if(isset($incident['player_name']) && $incident['player_name'])
                                        <span class="text-sm text-gray-400">
                                            Player: <span class="text-white">{{ $incident['player_name'] }}</span>
                                        </span>
                                    @endif
                                </div>
                                <p class="text-gray-300 mb-2">{{ $incident['description'] ?? '' }}</p>
                                @if(isset($incident['timestamp']) && $incident['timestamp'])
                                    <p class="text-sm text-gray-400">
                                        Time: {{ $incident['timestamp'] }}
                                    </p>
                                @endif
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
            {{-- Actions --}}
            @if($report->status === 'submitted' && auth()->user()->isAdmin())
                <div class="glass-card rounded-xl p-6 mb-6">
                    <h2 class="text-xl font-bold text-white mb-4">Admin Actions</h2>
                    <div class="flex gap-3">
                        <form action="{{ route('referee.report.approve', $report) }}" method="POST">
                            @csrf
                            <button type="submit"
                                    class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                </svg>
                                Approve Report
                            </button>
                        </form>
                        <button type="button"
                                onclick="document.getElementById('dispute-form').classList.toggle('hidden')"
                                class="px-6 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition flex items-center gap-2">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            Dispute Report
                        </button>
                    </div>
                    <form id="dispute-form" action="{{ route('referee.report.dispute', $report) }}" method="POST" class="mt-4 hidden">
                        @csrf
                        <label class="block text-sm font-medium text-gray-300 mb-2">Dispute Reason *</label>
                        <textarea name="dispute_reason"
                                  rows="3"
                                  placeholder="Explain why this report is being disputed"
                                  class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white placeholder-gray-500 focus:border-red-500 focus:ring-2 focus:ring-red-500/20 transition mb-3"
                                  required></textarea>
                        <button type="submit"
                                class="px-6 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition">
                            Submit Dispute
                        </button>
                    </form>
                </div>
            @endif
            {{-- View Match Button --}}
            <div class="flex justify-center">
                <a href="{{ route('matches.show', $report->match_id) }}"
                   class="px-6 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/>
                    </svg>
                    View Full Match Details
                </a>
            </div>
        </div>
    
@endsection
