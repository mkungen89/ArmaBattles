@extends('layouts.app')

@section('title', 'Server Info')

@section('content')
<div class="space-y-6">
    @if($server)
    @php
        $attr = $server['attributes'] ?? [];
        $details = $attr['details'] ?? [];
        $reforger = $details['reforger'] ?? [];
        $mods = $reforger['mods'] ?? [];
        $players = $attr['players'] ?? 0;
        $maxPlayers = $attr['maxPlayers'] ?? 128;
        $percentage = $maxPlayers > 0 ? round(($players / $maxPlayers) * 100) : 0;
    @endphp

    <!-- Header -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
            <div>
                <h1 class="text-2xl font-bold mb-2">{{ $attr['name'] ?? 'Unknown Server' }}</h1>
                <div class="flex flex-wrap items-center gap-3">
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium {{ ($attr['status'] ?? '') === 'online' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                        <span class="relative flex h-2 w-2 mr-2">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ ($attr['status'] ?? '') === 'online' ? 'bg-green-400' : 'bg-red-400' }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-2 w-2 {{ ($attr['status'] ?? '') === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        </span>
                        {{ ($attr['status'] ?? '') === 'online' ? 'Online' : 'Offline' }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-300">
                        <svg class="w-4 h-4 mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        {{ $players }}/{{ $maxPlayers }}
                    </span>
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-700 text-gray-300">
                        {{ $percentage }}% Full
                    </span>
                </div>
            </div>
            <div class="flex flex-wrap gap-2">
                <a href="steam://connect/{{ $attr['ip'] ?? '' }}:{{ $attr['port'] ?? '' }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-500 rounded-lg font-medium transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    Join Server
                </a>
                <button onclick="copyToClipboard('{{ $attr['ip'] ?? '' }}:{{ $attr['port'] ?? '' }}')" class="inline-flex items-center px-4 py-2 bg-gray-700 hover:bg-gray-600 rounded-lg font-medium transition">
                    <svg class="w-5 h-5 mr-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 5H6a2 2 0 00-2 2v12a2 2 0 002 2h10a2 2 0 002-2v-1M8 5a2 2 0 002 2h2a2 2 0 002-2M8 5a2 2 0 012-2h2a2 2 0 012 2m0 0h2a2 2 0 012 2v3m2 4H10m0 0l3-3m-3 3l3 3"/>
                    </svg>
                    Copy IP
                </button>
            </div>
        </div>
    </div>

    <!-- Server Info Grid -->
    <div class="grid md:grid-cols-2 lg:grid-cols-4 gap-4">
        <!-- Connection Info -->
        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Connection</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">IP Address</p>
                    <p class="font-mono text-sm">{{ $attr['ip'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Port</p>
                    <p class="font-mono text-sm">{{ $attr['port'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Server ID</p>
                    <p class="font-mono text-xs text-gray-400">{{ $server['id'] ?? 'N/A' }}</p>
                </div>
            </div>
        </div>

        <!-- Version Info -->
        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Version</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Game Version</p>
                    <p class="font-mono text-sm">{{ $details['version'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Scenario</p>
                    <p class="text-sm">{{ $reforger['scenarioName'] ?? 'N/A' }}</p>
                </div>
                <div>
                    <p class="text-xs text-gray-500">Platforms</p>
                    <div class="flex gap-1 mt-1">
                        @foreach($reforger['supportedGameClientTypes'] ?? [] as $platform)
                            <span class="px-2 py-0.5 bg-gray-700 rounded text-xs">
                                {{ str_replace('PLATFORM_', '', $platform) }}
                            </span>
                        @endforeach
                    </div>
                </div>
            </div>
        </div>

        <!-- Server Status -->
        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Status</h3>
            <div class="space-y-2">
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Official</span>
                    <span class="text-sm {{ ($details['official'] ?? false) ? 'text-green-400' : 'text-gray-500' }}">
                        {{ ($details['official'] ?? false) ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Password</span>
                    <span class="text-sm {{ ($details['password'] ?? false) ? 'text-yellow-400' : 'text-green-400' }}">
                        {{ ($details['password'] ?? false) ? 'Yes' : 'No' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">BattlEye</span>
                    <span class="text-sm {{ ($reforger['battlEye'] ?? false) ? 'text-green-400' : 'text-gray-500' }}">
                        {{ ($reforger['battlEye'] ?? false) ? 'Enabled' : 'Disabled' }}
                    </span>
                </div>
                <div class="flex items-center justify-between">
                    <span class="text-sm text-gray-400">Rank</span>
                    <span class="text-sm">#{{ $attr['rank'] ?? 'N/A' }}</span>
                </div>
            </div>
        </div>

        <!-- Location -->
        <div class="bg-gray-800 rounded-xl p-5 border border-gray-700">
            <h3 class="text-sm font-semibold text-gray-400 uppercase tracking-wide mb-3">Location</h3>
            <div class="space-y-3">
                <div>
                    <p class="text-xs text-gray-500">Region</p>
                    <p class="text-sm flex items-center">
                        <span class="text-2xl mr-2">🇪🇺</span> Europe
                    </p>
                </div>
                @if(isset($attr['location']))
                <div>
                    <p class="text-xs text-gray-500">Coordinates</p>
                    <p class="font-mono text-xs text-gray-400">{{ $attr['location'][1] ?? '' }}, {{ $attr['location'][0] ?? '' }}</p>
                </div>
                @endif
            </div>
        </div>
    </div>

    <!-- Player Count Graph -->
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">Player Count Over Time</h2>
            <div class="flex gap-2">
                <button onclick="loadHistory('6h')" id="btn-6h" class="px-3 py-1 text-sm rounded-lg bg-gray-700 hover:bg-gray-600 transition">6h</button>
                <button onclick="loadHistory('24h')" id="btn-24h" class="px-3 py-1 text-sm rounded-lg bg-green-600 text-white transition">24h</button>
                <button onclick="loadHistory('72h')" id="btn-72h" class="px-3 py-1 text-sm rounded-lg bg-gray-700 hover:bg-gray-600 transition">72h</button>
            </div>
        </div>
        <div id="player-chart" class="h-64 relative">
            <canvas id="playerCanvas" class="w-full h-full"></canvas>
            <div id="chart-loading" class="absolute inset-0 flex items-center justify-center">
                <p class="text-gray-400">Loading chart...</p>
            </div>
        </div>
    </div>

    <!-- Mods Section -->
    @if(count($mods) > 0)
    <div class="bg-gray-800 rounded-xl p-6 border border-gray-700">
        <div class="flex items-center justify-between mb-4">
            <h2 class="text-lg font-semibold">
                Mods
                <span class="ml-2 px-2 py-0.5 bg-gray-700 rounded-full text-sm font-normal">{{ count($mods) }}</span>
            </h2>
        </div>
        <div class="grid sm:grid-cols-2 lg:grid-cols-3 gap-3">
            @foreach($mods as $mod)
            <div class="bg-gray-700/50 rounded-lg p-4 hover:bg-gray-700 transition">
                <div class="flex items-start justify-between">
                    <div class="flex-1 min-w-0">
                        <h4 class="font-medium text-sm truncate">{{ $mod['name'] ?? 'Unknown Mod' }}</h4>
                        <p class="text-xs text-gray-400 mt-1">v{{ $mod['version'] ?? '?' }}</p>
                    </div>
                    <a href="https://reforger.armaplatform.com/workshop/{{ $mod['modId'] ?? '' }}" target="_blank" class="ml-2 p-1.5 text-gray-400 hover:text-white hover:bg-gray-600 rounded transition" title="View in Workshop">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                        </svg>
                    </a>
                </div>
                <p class="text-xs text-gray-500 mt-2 font-mono truncate">{{ $mod['modId'] ?? '' }}</p>
            </div>
            @endforeach
        </div>
    </div>
    @endif

    @else
    <div class="bg-gray-800 rounded-xl p-12 border border-gray-700 text-center">
        <svg class="w-16 h-16 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9.172 16.172a4 4 0 015.656 0M9 10h.01M15 10h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <h2 class="text-xl font-semibold mb-2">Server Not Found</h2>
        <p class="text-gray-400">Could not fetch server information.</p>
    </div>
    @endif
</div>

@push('scripts')
<script>
    function copyToClipboard(text) {
        navigator.clipboard.writeText(text).then(() => {
            alert('Copied to clipboard: ' + text);
        });
    }

    let currentPeriod = '24h';

    async function loadHistory(period) {
        currentPeriod = period;

        // Update button styles
        ['6h', '24h', '72h'].forEach(p => {
            const btn = document.getElementById('btn-' + p);
            if (p === period) {
                btn.className = 'px-3 py-1 text-sm rounded-lg bg-green-600 text-white transition';
            } else {
                btn.className = 'px-3 py-1 text-sm rounded-lg bg-gray-700 hover:bg-gray-600 transition';
            }
        });

        const hours = parseInt(period);
        const start = new Date(Date.now() - hours * 60 * 60 * 1000).toISOString();
        const end = new Date().toISOString();

        try {
            document.getElementById('chart-loading').style.display = 'flex';
            const response = await fetch(`{{ route('api.server.history') }}?start=${start}&end=${end}`);
            const data = await response.json();

            document.getElementById('chart-loading').style.display = 'none';

            if (!data || !Array.isArray(data) || data.length === 0) {
                document.getElementById('chart-loading').style.display = 'flex';
                document.getElementById('chart-loading').innerHTML = '<p class="text-gray-400">No history data available</p>';
                return;
            }

            drawChart(data);
        } catch (error) {
            console.error('Error:', error);
            document.getElementById('chart-loading').innerHTML = '<p class="text-gray-400">Could not load chart</p>';
        }
    }

    function drawChart(data) {
        const canvas = document.getElementById('playerCanvas');
        const ctx = canvas.getContext('2d');

        const rect = canvas.parentElement.getBoundingClientRect();
        canvas.width = rect.width;
        canvas.height = rect.height;

        const padding = { top: 20, right: 20, bottom: 30, left: 40 };
        const chartWidth = canvas.width - padding.left - padding.right;
        const chartHeight = canvas.height - padding.top - padding.bottom;

        const points = data.map(d => ({
            time: new Date(d.attributes?.timestamp || d.timestamp),
            value: d.attributes?.value ?? d.value ?? 0
        })).filter(d => !isNaN(d.value));

        if (points.length === 0) return;

        const maxValue = Math.max(...points.map(p => p.value), 10);
        const minTime = Math.min(...points.map(p => p.time.getTime()));
        const maxTime = Math.max(...points.map(p => p.time.getTime()));

        ctx.clearRect(0, 0, canvas.width, canvas.height);

        // Grid
        ctx.strokeStyle = '#374151';
        ctx.lineWidth = 1;
        for (let i = 0; i <= 4; i++) {
            const y = padding.top + (chartHeight / 4) * i;
            ctx.beginPath();
            ctx.moveTo(padding.left, y);
            ctx.lineTo(canvas.width - padding.right, y);
            ctx.stroke();

            ctx.fillStyle = '#9CA3AF';
            ctx.font = '12px sans-serif';
            ctx.textAlign = 'right';
            ctx.fillText(Math.round(maxValue - (maxValue / 4) * i).toString(), padding.left - 8, y + 4);
        }

        // Area
        ctx.beginPath();
        ctx.moveTo(padding.left, padding.top + chartHeight);
        points.forEach((point) => {
            const x = padding.left + ((point.time.getTime() - minTime) / (maxTime - minTime)) * chartWidth;
            const y = padding.top + chartHeight - (point.value / maxValue) * chartHeight;
            ctx.lineTo(x, y);
        });
        ctx.lineTo(padding.left + chartWidth, padding.top + chartHeight);
        ctx.closePath();

        const gradient = ctx.createLinearGradient(0, padding.top, 0, padding.top + chartHeight);
        gradient.addColorStop(0, 'rgba(34, 197, 94, 0.3)');
        gradient.addColorStop(1, 'rgba(34, 197, 94, 0.0)');
        ctx.fillStyle = gradient;
        ctx.fill();

        // Line
        ctx.beginPath();
        points.forEach((point, i) => {
            const x = padding.left + ((point.time.getTime() - minTime) / (maxTime - minTime)) * chartWidth;
            const y = padding.top + chartHeight - (point.value / maxValue) * chartHeight;
            i === 0 ? ctx.moveTo(x, y) : ctx.lineTo(x, y);
        });
        ctx.strokeStyle = '#22C55E';
        ctx.lineWidth = 2;
        ctx.stroke();

        // Time labels
        ctx.fillStyle = '#9CA3AF';
        ctx.font = '11px sans-serif';
        ctx.textAlign = 'center';
        [0, 0.25, 0.5, 0.75, 1].forEach(ratio => {
            const time = new Date(minTime + (maxTime - minTime) * ratio);
            const x = padding.left + chartWidth * ratio;
            ctx.fillText(time.getHours().toString().padStart(2, '0') + ':00', x, canvas.height - 8);
        });
    }

    window.addEventListener('resize', () => loadHistory(currentPeriod));
    loadHistory('24h');
</script>
@endpush
@endsection
