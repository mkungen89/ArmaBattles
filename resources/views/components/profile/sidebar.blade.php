@props(['user', 'team', 'stats', 'recentMatches', 'isOwner' => true])

<div class="space-y-4">
    {{-- Tournament Stats --}}
    <div class="glass-card p-5">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Tournament Stats</h3>
        <div class="grid grid-cols-2 gap-2">
            <div class="bg-white/3 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-white">{{ $stats['tournaments_played'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase">Tournaments</p>
            </div>
            <div class="bg-white/3 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-white">{{ $stats['matches_played'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase">Matches</p>
            </div>
            <div class="bg-white/3 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-green-400">{{ $stats['wins'] }}</p>
                <p class="text-[10px] text-gray-500 uppercase">Wins</p>
            </div>
            <div class="bg-white/3 rounded-lg p-3 text-center">
                <p class="text-xl font-bold text-white">{{ $stats['win_rate'] }}%</p>
                <p class="text-[10px] text-gray-500 uppercase">Win Rate</p>
            </div>
        </div>
    </div>

    {{-- Recent Matches --}}
    @if($recentMatches->count() > 0 && $team)
    <div class="glass-card p-5">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Recent Matches</h3>
        <div class="space-y-2">
            @foreach($recentMatches as $match)
            <div class="flex items-center justify-between bg-white/3 rounded-lg px-3 py-2">
                <div class="flex items-center gap-2 min-w-0">
                    <span class="text-xs font-bold {{ $match->winner_id === $team->id ? 'text-green-400' : 'text-red-400' }}">
                        {{ $match->winner_id === $team->id ? 'W' : 'L' }}
                    </span>
                    <div class="min-w-0">
                        <p class="text-xs text-white truncate">vs {{ $match->team1_id === $team->id ? $match->team2->name : $match->team1->name }}</p>
                        <p class="text-[10px] text-gray-500 truncate">{{ $match->tournament->name }}</p>
                    </div>
                </div>
                <div class="text-right flex-shrink-0 ml-2">
                    <p class="text-xs text-white">{{ $match->team1_score ?? 0 }}-{{ $match->team2_score ?? 0 }}</p>
                    <p class="text-[10px] text-gray-600">{{ $match->completed_at?->format('M d') }}</p>
                </div>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    {{-- Platoon Card --}}
    @if($team)
    <div class="glass-card p-5">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">{{ $isOwner ? 'My Platoon' : 'Platoon' }}</h3>
        <div class="flex items-center gap-3">
            @if($team->avatar_url)
                <img src="{{ $team->avatar_url }}" alt="{{ $team->name }}" class="w-11 h-11 rounded-lg object-cover ring-1 ring-white/10">
            @else
                <div class="w-11 h-11 rounded-lg bg-white/5 flex items-center justify-center text-sm font-bold text-gray-400 ring-1 ring-white/10">
                    {{ strtoupper(substr($team->tag, 0, 2)) }}
                </div>
            @endif
            <div class="min-w-0">
                <a href="{{ route('teams.show', $team) }}" class="text-sm font-semibold text-white hover:text-green-400 transition truncate block">
                    {{ $team->name }}
                </a>
                <p class="text-xs text-gray-500">[{{ $team->tag }}]</p>
            </div>
        </div>
        @if($isOwner)
        <a href="{{ route('teams.my') }}" class="mt-3 block text-xs text-green-400 hover:text-green-300 transition">
            Manage Platoon &rarr;
        </a>
        @endif
    </div>
    @elseif($isOwner)
    <div class="glass-card p-5">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">My Platoon</h3>
        <p class="text-xs text-gray-500 mb-3">You are not a member of any platoon.</p>
        <div class="flex flex-col gap-1.5">
            <a href="{{ route('teams.index') }}" class="text-xs text-green-400 hover:text-green-300 transition">Browse Platoons &rarr;</a>
            <a href="{{ route('teams.create') }}" class="text-xs text-green-400 hover:text-green-300 transition">Create a Platoon &rarr;</a>
        </div>
    </div>
    @endif

    {{-- Quick Links --}}
    <div class="glass-card p-5">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Quick Links</h3>
        <div class="space-y-1.5">
            <a href="{{ route('leaderboard') }}" class="flex items-center gap-2.5 bg-white/3 rounded-lg px-3 py-2 hover:bg-white/7 transition group">
                <svg class="w-4 h-4 text-green-500 group-hover:text-green-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                <span class="text-xs text-gray-300">Leaderboard</span>
            </a>
            <a href="{{ route('tournaments.index') }}" class="flex items-center gap-2.5 bg-white/3 rounded-lg px-3 py-2 hover:bg-white/7 transition group">
                <svg class="w-4 h-4 text-green-500 group-hover:text-green-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                <span class="text-xs text-gray-300">Tournaments</span>
            </a>
            <a href="{{ route('teams.index') }}" class="flex items-center gap-2.5 bg-white/3 rounded-lg px-3 py-2 hover:bg-white/7 transition group">
                <svg class="w-4 h-4 text-green-500 group-hover:text-green-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                <span class="text-xs text-gray-300">Platoons</span>
            </a>
            <a href="{{ route('servers.show', config('services.battlemetrics.server_id', '0')) }}" class="flex items-center gap-2.5 bg-white/3 rounded-lg px-3 py-2 hover:bg-white/7 transition group">
                <svg class="w-4 h-4 text-green-500 group-hover:text-green-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/></svg>
                <span class="text-xs text-gray-300">Server</span>
            </a>
            <a href="{{ route('achievements.index') }}" class="flex items-center gap-2.5 bg-white/3 rounded-lg px-3 py-2 hover:bg-white/7 transition group">
                <svg class="w-4 h-4 text-green-500 group-hover:text-green-400 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                <span class="text-xs text-gray-300">Achievements</span>
            </a>
        </div>
    </div>
</div>
