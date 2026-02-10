@extends('layouts.app')
@section('title', 'Ranked Leaderboard')
@section('content')
    <div class="py-12">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600/10 to-indigo-600/10 border border-purple-500/20 rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">Ranked Leaderboard</h1>
                    <p class="text-gray-400">Competitive skill ratings powered by Glicko-2</p>
                </div>
                <div class="hidden sm:flex items-center gap-3">
                    <a href="{{ route('ranked.about') }}" class="px-4 py-2 rounded-xl bg-purple-500/20 border border-purple-500/30 text-purple-300 text-sm hover:bg-purple-500/30 transition">
                        How it works
                    </a>
                    @auth
                        @php $myRating = auth()->user()->playerRating @endphp
                        @if(!$myRating || !$myRating->opted_in_at)
                            <form action="{{ route('ranked.opt-in') }}" method="POST">
                                @csrf
                                <button type="submit" class="px-4 py-2 rounded-xl bg-purple-600 text-white text-sm font-medium hover:bg-purple-700 transition">
                                    Enable Competitive
                                </button>
                            </form>
                        @endif
                    @endauth
                </div>
            </div>
        </div>

        {{-- Stats Summary --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="glass-card rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-white">{{ number_format($totalCompetitive) }}</div>
                <div class="text-sm text-gray-400">Competitive Players</div>
            </div>
            <div class="glass-card rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-purple-400">{{ number_format($totalPlaced) }}</div>
                <div class="text-sm text-gray-400">Placed Players</div>
            </div>
            <div class="glass-card rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-yellow-400">{{ $ratings->firstWhere('rank_tier', 'gold') ? 'Gold+' : '-' }}</div>
                <div class="text-sm text-gray-400">Median Tier</div>
            </div>
            <div class="glass-card rounded-xl p-4 text-center">
                <div class="text-2xl font-bold text-red-400">{{ $ratings->where('rank_tier', 'elite')->count() }}</div>
                <div class="text-sm text-gray-400">Elite Players</div>
            </div>
        </div>

        @if(session('success'))
            <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 mb-6">
                <p class="text-green-400">{{ session('success') }}</p>
            </div>
        @endif
        @if(session('error'))
            <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-6">
                <p class="text-red-400">{{ session('error') }}</p>
            </div>
        @endif

        {{-- Leaderboard Table --}}
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3 w-16">#</th>
                            <th class="px-4 py-3">Player</th>
                            <th class="px-4 py-3 text-center">Rating</th>
                            <th class="px-4 py-3 text-center">Tier</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">Confidence</th>
                            <th class="px-4 py-3 text-center hidden md:table-cell">Ranked K/D</th>
                            <th class="px-4 py-3 text-center hidden lg:table-cell">Games</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @forelse($ratings as $index => $rating)
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-4 py-3 text-gray-400 font-mono">
                                    {{ $ratings->firstItem() + $index }}
                                </td>
                                <td class="px-4 py-3">
                                    <a href="{{ route('ranked.show', $rating->user_id) }}" class="flex items-center gap-3 hover:text-purple-400 transition">
                                        <img src="{{ $rating->custom_avatar ? Storage::url($rating->custom_avatar) : ($rating->avatar_full ?? $rating->avatar ?? '') }}"
                                             alt="" class="w-8 h-8 rounded-full bg-white/5">
                                        <span class="text-white font-medium">{{ $rating->name }}</span>
                                    </a>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @if($rating->is_placed)
                                        <span class="text-white font-bold">{{ number_format($rating->rating, 0) }}</span>
                                    @else
                                        <span class="text-gray-500">Placement {{ $rating->placement_games }}/10</span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php $tierConfig = \App\Models\PlayerRating::TIERS[$rating->rank_tier] ?? \App\Models\PlayerRating::TIERS['unranked'] @endphp
                                    @if($tierConfig['icon'])
                                        <img src="{{ $tierConfig['icon'] }}" alt="{{ $tierConfig['label'] }}" class="w-10 h-10 object-contain inline-block" title="{{ $tierConfig['label'] }}">
                                    @else
                                        <span class="inline-flex items-center px-2 py-1 rounded-full text-xs font-medium border {{ $tierConfig['bg'] }} {{ $tierConfig['color'] }}">
                                            {{ $tierConfig['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center hidden md:table-cell">
                                    @php
                                        $rd = $rating->rating_deviation;
                                        $conf = $rd <= 60 ? 'High' : ($rd <= 120 ? 'Medium' : 'Low');
                                        $confColor = $rd <= 60 ? 'text-green-400' : ($rd <= 120 ? 'text-yellow-400' : 'text-red-400');
                                    @endphp
                                    <span class="{{ $confColor }}">{{ $conf }}</span>
                                </td>
                                <td class="px-4 py-3 text-center hidden md:table-cell text-gray-300">
                                    {{ $rating->ranked_deaths > 0 ? number_format($rating->ranked_kills / $rating->ranked_deaths, 2) : $rating->ranked_kills }}
                                </td>
                                <td class="px-4 py-3 text-center hidden lg:table-cell text-gray-400">
                                    {{ number_format($rating->games_played) }}
                                </td>
                            </tr>
                        @empty
                            <tr>
                                <td colspan="7" class="px-4 py-12 text-center text-gray-500">
                                    No ranked players yet. Be the first to enable competitive mode!
                                </td>
                            </tr>
                        @endforelse
                    </tbody>
                </table>
            </div>

            @if($ratings->hasPages())
                <div class="px-4 py-3 border-t border-white/5">
                    {{ $ratings->links() }}
                </div>
            @endif
        </div>

    </div>
@endsection
