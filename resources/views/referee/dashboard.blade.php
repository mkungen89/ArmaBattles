@extends('layouts.app')
@section('title', 'Referee Dashboard')
@section('content')
    <div class="py-12">
        
            {{-- Header --}}
            <div class="bg-gradient-to-r from-blue-600/10 to-purple-600/10 border border-blue-500/20 rounded-2xl p-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Referee Dashboard</h1>
                        <p class="text-gray-400">Manage tournament matches and submit official reports</p>
                    </div>
                    <div class="hidden sm:block">
                        <svg class="w-16 h-16 text-blue-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                    </div>
                </div>
            </div>
            {{-- Disputed Matches Alert --}}
            @if($disputedMatches->count() > 0)
                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4">
                    <div class="flex items-start">
                        <svg class="w-6 h-6 text-red-500 mr-3 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        <div>
                            <h3 class="text-lg font-semibold text-red-400 mb-1">
                                {{ $disputedMatches->count() }} Disputed {{ Str::plural('Match', $disputedMatches->count()) }}
                            </h3>
                            <p class="text-gray-300">These matches require admin review and resolution.</p>
                        </div>
                    </div>
                </div>
            @endif
            {{-- Stats Cards --}}
            <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
                <div class="glass-card rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Active Tournaments</p>
                            <p class="text-3xl font-bold text-white">{{ $activeTournaments->count() }}</p>
                        </div>
                        <div class="p-3 bg-blue-500/10 rounded-lg">
                            <svg class="w-8 h-8 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">Matches Needing Reports</p>
                            <p class="text-3xl font-bold text-white">{{ $upcomingMatches->count() }}</p>
                        </div>
                        <div class="p-3 bg-yellow-500/10 rounded-lg">
                            <svg class="w-8 h-8 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2"/>
                            </svg>
                        </div>
                    </div>
                </div>
                <div class="glass-card rounded-xl p-6">
                    <div class="flex items-center justify-between">
                        <div>
                            <p class="text-gray-400 text-sm mb-1">My Reports</p>
                            <p class="text-3xl font-bold text-white">{{ $myReports->count() }}</p>
                        </div>
                        <div class="p-3 bg-green-500/10 rounded-lg">
                            <svg class="w-8 h-8 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                            </svg>
                        </div>
                    </div>
                </div>
            </div>
            {{-- Upcoming Matches --}}
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-4">Matches Needing Reports</h2>
                @if($upcomingMatches->isEmpty())
                    <p class="text-gray-400 text-center py-8">No matches currently need reporting.</p>
                @else
                    <div class="space-y-4">
                        @foreach($upcomingMatches as $match)
                            <div class="bg-white/3 border border-white/5 rounded-lg p-4 hover:border-blue-500/50 transition">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-500/20 text-blue-400">
                                                {{ $match->tournament->name }}
                                            </span>
                                            <span class="text-xs font-semibold px-2 py-1 rounded bg-white/3 text-gray-300">
                                                {{ $match->round_label }}
                                            </span>
                                            <span class="text-xs font-semibold px-2 py-1 rounded {{ $match->status_badge }}">
                                                {{ ucfirst($match->status) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 text-gray-300">
                                            <span class="font-semibold">{{ $match->team1->name }}</span>
                                            <span class="text-gray-500">vs</span>
                                            <span class="font-semibold">{{ $match->team2->name }}</span>
                                        </div>
                                        <p class="text-sm text-gray-400 mt-1">
                                            Scheduled: {{ $match->scheduled_at?->format('M j, Y @ g:i A') ?? 'TBD' }}
                                        </p>
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('referee.match.report', $match) }}"
                                           class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm rounded-xl transition">
                                            View Match
                                        </a>
                                        @if($match->status === 'in_progress' || $match->status === 'scheduled')
                                            <a href="{{ route('referee.match.report', $match) }}"
                                               class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-xl transition flex items-center gap-2">
                                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5H7a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2V7a2 2 0 00-2-2h-2M9 5a2 2 0 002 2h2a2 2 0 002-2M9 5a2 2 0 012-2h2a2 2 0 012 2m-6 9l2 2 4-4"/>
                                                </svg>
                                                Submit Report
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            {{-- My Recent Reports --}}
            <div class="glass-card rounded-xl p-6">
                <h2 class="text-2xl font-bold text-white mb-4">My Recent Reports</h2>
                @if($myReports->isEmpty())
                    <p class="text-gray-400 text-center py-8">You haven't submitted any reports yet.</p>
                @else
                    <div class="space-y-4">
                        @foreach($myReports as $report)
                            <div class="bg-white/3 border border-white/5 rounded-lg p-4">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-500/20 text-blue-400">
                                                {{ $report->match->tournament->name }}
                                            </span>
                                            <span class="text-xs font-semibold px-2 py-1 rounded
                                                {{ $report->status === 'submitted' ? 'bg-yellow-500/20 text-yellow-400' : '' }}
                                                {{ $report->status === 'approved' ? 'bg-green-500/20 text-green-400' : '' }}
                                                {{ $report->status === 'disputed' ? 'bg-red-500/20 text-red-400' : '' }}">
                                                {{ ucfirst($report->status) }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 text-gray-300 mb-1">
                                            <span>{{ $report->match->team1->name }}</span>
                                            <span class="font-bold text-white">{{ $report->team1_score }}</span>
                                            <span class="text-gray-500">-</span>
                                            <span class="font-bold text-white">{{ $report->team2_score }}</span>
                                            <span>{{ $report->match->team2->name }}</span>
                                        </div>
                                        <p class="text-sm text-gray-400">
                                            Winner: <span class="text-green-400 font-semibold">{{ $report->winningTeam->name }}</span>
                                            • Reported: {{ $report->reported_at->diffForHumans() }}
                                        </p>
                                    </div>
                                    <a href="{{ route('referee.report.view', $report) }}"
                                       class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm rounded-xl transition">
                                        View Report
                                    </a>
                                </div>
                            </div>
                        @endforeach
                    </div>
                @endif
            </div>
            {{-- Disputed Matches Section --}}
            @if($disputedMatches->count() > 0)
                <div class="glass-card border-red-500/30 rounded-xl p-6">
                    <h2 class="text-2xl font-bold text-red-400 mb-4">Disputed Matches</h2>
                    <div class="space-y-4">
                        @foreach($disputedMatches as $match)
                            <div class="bg-white/3 border border-red-500/30 rounded-lg p-4">
                                <div class="flex items-center justify-between flex-wrap gap-4">
                                    <div class="flex-1 min-w-0">
                                        <div class="flex items-center gap-2 mb-2">
                                            <span class="text-xs font-semibold px-2 py-1 rounded bg-red-500/20 text-red-400">
                                                DISPUTED
                                            </span>
                                            <span class="text-xs font-semibold px-2 py-1 rounded bg-blue-500/20 text-blue-400">
                                                {{ $match->tournament->name }}
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-3 text-gray-300">
                                            <span class="font-semibold">{{ $match->team1->name }}</span>
                                            <span class="text-gray-500">vs</span>
                                            <span class="font-semibold">{{ $match->team2->name }}</span>
                                        </div>
                                        @if($match->reports->first())
                                            <p class="text-sm text-gray-400 mt-1">
                                                Reported by: {{ $match->reports->first()->referee->name }}
                                            </p>
                                        @endif
                                    </div>
                                    <div class="flex gap-2">
                                        <a href="{{ route('referee.match.report', $match) }}"
                                           class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white text-sm rounded-xl transition">
                                            View Match
                                        </a>
                                        @if($match->reports->first())
                                            <a href="{{ route('referee.report.view', $match->reports->first()) }}"
                                               class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white text-sm rounded-xl transition">
                                                View Report
                                            </a>
                                        @endif
                                    </div>
                                </div>
                            </div>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    
@endsection
