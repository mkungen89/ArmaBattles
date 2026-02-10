@extends('admin.layout')

@section('admin-title', 'Metrics & Tracking')

@section('admin-content')
<div x-data="metricsDashboard()" x-init="init()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between flex-wrap gap-4">
        <div>
            <h1 class="text-2xl font-bold text-white">Metrics & Tracking</h1>
            <p class="text-sm text-gray-500 mt-1">Analytics, API usage, and system performance</p>
        </div>

        {{-- Time Range Selector --}}
        <div class="flex items-center gap-1 glass-card rounded-lg p-1">
            <template x-for="r in ['6h','24h','72h','7d']" :key="r">
                <button @click="range = r" :class="range === r ? 'bg-green-600 text-white' : 'bg-white/5 text-gray-400 hover:bg-white/10'" class="px-3 py-1.5 rounded-md text-sm font-medium transition" x-text="r"></button>
            </template>
        </div>
    </div>

    {{-- Summary Stat Cards --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="glass-card rounded-xl p-4">
            <span class="text-xs text-gray-500 uppercase tracking-wider">Page Views (24h)</span>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($pageViews24h) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <span class="text-xs text-gray-500 uppercase tracking-wider">API Requests (24h)</span>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($apiRequests24h) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <span class="text-xs text-gray-500 uppercase tracking-wider">Unique Visitors (24h)</span>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($uniqueVisitors24h) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <span class="text-xs text-gray-500 uppercase tracking-wider">Feature Uses (24h)</span>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($featureUses24h) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <span class="text-xs text-gray-500 uppercase tracking-wider">Tournament Regs (30d)</span>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($tournamentRegistrations30d) }}</p>
        </div>
        <div class="glass-card rounded-xl p-4">
            <span class="text-xs text-gray-500 uppercase tracking-wider">Team Apps (30d)</span>
            <p class="text-2xl font-bold text-white mt-2">{{ number_format($teamApplications30d) }}</p>
        </div>
    </div>

    {{-- Tabs --}}
    <div class="border-b border-white/5">
        <nav class="flex gap-6">
            <button @click="tab = 'analytics'" :class="tab === 'analytics' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400 hover:text-white'" class="pb-3 border-b-2 text-sm font-medium transition">Analytics</button>
            <button @click="tab = 'api'" :class="tab === 'api' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400 hover:text-white'" class="pb-3 border-b-2 text-sm font-medium transition">API Usage</button>
            <button @click="tab = 'performance'" :class="tab === 'performance' ? 'border-green-500 text-green-400' : 'border-transparent text-gray-400 hover:text-white'" class="pb-3 border-b-2 text-sm font-medium transition">Performance</button>
        </nav>
    </div>

    {{-- Analytics Tab --}}
    <div x-show="tab === 'analytics'" x-cloak class="space-y-6">
        {{-- Page Views Chart --}}
        <div class="glass-card rounded-xl p-6">
            <h3 class="text-sm font-semibold text-white mb-4">Page Views Over Time</h3>
            <div class="relative" style="height: 250px;">
                <canvas id="pageViewsChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Top Pages --}}
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Top Pages</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-gray-500 pb-2 font-medium">Page</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Views</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Unique</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="page in analyticsData.top_pages" :key="page.event_name">
                                <tr class="border-b border-white/5">
                                    <td class="py-2 text-gray-300 truncate max-w-[200px]" x-text="page.event_name"></td>
                                    <td class="py-2 text-right text-white font-medium" x-text="Number(page.views).toLocaleString()"></td>
                                    <td class="py-2 text-right text-gray-400" x-text="Number(page.unique_visitors).toLocaleString()"></td>
                                </tr>
                            </template>
                            <tr x-show="!analyticsData.top_pages?.length">
                                <td colspan="3" class="py-4 text-center text-gray-500">No data yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Feature Adoption --}}
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Feature Adoption</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-gray-500 pb-2 font-medium">Feature</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Uses</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Users</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="feat in analyticsData.feature_adoption" :key="feat.event_name">
                                <tr class="border-b border-white/5">
                                    <td class="py-2 text-gray-300" x-text="feat.event_name"></td>
                                    <td class="py-2 text-right text-white font-medium" x-text="Number(feat.uses).toLocaleString()"></td>
                                    <td class="py-2 text-right text-gray-400" x-text="Number(feat.unique_users).toLocaleString()"></td>
                                </tr>
                            </template>
                            <tr x-show="!analyticsData.feature_adoption?.length">
                                <td colspan="3" class="py-4 text-center text-gray-500">No data yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- API Usage Tab --}}
    <div x-show="tab === 'api'" x-cloak class="space-y-6">
        {{-- Error Rate Badge + API Requests Chart --}}
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h3 class="text-sm font-semibold text-white">API Requests Over Time</h3>
                <div class="flex items-center gap-3">
                    <span class="text-xs text-gray-500">Error Rate:</span>
                    <span class="px-2 py-0.5 rounded-full text-xs font-medium"
                          :class="apiData.error_rate > 5 ? 'bg-red-500/20 text-red-400' : apiData.error_rate > 1 ? 'bg-yellow-500/20 text-yellow-400' : 'bg-green-500/20 text-green-400'"
                          x-text="(apiData.error_rate ?? 0) + '%'"></span>
                </div>
            </div>
            <div class="relative" style="height: 250px;">
                <canvas id="apiRequestsChart"></canvas>
            </div>
        </div>

        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            {{-- Per-Token Usage --}}
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Requests by API Token</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-gray-500 pb-2 font-medium">Token</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Requests</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Avg (ms)</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="token in apiData.per_token" :key="token.token_id">
                                <tr class="border-b border-white/5">
                                    <td class="py-2 text-gray-300 truncate max-w-[200px]" x-text="token.token_name"></td>
                                    <td class="py-2 text-right text-white font-medium" x-text="Number(token.requests).toLocaleString()"></td>
                                    <td class="py-2 text-right text-gray-400" x-text="token.avg_ms + 'ms'"></td>
                                </tr>
                            </template>
                            <tr x-show="!apiData.per_token?.length">
                                <td colspan="3" class="py-4 text-center text-gray-500">No data yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>

            {{-- Top Endpoints --}}
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Top API Endpoints</h3>
                <div class="overflow-x-auto">
                    <table class="w-full text-sm">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-gray-500 pb-2 font-medium">Endpoint</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Reqs</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">Avg</th>
                                <th class="text-right text-gray-500 pb-2 font-medium">P95</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="ep in apiData.top_endpoints" :key="ep.event_name">
                                <tr class="border-b border-white/5">
                                    <td class="py-2 text-gray-300 truncate max-w-[180px]" x-text="ep.event_name"></td>
                                    <td class="py-2 text-right text-white font-medium" x-text="Number(ep.requests).toLocaleString()"></td>
                                    <td class="py-2 text-right text-gray-400" x-text="Math.round(ep.avg_ms) + 'ms'"></td>
                                    <td class="py-2 text-right text-gray-400" x-text="Math.round(ep.p95_ms) + 'ms'"></td>
                                </tr>
                            </template>
                            <tr x-show="!apiData.top_endpoints?.length">
                                <td colspan="4" class="py-4 text-center text-gray-500">No data yet</td>
                            </tr>
                        </tbody>
                    </table>
                </div>
            </div>
        </div>
    </div>

    {{-- Performance Tab --}}
    <div x-show="tab === 'performance'" x-cloak class="space-y-6">
        {{-- Performance Summary Cards --}}
        <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
            <div class="glass-card rounded-xl p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider">API P50</span>
                <p class="text-2xl font-bold text-white mt-2" x-text="perfData.summary?.api_p50 !== null ? perfData.summary.api_p50 + 'ms' : '--'">--</p>
            </div>
            <div class="glass-card rounded-xl p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider">API P95</span>
                <p class="text-2xl font-bold text-white mt-2" x-text="perfData.summary?.api_p95 !== null ? perfData.summary.api_p95 + 'ms' : '--'">--</p>
            </div>
            <div class="glass-card rounded-xl p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Cache Hit Rate</span>
                <p class="text-2xl font-bold text-white mt-2" x-text="perfData.summary?.cache_hit_rate !== null ? perfData.summary.cache_hit_rate + '%' : '--'">--</p>
            </div>
            <div class="glass-card rounded-xl p-4">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Queue Size</span>
                <p class="text-2xl font-bold text-white mt-2" x-text="perfData.summary?.queue_size !== null ? perfData.summary.queue_size : '--'">--</p>
            </div>
        </div>

        {{-- Performance Charts --}}
        <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">API Response Times (ms)</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="apiResponseChart"></canvas>
                </div>
            </div>

            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Cache Hit Rate (%)</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="cacheChart"></canvas>
                </div>
            </div>

            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">System Memory (MB)</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="sysMemoryChart"></canvas>
                </div>
            </div>

            <div class="glass-card rounded-xl p-6">
                <h3 class="text-sm font-semibold text-white mb-4">Queue Jobs</h3>
                <div class="relative" style="height: 220px;">
                    <canvas id="queueChart"></canvas>
                </div>
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
            <span class="text-white text-sm">Loading metrics...</span>
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/chart.js"></script>

