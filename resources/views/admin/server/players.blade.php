@extends('admin.layout')

@section('title', 'Server Players')

@section('admin-content')
<div x-data="playerManager()" x-init="startPolling()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Player Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage online players, bans, and broadcasts</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Dashboard</a>
            <a href="{{ route('admin.server.logs') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Logs</a>
            <a href="{{ route('admin.server.mods') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Mods</a>
            <a href="{{ route('admin.server.config') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Config</a>
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Online Players --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white">
                        Online Players
                        <span class="ml-2 text-sm font-normal text-gray-500" x-text="'(' + players.length + ')'"></span>
                    </h2>
                    <div class="flex items-center gap-2">
                        <span class="text-xs text-gray-500" x-text="lastRefresh ? 'Updated ' + lastRefresh : ''"></span>
                        <button @click="fetchPlayers()" class="p-1.5 bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-white rounded-lg transition">
                            <svg class="w-4 h-4" :class="loading && 'animate-spin'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                            </svg>
                        </button>
                    </div>
                </div>

                <div x-show="error" class="mb-4 p-3 bg-red-500/10 border border-red-500/30 rounded-lg text-sm text-red-400" x-text="error"></div>

                <div class="overflow-x-auto">
                    <table class="w-full" x-show="players.length > 0">
                        <thead>
                            <tr class="border-b border-gray-700">
                                <th class="text-left text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">ID</th>
                                <th class="text-left text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">GUID</th>
                                <th class="text-left text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">Name</th>
                                <th class="text-right text-xs text-gray-500 uppercase tracking-wider pb-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            <template x-for="player in players" :key="player.id">
                                <tr class="border-b border-gray-700/50 hover:bg-gray-700/20 transition">
                                    <td class="py-3 pr-4">
                                        <code class="text-xs text-gray-400" x-text="player.id"></code>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <code class="text-xs text-gray-500 select-all" x-text="player.guid || '—'"></code>
                                    </td>
                                    <td class="py-3 pr-4">
                                        <span class="text-sm text-white font-medium" x-text="player.name"></span>
                                    </td>
                                    <td class="py-3 text-right">
                                        <div class="flex items-center justify-end gap-2">
                                            <button @click="openKickModal(player)"
                                                    class="px-2.5 py-1 text-xs bg-yellow-600/20 border border-yellow-500/30 hover:bg-yellow-600/30 text-yellow-400 rounded-lg transition">
                                                Kick
                                            </button>
                                            <button @click="openBanModal(player)"
                                                    class="px-2.5 py-1 text-xs bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg transition">
                                                Ban
                                            </button>
                                        </div>
                                    </td>
                                </tr>
                            </template>
                        </tbody>
                    </table>
                </div>

                <div x-show="players.length === 0 && !loading" class="text-center py-12">
                    <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                    </svg>
                    <p class="text-gray-500">No players online</p>
                </div>
            </div>

            {{-- Ban List --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4 cursor-pointer" @click="showBans = !showBans">
                    <h2 class="text-lg font-semibold text-white">Ban List</h2>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="showBans && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>

                <div x-show="showBans" x-collapse>
                    <div x-show="banListLoading" class="flex items-center justify-center py-6">
                        <svg class="w-5 h-5 text-gray-500 animate-spin" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4z"></path>
                        </svg>
                    </div>

                    <div x-show="!banListLoading" class="bg-gray-900/50 rounded-lg p-4 font-mono text-xs text-gray-400 whitespace-pre-wrap max-h-64 overflow-y-auto" x-text="banList || 'No ban data available'"></div>

                    <div x-show="!banListLoading" class="mt-3 flex items-center gap-2">
                        <input type="number" x-model="unbanIndex" placeholder="Ban index to remove" min="0"
                               class="flex-1 px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
                        <button @click="submitUnban()" :disabled="unbanSubmitting"
                                class="px-4 py-2 bg-green-600/20 border border-green-500/30 hover:bg-green-600/30 text-green-400 rounded-lg text-sm transition disabled:opacity-50">
                            <span x-show="!unbanSubmitting">Unban</span>
                            <span x-show="unbanSubmitting">...</span>
                        </button>
                    </div>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Broadcast --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Broadcast Message</h2>
                <form method="POST" action="{{ route('admin.server.players.broadcast') }}">
                    @csrf
                    <textarea name="message" rows="3" maxlength="500" required placeholder="Type your message..."
                              class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50 resize-none"></textarea>
                    <p class="text-xs text-gray-600 mt-1 mb-3">Max 500 characters. Sent to all players in-game.</p>
                    <button type="submit" class="w-full px-4 py-2 bg-blue-600/20 border border-blue-500/30 hover:bg-blue-600/30 text-blue-400 rounded-lg text-sm font-medium transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        Send Broadcast
                    </button>
                </form>
            </div>

            {{-- Quick Stats --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Quick Stats</h2>
                <div class="space-y-3">
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Online Players</span>
                        <span class="text-sm font-medium text-white" x-text="players.length">0</span>
                    </div>
                    <div class="flex items-center justify-between">
                        <span class="text-sm text-gray-400">Auto-Refresh</span>
                        <span class="text-xs px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full">Every 30s</span>
                    </div>
                </div>
            </div>
        </div>
    </div>

    {{-- Kick Modal --}}
    <div x-show="kickModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="kickModal = false">
        <div class="fixed inset-0 bg-black/60" @click="kickModal = false"></div>
        <div class="relative bg-gray-800 border border-gray-700 rounded-xl p-6 w-full max-w-md" @click.stop>
            <h3 class="text-lg font-semibold text-white mb-1">Kick Player</h3>
            <p class="text-sm text-gray-400 mb-4">Kicking <span class="text-white font-medium" x-text="selectedPlayer?.name"></span> (ID: <span x-text="selectedPlayer?.id"></span>)</p>

            <form method="POST" action="{{ route('admin.server.players.kick') }}">
                @csrf
                <input type="hidden" name="player_id" :value="selectedPlayer?.id">
                <label class="block text-sm text-gray-400 mb-1">Reason</label>
                <input type="text" name="reason" value="Kicked by admin" class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50 mb-4">
                <div class="flex items-center gap-3">
                    <button type="button" @click="kickModal = false" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-yellow-600 hover:bg-yellow-500 text-white rounded-lg text-sm font-medium transition">Kick Player</button>
                </div>
            </form>
        </div>
    </div>

    {{-- Ban Modal --}}
    <div x-show="banModal" x-cloak class="fixed inset-0 z-50 flex items-center justify-center p-4" @keydown.escape.window="banModal = false">
        <div class="fixed inset-0 bg-black/60" @click="banModal = false"></div>
        <div class="relative bg-gray-800 border border-gray-700 rounded-xl p-6 w-full max-w-md" @click.stop>
            <h3 class="text-lg font-semibold text-white mb-1">Ban Player</h3>
            <p class="text-sm text-gray-400 mb-4">Banning <span class="text-white font-medium" x-text="selectedPlayer?.name"></span> (ID: <span x-text="selectedPlayer?.id"></span>)</p>

            <form method="POST" action="{{ route('admin.server.players.ban') }}">
                @csrf
                <input type="hidden" name="player_id" :value="selectedPlayer?.id">

                <label class="block text-sm text-gray-400 mb-1">Duration (minutes)</label>
                <input type="number" name="minutes" value="60" min="0" class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50 mb-1">
                <p class="text-xs text-gray-600 mb-4">0 = permanent ban</p>

                <label class="block text-sm text-gray-400 mb-1">Reason</label>
                <input type="text" name="reason" value="Banned by admin" class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50 mb-4">

                <div class="flex items-center gap-3">
                    <button type="button" @click="banModal = false" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Cancel</button>
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg text-sm font-medium transition">Ban Player</button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function playerManager() {
    return {
        players: [],
        loading: false,
        error: null,
        lastRefresh: null,
        showBans: false,
        banList: null,
        banListLoading: false,
        unbanIndex: '',
        unbanSubmitting: false,
        kickModal: false,
        banModal: false,
        selectedPlayer: null,

        async startPolling() {
            await this.fetchPlayers();
            let pollMs = 30000;
            if (window.Echo) {
                window.Echo.channel('server.{{ config("services.battlemetrics.server_id", 1) }}')
                    .listen('.player.connected', () => {
                        this.fetchPlayers();
                        pollMs = 60000;
                    });
            }
            setInterval(() => this.fetchPlayers(), pollMs);
            this.$watch('showBans', (val) => { if (val && !this.banList) this.fetchBans(); });
        },

        async fetchPlayers() {
            this.loading = true;
            try {
                const r = await fetch('{{ route("admin.server.api.players") }}');
                if (r.ok) {
                    const data = await r.json();
                    this.players = data.players ?? [];
                    this.error = null;
                } else {
                    const data = await r.json().catch(() => ({}));
                    this.error = data.error ?? 'Failed to fetch players';
                }
            } catch {
                this.error = 'Could not connect to game server';
            }
            this.loading = false;
            this.lastRefresh = new Date().toLocaleTimeString();
        },

        async fetchBans() {
            this.banListLoading = true;
            try {
                const r = await fetch('{{ route("admin.server.api.bans") }}');
                if (r.ok) {
                    const data = await r.json();
                    this.banList = data.bans ?? 'No bans';
                }
            } catch {
                this.banList = 'Could not fetch ban list';
            }
            this.banListLoading = false;
        },

        async submitUnban() {
            if (this.unbanIndex === '') return;
            this.unbanSubmitting = true;
            try {
                const r = await fetch('{{ route("admin.server.players.unban") }}', {
                    method: 'POST',
                    headers: { 'Content-Type': 'application/json', 'X-CSRF-TOKEN': '{{ csrf_token() }}' },
                    body: JSON.stringify({ ban_index: this.unbanIndex })
                });
                if (r.ok) {
                    this.unbanIndex = '';
                    this.banList = null;
                    await this.fetchBans();
                }
            } catch {}
            this.unbanSubmitting = false;
        },

        openKickModal(player) {
            this.selectedPlayer = player;
            this.kickModal = true;
        },

        openBanModal(player) {
            this.selectedPlayer = player;
            this.banModal = true;
        }
    };
}
</script>
@endpush
@endsection
