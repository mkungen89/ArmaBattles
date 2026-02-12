@props(['user', 'reputation', 'playerRating' => null, 'gameStats' => null, 'isOwner' => true])

<div class="relative glass-card overflow-hidden glow-green-sm">
    {{-- Background gradient --}}
    <div class="absolute inset-0 bg-gradient-to-br from-green-600/10 via-transparent to-emerald-600/5 pointer-events-none"></div>

    <div class="relative p-6 sm:p-8">
        <div class="flex flex-col sm:flex-row items-center sm:items-start gap-6">
            {{-- Avatar --}}
            <div class="relative flex-shrink-0">
                <x-blur-image src="{{ $user->avatar_display }}"
                    alt="{{ $user->name }}"
                    class="w-28 h-28 rounded-2xl ring-2 ring-green-500/40 shadow-lg shadow-green-500/10" />
                {{-- Online indicator --}}
                @if($user->last_seen_at && $user->last_seen_at->diffInMinutes(now()) < 5)
                    <span class="absolute -bottom-1 -right-1 w-5 h-5 bg-green-500 rounded-full border-2 border-gray-900 animate-live-glow" title="Online now"></span>
                @endif
            </div>

            {{-- Info --}}
            <div class="flex-1 text-center sm:text-left min-w-0">
                <div class="flex flex-col sm:flex-row items-center sm:items-center gap-2.5 mb-3 flex-wrap">
                    <h1 class="text-2xl sm:text-3xl font-bold text-white tracking-tight">{{ $user->name }}</h1>

                    {{-- Role pill --}}
                    <span class="px-2.5 py-0.5 rounded-lg text-xs font-semibold uppercase tracking-wider
                        @if($user->isAdmin()) bg-red-500/15 text-red-400 border border-red-500/20
                        @elseif($user->isModerator()) bg-yellow-500/15 text-yellow-400 border border-yellow-500/20
                        @else bg-green-500/15 text-green-400 border border-green-500/20
                        @endif">
                        {{ ucfirst($user->role) }}
                    </span>

                    {{-- Rep badge --}}
                    <a href="{{ route('reputation.show', $user) }}" class="inline-flex items-center gap-1 px-2.5 py-0.5 rounded-lg text-xs font-medium {{ $reputation->badge_color }} bg-white/5 border border-white/10 hover:bg-white/10 transition" aria-label="Reputation: {{ $reputation->total_score }}">
                        @if($reputation->isTrusted())
                            <svg class="w-3.5 h-3.5 text-green-400" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M6.267 3.455a3.066 3.066 0 001.745-.723 3.066 3.066 0 013.976 0 3.066 3.066 0 001.745.723 3.066 3.066 0 012.812 2.812c.051.643.304 1.254.723 1.745a3.066 3.066 0 010 3.976 3.066 3.066 0 00-.723 1.745 3.066 3.066 0 01-2.812 2.812 3.066 3.066 0 00-1.745.723 3.066 3.066 0 01-3.976 0 3.066 3.066 0 00-1.745-.723 3.066 3.066 0 01-2.812-2.812 3.066 3.066 0 00-.723-1.745 3.066 3.066 0 010-3.976 3.066 3.066 0 00.723-1.745 3.066 3.066 0 012.812-2.812zm7.44 5.252a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/></svg>
                        @endif
                        {{ $reputation->total_score > 0 ? '+' : '' }}{{ $reputation->total_score }} Rep
                    </a>

                    {{-- Ranked badge --}}
                    @if(isset($playerRating) && $playerRating && $playerRating->opted_in_at)
                        @php $rTier = \App\Models\PlayerRating::TIERS[$playerRating->rank_tier] ?? \App\Models\PlayerRating::TIERS['unranked'] @endphp
                        <a href="{{ route('ranked.show', $user) }}" class="inline-flex items-center gap-1 px-2 py-0.5 rounded-lg text-xs font-medium {{ $rTier['color'] }} bg-white/5 border border-white/10 hover:bg-white/10 transition">
                            @if($rTier['icon'])
                                <img src="{{ $rTier['icon'] }}" alt="{{ $rTier['label'] }}" class="w-10 h-10 object-contain" title="{{ $rTier['label'] }}">
                            @endif
                            @if($playerRating->is_placed)
                                {{ number_format($playerRating->rating, 0) }}
                            @else
                                Placement {{ $playerRating->placement_games }}/10
                            @endif
                        </a>
                    @endif

                    {{-- Level badge (shows progress to next rank) --}}
                    @if($gameStats && isset($gameStats->level))
                        @php
                            $levelService = app(\App\Services\PlayerLevelService::class);
                            $currentRank = $levelService->getRankForLevel($gameStats->level);
                            $rankInfo = \App\Models\RankLogo::forRank($currentRank);
                        @endphp
                        @if($rankInfo)
                            @php
                                $levelInRank = $gameStats->level - ($rankInfo->min_level - 1); // Level within current rank (1-10)
                                $levelsInRank = 10; // Each rank spans 10 levels
                            @endphp
                            <span class="inline-flex items-center gap-1.5 px-2.5 py-0.5 rounded-lg text-xs font-semibold border"
                                  style="background-color: {{ $rankInfo->color }}15; color: {{ $rankInfo->color }}; border-color: {{ $rankInfo->color }}40;"
                                  title="Level {{ $gameStats->level }} - {{ $levelInRank }}/{{ $levelsInRank }} levels in {{ $rankInfo->name }}">
                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                                </svg>
                                Level {{ $gameStats->level }}
                                <span class="opacity-60 text-[10px]">({{ $levelInRank }}/{{ $levelsInRank }})</span>
                            </span>
                        @endif
                    @endif
                </div>

                {{-- KPI row --}}
                @if($gameStats)
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-x-5 gap-y-1 mb-3">
                    <span class="text-sm"><span class="text-green-400 font-bold">{{ number_format($gameStats->kills) }}</span> <span class="text-gray-500">kills</span></span>
                    <span class="text-gray-700">&bull;</span>
                    <span class="text-sm"><span class="text-red-400 font-bold">{{ number_format($gameStats->deaths) }}</span> <span class="text-gray-500">deaths</span></span>
                    <span class="text-gray-700">&bull;</span>
                    <span class="text-sm"><span class="text-yellow-400 font-bold">{{ $gameStats->deaths > 0 ? number_format($gameStats->player_kills_count / $gameStats->deaths, 2) : $gameStats->player_kills_count }}</span> <span class="text-gray-500">K/D</span></span>
                    <span class="text-gray-700">&bull;</span>
                    @php $hours = floor($gameStats->playtime_seconds / 3600); @endphp
                    <span class="text-sm"><span class="text-blue-400 font-bold">{{ $hours }}h</span> <span class="text-gray-500">playtime</span></span>
                </div>

                {{-- Rank & Level Progress --}}
                @if($gameStats)
                    @php
                        $levelService = app(\App\Services\PlayerLevelService::class);
                    @endphp
                    <div class="mb-3">
                        <x-profile.rank-progress-bar :gameStats="$gameStats" :levelService="$levelService" />
                    </div>
                @endif
                @endif

                {{-- Meta row --}}
                <div class="flex flex-wrap items-center justify-center sm:justify-start gap-3 text-xs text-gray-500">
                    <span>Steam ID: {{ $user->steam_id }}</span>
                    @if($user->discord_username)
                        <span class="text-indigo-400">{{ $user->discord_username }}</span>
                    @endif
                    @include('profile._social-links', ['user' => $user])
                    <span>Since {{ $user->created_at->format('M Y') }}</span>
                    @if($user->last_seen_at)
                        <span>Last seen {{ $user->last_seen_at->diffForHumans() }}</span>
                    @endif
                </div>
            </div>

            {{-- Action Buttons --}}
            <div class="flex gap-2 flex-shrink-0">
                @if(!$isOwner)
                    @include('components.favorite-button', ['model' => $user, 'type' => 'player'])
                @endif
                @if($gameStats)
                <div x-data="{ open: false }" class="relative">
                    <button @click="open = !open" class="p-2.5 glass-card hover:bg-white/10 transition rounded-xl" aria-label="Export stats">
                        <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                    </button>
                    <div x-show="open" @click.away="open = false" x-cloak x-transition class="absolute right-0 mt-2 w-48 glass-card rounded-xl shadow-2xl z-50 py-1">
                        <a href="{{ route('export.player.stats', $gameStats->player_uuid) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/10 transition">Stats (CSV)</a>
                        <a href="{{ route('export.player.history', $gameStats->player_uuid) }}" class="block px-4 py-2 text-sm text-gray-300 hover:bg-white/10 transition">Match History (CSV)</a>
                    </div>
                </div>
                @endif
                @if($isOwner)
                <a href="{{ route('profile.settings') }}" class="p-2.5 glass-card hover:bg-white/10 transition rounded-xl" aria-label="Settings">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                </a>
                @endif
            </div>
        </div>
    </div>
</div>