@push('scripts')
<script>
function metricsDashboard() {
    return {
        range: '24h',
        tab: 'analytics',
        loading: false,
        analyticsData: {},
        apiData: {},
        perfData: {},
        charts: {},

        async init() {
            this.$watch('range', () => this.fetchTabData());
            this.$watch('tab', () => this.fetchTabData());
            await this.fetchTabData();
        },

        async fetchTabData() {
            this.loading = true;
            try {
                if (this.tab === 'analytics') {
                    await this.fetchAnalytics();
                } else if (this.tab === 'api') {
                    await this.fetchApiUsage();
                } else if (this.tab === 'performance') {
                    await this.fetchPerformance();
                }
            } catch (e) {
                console.error('Failed to load metrics:', e);
            } finally {
                this.loading = false;
            }
        },

        async fetchAnalytics() {
            const res = await fetch(`{{ route('admin.metrics.analytics-data') }}?range=${this.range}`);
            if (!res.ok) throw new Error('Failed to fetch analytics');
            this.analyticsData = await res.json();
            this.$nextTick(() => this.renderAnalyticsCharts());
        },

        async fetchApiUsage() {
            const res = await fetch(`{{ route('admin.metrics.usage-data') }}?range=${this.range}`);
            if (!res.ok) throw new Error('Failed to fetch API usage');
            this.apiData = await res.json();
            this.$nextTick(() => this.renderApiCharts());
        },

        async fetchPerformance() {
            const res = await fetch(`{{ route('admin.metrics.performance-data') }}?range=${this.range}`);
            if (!res.ok) throw new Error('Failed to fetch performance');
            this.perfData = await res.json();
            this.$nextTick(() => this.renderPerformanceCharts());
        },

        renderAnalyticsCharts() {
            const pv = this.analyticsData.page_views_over_time ?? { labels: [], data: [] };
            this.createChart('pageViews', 'pageViewsChart', this.formatLabels(pv.labels), pv.data, 'Page Views', '#22c55e');
        },

        renderApiCharts() {
            const rt = this.apiData.requests_over_time ?? { labels: [], data: [] };
            this.createChart('apiRequests', 'apiRequestsChart', this.formatLabels(rt.labels), rt.data, 'Requests', '#3b82f6');
        },

        renderPerformanceCharts() {
            const labels = this.formatLabels(this.perfData.labels ?? []);

            // API response times - multi-line
            this.createMultiChart('apiResponse', 'apiResponseChart', labels, [
                { data: this.perfData.api_p50 ?? [], label: 'P50', color: '#22c55e' },
                { data: this.perfData.api_p95 ?? [], label: 'P95', color: '#f59e0b' },
                { data: this.perfData.api_p99 ?? [], label: 'P99', color: '#ef4444' },
            ]);

            // Cache hit rate
            this.createChart('cache', 'cacheChart', labels, this.perfData.cache_hit_rate ?? [], 'Hit Rate %', '#8b5cf6');

            // System memory
            this.createChart('sysMemory', 'sysMemoryChart', labels, this.perfData.memory ?? [], 'MB', '#3b82f6');

            // Queue jobs - multi-line
            this.createMultiChart('queue', 'queueChart', labels, [
                { data: this.perfData.queue_size ?? [], label: 'Queue Size', color: '#f59e0b' },
                { data: this.perfData.jobs_failed ?? [], label: 'Failed', color: '#ef4444' },
            ]);
        },

        formatLabels(labels) {
            return labels.map(l => {
                const d = new Date(l);
                return d.toLocaleTimeString([], { hour: '2-digit', minute: '2-digit' });
            });
        },

        chartOptions(showLegend = false) {
            return {
                responsive: true,
                maintainAspectRatio: false,
                plugins: {
                    legend: { display: showLegend, labels: { color: '#9ca3af', boxWidth: 12, padding: 12 } },
                    tooltip: {
                        backgroundColor: '#1f2937',
                        titleColor: '#9ca3af',
                        bodyColor: '#ffffff',
                        borderColor: '#374151',
                        borderWidth: 1,
                        padding: 8,
                        displayColors: showLegend,
                    },
                },
                scales: {
                    x: {
                        display: true,
                        grid: { display: false },
                        ticks: { color: '#6b7280', maxTicksLimit: 8, maxRotation: 0, font: { size: 10 } },
                        border: { color: '#374151' },
                    },
                    y: {
                        display: true,
                        beginAtZero: true,
                        grid: { color: '#374151' },
                        ticks: { color: '#6b7280', font: { size: 10 } },
                        border: { color: '#374151' },
                    },
                },
                interaction: { intersect: false, mode: 'index' },
            };
        },

        createChart(key, canvasId, labels, dataset, label, color) {
            if (this.charts[key]) { this.charts[key].destroy(); this.charts[key] = null; }
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            this.charts[key] = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: [{
                        label: label,
                        data: dataset,
                        borderColor: color,
                        backgroundColor: color + '1a',
                        borderWidth: 2,
                        fill: true,
                        tension: 0.3,
                        pointRadius: 0,
                        pointHitRadius: 10,
                    }],
                },
                options: this.chartOptions(),
            });
        },

        createMultiChart(key, canvasId, labels, datasets) {
            if (this.charts[key]) { this.charts[key].destroy(); this.charts[key] = null; }
            const canvas = document.getElementById(canvasId);
            if (!canvas) return;

            this.charts[key] = new Chart(canvas.getContext('2d'), {
                type: 'line',
                data: {
                    labels: labels,
                    datasets: datasets.map(ds => ({
                        label: ds.label,
                        data: ds.data,
                        borderColor: ds.color,
                        backgroundColor: ds.color + '1a',
                        borderWidth: 2,
                        fill: false,
                        tension: 0.3,
                        pointRadius: 0,
                        pointHitRadius: 10,
                    })),
                },
                options: this.chartOptions(true),
            });
        },
    };
}
</script>
@endpush
@endsection
