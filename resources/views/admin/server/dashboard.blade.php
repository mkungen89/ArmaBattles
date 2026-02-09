@extends('admin.layout')

@section('title', 'Server Manager')

@section('admin-content')
<div x-data="serverDashboard()" x-init="startPolling()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Server Manager</h1>
            <p class="text-sm text-gray-500 mt-1">Real-time monitoring and control</p>
        </div>
        <div class="flex items-center gap-2 flex-wrap">
            <a href="{{ route('admin.server.players') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Players</a>
            <a href="{{ route('admin.server.logs') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Logs</a>
            <a href="{{ route('admin.server.mods') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Mods</a>
            <a href="{{ route('admin.server.config') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Config</a>
            <a href="{{ route('admin.server.player-history') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Player History</a>
            <a href="{{ route('admin.server.performance') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Performance</a>
            <a href="{{ route('admin.server.scheduled-restarts') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Restarts</a>
            <a href="{{ route('admin.server.quick-messages') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Messages</a>
            <a href="{{ route('admin.server.mod-updates') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Mod Updates</a>
            <a href="{{ route('admin.server.compare') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Compare</a>
        </div>
    </div>

    {{-- Status Cards --}}
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
        {{-- Arma Server --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Arma Server</span>
                <span class="relative flex h-3 w-3">
                    <span :class="health.services?.arma === 'running' ? 'bg-green-400' : 'bg-red-400'"
                          class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                    <span :class="health.services?.arma === 'running' ? 'bg-green-500' : 'bg-red-500'"
                          class="relative inline-flex rounded-full h-3 w-3"></span>
                </span>
            </div>
            <p class="text-lg font-semibold" :class="health.services?.arma === 'running' ? 'text-green-400' : 'text-red-400'"
               x-text="health.services?.arma === 'running' ? 'Running' : (health.status === 'unreachable' ? 'Unreachable' : 'Stopped')">Loading...</p>
        </div>

        {{-- Stats Collector --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Stats Collector</span>
                <span class="relative flex h-3 w-3">
                    <span :class="health.services?.stats === 'running' ? 'bg-green-400' : 'bg-red-400'"
                          class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                    <span :class="health.services?.stats === 'running' ? 'bg-green-500' : 'bg-red-500'"
                          class="relative inline-flex rounded-full h-3 w-3"></span>
                </span>
            </div>
            <p class="text-lg font-semibold" :class="health.services?.stats === 'running' ? 'text-green-400' : 'text-red-400'"
               x-text="health.services?.stats === 'running' ? 'Running' : 'Stopped'">Loading...</p>
        </div>

        {{-- RCON --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase tracking-wider">RCON</span>
                <span class="relative flex h-3 w-3">
                    <span :class="health.rcon === 'connected' ? 'bg-green-400' : 'bg-red-400'"
                          class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"></span>
                    <span :class="health.rcon === 'connected' ? 'bg-green-500' : 'bg-red-500'"
                          class="relative inline-flex rounded-full h-3 w-3"></span>
                </span>
            </div>
            <p class="text-lg font-semibold" :class="health.rcon === 'connected' ? 'text-green-400' : 'text-red-400'"
               x-text="health.rcon === 'connected' ? 'Connected' : 'Disconnected'">Loading...</p>
        </div>

        {{-- Players --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center justify-between mb-2">
                <span class="text-xs text-gray-500 uppercase tracking-wider">Players Online</span>
                <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>
            <p class="text-lg font-semibold text-white" x-text="statusData?.players?.count ?? '—'">—</p>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Service Controls --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Quick Actions --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Service Controls</h2>
                <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
                    <form method="POST" action="{{ route('admin.server.service', ['arma', 'restart']) }}"
                          onsubmit="return confirm('Restart the Arma server? Players will be disconnected.')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-yellow-600/20 border border-yellow-500/30 hover:bg-yellow-600/30 text-yellow-400 rounded-lg transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Restart Arma
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.server.service', ['arma', 'stop']) }}"
                          onsubmit="return confirm('Stop the Arma server? Players will be disconnected.')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 10a1 1 0 011-1h4a1 1 0 011 1v4a1 1 0 01-1 1h-4a1 1 0 01-1-1v-4z"/></svg>
                            Stop Arma
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.server.service', ['arma', 'start']) }}">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-green-600/20 border border-green-500/30 hover:bg-green-600/30 text-green-400 rounded-lg transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14.752 11.168l-3.197-2.132A1 1 0 0010 9.87v4.263a1 1 0 001.555.832l3.197-2.132a1 1 0 000-1.664z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                            Start Arma
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.server.service', ['stats', 'restart']) }}"
                          onsubmit="return confirm('Restart stats collector? API will be unavailable for ~12 seconds.')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-blue-600/20 border border-blue-500/30 hover:bg-blue-600/30 text-blue-400 rounded-lg transition text-sm font-medium">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/></svg>
                            Restart Stats
                        </button>
                    </form>

                    <form method="POST" action="{{ route('admin.server.update') }}"
                          onsubmit="return confirm('Start SteamCMD update? This will stop the server, update, and restart. Players will be disconnected. This can take up to 10 minutes.')">
                        @csrf
                        <button type="submit" class="w-full flex items-center justify-center gap-2 px-4 py-3 bg-purple-600/20 border border-purple-500/30 hover:bg-purple-600/30 text-purple-400 rounded-lg transition text-sm font-medium col-span-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/></svg>
                            Update Server
                        </button>
                    </form>
                </div>

                {{-- Update Progress --}}
                <div x-show="updateStatus?.updateInProgress" x-cloak class="mt-4 p-4 bg-purple-500/10 border border-purple-500/30 rounded-lg">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5 text-purple-400 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                        <div>
                            <p class="text-sm font-medium text-purple-400">Update in progress</p>
                            <p class="text-xs text-gray-500" x-text="'Phase: ' + (updateStatus?.status?.phase ?? 'unknown')"></p>
                        </div>
                    </div>
                </div>
            </div>

            {{-- System Info --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">System Resources</h2>
                <div class="grid grid-cols-2 lg:grid-cols-4 gap-4">
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">CPU Load (1m)</p>
                        <p class="text-xl font-bold text-white" x-text="statusData?.system?.loadAvg?.['1m']?.toFixed(2) ?? '—'">—</p>
                        <p class="text-xs text-gray-500" x-text="(statusData?.system?.cpuCount ?? '?') + ' cores'"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Memory</p>
                        <p class="text-xl font-bold" :class="(statusData?.system?.memory?.usagePercent ?? 0) > 80 ? 'text-red-400' : 'text-white'"
                           x-text="(statusData?.system?.memory?.usagePercent ?? '—') + '%'">—</p>
                        <p class="text-xs text-gray-500" x-text="statusData?.system?.memory ? formatBytes(statusData.system.memory.used) + ' / ' + formatBytes(statusData.system.memory.total) : ''"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Disk</p>
                        <p class="text-xl font-bold text-white" x-text="statusData?.system?.disk?.usagePercent ?? '—'">—</p>
                        <p class="text-xs text-gray-500" x-text="statusData?.system?.disk ? statusData.system.disk.used + ' / ' + statusData.system.disk.size : ''"></p>
                    </div>
                    <div>
                        <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Uptime</p>
                        <p class="text-xl font-bold text-white" x-text="statusData?.system?.uptime ? formatUptime(statusData.system.uptime) : '—'">—</p>
                        <p class="text-xs text-gray-500" x-text="statusData?.system?.hostname ?? ''"></p>
                    </div>
                </div>
            </div>

            {{-- Recent Logs --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Recent Log</h2>
                    <a href="{{ route('admin.server.logs') }}" class="text-xs text-green-400 hover:text-green-300 transition">View all</a>
                </div>
                <div class="bg-gray-900/50 rounded-lg p-3 max-h-48 overflow-y-auto font-mono text-xs text-gray-400 space-y-0.5">
                    <template x-for="line in logLines" :key="line">
                        <div x-text="line" class="whitespace-pre-wrap break-all"></div>
                    </template>
                    <div x-show="logLines.length === 0" class="text-gray-600">No log data available</div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Anticheat Panel --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Raven Anti-Cheat</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Active Players</span>
                        <span class="text-sm font-medium text-white" x-text="acStats?.activePlayers ?? '—'">—</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Registered Players</span>
                        <span class="text-sm font-medium text-white" x-text="acStats?.registeredPlayers ?? '—'">—</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Potential Cheaters</span>
                        <span class="text-sm font-medium" :class="(acStats?.potentialCheaters ?? 0) > 0 ? 'text-red-400' : 'text-white'"
                              x-text="acStats?.potentialCheaters ?? '—'">—</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Banned Players</span>
                        <span class="text-sm font-medium text-white" x-text="acStats?.bannedPlayers?.length ?? '0'">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Confirmed Cheaters</span>
                        <span class="text-sm font-medium" :class="(acStats?.confirmedCheaters?.length ?? 0) > 0 ? 'text-red-400' : 'text-white'"
                              x-text="acStats?.confirmedCheaters?.length ?? '0'">0</span>
                    </div>
                </div>
            </div>

            {{-- Server Info --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Server Info</h2>
                <div class="space-y-3 text-sm">
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">Name</span>
                        <span class="text-white text-xs truncate max-w-[180px]" x-text="statusData?.server?.name ?? '—'" title="">—</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">Host</span>
                        <code class="text-xs text-gray-300" x-text="statusData?.server?.host ?? '—'">—</code>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">Query Port</span>
                        <code class="text-xs text-gray-300" x-text="statusData?.server?.queryPort ?? '—'">—</code>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-gray-400">RCON Port</span>
                        <code class="text-xs text-gray-300" x-text="statusData?.server?.rconPort ?? '—'">—</code>
                    </div>
                </div>
            </div>

            {{-- Online Players Quick List --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">Online Players</h2>
                    <a href="{{ route('admin.server.players') }}" class="text-xs text-green-400 hover:text-green-300 transition">Manage</a>
                </div>
                <div class="space-y-2 max-h-48 overflow-y-auto">
                    <template x-for="player in (statusData?.players?.list ?? [])" :key="player.id">
                        <div class="flex items-center justify-between text-sm">
                            <span class="text-white truncate" x-text="player.name"></span>
                            <code class="text-xs text-gray-500" x-text="'#' + player.id"></code>
                        </div>
                    </template>
                    <div x-show="!statusData?.players?.list?.length" class="text-sm text-gray-600">No players online</div>
                </div>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function serverDashboard() {
    return {
        health: {},
        statusData: {!! json_encode($status) !!} || {},
        acStats: null,
        updateStatus: null,
        logLines: [],

        wsConnected: false,
        async startPolling() {
            await this.fetchAll();
            let healthMs = 10000, statusMs = 30000, acMs = 30000, logsMs = 30000;

            if (window.Echo) {
                window.Echo.channel('server.{{ config("services.battlemetrics.server_id", 1) }}')
                    .listen('.status.updated', () => {
                        this.fetchStatus();
                        this.fetchHealth();
                        if (!this.wsConnected) {
                            this.wsConnected = true;
                            healthMs = 60000;
                            statusMs = 60000;
                            acMs = 60000;
                            logsMs = 60000;
                        }
                    })
                    .listen('.player.connected', () => {
                        this.fetchStatus();
                    });
            }

            setInterval(() => this.fetchHealth(), healthMs);
            setInterval(() => this.fetchStatus(), statusMs);
            setInterval(() => this.fetchAnticheat(), acMs);
            setInterval(() => this.fetchLogs(), logsMs);
            setInterval(() => this.fetchUpdateStatus(), 5000);
        },

        async fetchAll() {
            await Promise.all([
                this.fetchHealth(),
                this.fetchStatus(),
                this.fetchAnticheat(),
                this.fetchLogs(),
            ]);
        },

        async fetchHealth() {
            try {
                const r = await fetch('{{ route("admin.server.api.health") }}');
                this.health = await r.json();
            } catch { this.health = { status: 'unreachable' }; }
        },

        async fetchStatus() {
            try {
                const r = await fetch('{{ route("admin.server.api.status") }}');
                if (r.ok) this.statusData = await r.json();
            } catch {}
        },

        async fetchAnticheat() {
            try {
                const r = await fetch('{{ route("admin.server.api.anticheat") }}');
                if (r.ok) {
                    const data = await r.json();
                    this.acStats = data.stats;
                }
            } catch {}
        },

        async fetchLogs() {
            try {
                const r = await fetch('{{ route("admin.server.api.logs", "arma") }}?lines=10');
                if (r.ok) {
                    const data = await r.json();
                    this.logLines = data.content ?? [];
                }
            } catch {}
        },

        async fetchUpdateStatus() {
            try {
                const r = await fetch('{{ route("admin.server.api.update-status") }}');
                if (r.ok) this.updateStatus = await r.json();
            } catch {}
        },

        formatBytes(bytes) {
            if (!bytes) return '0 B';
            const units = ['B', 'KB', 'MB', 'GB'];
            let i = 0;
            while (bytes >= 1024 && i < units.length - 1) { bytes /= 1024; i++; }
            return bytes.toFixed(1) + ' ' + units[i];
        },

        formatUptime(seconds) {
            const d = Math.floor(seconds / 86400);
            const h = Math.floor((seconds % 86400) / 3600);
            if (d > 0) return d + 'd ' + h + 'h';
            const m = Math.floor((seconds % 3600) / 60);
            return h + 'h ' + m + 'm';
        }
    };
}
</script>
@endpush
@endsection
