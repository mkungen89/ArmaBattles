@extends('layouts.app')
@section('title', 'How Ranked Ratings Work')
@section('content')
    <div class="py-12 max-w-4xl mx-auto">

        {{-- Header --}}
        <div class="bg-gradient-to-r from-purple-600/10 to-indigo-600/10 border border-purple-500/20 rounded-2xl p-6 mb-6">
            <div class="flex items-center justify-between">
                <div>
                    <h1 class="text-3xl font-bold text-white mb-2">How Rankings Work</h1>
                    <p class="text-gray-400">Understanding the competitive skill rating system</p>
                </div>
                <a href="{{ route('ranked.index') }}" class="px-4 py-2 rounded-xl bg-purple-500/20 border border-purple-500/30 text-purple-300 text-sm hover:bg-purple-500/30 transition">
                    View Leaderboard
                </a>
            </div>
        </div>

        {{-- Overview --}}
        <div class="glass-card rounded-xl p-6 mb-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Overview</h2>
            <div class="prose prose-invert prose-sm max-w-none text-gray-300 space-y-3">
                <p>Our ranked system uses the <strong class="text-white">Glicko-2 algorithm</strong>, the same system used by many competitive games and chess organizations. It tracks three values for each player:</p>
                <ul class="list-disc list-inside space-y-1">
                    <li><strong class="text-white">Rating</strong> &mdash; Your estimated skill level (starts at 1500)</li>
                    <li><strong class="text-white">Rating Deviation (RD)</strong> &mdash; How confident the system is in your rating. Lower RD = higher confidence</li>
                    <li><strong class="text-white">Volatility</strong> &mdash; How consistently you perform. Stable players have low volatility</li>
                </ul>
                <p>This is Arma, not Call of Duty. Our rating system rewards <strong class="text-white">tactical play</strong>, not just kills. Capturing bases, healing teammates, and delivering supplies all boost your rating. Team kills <strong class="text-red-400">lower</strong> your rating significantly.</p>
            </div>
        </div>

        {{-- What Affects Your Rating --}}
        <div class="glass-card rounded-xl p-6 mb-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">What Affects Your Rating</h2>
            <div class="space-y-3">
                <div class="flex items-start gap-3 p-3 rounded-lg bg-green-500/5 border border-green-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">PvP Kills</p>
                        <p class="text-sm text-gray-400">Killing another competitive player increases your rating. The boost is larger when defeating higher-rated opponents.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-green-500/5 border border-green-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Vehicle Destruction</p>
                        <p class="text-sm text-gray-400">Destroying an enemy vehicle gives the <strong class="text-white">largest objective boost</strong>. Taking out armor is hard and game-changing.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-green-500/5 border border-green-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Base Captures</p>
                        <p class="text-sm text-gray-400">Capturing an objective gives a solid rating boost &mdash; equivalent to beating an average player. Playing the objective matters.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-500/5 border border-blue-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Healing Teammates</p>
                        <p class="text-sm text-gray-400">Healing another player gives a smaller rating boost. Self-heals don't count &mdash; only teamplay is rewarded.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-500/5 border border-blue-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Supply Deliveries</p>
                        <p class="text-sm text-gray-400">Delivering supplies gives a smaller rating boost. Logistics wins wars.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-blue-500/5 border border-blue-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 10l7-7m0 0l7 7m-7-7v18"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Building Fortifications</p>
                        <p class="text-sm text-gray-400">Placing structures gives a small rating boost. Engineers who build defenses help the team.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-red-500/5 border border-red-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">PvP Deaths</p>
                        <p class="text-sm text-gray-400">Being killed by another competitive player lowers your rating. The penalty is larger when losing to lower-rated opponents.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-red-500/5 border border-red-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Team Kills</p>
                        <p class="text-sm text-gray-400">Killing a teammate results in a <strong class="text-red-400">significant rating penalty</strong>. Friendly fire is serious &mdash; watch your fire.</p>
                    </div>
                </div>
                <div class="flex items-start gap-3 p-3 rounded-lg bg-red-500/5 border border-red-500/10">
                    <div class="mt-0.5">
                        <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                    </div>
                    <div>
                        <p class="text-white font-medium">Friendly Fire Damage</p>
                        <p class="text-sm text-gray-400">Hitting a teammate (even without killing them) gives a moderate rating penalty. Less severe than a team kill, but it adds up.</p>
                    </div>
                </div>
            </div>
            <p class="text-xs text-gray-500 mt-4">AI kills do not affect your rating. Only events involving opted-in competitive players are counted.</p>
        </div>

        {{-- Tier Table --}}
        <div class="glass-card rounded-xl p-6 mb-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Rank Tiers</h2>
            <div class="overflow-x-auto">
                <table class="w-full text-sm text-left">
                    <thead class="text-xs text-gray-400 uppercase">
                        <tr>
                            <th class="px-4 py-3">Tier</th>
                            <th class="px-4 py-3">Rating Range</th>
                            <th class="px-4 py-3">Description</th>
                        </tr>
                    </thead>
                    <tbody class="divide-y divide-white/5">
                        @foreach(\App\Models\PlayerRating::TIERS as $key => $tier)
                            @if($key !== 'unranked')
                            <tr>
                                <td class="px-4 py-3">
                                    @if($tier['icon'])
                                        <img src="{{ $tier['icon'] }}" alt="{{ $tier['label'] }}" class="w-12 h-12 object-contain" title="{{ $tier['label'] }}">
                                    @else
                                        <span class="inline-flex items-center px-3 py-1 rounded-full text-xs font-medium border {{ $tier['bg'] }} {{ $tier['color'] }}">
                                            {{ $tier['label'] }}
                                        </span>
                                    @endif
                                </td>
                                <td class="px-4 py-3 text-gray-300 font-mono">
                                    {{ number_format($tier['min']) }}+
                                </td>
                                <td class="px-4 py-3 text-gray-400">
                                    @switch($key)
                                        @case('elite') Top-tier competitive players @break
                                        @case('master') Exceptional skill and consistency @break
                                        @case('diamond') Highly skilled veteran @break
                                        @case('platinum') Above average competitive player @break
                                        @case('gold') Solid competitive player @break
                                        @case('silver') Developing competitive skills @break
                                        @case('bronze') Starting competitive journey @break
                                    @endswitch
                                </td>
                            </tr>
                            @endif
                        @endforeach
                    </tbody>
                </table>
            </div>
        </div>

        {{-- Tier Distribution --}}
        @if(!empty($tierDistribution))
        <div class="glass-card rounded-xl p-6 mb-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Current Distribution</h2>
            <div class="h-64 flex items-center justify-center">
                <canvas id="tierChart" class="max-w-xs"></canvas>
            </div>
        </div>
        @endif

        {{-- FAQ --}}
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">FAQ</h2>
            <div class="space-y-4" x-data="{ open: null }">
                @php
                    $faqs = [
                        ['How do I enable competitive mode?', 'Go to your Profile Settings and link your Arma Reforger ID first, then click "Enable Competitive Mode". You can also enable it from the ranked leaderboard page.'],
                        ['What are placement games?', 'You need to participate in 10 rated encounters before receiving an official tier. During placement, your rating changes more significantly to quickly find your true skill level. Encounters include kills, base captures, heals, and supply deliveries.'],
                        ['What happens if I stop playing?', 'After 14 days of inactivity, your Rating Deviation slowly increases. This means the system becomes less confident in your rating. Play again and it will stabilize quickly.'],
                        ['Do AI kills count?', 'No. AI kills do not affect your rating at all. Only PvP kills between two opted-in competitive players are counted.'],
                        ['Do team kills affect my rating?', 'Yes. Team killing results in a significant rating penalty. Friendly fire is treated as a serious offense in the competitive system. Watch your fire.'],
                        ['How do objectives affect my rating?', 'Capturing a base gives a solid rating boost (equivalent to beating an average player). Healing teammates and delivering supplies give smaller boosts. You can climb the ranks through tactical play, not just kills.'],
                        ['Can I opt out?', 'Yes. You can disable competitive mode at any time from your settings. Your rating data is preserved, so re-enabling will resume from where you left off.'],
                        ['How often are ratings updated?', 'Ratings are recalculated every 4 hours in batches. This is by design: Glicko-2 works best with batched rating periods rather than instant updates.'],
                    ];
                @endphp
                @foreach($faqs as $i => $faq)
                    <div class="border border-white/5 rounded-lg overflow-hidden">
                        <button @click="open = open === {{ $i }} ? null : {{ $i }}" class="w-full flex items-center justify-between px-4 py-3 text-left hover:bg-white/5 transition">
                            <span class="font-medium text-white">{{ $faq[0] }}</span>
                            <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open === {{ $i }} ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/></svg>
                        </button>
                        <div x-show="open === {{ $i }}" x-collapse class="px-4 pb-3 text-sm text-gray-400">
                            {{ $faq[1] }}
                        </div>
                    </div>
                @endforeach
            </div>
        </div>

    </div>

    @if(!empty($tierDistribution))
    @push('scripts')
    <script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
    <script>
        document.addEventListener('DOMContentLoaded', function() {
            const tiers = @json($tierDistribution);
            const tierMeta = @json(collect(\App\Models\PlayerRating::TIERS)->except('unranked')->map(fn($t) => ['label' => $t['label']]));
            const colorMap = {
                elite: '#f87171', master: '#fbbf24', diamond: '#22d3ee',
                platinum: '#93c5fd', gold: '#facc15', silver: '#d1d5db', bronze: '#fb923c'
            };

            const labels = [], data = [], colors = [];
            for (const [key, meta] of Object.entries(tierMeta)) {
                labels.push(meta.label);
                data.push(tiers[key] || 0);
                colors.push(colorMap[key] || '#6b7280');
            }

            new Chart(document.getElementById('tierChart'), {
                type: 'doughnut',
                data: {
                    labels: labels,
                    datasets: [{ data: data, backgroundColor: colors, borderWidth: 0 }]
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: { position: 'right', labels: { color: '#9ca3af', padding: 12 } }
                    }
                }
            });
        });
    </script>
    @endpush
    @endif
@endsection
