@extends('layouts.app')

@section('title', 'Kill Feed')

@section('content')
<div class="space-y-6" x-data="killFeed()" x-init="init()">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-white">Kill Feed</h1>
        <div class="flex items-center gap-3">
            <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                <input type="checkbox" x-model="showAI" class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500">
                Show AI kills
            </label>
            <label class="flex items-center gap-2 text-sm text-gray-400 cursor-pointer">
                <input type="checkbox" x-model="headshotsOnly" class="rounded bg-gray-700 border-gray-600 text-green-500 focus:ring-green-500">
                Headshots only
            </label>
            <span class="text-xs text-gray-600" x-text="'(' + filteredKills.length + ' shown)'"></span>
        </div>
    </div>

    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Killer</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase">Weapon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Victim</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Distance</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700/50">
                <template x-for="kill in filteredKills" :key="kill.id">
                    <tr class="hover:bg-gray-700/30 transition-colors" :class="kill._new ? 'animate-pulse bg-green-900/20' : ''">
                        <td class="px-4 py-2 text-sm text-gray-400" x-text="timeAgo(kill.killed_at)"></td>
                        <td class="px-4 py-2">
                            <span class="text-green-400 text-sm font-medium" x-text="kill.killer_name || 'Unknown'"></span>
                        </td>
                        <td class="px-4 py-2 text-center">
                            <div class="flex items-center justify-center gap-1.5">
                                <span class="text-xs text-gray-300" x-text="kill.weapon_name"></span>
                                <template x-if="kill.is_headshot">
                                    <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24">
                                        <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                    </svg>
                                </template>
                                <template x-if="kill.is_roadkill">
                                    <span class="px-1.5 py-0.5 text-[10px] font-bold bg-violet-500/20 text-violet-400 rounded">ROADKILL</span>
                                </template>
                            </div>
                        </td>
                        <td class="px-4 py-2">
                            <template x-if="kill.victim_type === 'AI'">
                                <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 text-xs font-semibold rounded">AI</span>
                            </template>
                            <template x-if="kill.victim_type !== 'AI'">
                                <span class="text-red-400 text-sm" x-text="kill.victim_name || 'Unknown'"></span>
                            </template>
                            <template x-if="kill.is_team_kill">
                                <span class="ml-1 px-1.5 py-0.5 text-[10px] font-bold bg-red-500/20 text-red-400 rounded">TK</span>
                            </template>
                        </td>
                        <td class="px-4 py-2 text-right text-sm font-medium"
                            :class="kill.kill_distance < 50 ? 'text-green-400' : (kill.kill_distance < 200 ? 'text-yellow-400' : 'text-red-400')"
                            x-text="kill.kill_distance ? Math.round(kill.kill_distance) + 'm' : '-'"></td>
                    </tr>
                </template>
            </tbody>
        </table>
        </div>
    </div>
</div>

@push('scripts')
<script>
function killFeed() {
    return {
        kills: @json($kills),
        showAI: true,
        headshotsOnly: false,
        weaponImages: @json($weaponImages),
        get filteredKills() {
            return this.kills.filter(k => {
                if (!this.showAI && k.victim_type === 'AI') return false;
                if (this.headshotsOnly && !k.is_headshot) return false;
                return true;
            });
        },
        init() {
            this.pollTimer = setInterval(() => this.fetchNew(), 12000);
            if (window.Echo) {
                window.Echo.channel('server.global')
                    .listen('.activity.new', (e) => {
                        if (e.type === 'kill') { this.fetchNew(); }
                    });
            }
        },
        async fetchNew() {
            if (this.kills.length === 0) return;
            const latest = this.kills[0]?.killed_at;
            if (!latest) return;
            try {
                const res = await fetch('/api/kill-feed?since=' + encodeURIComponent(latest));
                const newKills = await res.json();
                if (newKills.length > 0) {
                    newKills.forEach(k => k._new = true);
                    this.kills = [...newKills, ...this.kills].slice(0, 100);
                    setTimeout(() => { newKills.forEach(k => k._new = false); }, 3000);
                }
            } catch(e) {}
        },
        timeAgo(dateStr) {
            if (!dateStr) return '-';
            const date = new Date(dateStr);
            const now = new Date();
            const seconds = Math.floor((now - date) / 1000);
            if (seconds < 60) return seconds + 's ago';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm ago';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h ago';
            return Math.floor(seconds / 86400) + 'd ago';
        }
    };
}
</script>
@endpush
@endsection
