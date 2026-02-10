@extends('admin.layout')
@section('title', 'Ranked Ratings')
@section('admin-content')

    <h1 class="text-2xl font-bold text-white mb-6">Ranked Ratings Dashboard</h1>

    @if(session('success'))
        <div class="bg-green-500/10 border border-green-500/30 rounded-lg p-4 mb-6">
            <p class="text-green-400">{{ session('success') }}</p>
        </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
        <div class="glass-card rounded-xl p-4">
            <div class="text-sm text-gray-400 mb-1">Competitive Players</div>
            <div class="text-2xl font-bold text-white">{{ number_format($totalCompetitive) }}</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-sm text-gray-400 mb-1">Placed Players</div>
            <div class="text-2xl font-bold text-purple-400">{{ number_format($totalPlaced) }}</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-sm text-gray-400 mb-1">Avg Rating (Placed)</div>
            <div class="text-2xl font-bold text-white">{{ $avgRating ? number_format($avgRating, 0) : '-' }}</div>
        </div>
        <div class="glass-card rounded-xl p-4">
            <div class="text-sm text-gray-400 mb-1">Queue Size</div>
            <div class="text-2xl font-bold {{ $queueSize > 0 ? 'text-yellow-400' : 'text-gray-500' }}">{{ number_format($queueSize) }}</div>
        </div>
    </div>

    {{-- Tier Distribution --}}
    <div class="glass-card rounded-xl p-6 mb-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Tier Distribution</h2>
        @if(!empty($tierDistribution))
            <div class="grid grid-cols-2 md:grid-cols-4 lg:grid-cols-7 gap-3">
                @foreach(\App\Models\PlayerRating::TIERS as $key => $tier)
                    @if($key !== 'unranked')
                        <div class="text-center p-3 rounded-lg border {{ $tier['bg'] }}">
                            @if($tier['icon'])
                                <img src="{{ $tier['icon'] }}" alt="{{ $tier['label'] }}" class="w-16 h-16 object-contain mx-auto mb-1" title="{{ $tier['label'] }}">
                            @else
                                <div class="text-xs {{ $tier['color'] }} mb-1">{{ $tier['label'] }}</div>
                            @endif
                            <div class="text-2xl font-bold {{ $tier['color'] }}">{{ $tierDistribution[$key] ?? 0 }}</div>
                        </div>
                    @endif
                @endforeach
            </div>
        @else
            <p class="text-gray-500">No placed players yet.</p>
        @endif
    </div>

    {{-- Suspicious Players --}}
    @if($suspicious->isNotEmpty())
    <div class="bg-white/3 border border-yellow-500/30 rounded-xl overflow-hidden mb-6">
        <div class="px-6 py-4 border-b border-white/5 flex items-center gap-2">
            <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-2.5L13.732 4c-.77-.833-1.964-.833-2.732 0L4.082 16.5c-.77.833.192 2.5 1.732 2.5z"/></svg>
            <h2 class="text-lg font-semibold text-white">Suspicious Ratings</h2>
            <span class="text-xs text-gray-400">(High rating + High RD)</span>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-400 uppercase bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3">Player</th>
                        <th class="px-4 py-3 text-center">Rating</th>
                        <th class="px-4 py-3 text-center">RD</th>
                        <th class="px-4 py-3 text-center">Games</th>
                        <th class="px-4 py-3 text-center">Actions</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($suspicious as $s)
                        <tr>
                            <td class="px-4 py-3 text-white">{{ $s->name }}</td>
                            <td class="px-4 py-3 text-center text-white font-bold">{{ number_format($s->rating, 0) }}</td>
                            <td class="px-4 py-3 text-center text-red-400">{{ number_format($s->rating_deviation, 0) }}</td>
                            <td class="px-4 py-3 text-center text-gray-400">{{ $s->games_played }}</td>
                            <td class="px-4 py-3 text-center">
                                <form action="{{ route('admin.ranked.reset', $s->id) }}" method="POST" onsubmit="return confirm('Reset this player\'s rating to 1500?')">
                                    @csrf
                                    <button class="text-red-400 hover:text-red-300 text-xs font-medium">Reset</button>
                                </form>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

    {{-- Recent Rating Updates --}}
    @if($recentHistory->isNotEmpty())
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="px-6 py-4 border-b border-white/5">
            <h2 class="text-lg font-semibold text-white">Recent Rating Updates</h2>
        </div>
        <div class="overflow-x-auto">
            <table class="w-full text-sm text-left">
                <thead class="text-xs text-gray-400 uppercase bg-gray-900/50">
                    <tr>
                        <th class="px-4 py-3">Player UUID</th>
                        <th class="px-4 py-3 text-center">Before</th>
                        <th class="px-4 py-3 text-center">After</th>
                        <th class="px-4 py-3 text-center">Change</th>
                        <th class="px-4 py-3 text-center">Encounters</th>
                        <th class="px-4 py-3">Date</th>
                    </tr>
                </thead>
                <tbody class="divide-y divide-white/5">
                    @foreach($recentHistory as $entry)
                        @php
                            $change = $entry->rating_after - $entry->rating_before;
                            $changeColor = $change >= 0 ? 'text-green-400' : 'text-red-400';
                        @endphp
                        <tr>
                            <td class="px-4 py-3 text-gray-400 font-mono text-xs">{{ Str::limit($entry->player_uuid, 20) }}</td>
                            <td class="px-4 py-3 text-center text-gray-400">{{ number_format($entry->rating_before, 0) }}</td>
                            <td class="px-4 py-3 text-center text-white font-medium">{{ number_format($entry->rating_after, 0) }}</td>
                            <td class="px-4 py-3 text-center {{ $changeColor }}">{{ $change >= 0 ? '+' : '' }}{{ number_format($change, 0) }}</td>
                            <td class="px-4 py-3 text-center text-gray-400">{{ $entry->period_encounters }}</td>
                            <td class="px-4 py-3 text-gray-500 text-xs">{{ \Carbon\Carbon::parse($entry->created_at)->format('M j, H:i') }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif

@endsection
