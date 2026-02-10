@extends('admin.layout')

@section('title', 'Server Performance')

@section('admin-content')
<div x-data="performanceDashboard()" x-init="init()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.server.dashboard') }}" class="p-2 bg-white/5 hover:bg-white/10 text-gray-400 hover:text-white rounded-xl transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Server Performance</h1>
                <p class="text-sm text-gray-500 mt-1">Historical metrics and resource usage</p>
            </div>
        </div>

        {{-- Time Range Selector --}}
        <div class="flex items-center gap-1 glass-card rounded-lg p-1">
            <button @click="range = '6h'" :class="range === '6h' ? 'bg-green-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10'" class="px-3 py-1.5 rounded-md text-sm font-medium transition">6h</button>
            <button @click="range = '24h'" :class="range === '24h' ? 'bg-green-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10'" class="px-3 py-1.5 rounded-md text-sm font-medium transition">24h</button>
            <button @click="range = '72h'" :class="range === '72h' ? 'bg-green-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10'" class="px-3 py-1.5 rounded-md text-sm font-medium transition">72h</button>
        </div>
    </div>

    {{-- Summary Stats Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-3 gap-4">
        {{-- FPS --}}
        <div class="glass-card rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-gray-500 uppercase tracking-wider">FPS</span>
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-white" x-text="summary.fps.avg !== null ? summary.fps.avg.toFixed(1) : '--'">--</p>
            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                <span>Min: <span class="text-gray-300" x-text="summary.fps.min !== null ? summary.fps.min.toFixed(1) : '--'">--</span></span>
                <span>Max: <span class="text-gray-300" x-text="summary.fps.max !== null ? summary.fps.max.toFixed(1) : '--'">--</span></span>
            </div>
        </div>

        {{-- Memory --}}
        <div class="glass-card rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Memory</span>
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-white" x-text="summary.memory.avg !== null ? summary.memory.avg.toFixed(0) + ' MB' : '--'">--</p>
            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                <span>Min: <span class="text-gray-300" x-text="summary.memory.min !== null ? summary.memory.min.toFixed(0) + ' MB' : '--'">--</span></span>
                <span>Max: <span class="text-gray-300" x-text="summary.memory.max !== null ? summary.memory.max.toFixed(0) + ' MB' : '--'">--</span></span>
            </div>
        </div>

        {{-- Players --}}
        <div class="glass-card rounded-xl p-4">
            <div class="flex items-center justify-between mb-3">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Players</span>
                <svg class="w-4 h-4 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-2xl font-bold text-white" x-text="summary.players.avg !== null ? summary.players.avg.toFixed(0) : '--'">--</p>
            <div class="flex items-center gap-3 mt-2 text-xs text-gray-500">
                <span>Min: <span class="text-gray-300" x-text="summary.players.min !== null ? summary.players.min : '--'">--</span></span>
                <span>Max: <span class="text-gray-300" x-text="summary.players.max !== null ? summary.players.max : '--'">--</span></span>
            </div>
        </div>
    </div>

    {{-- Charts Grid --}}
    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- FPS Chart --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-white mb-4">FPS Over Time</h3>
            <div class="relative" style="height: 220px;">
                <canvas id="fpsChart"></canvas>
            </div>
        </div>

        {{-- Memory Chart --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-white mb-4">Memory Usage (MB)</h3>
            <div class="relative" style="height: 220px;">
                <canvas id="memoryChart"></canvas>
            </div>
        </div>

        {{-- Players Chart --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-white mb-4">Player Count</h3>
            <div class="relative" style="height: 220px;">
                <canvas id="playersChart"></canvas>
            </div>
        </div>

        {{-- Uptime Chart --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-white mb-4">Uptime (Hours)</h3>
            <div class="relative" style="height: 220px;">
                <canvas id="uptimeChart"></canvas>
            </div>
        </div>
    </div>

    {{-- Loading Overlay --}}
    <div x-show="loading" x-cloak class="fixed inset-0 bg-gray-900/50 flex items-center justify-center z-50">
        <div class="glass-card backdrop-blur-xl rounded-xl p-6 flex items-center gap-3">
            <svg class="w-5 h-5 text-green-500 animate-spin" fill="none" viewBox="0 0 24 24">
                <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
            </svg>
            <span class="text-white text-sm">Loading performance data...</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@push('scripts')
<script>
function performanceDashboard() {
    return {
        range: '24h',
        loading: false,
        charts: {
            fps: null,
            memory: null,
            players: null,
            uptime: null,
        },
        summary: {
            fps: { avg: null, min: null, max: null },
            memory: { avg: null, min: null, max: null },
            players: { avg: null, min: null, max: null },
        },

        async init() {
            this.$watch('range', () => this.fetchData());
            await this.fetchData();
        },

        async fetchData() {
            this.loading = true;
            try {
                const url = `{{ route('admin.server.api.performance-data') }}?range=${this.range}`;
                const response = await fetch(url);
                if (!response.ok) throw new Error('Failed to fetch');
                const data = await response.json();
                this.updateSummary(data);
                this.updateCharts(data);
            } catch (e) {
                console.error('Failed to load performance data:', e);
            } finally {
                this.loading = false;
            }
        },

        updateSummary(data) {
            if (data.summary) {
                this.summary.fps = {
                    avg: data.summary.fps_avg ?? null,
                    min: data.summary.fps_min ?? null,
                    max: data.summary.fps_max ?? null,
                };
                this.summary.memory = {
                    avg: data.summary.memory_avg ?? null,
                    min: data.summary.memory_min ?? null,
                    max: data.summary.memory_max ?? null,
                };
                this.summary.players = {
                    avg: data.summary.players_avg ?? null,
                    min: data.summary.players_min ?? null,
                    max: data.summary.players_max ?? null,
                };
            }
        },

        updateCharts(data) {
            const labels = data.labels ?? [];
            this.createChart('fps', 'fpsChart', labels, data.fps ?? [], 'FPS');
            this.createChart('memory', 'memoryChart', labels, data.memory ?? [], 'MB');
            this.createChart('players', 'playersChart', labels, data.players ?? [], 'Players');
            this.createChart('uptime', 'uptimeChart', labels, data.uptime ?? [], 'Hours');
        },

        createChart(key, canvasId, labels, dataset, label) {
            if (this.charts[key]) {
                this.charts[key].destroy();
                this.charts[key] = null;
            }

            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            const ctx = canvas.getContext('2d');

            this.charts[key] = new Chart(ctx, {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: dataset,
                        borderColor: '#22c55e',
                        backgroundColor: 'rgba(34, 197, 94, 0.1)',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                        pointHitRadius: 10,
                    }],
                },
                options: {
                    responsive: true,
                    maintainAspectRatio: false,
                    plugins: {
                        legend: {
                            display: false,
                        },
                        tooltip: {
                            backgroundColor: '#1f2937',
                            titleColor: '#9ca3af',
                            bodyColor: '#ffffff',
                            borderColor: '#374151',
                            borderWidth: 1,
                            padding: 8,
                            displayColors: false,
                        },
                    },
                    scales: {
                        x: {
                            display: true,
                            grid: {
                                display: false,
                            },
                            ticks: {
                                color: '#6b7280',
                                maxTicksLimit: 6,
                                maxRotation: 0,
                                font: {
                                    size: 10,
                                },
                            },
                            border: {
                                color: '#374151',
                            },
                        },
                        y: {
                            display: true,
                            beginAtZero: true,
                            grid: {
                                color: '#374151',
                            },
                            ticks: {
                                color: '#6b7280',
                                font: {
                                    size: 10,
                                },
                            },
                            border: {
                                color: '#374151',
                            },
                        },
                    },
                    interaction: {
                        intersect: false,
                        mode: 'index',
                    },
                },
            });
        },
    };
}
</script>
@endpush
@endsection
