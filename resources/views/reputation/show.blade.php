@extends('layouts.app')
@section('title', $user->name . ' - Reputation')
@section('content')
    <div class="py-12">

            {{-- Back Button --}}
            <a href="{{ route('reputation.index') }}"
               class="inline-flex items-center text-sm text-gray-400 hover:text-white transition mb-6">
                <svg class="w-4 h-4 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
                Back to Reputation Leaderboard
            </a>
            {{-- Player Header --}}
            <div class="glass-card backdrop-blur rounded-xl p-6 mb-6">
                <div class="flex items-center gap-4 mb-6">
                    <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-20 h-20 rounded-full">
                    <div class="flex-1">
                        <h1 class="text-2xl font-bold text-white">{{ $user->name }}</h1>
                        <p class="text-gray-400 capitalize">{{ $user->role }}</p>
                    </div>
                    @if($reputation->isTrusted())
                        <div class="flex items-center gap-2 px-4 py-2 bg-green-600/20 border border-green-500/30 rounded-xl">
                            <svg class="w-6 h-6 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                            </svg>
                            <span class="text-sm font-semibold text-green-400">Trusted Player</span>
                        </div>
                    @elseif($reputation->isFlagged())
                        <div class="flex items-center gap-2 px-4 py-2 bg-red-600/20 border border-red-500/30 rounded-xl">
                            <svg class="w-6 h-6 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                            </svg>
                            <span class="text-sm font-semibold text-red-400">Flagged</span>
                        </div>
                    @endif
                </div>
                {{-- Reputation Score --}}
                <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
                    <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-400 mb-1">Total Reputation</p>
                        <p class="text-3xl font-bold {{ $reputation->badge_color }}">
                            {{ $reputation->total_score > 0 ? '+' : '' }}{{ $reputation->total_score }}
                        </p>
                    </div>
                    <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-400 mb-1">Positive Votes</p>
                        <p class="text-3xl font-bold text-green-400">{{ $reputation->positive_votes }}</p>
                    </div>
                    <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-400 mb-1">Negative Votes</p>
                        <p class="text-3xl font-bold text-red-400">{{ $reputation->negative_votes }}</p>
                    </div>
                    <div class="bg-gray-900/50 rounded-xl p-4 text-center">
                        <p class="text-sm text-gray-400 mb-1">Status</p>
                        <p class="text-lg font-bold {{ $reputation->badge_color }}">{{ $reputation->label }}</p>
                    </div>
                </div>
            </div>
            {{-- Commendations --}}
            <div class="glass-card backdrop-blur rounded-xl p-6 mb-6">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Commendations</h2>
                <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 text-center">
                        <svg class="w-10 h-10 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        <p class="text-sm text-gray-400">Teamwork</p>
                        <p class="text-2xl font-bold text-green-400">{{ $reputation->teamwork_count }}</p>
                    </div>
                    <div class="bg-emerald-500/10 border border-emerald-500/30 rounded-xl p-4 text-center">
                        <svg class="w-10 h-10 text-emerald-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        <p class="text-sm text-gray-400">Leadership</p>
                        <p class="text-2xl font-bold text-emerald-400">{{ $reputation->leadership_count }}</p>
                    </div>
                    <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 text-center">
                        <svg class="w-10 h-10 text-green-400 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                        </svg>
                        <p class="text-sm text-gray-400">Sportsmanship</p>
                        <p class="text-2xl font-bold text-green-400">{{ $reputation->sportsmanship_count }}</p>
                    </div>
                </div>
            </div>
            {{-- Vote Section --}}
            @auth
                @if($user->id !== auth()->id())
                    <div class="glass-card backdrop-blur rounded-xl p-6 mb-6">
                        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Rate This Player</h2>
                        @if($myVote)
                            <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-4">
                                <p class="text-sm text-green-400 mb-2">
                                    <strong>You voted:</strong>
                                    {{ $myVote->vote_type === 'positive' ? '+Rep' : '-Rep' }}
                                    ({{ ucfirst($myVote->category) }})
                                    <span class="text-gray-400">- {{ $myVote->created_at->diffForHumans() }}</span>
                                </p>
                                @if($myVote->comment)
                                    <p class="text-sm text-gray-300 italic">"{{ $myVote->comment }}"</p>
                                @endif
                                @if($myVote->canBeChanged())
                                    <form action="{{ route('reputation.remove-vote', $user) }}" method="POST" class="mt-3">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-sm text-red-400 hover:text-red-300 transition">
                                            Remove Vote
                                        </button>
                                    </form>
                                @else
                                    <p class="text-xs text-gray-500 mt-2">You can change your vote after 24 hours</p>
                                @endif
                            </div>
                        @endif
                        <form action="{{ route('reputation.vote', $user) }}" method="POST" class="space-y-4">
                            @csrf
                            {{-- Vote Type --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Your Vote *</label>
                                <div class="grid grid-cols-2 gap-4">
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="vote_type" value="positive" required class="peer sr-only">
                                        <div class="px-4 py-3 border-2 border-white/5 peer-checked:border-green-500 peer-checked:bg-green-500/10 rounded-xl text-center transition">
                                            <svg class="w-8 h-8 text-green-400 mx-auto mb-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                            </svg>
                                            <p class="font-semibold text-white">+Rep</p>
                                            <p class="text-xs text-gray-400">Positive Experience</p>
                                        </div>
                                    </label>
                                    <label class="relative cursor-pointer">
                                        <input type="radio" name="vote_type" value="negative" required class="peer sr-only">
                                        <div class="px-4 py-3 border-2 border-white/5 peer-checked:border-red-500 peer-checked:bg-red-500/10 rounded-xl text-center transition">
                                            <svg class="w-8 h-8 text-red-400 mx-auto mb-1 transform rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905 0 .714-.211 1.412-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/>
                                            </svg>
                                            <p class="font-semibold text-white">-Rep</p>
                                            <p class="text-xs text-gray-400">Negative Experience</p>
                                        </div>
                                    </label>
                                </div>
                            </div>
                            {{-- Category --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Category *</label>
                                <select name="category" required class="w-full px-4 py-2 bg-gray-900/50 border border-white/5 rounded-xl text-white focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition">
                                    <option value="general">General</option>
                                    <option value="teamwork">Teamwork</option>
                                    <option value="leadership">Leadership</option>
                                    <option value="sportsmanship">Sportsmanship</option>
                                </select>
                            </div>
                            {{-- Comment --}}
                            <div>
                                <label class="block text-sm font-medium text-gray-300 mb-2">Comment (Optional)</label>
                                <textarea name="comment"
                                          rows="3"
                                          maxlength="500"
                                          placeholder="Share your experience with this player..."
                                          class="w-full px-4 py-2 bg-gray-900/50 border border-white/5 rounded-xl text-white placeholder-gray-500 focus:border-green-500 focus:ring-2 focus:ring-green-500/20 transition"></textarea>
                                <p class="text-xs text-gray-500 mt-1">Max 500 characters</p>
                            </div>
                            <button type="submit"
                                    class="w-full px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition">
                                {{ $myVote ? 'Update Vote' : 'Submit Vote' }}
                            </button>
                        </form>
                    </div>
                @endif
            @else
                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-xl p-4 mb-6 text-center">
                    <p class="text-yellow-400">
                        <a href="{{ route('login') }}" class="font-semibold hover:underline">Login</a> to vote on this player's reputation.
                    </p>
                </div>
            @endauth
            {{-- Recent Votes --}}
            <div class="glass-card backdrop-blur rounded-xl p-6">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Recent Votes ({{ $recentVotes->count() }})</h2>
                <div class="space-y-3">
                    @forelse($recentVotes as $vote)
                        <div class="bg-gray-900/50 rounded-xl p-4">
                            <div class="flex items-start justify-between mb-2">
                                <div class="flex items-center gap-3">
                                    <img src="{{ $vote->voter->avatar_display }}" alt="{{ $vote->voter->name }}" class="w-10 h-10 rounded-full">
                                    <div>
                                        <p class="font-semibold text-white">{{ $vote->voter->name }}</p>
                                        <p class="text-xs text-gray-400">{{ $vote->created_at->diffForHumans() }}</p>
                                    </div>
                                </div>
                                <div class="flex items-center gap-2">
                                    <span class="px-3 py-1 rounded-full text-sm font-semibold
                                        {{ $vote->vote_type === 'positive' ? 'bg-green-600/20 text-green-400' : 'bg-red-600/20 text-red-400' }}">
                                        {{ $vote->vote_type === 'positive' ? '+Rep' : '-Rep' }}
                                    </span>
                                    <span class="px-3 py-1 rounded-full text-sm bg-white/3 text-gray-300 capitalize">
                                        {{ $vote->category }}
                                    </span>
                                </div>
                            </div>
                            @if($vote->comment)
                                <p class="text-sm text-gray-300 italic pl-13">"{{ $vote->comment }}"</p>
                            @endif
                        </div>
                    @empty
                        <p class="text-center text-gray-400 py-8">No votes yet.</p>
                    @endforelse
                </div>
            </div>
        </div>

@endsection
