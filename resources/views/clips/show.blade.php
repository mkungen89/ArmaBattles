@extends('layouts.app')
@section('title', $clip->title)
@section('content')
<div class="py-12 space-y-6">

    {{-- Back Button --}}
    <div>
        <a href="{{ route('clips.index') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
            Back to Videos
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Main Content --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Video Player --}}
            <div class="glass-card rounded-2xl overflow-hidden" style="{{ $clip->is_featured ? 'border-color: #fbbf24;' : '' }}">
                @if($clip->is_featured)
                <div class="bg-gradient-to-r from-yellow-600/20 to-orange-600/20 border-b border-yellow-500/30 px-4 py-2">
                    <div class="flex items-center gap-2 text-yellow-400 text-sm font-semibold">
                        <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                            <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                        </svg>
                        FEATURED VIDEO
                    </div>
                </div>
                @endif

                <div class="aspect-video bg-black">
                    @if($clip->embed_url)
                        @if($clip->platform === 'youtube')
                            <iframe
                                src="{{ $clip->embed_url }}"
                                class="w-full h-full"
                                frameborder="0"
                                allow="accelerometer; autoplay; clipboard-write; encrypted-media; gyroscope; picture-in-picture"
                                allowfullscreen
                            ></iframe>
                        @elseif($clip->platform === 'twitch')
                            <iframe
                                src="{{ $clip->embed_url }}"
                                class="w-full h-full"
                                frameborder="0"
                                allowfullscreen
                            ></iframe>
                        @endif
                    @else
                        {{-- Fallback for TikTok/Kick or invalid URLs --}}
                        <div class="w-full h-full flex flex-col items-center justify-center text-center p-8">
                            <svg class="w-16 h-16 text-gray-600 mb-4" fill="currentColor" viewBox="0 0 20 20">
                                <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                            </svg>
                            <p class="text-gray-400 mb-3">Embed not available for this platform</p>
                            <a href="{{ $clip->url }}" target="_blank" rel="noopener noreferrer" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition inline-flex items-center gap-2">
                                Watch on {{ $clip->platform_name }}
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </div>
                    @endif
                </div>
            </div>

            {{-- Clip Info --}}
            <div class="glass-card rounded-2xl p-6">
                <h1 class="text-3xl font-bold text-white mb-4">{{ $clip->title }}</h1>

                @if($clip->author)
                <div class="flex items-center gap-2 mb-4 text-gray-400">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
                    </svg>
                    <span class="font-medium">{{ $clip->author }}</span>
                    <span class="text-xs {{ $clip->platform_color }}">({{ $clip->platform_name }})</span>
                </div>
                @endif

                @if($clip->description)
                <div class="text-gray-300 mb-6 leading-relaxed whitespace-pre-wrap">{{ $clip->description }}</div>
                @endif

                <div class="flex flex-wrap items-center gap-4 text-sm">
                    <div class="flex items-center gap-2">
                        <img src="{{ $clip->user->avatar_display }}" alt="{{ $clip->user->name }}" class="w-8 h-8 rounded-full">
                        <span class="text-gray-400">Submitted by</span>
                        <a href="{{ route('players.show', $clip->user) }}" class="text-white hover:text-green-400 transition">
                            {{ $clip->user->name }}
                        </a>
                    </div>

                    <span class="text-gray-700">•</span>

                    <span class="text-gray-400">{{ $clip->created_at->diffForHumans() }}</span>

                    <span class="text-gray-700">•</span>

                    <a href="{{ $clip->url }}" target="_blank" rel="noopener noreferrer" class="text-green-400 hover:text-green-300 transition inline-flex items-center gap-1">
                        View Original
                        <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
            </div>

            {{-- Actions --}}
            <div class="flex flex-wrap items-center gap-3">
                @auth
                    {{-- Vote Buttons --}}
                    <div x-data="clipVoting({{ $clip->id }}, {{ $clip->votes }}, {{ $userHasVoted ? 'true' : 'false' }})" class="flex items-center gap-2">
                        <button @click="vote()" :disabled="hasVoted"
                                class="px-4 py-2 rounded-xl transition inline-flex items-center gap-2"
                                :class="hasVoted ? 'bg-green-600/50 text-white cursor-not-allowed' : 'bg-green-600 hover:bg-green-500 text-white'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                            </svg>
                            <span x-text="hasVoted ? 'Voted' : 'Upvote'"></span>
                        </button>

                        <div class="px-4 py-2 bg-white/5 rounded-xl text-white font-bold text-lg">
                            <span x-text="votes"></span>
                        </div>

                        <button @click="unvote()" :disabled="!hasVoted"
                                class="px-4 py-2 rounded-xl transition inline-flex items-center gap-2"
                                :class="!hasVoted ? 'bg-gray-600/50 text-gray-400 cursor-not-allowed' : 'bg-red-600 hover:bg-red-500 text-white'">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                            </svg>
                            Remove Vote
                        </button>
                    </div>

                    @if(Auth::user()->isAdmin())
                        <span class="text-gray-700">|</span>

                        {{-- Feature/Unfeature --}}
                        <form method="POST" action="{{ $clip->is_featured ? route('clips.unfeature', $clip) : route('clips.feature', $clip) }}" class="inline">
                            @csrf
                            <button type="submit" class="px-4 py-2 rounded-xl transition inline-flex items-center gap-2 {{ $clip->is_featured ? 'bg-gray-600 hover:bg-gray-500' : 'bg-yellow-600 hover:bg-yellow-500' }} text-white">
                                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.54 1.118l-2.8-2.034a1 1 0 00-1.175 0l-2.8 2.034c-.784.57-1.838-.197-1.539-1.118l1.07-3.292a1 1 0 00-.364-1.118L2.98 8.72c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                                </svg>
                                {{ $clip->is_featured ? 'Unfeature' : 'Feature' }}
                            </button>
                        </form>
                    @endif

                    @if(Auth::id() === $clip->user_id || Auth::user()->isAdmin())
                        <span class="text-gray-700">|</span>

                        {{-- Delete --}}
                        <form method="POST" action="{{ route('admin.clips.destroy', $clip) }}" class="inline"
                              onsubmit="return confirm('Are you sure you want to delete this video?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition inline-flex items-center gap-2">
                                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                </svg>
                                Delete
                            </button>
                        </form>
                    @endif
                @else
                    <div class="px-4 py-2 bg-white/5 rounded-xl text-gray-400">
                        <a href="{{ route('auth.steam') }}" class="text-green-400 hover:text-green-300">Log in</a> to vote on videos
                    </div>
                @endauth
            </div>
        </div>

        {{-- Sidebar --}}
        <div class="space-y-6">
            {{-- Stats Card --}}
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Stats</h3>
                <div class="space-y-3">
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Votes</span>
                        <span class="text-white font-bold">{{ $clip->votes }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Voters</span>
                        <span class="text-white font-bold">{{ $clip->clipVotes->count() }}</span>
                    </div>
                    <div class="flex justify-between items-center">
                        <span class="text-gray-400">Status</span>
                        <span class="px-2 py-0.5 rounded text-xs font-semibold uppercase
                            @if($clip->status === 'approved') bg-green-500/20 text-green-400
                            @elseif($clip->status === 'pending') bg-yellow-500/20 text-yellow-400
                            @else bg-red-500/20 text-red-400
                            @endif">
                            @if($clip->status === 'approved')
                                ✓ Approved
                            @elseif($clip->status === 'pending')
                                ⏳ Awaiting Approval
                            @else
                                ✗ Rejected
                            @endif
                        </span>
                    </div>
                </div>
            </div>

            {{-- Pending Message --}}
            @if($clip->status === 'pending')
            <div class="glass-card rounded-2xl p-4 border border-yellow-500/30">
                <div class="flex items-start gap-3">
                    <svg class="w-5 h-5 text-yellow-400 flex-shrink-0 mt-0.5" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M18 10a8 8 0 11-16 0 8 8 0 0116 0zm-7-4a1 1 0 11-2 0 1 1 0 012 0zM9 9a1 1 0 000 2v3a1 1 0 001 1h1a1 1 0 100-2v-3a1 1 0 00-1-1H9z" clip-rule="evenodd"/>
                    </svg>
                    <div>
                        <p class="text-sm font-medium text-yellow-400 mb-1">Under Review</p>
                        <p class="text-xs text-gray-400">This video is awaiting moderator approval before it appears publicly.</p>
                    </div>
                </div>
            </div>
            @endif

            {{-- Recent Voters --}}
            @if($clip->clipVotes->count() > 0)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">Recent Voters</h3>
                <div class="space-y-2">
                    @foreach($clip->clipVotes->take(10) as $vote)
                    <div class="flex items-center gap-2">
                        <img src="{{ $vote->user->avatar_display }}" alt="{{ $vote->user->name }}" class="w-6 h-6 rounded-full">
                        <a href="{{ route('players.show', $vote->user) }}" class="text-sm text-gray-300 hover:text-white transition">
                            {{ $vote->user->name }}
                        </a>
                        @if($vote->vote_type === 'upvote')
                        <svg class="w-3 h-3 text-green-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 15l7-7 7 7"/>
                        </svg>
                        @else
                        <svg class="w-3 h-3 text-red-400 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                        </svg>
                        @endif
                    </div>
                    @endforeach

                    @if($clip->clipVotes->count() > 10)
                    <p class="text-xs text-gray-500 mt-2">+ {{ $clip->clipVotes->count() - 10 }} more</p>
                    @endif
                </div>
            </div>
            @endif

            {{-- More from this creator --}}
            @php
                $moreClips = \App\Models\HighlightClip::where('user_id', $clip->user_id)
                    ->where('id', '!=', $clip->id)
                    ->where('status', 'approved')
                    ->orderByDesc('created_at')
                    ->limit(3)
                    ->get();
            @endphp

            @if($moreClips->count() > 0)
            <div class="glass-card rounded-2xl p-6">
                <h3 class="text-lg font-bold text-white mb-4">More from {{ $clip->user->name }}</h3>
                <div class="space-y-3">
                    @foreach($moreClips as $otherClip)
                    <a href="{{ route('clips.show', $otherClip) }}" class="block hover:bg-white/5 rounded-xl p-2 transition group">
                        <div class="flex gap-3">
                            <div class="w-16 h-16 bg-gray-900 rounded flex-shrink-0 flex items-center justify-center">
                                <svg class="w-6 h-6 text-gray-600 group-hover:text-green-500 transition" fill="currentColor" viewBox="0 0 20 20">
                                    <path d="M10 18a8 8 0 100-16 8 8 0 000 16zM9.555 7.168A1 1 0 008 8v4a1 1 0 001.555.832l3-2a1 1 0 000-1.664l-3-2z"/>
                                </svg>
                            </div>
                            <div class="flex-1 min-w-0">
                                <p class="text-sm font-medium text-white group-hover:text-green-400 transition line-clamp-2">{{ $otherClip->title }}</p>
                                <p class="text-xs text-gray-500">{{ $otherClip->votes }} votes</p>
                            </div>
                        </div>
                    </a>
                    @endforeach
                </div>
            </div>
            @endif
        </div>
    </div>
</div>

@push('scripts')
<script>
function clipVoting(clipId, initialVotes, initialHasVoted) {
    return {
        votes: initialVotes,
        hasVoted: initialHasVoted,

        async vote() {
            if (this.hasVoted) return;

            try {
                const response = await fetch(`/clips/${clipId}/vote`, {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ vote_type: 'upvote' })
                });

                const data = await response.json();

                if (data.success) {
                    this.votes = data.votes;
                    this.hasVoted = true;
                }
            } catch (error) {
                console.error('Vote failed:', error);
            }
        },

        async unvote() {
            if (!this.hasVoted) return;

            try {
                const response = await fetch(`/clips/${clipId}/vote`, {
                    method: 'DELETE',
                    headers: {
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    }
                });

                const data = await response.json();

                if (data.success) {
                    this.votes = data.votes;
                    this.hasVoted = false;
                }
            } catch (error) {
                console.error('Unvote failed:', error);
            }
        }
    }
}
</script>
@endpush
@endsection
