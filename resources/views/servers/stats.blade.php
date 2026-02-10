@extends('layouts.app')

@section('title', 'Server Statistics')

@section('content')
<div class="space-y-6" x-data="serverStats()" x-init="fetchData()">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-white">Server Statistics</h1>
        <div class="flex gap-2">
            <template x-for="r in ['6h', '24h', '72h']" :key="r">
                <button @click="range = r; fetchData()"
                        :class="range === r ? 'bg-green-500/20 text-green-400 border-green-500/50' : 'bg-white/3 text-gray-400 border-white/10 hover:text-white'"
                        class="px-3 py-1.5 rounded-lg text-sm font-medium transition border"
                        x-text="r"></button>
            </template>
        </div>
    </div>

    {{-- Summary Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-5 text-center">
            <p class="text-3xl font-bold text-green-400" x-text="summary.avg_players || '0'"></p>
            <p class="text-sm text-gray-400">Avg Players</p>
        </div>
        <div class="glass-card rounded-xl p-5 text-center">
            <p class="text-3xl font-bold text-yellow-400" x-text="summary.peak_players || '0'"></p>
            <p class="text-sm text-gray-400">Peak Players</p>
        </div>
        <div class="glass-card rounded-xl p-5 text-center">
            <p class="text-3xl font-bold text-blue-400" x-text="summary.avg_fps || '0'"></p>
            <p class="text-sm text-gray-400">Avg FPS</p>
        </div>
        <div class="glass-card rounded-xl p-5 text-center">
            <p class="text-3xl font-bold text-red-400" x-text="summary.min_fps || '0'"></p>
            <p class="text-sm text-gray-400">Min FPS</p>
        </div>
    </div>

    {{-- Player Count Chart --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Player Count</h3>
        <div class="relative" style="height: 300px;">
            <canvas id="playersChart"></canvas>
        </div>
    </div>

    {{-- FPS Chart --}}
    <div class="glass-card rounded-xl p-6">
        <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Server FPS</h3>
        <div class="relative" style="height: 300px;">
            <canvas id="fpsChart"></canvas>
        </div>
    </div>

    <div x-show="loading" class="text-center text-gray-500 py-4">Loading data...</div>
    <div x-show="!loading && summary.data_points === 0" class="text-center text-gray-500 py-4">No performance data available for this time range.</div>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/chart.js@4"></script>
<script>
function serverStats() {
    let playersChart = null;
    let fpsChart = null;
    return {
        range: '24h',
        summary: {},
        loading: false,
        async fetchData() {
            this.loading = true;
            try {
                const res = await fetch('/servers/{{ $serverId }}/stats/data?range=' + this.range);
                const data = await res.json();
                this.summary = data.summary || {};
                this.renderCharts(data);
            } catch(e) {} finally { this.loading = false; }
        },
        renderCharts(data) {
            const chartOptions = {
                responsive: true,
                maintainAspectRatio: false,
                plugins: { legend: { display: false } },
                scales: {
                    x: { ticks: { color: '#6b7280', maxTicksLimit: 20 }, grid: { color: '#374151' } },
                    y: { ticks: { color: '#6b7280' }, grid: { color: '#374151' }, beginAtZero: true }
                }
            };
            // Players chart
            if (playersChart) playersChart.destroy();
            const pCtx = document.getElementById('playersChart').getContext('2d');
            playersChart = new Chart(pCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'Players',
                        data: data.players,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34,197,94,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                    }]
                },
                options: chartOptions
            });
            // FPS chart
            if (fpsChart) fpsChart.destroy();
            const fCtx = document.getElementById('fpsChart').getContext('2d');
            fpsChart = new Chart(fCtx, {
                type: 'line',
                data: {
                    labels: data.labels,
                    datasets: [{
                        label: 'FPS',
                        data: data.fps,
                        borderColor: '#3b82f6',
                        backgroundColor: 'rgba(59,130,246,0.1)',
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                    }]
                },
                options: chartOptions
            });
        }
    };
}
</script>
@endpush
@endsection
