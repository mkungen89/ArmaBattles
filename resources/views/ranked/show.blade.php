@extends('layouts.app')
@section('title', $user->name . ' - Ranked Profile')
@section('content')
    <div class="py-12">

        {{-- Back Link --}}
        <a href="{{ route('ranked.index') }}" class="inline-flex items-center gap-2 text-gray-400 hover:text-white mb-6 transition">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
            Back to Leaderboard
        </a>

        @php $tierConfig = \App\Models\PlayerRating::TIERS[$playerRating->rank_tier] ?? \App\Models\PlayerRating::TIERS['unranked'] @endphp

        {{-- Rating Card --}}
        <div class="bg-gradient-to-r from-purple-600/10 to-indigo-600/10 border border-purple-500/20 rounded-2xl p-8 mb-6">
            <div class="flex flex-col md:flex-row items-center gap-6">
                <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full border-2 border-purple-500/50">
                <div class="text-center md:text-left flex-1">
                    <h1 class="text-3xl font-bold text-white mb-1">{{ $user->name }}</h1>
                    <div class="flex items-center gap-3 justify-center md:justify-start">
                        @if($tierConfig['icon'])
                            <img src="{{ $tierConfig['icon'] }}" alt="{{ $tierConfig['label'] }}" class="w-14 h-14 object-contain" title="{{ $tierConfig['label'] }}">
                        @else
                            <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium border {{ $tierConfig['bg'] }} {{ $tierConfig['color'] }}">
                                {{ $tierConfig['label'] }}
                            </span>
                        @endif
                        @if($rank)
                            <span class="text-gray-400 text-sm">Rank #{{ number_format($rank) }}</span>
                        @endif
                    </div>
                </div>
                <div class="text-center">
                    @if($playerRating->is_placed)
                        <div class="text-5xl font-bold text-white">{{ number_format($playerRating->rating, 0) }}</div>
                        <div class="text-sm text-gray-400 mt-1">Rating</div>
                    @else
                        <div class="text-3xl font-bold text-gray-400">Placement</div>
                        <div class="text-lg text-purple-400 mt-1">{{ $playerRating->placement_games }}/10 games</div>
                    @endif
                </div>
            </div>
        </div>

        {{-- Stats Grid --}}
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4 mb-6">
            <div class="glass-card rounded-xl p-4">
                <div class="text-sm text-gray-400 mb-1">Ranked K/D</div>
                <div class="text-2xl font-bold text-white">{{ $playerRating->kd_ratio }}</div>
                <div class="text-xs text-gray-500">{{ number_format($playerRating->ranked_kills) }}K / {{ number_format($playerRating->ranked_deaths) }}D</div>
            </div>
            <div class="glass-card rounded-xl p-4">
                <div class="text-sm text-gray-400 mb-1">Games Played</div>
                <div class="text-2xl font-bold text-white">{{ number_format($playerRating->games_played) }}</div>
            </div>
            <div class="glass-card rounded-xl p-4">
                <div class="text-sm text-gray-400 mb-1">Peak Rating</div>
                <div class="text-2xl font-bold text-yellow-400">{{ number_format($playerRating->peak_rating, 0) }}</div>
            </div>
            <div class="glass-card rounded-xl p-4">
                <div class="text-sm text-gray-400 mb-1">Confidence</div>
                @php
                    $confColor = $playerRating->confidence === 'High' ? 'text-green-400' : ($playerRating->confidence === 'Medium' ? 'text-yellow-400' : 'text-red-400');
                @endphp
                <div class="text-2xl font-bold {{ $confColor }}">{{ $playerRating->confidence }}</div>
                <div class="text-xs text-gray-500">RD: {{ number_format($playerRating->rating_deviation, 0) }}</div>
            </div>
        </div>

        {{-- Rating History Chart --}}
        <div class="glass-card rounded-xl p-6 mb-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Rating History</h2>
            <div class="h-64">
                <canvas id="ratingChart"></canvas>
            </div>
        </div>

        {{-- Recent Rating Periods --}}
        @if($recentHistory->isNotEmpty())
        <div class="glass-card rounded-xl overflow-hidden">
            <div class="px-6 py-4 border-b border-white/5">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider">Recent Rating Updates</h2>
            </div>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-400 uppercase bg-gray-900/50">
                        <tr>
                            <th class="px-4 py-3">Date</th>
                            <th class="px-4 py-3 text-center">Rating Change</th>
                            <th class="px-4 py-3 text-center">Tier</th>
                            <th class="px-4 py-3 text-center">Kills</th>
                            <th class="px-4 py-3 text-center">Deaths</th>
                            <th class="px-4 py-3 text-center">Encounters</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach($recentHistory as $entry)
                            @php
                                $change = $entry->rating_after - $entry->rating_before;
                                $changeColor = $change >= 0 ? 'text-green-400' : 'text-red-400';
                                $changeSign = $change >= 0 ? '+' : '';
                            @endphp
                            <tr class="hover:bg-white/5 transition">
                                <td class="px-4 py-3 text-gray-400">{{ $entry->created_at->format('M j, Y H:i') }}</td>
                                <td class="px-4 py-3 text-center">
                                    <span class="text-white font-medium">{{ number_format($entry->rating_after, 0) }}</span>
                                    <span class="{{ $changeColor }} text-xs ml-1">({{ $changeSign }}{{ number_format($change, 0) }})</span>
                                </td>
                                <td class="px-4 py-3 text-center">
                                    @php $entryTier = \App\Models\PlayerRating::TIERS[$entry->rank_tier_after] ?? \App\Models\PlayerRating::TIERS['unranked'] @endphp
                                    @if($entryTier['icon'])
                                        <img src="{{ $entryTier['icon'] }}" alt="{{ $entryTier['label'] }}" class="w-9 h-9 object-contain inline-block" title="{{ $entryTier['label'] }}">
                                    @else
                                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium border {{ $entryTier['bg'] }} {{ $entryTier['color'] }}">
                                            {{ $entryTier['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-center text-green-400">{{ $entry->period_kills }}</td>
                                <td class="px-4 py-3 text-center text-red-400">{{ $entry->period_deaths }}</td>
                                <td class="px-4 py-3 text-center text-gray-400">{{ $entry->period_encounters }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>
        @endif

    </div>

    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            fetch('{{ route('ranked.history', $user) }}')
                .then(r => r.json())
                .then(data => {
                    if (data.labels.length === 0) return;

                    const ctx = document.getElementById('ratingChart').getContext('2d');
                    new Chart(ctx, {
                        type: 'line',
                        data: {
                            labels: data.labels,
                            datasets: [{
                                label: 'Rating',
                                data: data.data,
                                borderColor: '#a855f7',
                                backgroundColor: 'rgba(168, 85, 247, 0.1)',
                                fill: true,
                                tension: 0.3,
                                pointRadius: 3,
                                pointBackgroundColor: '#a855f7',
                            }]
                        },
                        options: {
                            responsive: true,
                            maintainAspectRatio: false,
                            plugins: {
                                legend: { display: false }
                            },
                            scales: {
                                x: {
                                    ticks: { color: '#9ca3af', maxTicksLimit: 10 },
                                    grid: { color: 'rgba(75, 85, 99, 0.3)' }
                                },
                                y: {
                                    ticks: { color: '#9ca3af' },
                                    grid: { color: 'rgba(75, 85, 99, 0.3)' }
                                }
                            }
                        }
                    });
                });
        });
    </script>
    @endpush
@endsection
