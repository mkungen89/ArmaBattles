@extends('layouts.app')
@section('title', $team->name)
@section('content')
    <!-- Header Banner -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden mb-6">
        <div class="relative h-48 sm:h-56">
            @if($team->header_image_url)
                <img src="{{ $team->header_image_url }}" alt="{{ $team->name }} banner" class="w-full h-full object-cover">
            @else
                <div class="w-full h-full bg-gradient-to-br from-gray-700 via-gray-800 to-gray-900"></div>
            @endif
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900/80 to-transparent"></div>
        </div>
        <div class="relative px-6 pb-6">
            <!-- Avatar -->
            <div class="-mt-16 mb-4">
                @if($team->avatar_url)
                    <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-28 h-28 rounded-xl object-cover border-4 border-gray-800 shadow-lg">
                @else
                    <div class="w-28 h-28 rounded-xl bg-gray-700 border-4 border-gray-800 shadow-lg flex items-center justify-center text-3xl font-bold text-gray-400">
                        {{ strtoupper(substr($team->tag, 0, 2)) }}
                    </div>
                @endif
            </div>
            <!-- Name, Badges, Description -->
            <div class="flex flex-col sm:flex-row sm:items-start gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2 flex-wrap">
                        <h1 class="text-2xl font-bold text-white">{{ $team->name }}</h1>
                        @if($team->is_verified)
                            <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                        @endif
                        <span class="px-3 py-1 text-sm rounded-full {{ $team->status_badge }}">
                            {{ $team->status_text }}
                        </span>
                        @if($team->is_recruiting)
                            <span class="px-3 py-1 text-sm rounded-full bg-green-500/20 text-green-400 border border-green-500/30 font-medium animate-pulse">
                                <svg class="w-3.5 h-3.5 inline -mt-0.5 mr-0.5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z"/>
                                </svg>
                                Recruiting
                            </span>
                        @endif
                    </div>
                    <p class="text-xl text-gray-400 mb-2">[{{ $team->tag }}]</p>
                    @if($team->description)
                        <p class="text-gray-300 mt-3">{{ $team->description }}</p>
                    @endif
                    <!-- Social Links -->
                    <div class="flex flex-wrap gap-4 mt-4">
                        @include('teams._social-links', ['team' => $team])
                    </div>
                    @include('components.favorite-button', ['model' => $team, 'type' => 'team'])
                </div>
            </div>
        </div>
        {{-- Apply to join --}}
        @auth
            @php
                $user = auth()->user();
                $isMember = $team->isUserMember($user);
                $hasPendingApplication = $user->hasPendingApplicationTo($team);
            @endphp
            @if(!$isMember && $team->is_active)
                <div class="px-6 pb-6 pt-0">
                    <div class="pt-6 border-t border-gray-700">
                        @if($team->is_recruiting)
                            @if($hasPendingApplication)
                                <div class="flex items-center justify-between bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
                                    <span class="text-yellow-400">You have a pending application to this platoon.</span>
                                </div>
                            @elseif($user->hasTeam())
                                <div class="text-gray-400 text-sm">Leave your current platoon to apply here.</div>
                            @else
                                @if($team->recruitment_message)
                                    <div class="bg-gray-700/50 rounded-lg p-4 mb-4">
                                        <h3 class="text-sm font-medium text-gray-300 mb-2">Recruitment Message</h3>
                                        <p class="text-gray-400">{{ $team->recruitment_message }}</p>
                                    </div>
                                @endif
                                <button onclick="document.getElementById('apply-modal').classList.remove('hidden')"
                                        class="w-full px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                                    Apply to Join
                                </button>
                            @endif
                        @else
                            <div class="text-gray-400 text-sm text-center">This platoon is not currently recruiting.</div>
                        @endif
                    </div>
                </div>
            @endif
        @else
            @if($team->is_recruiting)
                <div class="px-6 pb-6 text-center">
                    <div class="pt-6 border-t border-gray-700">
                        <a href="{{ route('auth.steam') }}" class="text-green-400 hover:text-green-300">
                            Login with Steam to apply
                        </a>
                    </div>
                </div>
            @endif
        @endauth
    </div>
    <div class="grid lg:grid-cols-3 gap-6">
        <!-- Main Content -->
        <div class="lg:col-span-2 space-y-6">
            <!-- Members -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">
                    Members ({{ $team->activeMembers->count() }})
                </h2>
                <div class="space-y-3">
                    @foreach($team->activeMembers->sortBy(function($member) {
                        return ['captain' => 0, 'officer' => 1, 'member' => 2][$member->pivot->role] ?? 3;
                    }) as $member)
                        <div class="flex items-center gap-4 bg-gray-700/50 rounded-lg p-3">
                            <img src="{{ $member->avatar_display }}" alt="{{ $member->name }}" class="w-12 h-12 rounded-full">
                            <div class="flex-1 min-w-0">
                                <div class="flex items-center gap-2">
                                    <span class="text-white font-medium truncate">{{ $member->name }}</span>
                                    @if($member->pivot->role === 'captain')
                                        <span class="px-2 py-0.5 text-xs rounded bg-yellow-500/20 text-yellow-400">Captain</span>
                                    @elseif($member->pivot->role === 'officer')
                                        <span class="px-2 py-0.5 text-xs rounded bg-blue-500/20 text-blue-400">Officer</span>
                                    @endif
                                </div>
                                @if($member->pivot->joined_at)
                                    <p class="text-xs text-gray-500">Joined {{ \Carbon\Carbon::parse($member->pivot->joined_at)->diffForHumans() }}</p>
                                @endif
                            </div>
                            <a href="{{ $member->profile_url }}" target="_blank" class="text-gray-400 hover:text-white transition">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    @endforeach
                </div>
            </div>
            <!-- Combat Statistics -->
            @if(isset($combatStats) && $combatStats['member_count'] > 0)
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Combat Statistics</h2>
                <div class="grid grid-cols-2 sm:grid-cols-4 gap-4">
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-green-400">{{ number_format($combatStats['total_kills']) }}</div>
                        <div class="text-xs text-gray-400">Total Kills</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-red-400">{{ number_format($combatStats['total_deaths']) }}</div>
                        <div class="text-xs text-gray-400">Total Deaths</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-yellow-400">{{ $combatStats['avg_kd'] }}</div>
                        <div class="text-xs text-gray-400">Avg K/D</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-amber-400">{{ number_format($combatStats['total_headshots']) }}</div>
                        <div class="text-xs text-gray-400">Headshots</div>
                    </div>
                </div>
                <div class="grid grid-cols-2 gap-4 mt-4">
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-xl font-bold text-blue-400">{{ $combatStats['total_playtime_hours'] }}h</div>
                        <div class="text-xs text-gray-400">Total Playtime</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-xl font-bold text-green-400">{{ number_format($combatStats['avg_kills'], 0) }}</div>
                        <div class="text-xs text-gray-400">Avg Kills/Member</div>
                    </div>
                </div>
                <div class="mt-3 flex justify-end">
                    <a href="{{ route('teams.compare', ['t1' => $team->id]) }}" class="text-xs text-green-400 hover:text-green-300">
                        Compare with another platoon &rarr;
                    </a>
                </div>
            </div>
            @endif
            <!-- Recent Matches -->
            @if($recentMatches->count() > 0)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Recent Matches</h2>
                    <div class="space-y-3">
                        @foreach($recentMatches as $match)
                            <a href="{{ route('tournaments.match', [$match->tournament, $match]) }}" class="block bg-gray-700/50 rounded-lg p-4 hover:bg-gray-700 transition">
                                <div class="text-xs text-gray-500 mb-2">{{ $match->tournament->name }}</div>
                                <div class="flex items-center justify-between">
                                    <div class="flex items-center gap-3">
                                        <span class="{{ $match->winner_id === $team->id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                            {{ $match->team1_id === $team->id ? $team->name : $match->team1?->name }}
                                        </span>
                                        <span class="text-gray-400 font-mono">{{ $match->score_display }}</span>
                                        <span class="{{ $match->winner_id === $team->id ? 'text-green-400 font-semibold' : 'text-white' }}">
                                            {{ $match->team2_id === $team->id ? $team->name : $match->team2?->name }}
                                        </span>
                                    </div>
                                    @if($match->winner_id === $team->id)
                                        <span class="text-xs px-2 py-1 rounded bg-green-500/20 text-green-400">Win</span>
                                    @else
                                        <span class="text-xs px-2 py-1 rounded bg-red-500/20 text-red-400">Loss</span>
                                    @endif
                                </div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
        <!-- Sidebar -->
        <div class="space-y-6">
            <!-- Statistics -->
            @php $stats = $team->getStatistics(); $form = $team->getRecentForm(5); @endphp
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Statistics</h2>
                <div class="grid grid-cols-2 gap-4 mb-4">
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-white">{{ $stats['total_matches'] }}</div>
                        <div class="text-xs text-gray-400">Matches</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-blue-400">{{ $stats['win_rate'] }}%</div>
                        <div class="text-xs text-gray-400">Win Rate</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-green-400">{{ $stats['wins'] }}</div>
                        <div class="text-xs text-gray-400">Wins</div>
                    </div>
                    <div class="text-center bg-gray-700/50 rounded-lg p-3">
                        <div class="text-2xl font-bold text-red-400">{{ $stats['losses'] }}</div>
                        <div class="text-xs text-gray-400">Losses</div>
                    </div>
                </div>
                @if(count($form) > 0)
                    <div class="mb-4">
                        <div class="text-xs text-gray-400 mb-2">Recent Form</div>
                        <div class="flex gap-1">
                            @foreach(array_reverse($form) as $result)
                                <span class="w-6 h-6 flex items-center justify-center rounded text-xs font-bold {{ $result === 'W' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                                    {{ $result }}
                                </span>
                            @endforeach
                        </div>
                    </div>
                @endif
                @if($stats['current_streak']['count'] > 0)
                    <div class="text-sm">
                        <span class="text-gray-400">Current:</span>
                        <span class="{{ $stats['current_streak']['type'] === 'win' ? 'text-green-400' : 'text-red-400' }} font-semibold">
                            {{ $stats['current_streak']['count'] }} {{ $stats['current_streak']['type'] === 'win' ? 'Win' : 'Loss' }} streak
                        </span>
                    </div>
                @endif
                <div class="mt-4 pt-4 border-t border-gray-700 grid grid-cols-2 gap-2 text-sm">
                    <div>
                        <span class="text-gray-400">Tournaments:</span>
                        <span class="text-white ml-1">{{ $stats['tournaments_participated'] }}</span>
                    </div>
                    <div>
                        <span class="text-gray-400">Trophies:</span>
                        <span class="text-yellow-400 ml-1">{{ $stats['tournaments_won'] }}</span>
                    </div>
                </div>
            </div>
            <!-- Info -->
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Information</h2>
                <dl class="space-y-4">
                    <div>
                        <dt class="text-sm text-gray-400">Captain</dt>
                        <dd class="flex items-center gap-2 mt-1">
                            <img src="{{ $team->captain->avatar_display }}" alt="{{ $team->captain->name }}" class="w-6 h-6 rounded-full">
                            <span class="text-white">{{ $team->captain->name }}</span>
                        </dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Created</dt>
                        <dd class="text-white">{{ $team->created_at->format('d M Y') }}</dd>
                    </div>
                    <div>
                        <dt class="text-sm text-gray-400">Members</dt>
                        <dd class="text-white">{{ $team->activeMembers->count() }}</dd>
                    </div>
                    @if($team->website)
                        <div>
                            <dt class="text-sm text-gray-400">Website</dt>
                            <dd class="mt-1">
                                <a href="{{ $team->website }}" target="_blank" rel="noopener noreferrer" class="text-green-400 hover:text-green-300 transition text-sm break-all">
                                    {{ parse_url($team->website, PHP_URL_HOST) }}
                                </a>
                            </dd>
                        </div>
                    @endif
                </dl>
            </div>
            <!-- Tournaments -->
            @if($team->tournaments->count() > 0)
                <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                    <h2 class="text-lg font-semibold text-white mb-4">Tournaments</h2>
                    <div class="space-y-3">
                        @foreach($team->tournaments->take(5) as $tournament)
                            <a href="{{ route('tournaments.show', $tournament) }}" class="block hover:text-green-400 transition">
                                <div class="text-white">{{ $tournament->name }}</div>
                                <div class="text-xs text-gray-500">{{ $tournament->starts_at?->format('M Y') }}</div>
                            </a>
                        @endforeach
                    </div>
                </div>
            @endif
        </div>
    </div>
</div>
{{-- Apply Modal --}}
@auth
<div id="apply-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
    <div class="flex items-center justify-center min-h-screen px-4">
        <div class="fixed inset-0 bg-black/70" onclick="document.getElementById('apply-modal').classList.add('hidden')"></div>
        <div class="relative bg-gray-800 border border-gray-700 rounded-xl max-w-md w-full p-6">
            <h3 class="text-lg font-semibold text-white mb-4">Apply to {{ $team->name }}</h3>
            <form method="POST" action="{{ route('teams.apply', $team) }}">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm text-gray-400 mb-2">Message (optional)</label>
                    <textarea name="message" rows="4"
                              class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white focus:outline-none focus:border-green-500"
                              placeholder="Tell the platoon leaders why you want to join..."></textarea>
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="document.getElementById('apply-modal').classList.add('hidden')"
                            class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        Submit Application
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>
@endauth
@endsection
