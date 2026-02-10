@extends('layouts.app')
@section('title', 'Player Reputation')
@section('content')
    <div class="py-12">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Player Reputation</h1>
                        <p class="text-gray-400">Community-driven player ratings and commendations</p>
                    </div>
                    <div class="hidden sm:block">
                        <svg class="w-16 h-16 text-green-500/30" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
                        </svg>
                    </div>
                </div>
            </div>
            {{-- Info Box --}}
            <div class="bg-green-500/10 border border-green-500/30 rounded-xl p-4 mb-6">
                <div class="flex items-start gap-3">
                    <svg class="w-6 h-6 text-green-400 flex-shrink-0 mt-0.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <div class="text-sm text-gray-300">
                        <p class="font-semibold text-white mb-1">How Reputation Works</p>
                        <ul class="list-disc list-inside space-y-1">
                            <li>Give +Rep or -Rep to players based on your experience with them</li>
                            <li>You can vote once per player every 24 hours</li>
                            <li>Choose categories: Teamwork, Leadership, Sportsmanship, or General</li>
                            <li>Players with 100+ rep earn "Trusted Player" status</li>
                            <li>Players with -50 or lower are flagged for review</li>
                        </ul>
                    </div>
                </div>
            </div>
            {{-- Tabs --}}
            <div x-data="{ tab: 'top' }" class="space-y-6">
                <div class="flex flex-wrap gap-2 border-b border-white/5">
                    <button @click="tab = 'top'"
                            :class="tab === 'top' ? 'border-green-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                        Top Players ({{ $topPlayers->count() }})
                    </button>
                    <button @click="tab = 'trusted'"
                            :class="tab === 'trusted' ? 'border-green-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                        Trusted Players ({{ $trustedPlayers->count() }})
                    </button>
                    <button @click="tab = 'flagged'"
                            :class="tab === 'flagged' ? 'border-red-500 text-white' : 'border-transparent text-gray-400 hover:text-white'"
                            class="px-4 py-2 border-b-2 font-medium transition">
                        Flagged Players ({{ $flaggedPlayers->count() }})
                    </button>
                </div>
                {{-- Top Players Tab --}}
                <div x-show="tab === 'top'" x-transition>
                    <div class="glass-card backdrop-blur rounded-xl overflow-hidden">
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-900/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Rank</th>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Reputation</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">+Rep</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">-Rep</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @forelse($topPlayers as $index => $rep)
                                        <tr class="hover:bg-white/5 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <span class="text-2xl font-bold
                                                    {{ $index === 0 ? 'text-yellow-400' : ($index === 1 ? 'text-gray-300' : ($index === 2 ? 'text-orange-400' : 'text-gray-500')) }}">
                                                    #{{ $index + 1 }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('reputation.show', $rep->user) }}" class="flex items-center hover:text-green-400 transition">
                                                    <img src="{{ $rep->user->avatar_display }}" alt="{{ $rep->user->name }}" class="w-10 h-10 rounded-full mr-3">
                                                    <div>
                                                        <p class="font-semibold text-white">{{ $rep->user->name }}</p>
                                                        <p class="text-xs text-gray-400">{{ $rep->user->role }}</p>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-2xl font-bold {{ $rep->badge_color }}">
                                                    {{ $rep->total_score > 0 ? '+' : '' }}{{ $rep->total_score }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-green-400">{{ $rep->positive_votes }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-red-400">{{ $rep->negative_votes }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="px-3 py-1 rounded-full text-xs font-semibold {{ $rep->badge_color }} bg-white/3">
                                                    {{ $rep->label }}
                                                </span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="{{ route('reputation.show', $rep->user) }}"
                                                   class="px-3 py-1 bg-green-600/20 hover:bg-green-600/30 text-green-400 text-sm rounded-xl transition">
                                                    View
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="7" class="px-6 py-12 text-center text-gray-400">
                                                No players with reputation yet.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
                {{-- Trusted Players Tab --}}
                <div x-show="tab === 'trusted'" x-transition style="display:none;">
                    <div class="glass-card backdrop-blur border border-green-500/30 rounded-xl p-6">
                        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                            @forelse($trustedPlayers as $rep)
                                <div class="bg-gray-900/50 border border-green-500/20 rounded-xl p-4">
                                    <div class="flex items-center gap-3 mb-3">
                                        <img src="{{ $rep->user->avatar_display }}" alt="{{ $rep->user->name }}" class="w-12 h-12 rounded-full">
                                        <div class="flex-1 min-w-0">
                                            <p class="font-semibold text-white truncate">{{ $rep->user->name }}</p>
                                            <p class="text-sm text-green-400 font-bold">+{{ $rep->total_score }} Rep</p>
                                        </div>
                                        <svg class="w-8 h-8 text-green-400" fill="currentColor" viewBox="0 0 20 20">
                                            <path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                        </svg>
                                    </div>
                                    <div class="flex justify-between text-xs text-gray-400 mb-3">
                                        <span>+{{ $rep->positive_votes }} positive</span>
                                        <span>-{{ $rep->negative_votes }} negative</span>
                                    </div>
                                    <a href="{{ route('reputation.show', $rep->user) }}"
                                       class="block w-full text-center px-3 py-2 bg-green-600/20 hover:bg-green-600/30 text-green-400 text-sm rounded-xl transition">
                                        View Profile
                                    </a>
                                </div>
                            @empty
                                <div class="col-span-full text-center py-12 text-gray-400">
                                    No trusted players yet. Be the first to reach 100+ reputation!
                                </div>
                            @endforelse
                        </div>
                    </div>
                </div>
                {{-- Flagged Players Tab --}}
                <div x-show="tab === 'flagged'" x-transition style="display:none;">
                    <div class="glass-card backdrop-blur border border-red-500/30 rounded-xl overflow-hidden">
                        @if(auth()->check() && auth()->user()->isAdmin())
                            <div class="bg-red-500/10 border-b border-red-500/30 p-4">
                                <p class="text-sm text-red-400">
                                    <strong>Admin Note:</strong> These players have -50 or lower reputation and may require review.
                                </p>
                            </div>
                        @endif
                        <div class="overflow-x-auto">
                            <table class="w-full">
                                <thead class="bg-gray-900/50">
                                    <tr>
                                        <th class="px-6 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Reputation</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Votes</th>
                                        <th class="px-6 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                                    </tr>
                                </thead>
                                <tbody class="divide-y divide-white/5">
                                    @forelse($flaggedPlayers as $rep)
                                        <tr class="hover:bg-white/5 transition">
                                            <td class="px-6 py-4 whitespace-nowrap">
                                                <a href="{{ route('reputation.show', $rep->user) }}" class="flex items-center hover:text-red-400 transition">
                                                    <img src="{{ $rep->user->avatar_display }}" alt="{{ $rep->user->name }}" class="w-10 h-10 rounded-full mr-3">
                                                    <div>
                                                        <p class="font-semibold text-white">{{ $rep->user->name }}</p>
                                                        <p class="text-xs text-gray-400">{{ $rep->user->role }}</p>
                                                    </div>
                                                </a>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-2xl font-bold text-red-400">{{ $rep->total_score }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <span class="text-green-400">+{{ $rep->positive_votes }}</span>
                                                <span class="text-gray-500 mx-2">/</span>
                                                <span class="text-red-400">-{{ $rep->negative_votes }}</span>
                                            </td>
                                            <td class="px-6 py-4 whitespace-nowrap text-center">
                                                <a href="{{ route('reputation.show', $rep->user) }}"
                                                   class="px-3 py-1 bg-red-600/20 hover:bg-red-600/30 text-red-400 text-sm rounded-xl transition">
                                                    Review
                                                </a>
                                            </td>
                                        </tr>
                                    @empty
                                        <tr>
                                            <td colspan="4" class="px-6 py-12 text-center text-gray-400">
                                                No flagged players.
                                            </td>
                                        </tr>
                                    @endforelse
                                </tbody>
                            </table>
                        </div>
                    </div>
                </div>
            </div>
        </div>

@endsection
