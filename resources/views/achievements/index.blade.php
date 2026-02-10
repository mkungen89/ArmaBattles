@extends('layouts.app')
@section('title', 'Achievements')
@section('content')
    <div class="py-12" x-data="{ showcaseOpen: false }" x-init="console.log('Alpine initialized, showcaseOpen:', showcaseOpen)">

            {{-- Header --}}
            <div class="bg-gradient-to-r from-green-600/10 to-emerald-600/10 border border-green-500/20 rounded-2xl p-6 mb-6">
                <div class="flex items-center justify-between flex-wrap gap-4">
                    <div>
                        <h1 class="text-3xl font-bold text-white mb-2">Achievements</h1>
                        <p class="text-gray-400">Track your progress and showcase your accomplishments</p>
                    </div>
                    <div class="flex items-center gap-4">
                        @auth
                            @if(auth()->user()->player_uuid)
                                <div class="text-right">
                                    <p class="text-sm text-gray-400">Your Progress</p>
                                    <p class="text-2xl font-bold text-white">{{ $earnedAchievements->count() }} / {{ $achievements->count() }}</p>
                                    <p class="text-xs text-gray-500">{{ $achievements->count() > 0 ? round(($earnedAchievements->count() / $achievements->count()) * 100, 1) : 0 }}% Complete</p>
                                </div>
                                <button type="button"
                                        @click="console.log('Button clicked'); showcaseOpen = true; console.log('showcaseOpen set to:', showcaseOpen)"
                                        class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition flex items-center gap-2">
                                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    Manage Showcase
                                </button>
                            @endif
                        @endauth
                    </div>
                </div>
            </div>
            {{-- Category Filter --}}
            <div class="flex flex-wrap gap-2 mb-6">
                <a href="{{ route('achievements.index', ['category' => 'all']) }}"
                   class="px-4 py-2 rounded-xl transition {{ $category === 'all' ? 'bg-green-600 text-white' : 'glass-card text-gray-400 hover:bg-white/5' }}">
                    All
                </a>
                @foreach($categories as $cat)
                    <a href="{{ route('achievements.index', ['category' => $cat]) }}"
                       class="px-4 py-2 rounded-xl transition capitalize {{ $category === $cat ? 'bg-green-600 text-white' : 'glass-card text-gray-400 hover:bg-white/5' }}">
                        {{ ucwords(str_replace('_', ' ', $cat)) }}
                    </a>
                @endforeach
            </div>
            {{-- Achievements Grid --}}
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-6">
                @foreach($achievements as $achievement)
                    @php
                        $isEarned = $earnedAchievements->contains($achievement->id);
                        $progress = $achievementProgress->get($achievement->id);
                        $rarity = $achievement->calculated_rarity;
                        $isRare = $rarity < 1.0;
                        $isUltraRare = $rarity < 0.1;
                    @endphp
                    <div class="achievement-card glass-card backdrop-blur {{ $isEarned ? 'border border-green-500/30 achievement-earned' : '' }} rounded-xl p-6 relative overflow-hidden group hover:border-green-500/30 transition"
                         x-data="{ showDetails: false }">
                        {{-- Rare Badge --}}
                        @if($isEarned && ($isRare || $isUltraRare))
                            <div class="absolute top-2 right-2 px-2 py-1 rounded-xl text-xs font-bold
                                {{ $isUltraRare ? 'bg-gradient-to-r from-green-500 to-emerald-500' : 'bg-gradient-to-r from-yellow-500 to-orange-500' }}
                                text-white shadow-lg">
                                {{ $isUltraRare ? 'ULTRA RARE' : 'RARE' }}
                            </div>
                        @endif
                        {{-- Earned Badge --}}
                        @if($isEarned)
                            <div class="absolute top-2 left-2">
                                <svg class="w-8 h-8 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                </svg>
                            </div>
                        @endif
                        {{-- Icon --}}
                        <div class="flex items-center justify-center mb-4">
                            @if($achievement->badge_path && $isEarned)
                                <x-blur-image src="{{ asset('storage/' . $achievement->badge_path) }}"
                                     alt="{{ $achievement->name }}"
                                     class="w-24 h-24 object-contain {{ !$isEarned ? 'opacity-30 grayscale' : '' }}" />
                            @else
                                <div class="w-24 h-24 rounded-full flex items-center justify-center {{ !$isEarned ? 'opacity-30 grayscale' : '' }}"
                                     style="background-color: {{ $achievement->color }}20;">
                                    <i data-lucide="{{ $achievement->icon }}"
                                       class="w-12 h-12"
                                       style="color: {{ $isEarned ? $achievement->color : '#6b7280' }};"></i>
                                </div>
                            @endif
                        </div>
                        {{-- Name & Category --}}
                        <h3 class="text-lg font-bold text-white text-center mb-1 {{ !$isEarned ? 'opacity-50' : '' }}">
                            {{ $achievement->name }}
                        </h3>
                        <p class="text-xs text-center mb-2">
                            <span class="px-2 py-1 rounded bg-white/3 text-gray-400 capitalize">
                                {{ ucwords(str_replace('_', ' ', $achievement->category)) }}
                            </span>
                        </p>
                        {{-- Description --}}
                        <p class="text-sm text-gray-400 text-center mb-3 {{ !$isEarned ? 'opacity-50' : '' }}">
                            {{ $achievement->description }}
                        </p>
                        {{-- Progress Bar (if not earned and has progress) --}}
                        @if(!$isEarned && $progress)
                            <div class="mb-3">
                                <div class="flex justify-between text-xs text-gray-400 mb-1">
                                    <span>Progress</span>
                                    <span>{{ number_format($progress->current_value) }} / {{ number_format($progress->target_value) }}</span>
                                </div>
                                <div class="w-full bg-white/5 rounded-full h-2 overflow-hidden">
                                    <div class="bg-gradient-to-r from-green-500 to-emerald-500 h-2 rounded-full transition-all duration-500"
                                         style="width: {{ min($progress->percentage, 100) }}%"></div>
                                </div>
                                <p class="text-xs text-center text-green-400 mt-1 font-semibold">{{ round($progress->percentage, 1) }}% Complete</p>
                            </div>
                        @endif
                        {{-- Stats --}}
                        <div class="flex items-center justify-between text-xs border-t border-white/5 pt-3">
                            <div class="text-center flex-1">
                                <p class="text-gray-500">Points</p>
                                <p class="text-white font-semibold">{{ $achievement->points }}</p>
                            </div>
                            <div class="text-center flex-1 border-l border-white/5">
                                <p class="text-gray-500">Rarity</p>
                                <p class="font-semibold {{ $rarity < 1 ? 'text-green-400' : ($rarity < 10 ? 'text-blue-400' : 'text-gray-400') }}">
                                    {{ $rarity }}%
                                </p>
                            </div>
                            <div class="text-center flex-1 border-l border-white/5">
                                <p class="text-gray-500">Unlocked</p>
                                <p class="text-white font-semibold">{{ number_format($achievement->unlock_count) }}</p>
                            </div>
                        </div>
                        {{-- Pin Button (if earned and logged in) --}}
                        @auth
                            @if($isEarned && auth()->user()->player_uuid)
                                <button onclick="togglePin({{ $achievement->id }})"
                                        id="pin-btn-{{ $achievement->id }}"
                                        class="mt-3 w-full px-3 py-2 bg-green-600/20 hover:bg-green-600/30 text-green-400 text-sm rounded-xl transition flex items-center justify-center gap-2">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/>
                                    </svg>
                                    <span>Pin to Showcase</span>
                                </button>
                            @endif
                        @endauth
                    </div>
                @endforeach
            </div>
            {{-- Empty State --}}
            @if($achievements->isEmpty())
                <div class="text-center py-12">
                    <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
                    </svg>
                    <h3 class="text-xl font-semibold text-gray-400 mb-2">No Achievements Found</h3>
                    <p class="text-gray-500">Check back later for new achievements!</p>
                </div>
            @endif

    {{-- Showcase Management Modal (if logged in with player_uuid) --}}
    @auth
        @if(auth()->user()->player_uuid)
            <div x-show="showcaseOpen"
                 x-init="console.log('Modal div initialized, showcaseOpen:', showcaseOpen)"
                 x-effect="console.log('Modal visibility changed, showcaseOpen:', showcaseOpen, 'display:', $el.style.display)"
                 @click.self="showcaseOpen = false"
                 @keydown.escape.window="showcaseOpen = false"
                 class="fixed inset-0 bg-black/70 backdrop-blur-sm flex items-center justify-center p-4"
                 style="z-index: 9999 !important;">
                <div x-data="{ pinnedIds: @js(optional(\App\Models\AchievementShowcase::where('player_uuid', auth()->user()->player_uuid)->first())->pinned_achievements ?? []) }"
                     x-init="console.log('Inner modal card rendered!')"
                     @click.stop
                     class="bg-red-900 border-4 border-yellow-500 rounded-2xl p-6 max-w-2xl w-full max-h-[80vh] overflow-y-auto"
                     style="z-index: 10000 !important;">
                    <div class="flex items-center justify-between mb-4">
                        <h2 class="text-2xl font-bold text-white">Achievement Showcase</h2>
                        <button type="button" @click="showcaseOpen = false" class="text-gray-400 hover:text-white transition">
                            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                        <p class="text-gray-400 mb-4">Select up to 3 achievements to showcase on your profile.</p>
                        <div class="mb-4 flex gap-2 flex-wrap">
                            <template x-for="id in pinnedIds" :key="id">
                                <span class="px-3 py-1 bg-green-600/20 text-green-400 rounded-xl text-sm">
                                    Achievement #<span x-text="id"></span>
                                </span>
                            </template>
                        </div>
                        <div class="grid grid-cols-1 sm:grid-cols-2 gap-3">
                            @foreach($achievements->where('id', '!=', null) as $achievement)
                                @php
                                    $isEarned = $earnedAchievements->contains($achievement->id);
                                @endphp
                                @if($isEarned)
                                    <button type="button"
                                            @click="if(pinnedIds.includes({{ $achievement->id }})) { pinnedIds = pinnedIds.filter(id => id !== {{ $achievement->id }}); } else if(pinnedIds.length < 3) { pinnedIds.push({{ $achievement->id }}); }"
                                            :class="pinnedIds.includes({{ $achievement->id }}) ? 'border-green-500 bg-green-500/10' : 'border-white/5'"
                                            class="text-left p-3 border rounded-xl hover:border-green-500/50 transition">
                                        <div class="flex items-center gap-3">
                                            <div class="w-12 h-12 rounded-full flex items-center justify-center flex-shrink-0"
                                                 style="background-color: {{ $achievement->color }}20;">
                                                <i data-lucide="{{ $achievement->icon }}"
                                                   class="w-6 h-6"
                                                   style="color: {{ $achievement->color }};"></i>
                                            </div>
                                            <div class="flex-1 min-w-0">
                                                <p class="text-white font-semibold truncate">{{ $achievement->name }}</p>
                                                <p class="text-xs text-gray-400">{{ $achievement->points }} points</p>
                                            </div>
                                            <div x-show="pinnedIds.includes({{ $achievement->id }})" class="flex-shrink-0">
                                                <svg class="w-5 h-5 text-green-500" fill="currentColor" viewBox="0 0 20 20">
                                                    <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                                                </svg>
                                            </div>
                                        </div>
                                    </button>
                                @endif
                            @endforeach
                        </div>
                        <form action="{{ route('achievements.showcase.update') }}" method="POST" class="mt-6">
                            @csrf
                            <template x-for="(id, index) in pinnedIds" :key="index">
                                <input type="hidden" :name="'pinned_achievements[' + index + ']'" :value="id">
                            </template>
                            <button type="submit"
                                    class="w-full px-6 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-semibold">
                                Save Showcase
                            </button>
                        </form>
                </div>
            </div>
            <script>
                function togglePin(achievementId) {
                    // This is a simple implementation - could be enhanced with API calls
                    console.log('Toggle pin for achievement:', achievementId);
                }
            </script>
        @endif
    @endauth
    </div>
    @push('scripts')
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });

        // Re-initialize icons when Alpine components update
        document.addEventListener('alpine:initialized', () => {
            if (typeof lucide !== 'undefined') {
                lucide.createIcons();
            }
        });
    </script>
    @endpush
@endsection
