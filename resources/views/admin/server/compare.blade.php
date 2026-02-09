@extends('admin.layout')

@section('title', 'Server Comparison')

@section('admin-content')
<div x-data="serverCompare()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Server Comparison</h1>
            <p class="text-sm text-gray-500 mt-1">Compare status and resources across managed servers</p>
        </div>
        <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    @if($servers->isEmpty())
        {{-- No managed servers --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                </svg>
                <p class="text-gray-400 text-lg font-medium mb-2">No Managed Servers</p>
                <p class="text-gray-500 text-sm">No managed servers configured. Add manager_url and manager_key to servers in the admin panel.</p>
            </div>
        </div>
    @else
        {{-- Server Selector --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Select Servers to Compare</h2>
            <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3 mb-4">
                @foreach($servers as $server)
                    <label class="flex items-center gap-3 px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg cursor-pointer hover:border-green-500/50 transition"
                           :class="selectedIds.includes({{ $server->id }}) ? 'border-green-500/70 bg-green-500/10' : ''">
                        <input type="checkbox"
                               value="{{ $server->id }}"
                               class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500 focus:ring-offset-0"
                               @change="toggleServer({{ $server->id }})">
                        <span class="text-sm text-white truncate">{{ $server->name }}</span>
                    </label>
                @endforeach
            </div>
            <button @click="fetchData()"
                    :disabled="selectedIds.length === 0 || loading"
                    class="px-4 py-2 bg-green-600 hover:bg-green-700 disabled:bg-gray-700 disabled:text-gray-500 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg x-show="loading" class="w-4 h-4 animate-spin" fill="none" viewBox="0 0 24 24">
                    <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                    <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                </svg>
                <svg x-show="!loading" class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                </svg>
                <span x-text="loading ? 'Loading...' : 'Compare'"></span>
            </button>
        </div>

        {{-- Results --}}
        <div x-show="results.length > 0" x-cloak>
            <div class="grid grid-cols-1 md:grid-cols-2 xl:grid-cols-3 gap-6">
                <template x-for="server in results" :key="server.id">
                    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                        {{-- Server Name --}}
                        <div class="flex items-center justify-between mb-4">
                            <h3 class="text-lg font-semibold text-white truncate" x-text="server.name"></h3>
                            <span class="relative flex h-3 w-3 flex-shrink-0 ml-2">
                                <span class="animate-ping absolute inline-flex h-full w-full rounded-full opacity-75"
                                      :class="server.status === 'running' ? 'bg-green-400' : (server.status === 'unreachable' ? 'bg-yellow-400' : 'bg-red-400')"></span>
                                <span class="relative inline-flex rounded-full h-3 w-3"
                                      :class="server.status === 'running' ? 'bg-green-500' : (server.status === 'unreachable' ? 'bg-yellow-500' : 'bg-red-500')"></span>
                            </span>
                        </div>

                        {{-- Status --}}
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Status</p>
                            <p class="text-sm font-medium"
                               :class="server.status === 'running' ? 'text-green-400' : (server.status === 'unreachable' ? 'text-yellow-400' : 'text-red-400')"
                               x-text="server.status === 'running' ? 'Running' : (server.status === 'unreachable' ? 'Unreachable' : 'Stopped')"></p>
                        </div>

                        {{-- Players --}}
                        <div class="mb-4">
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Players Online</p>
                            <p class="text-xl font-bold text-white" x-text="server.players ?? '—'"></p>
                        </div>

                        {{-- System Resources --}}
                        <div class="border-t border-gray-700 pt-4 space-y-3">
                            <p class="text-xs text-gray-500 uppercase tracking-wider">System Resources</p>

                            {{-- CPU --}}
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">CPU</span>
                                <span class="text-sm font-medium text-white" x-text="server.system?.cpu != null ? server.system.cpu + '%' : '—'"></span>
                            </div>
                            <div class="w-full bg-gray-900 rounded-full h-1.5" x-show="server.system?.cpu != null">
                                <div class="h-1.5 rounded-full transition-all duration-500"
                                     :class="server.system?.cpu > 80 ? 'bg-red-500' : (server.system?.cpu > 60 ? 'bg-yellow-500' : 'bg-green-500')"
                                     :style="'width: ' + (server.system?.cpu ?? 0) + '%'"></div>
                            </div>

                            {{-- Memory --}}
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">Memory</span>
                                <span class="text-sm font-medium"
                                      :class="(server.system?.memory ?? 0) > 80 ? 'text-red-400' : 'text-white'"
                                      x-text="server.system?.memory != null ? server.system.memory + '%' : '—'"></span>
                            </div>
                            <div class="w-full bg-gray-900 rounded-full h-1.5" x-show="server.system?.memory != null">
                                <div class="h-1.5 rounded-full transition-all duration-500"
                                     :class="server.system?.memory > 80 ? 'bg-red-500' : (server.system?.memory > 60 ? 'bg-yellow-500' : 'bg-green-500')"
                                     :style="'width: ' + (server.system?.memory ?? 0) + '%'"></div>
                            </div>

                            {{-- Disk --}}
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">Disk</span>
                                <span class="text-sm font-medium text-white" x-text="server.system?.disk ?? '—'"></span>
                            </div>

                            {{-- Uptime --}}
                            <div class="flex items-center justify-between">
                                <span class="text-sm text-gray-400">Uptime</span>
                                <span class="text-sm font-medium text-white" x-text="server.system?.uptime != null ? formatUptime(server.system.uptime) : '—'"></span>
                            </div>
                        </div>
                    </div>
                </template>
            </div>
        </div>

        {{-- Empty State --}}
        <div x-show="results.length === 0" x-cloak>
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="text-center py-8">
                    <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                    <p class="text-gray-400">Select servers above and click Compare</p>
                </div>
            </div>
        </div>
    @endif
</div>

@push('scripts')
<script>
function serverCompare() {
    return {
        selectedIds: [],
        results: [],
        loading: false,
        pollInterval: null,

        toggleServer(id) {
            const idx = this.selectedIds.indexOf(id);
            if (idx === -1) {
                this.selectedIds.push(id);
            } else {
                this.selectedIds.splice(idx, 1);
            }
        },

        async fetchData() {
            if (this.selectedIds.length === 0) return;

            this.loading = true;
            try {
                const params = this.selectedIds.map(id => 'server_ids[]=' + id).join('&');
                const response = await fetch(`{{ route('admin.server.api.compare-data') }}?${params}`);
                if (response.ok) {
                    this.results = await response.json();
                    this.startPolling();
                }
            } catch (e) {
                console.error('Failed to fetch comparison data:', e);
            } finally {
                this.loading = false;
            }
        },

        startPolling() {
            if (this.pollInterval) {
                clearInterval(this.pollInterval);
            }
            this.pollInterval = setInterval(() => {
                if (this.results.length > 0) {
                    this.fetchData();
                }
            }, 30000);
        },

        formatUptime(seconds) {
            if (!seconds && seconds !== 0) return '—';
            const d = Math.floor(seconds / 86400);
            const h = Math.floor((seconds % 86400) / 3600);
            const m = Math.floor((seconds % 3600) / 60);
            if (d > 0) return d + 'd ' + h + 'h';
            if (h > 0) return h + 'h ' + m + 'm';
            return m + 'm';
        }
    };
}
</script>
@endpush
@endsection
