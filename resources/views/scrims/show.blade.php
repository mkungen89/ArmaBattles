@extends('layouts.app')
@section('title', 'Scrim Details')
@section('content')
<div class="py-12">
    
        {{-- Header --}}
        <div class="flex items-center justify-between">
            <div class="flex items-center gap-4">
                <a href="{{ route('scrims.index') }}" class="text-gray-400 hover:text-white transition">
                    <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                    </svg>
                </a>
                <h1 class="text-3xl font-bold text-white">Scrim Details</h1>
            </div>
            <span class="px-4 py-2 rounded-full text-sm font-semibold {{ $scrim->status_color }} bg-gray-700/50">
                {{ $scrim->status_label }}
            </span>
        </div>
        {{-- Match Score Card --}}
        <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-2xl p-8">
            <div class="grid grid-cols-1 md:grid-cols-3 gap-8 items-center">
                {{-- Team 1 --}}
                <div class="text-center md:text-right">
                    <a href="{{ route('teams.show', $scrim->team1) }}" class="inline-block hover:opacity-80 transition">
                        @if($scrim->team1->avatar_url)
                        <img src="{{ $scrim->team1->avatar_url }}" alt="{{ $scrim->team1->name }}" class="w-24 h-24 rounded-full mx-auto md:ml-auto md:mr-0 mb-4">
                        @endif
                        <h2 class="text-2xl font-bold text-white mb-1">{{ $scrim->team1->name }}</h2>
                        @if($scrim->team1->tag)
                        <p class="text-gray-400 text-sm">[{{ $scrim->team1->tag }}]</p>
                        @endif
                    </a>
                </div>
                {{-- Score / VS --}}
                <div class="text-center">
                    @if($scrim->isCompleted())
                    <div class="flex items-center justify-center gap-6">
                        <span class="text-6xl font-bold {{ $scrim->winner_id === $scrim->team1_id ? 'text-green-400' : 'text-white' }}">
                            {{ $scrim->team1_score }}
                        </span>
                        <span class="text-3xl text-gray-500">-</span>
                        <span class="text-6xl font-bold {{ $scrim->winner_id === $scrim->team2_id ? 'text-green-400' : 'text-white' }}">
                            {{ $scrim->team2_score }}
                        </span>
                    </div>
                    @if($scrim->winner)
                    <p class="mt-4 text-green-400 font-semibold">Winner: {{ $scrim->winner->name }}</p>
                    @elseif($scrim->team1_score === $scrim->team2_score)
                    <p class="mt-4 text-yellow-400 font-semibold">Draw</p>
                    @endif
                    @else
                    <div class="text-5xl font-bold text-gray-400">VS</div>
                    <p class="mt-4 text-gray-500">{{ $scrim->scheduled_at->format('M j, Y') }}</p>
                    <p class="text-gray-500 text-sm">{{ $scrim->scheduled_at->format('g:i A') }}</p>
                    @endif
                </div>
                {{-- Team 2 --}}
                <div class="text-center md:text-left">
                    <a href="{{ route('teams.show', $scrim->team2) }}" class="inline-block hover:opacity-80 transition">
                        @if($scrim->team2->avatar_url)
                        <img src="{{ $scrim->team2->avatar_url }}" alt="{{ $scrim->team2->name }}" class="w-24 h-24 rounded-full mx-auto md:mr-auto md:ml-0 mb-4">
                        @endif
                        <h2 class="text-2xl font-bold text-white mb-1">{{ $scrim->team2->name }}</h2>
                        @if($scrim->team2->tag)
                        <p class="text-gray-400 text-sm">[{{ $scrim->team2->tag }}]</p>
                        @endif
                    </a>
                </div>
            </div>
        </div>
        {{-- Actions --}}
        @if($canManage && ($scrim->isScheduled() || $scrim->isInProgress()))
        <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-bold text-white mb-4">Match Actions</h3>
            <div class="flex flex-wrap gap-3">
                @if(!$scrim->isCompleted() && !$scrim->isCancelled())
                <button onclick="document.getElementById('reportResultModal').classList.remove('hidden')"
                        class="px-4 py-2 bg-green-600/20 hover:bg-green-600/30 text-green-400 rounded-lg transition">
                    Report Result
                </button>
                @endif
                @if(!$scrim->isCompleted())
                <form action="{{ route('scrims.cancel', $scrim) }}" method="POST" onsubmit="return confirm('Are you sure you want to cancel this scrim?')">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600/20 hover:bg-red-600/30 text-red-400 rounded-lg transition">
                        Cancel Scrim
                    </button>
                </form>
                @endif
            </div>
        </div>
        @endif
        {{-- Match Details --}}
        <div class="grid grid-cols-1 md:grid-cols-2 gap-6">
            {{-- Info --}}
            <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Match Information</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Scheduled:</span>
                        <span class="text-white font-semibold">{{ $scrim->scheduled_at->format('M j, Y @ g:i A') }}</span>
                    </div>
                    @if($scrim->map)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Map:</span>
                        <span class="text-white font-semibold">{{ $scrim->map }}</span>
                    </div>
                    @endif
                    @if($scrim->duration_minutes)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Duration:</span>
                        <span class="text-white font-semibold">{{ $scrim->duration_minutes }} minutes</span>
                    </div>
                    @endif
                    @if($scrim->password && $canManage)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Server Password:</span>
                        <span class="text-white font-mono font-semibold">{{ $scrim->password }}</span>
                    </div>
                    @endif
                    <div class="flex justify-between">
                        <span class="text-gray-400">Created by:</span>
                        <span class="text-white font-semibold">{{ $scrim->creator->name }}</span>
                    </div>
                    @if($scrim->completed_at)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Completed:</span>
                        <span class="text-white font-semibold">{{ $scrim->completed_at->format('M j, Y @ g:i A') }}</span>
                    </div>
                    @endif
                </div>
                @if($scrim->notes)
                <div class="mt-4 pt-4 border-t border-gray-700">
                    <p class="text-gray-400 text-sm mb-2">Notes:</p>
                    <p class="text-white">{{ $scrim->notes }}</p>
                </div>
                @endif
            </div>
            {{-- Invitation Status --}}
            @if($scrim->invitation)
            <div class="bg-gray-800/50 backdrop-blur border border-gray-700 rounded-xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Invitation Status</h3>
                <div class="space-y-3 text-sm">
                    <div class="flex justify-between">
                        <span class="text-gray-400">Inviting Team:</span>
                        <span class="text-white font-semibold">{{ $scrim->invitation->invitingTeam->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Invited Team:</span>
                        <span class="text-white font-semibold">{{ $scrim->invitation->invitedTeam->name }}</span>
                    </div>
                    <div class="flex justify-between">
                        <span class="text-gray-400">Status:</span>
                        <span class="font-semibold {{ $scrim->invitation->isPending() ? 'text-yellow-400' : ($scrim->invitation->isAccepted() ? 'text-green-400' : 'text-red-400') }}">
                            {{ ucfirst($scrim->invitation->status) }}
                        </span>
                    </div>
                    @if($scrim->invitation->responded_at)
                    <div class="flex justify-between">
                        <span class="text-gray-400">Responded:</span>
                        <span class="text-white font-semibold">{{ $scrim->invitation->responded_at->format('M j, Y @ g:i A') }}</span>
                    </div>
                    @else
                    <div class="flex justify-between">
                        <span class="text-gray-400">Expires:</span>
                        <span class="text-white font-semibold">{{ $scrim->invitation->expires_at->format('M j, Y @ g:i A') }}</span>
                    </div>
                    @endif
                </div>
            </div>
            @endif
        </div>
    </div>
{{-- Report Result Modal --}}
<div id="reportResultModal" class="hidden fixed inset-0 bg-black/50 flex items-center justify-center z-50 p-4">
    <div class="bg-gray-800 border border-gray-700 rounded-xl max-w-md w-full p-6">
        <h3 class="text-xl font-bold text-white mb-4">Report Scrim Result</h3>
        <form action="{{ route('scrims.report', $scrim) }}" method="POST">
            @csrf
            <div class="space-y-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        {{ $scrim->team1->name }} Score
                    </label>
                    <input type="number" name="team1_score" min="0" required
                           class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        {{ $scrim->team2->name }} Score
                    </label>
                    <input type="number" name="team2_score" min="0" required
                           class="w-full bg-gray-900/50 border border-gray-700 text-white rounded-lg px-4 py-2 focus:ring-2 focus:ring-green-500">
                </div>
                <div class="flex gap-3 pt-4">
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-lg transition">
                        Submit Result
                    </button>
                    <button type="button" onclick="document.getElementById('reportResultModal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-lg transition">
                        Cancel
                    </button>
                </div>
            </div>
        </form>
    </div>
</div>

@endsection
