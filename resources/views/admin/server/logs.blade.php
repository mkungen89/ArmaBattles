@extends('admin.layout')

@section('title', 'Server Logs')

@section('admin-content')
<div x-data="logViewer()" x-init="init()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Server Logs</h1>
            <p class="text-sm text-gray-500 mt-1">View real-time server and service logs</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Dashboard</a>
            <a href="{{ route('admin.server.players') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Players</a>
            <a href="{{ route('admin.server.mods') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Mods</a>
            <a href="{{ route('admin.server.config') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Config</a>
        </div>
    </div>

    {{-- Log Type Tabs --}}
    <div class="flex items-center gap-1 bg-gray-800/50 border border-gray-700 rounded-xl p-1.5">
        @foreach(['arma' => 'Arma Console', 'arma-stdout' => 'Arma Stdout', 'stats' => 'Stats', 'stats-service' => 'Stats Service'] as $logType => $label)
        <a href="{{ route('admin.server.logs', $logType) }}"
           class="flex-1 text-center px-4 py-2 rounded-lg text-sm font-medium transition {{ $type === $logType ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'text-gray-400 hover:bg-gray-700/50 hover:text-white border border-transparent' }}">
            {{ $label }}
        </a>
        @endforeach
    </div>

    {{-- Controls --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-3">
            <label class="text-sm text-gray-400">Lines:</label>
            <div class="flex items-center gap-1">
                @foreach([50, 100, 200, 500] as $lineCount)
                <a href="{{ route('admin.server.logs', $type) }}?lines={{ $lineCount }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ request('lines', 100) == $lineCount ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-gray-800/50 border border-gray-700 text-gray-400 hover:text-white' }}">
                    {{ $lineCount }}
                </a>
                @endforeach
            </div>
        </div>
        <div class="flex items-center gap-2">
            <button @click="scrollToBottom()" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-white rounded-lg text-xs transition flex items-center gap-1">
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 14l-7 7m0 0l-7-7m7 7V3"/></svg>
                Bottom
            </button>
            <button @click="fetchLogs()" :disabled="loading" class="px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-white rounded-lg text-xs transition flex items-center gap-1.5 disabled:opacity-50">
                <svg class="w-3.5 h-3.5" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </button>
        </div>
    </div>

    {{-- Log Content --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        {{-- File info bar --}}
        <div class="px-4 py-2 border-b border-gray-700 flex items-center justify-between bg-gray-900/30">
            <span class="text-xs text-gray-500 font-mono truncate" x-text="logFile || 'Loading...'"></span>
            <span class="text-xs text-gray-600" x-text="logContent.length + ' lines'"></span>
        </div>

        {{-- Log viewer --}}
        <div x-ref="logContainer" class="p-4 max-h-[65vh] overflow-y-auto overflow-x-auto">
            <div x-show="error" class="p-4 text-center">
                <svg class="w-10 h-10 text-red-500/50 mx-auto mb-2" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                </svg>
                <p class="text-sm text-red-400" x-text="error"></p>
            </div>

            <div x-show="!error && logContent.length === 0 && !loading" class="p-4 text-center text-gray-600 text-sm">
                No log content available
            </div>

            <div x-show="!error" class="font-mono text-xs leading-relaxed space-y-0">
                <template x-for="(line, i) in logContent" :key="i">
                    <div class="text-gray-400 hover:bg-gray-700/20 px-2 py-0.5 rounded whitespace-pre-wrap break-all flex">
                        <span class="text-gray-600 select-none w-10 flex-shrink-0 text-right mr-3" x-text="i + 1"></span>
                        <span x-text="line" :class="{
                            'text-red-400': line.toLowerCase().includes('error') || line.toLowerCase().includes('exception'),
                            'text-yellow-400': line.toLowerCase().includes('warn'),
                            'text-green-400': line.toLowerCase().includes('success') || line.toLowerCase().includes('started'),
                        }"></span>
                    </div>
                </template>
            </div>
        </div>
    </div>
</div>

@push('scripts')
<script>
function logViewer() {
    return {
        logContent: {!! json_encode($data['content'] ?? []) !!},
        logFile: {!! json_encode($data['file'] ?? null) !!},
        logType: '{{ $type }}',
        lines: {{ request('lines', 100) }},
        loading: false,
        error: {!! json_encode($data === null ? 'Could not connect to game server' : null) !!},

        init() {
            this.$nextTick(() => this.scrollToBottom());
        },

        async fetchLogs() {
            this.loading = true;
            try {
                const r = await fetch('{{ route("admin.server.api.logs", $type) }}?lines=' + this.lines);
                if (r.ok) {
                    const data = await r.json();
                    this.logContent = data.content ?? [];
                    this.logFile = data.file ?? null;
                    this.error = null;
                    this.$nextTick(() => this.scrollToBottom());
                } else {
                    const data = await r.json().catch(() => ({}));
                    this.error = data.error ?? 'Failed to fetch logs';
                }
            } catch {
                this.error = 'Could not connect to game server';
            }
            this.loading = false;
        },

        scrollToBottom() {
            if (this.$refs.logContainer) {
                this.$refs.logContainer.scrollTop = this.$refs.logContainer.scrollHeight;
            }
        }
    };
}
</script>
@endpush
@endsection
