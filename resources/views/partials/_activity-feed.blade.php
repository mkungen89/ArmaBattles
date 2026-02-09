<div class="activity-feed-wrapper bg-gray-800/50 border border-gray-700 rounded-xl p-6" x-data="activityFeed()" x-init="fetchEvents()">
    <div class="flex items-center gap-3 mb-4 flex-shrink-0">
        <h3 class="text-lg font-semibold text-white">Live Activity</h3>
        <span class="relative flex h-2 w-2">
            <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
            <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500"></span>
        </span>
        <div class="flex-1 h-px bg-gray-700"></div>
    </div>
    <div class="activity-feed-scroll space-y-2 max-h-96 overflow-y-auto">
        <template x-for="event in events" :key="event.occurred_at + event.actor + event.type">
            <div class="flex items-center gap-3 py-2 px-3 rounded-lg hover:bg-gray-700/30 transition">
                {{-- Icon --}}
                <template x-if="event.type === 'kill'">
                    <div class="w-8 h-8 rounded-full bg-red-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2z"/>
                        </svg>
                    </div>
                </template>
                <template x-if="event.type === 'base_capture'">
                    <div class="w-8 h-8 rounded-full bg-amber-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                        </svg>
                    </div>
                </template>
                <template x-if="event.type === 'connection'">
                    <div class="w-8 h-8 rounded-full bg-green-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18 9v3m0 0v3m0-3h3m-3 0h-3m-2-5a4 4 0 11-8 0 4 4 0 018 0zM3 20a6 6 0 0112 0v1H3v-1z"/>
                        </svg>
                    </div>
                </template>
                {{-- Text --}}
                <div class="flex-1 min-w-0">
                    <template x-if="event.type === 'kill'">
                        <p class="text-sm truncate">
                            <span class="text-green-400 font-medium" x-text="event.actor"></span>
                            <span class="text-gray-500">killed</span>
                            <span :class="event.victim_type === 'AI' ? 'text-yellow-400' : 'text-red-400'" x-text="event.victim_type === 'AI' ? 'AI' : event.target"></span>
                            <template x-if="event.is_headshot">
                                <svg class="w-3 h-3 text-yellow-400 inline ml-1" fill="currentColor" viewBox="0 0 24 24">
                                    <path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/>
                                </svg>
                            </template>
                        </p>
                    </template>
                    <template x-if="event.type === 'base_capture'">
                        <p class="text-sm truncate">
                            <span class="text-amber-400 font-medium" x-text="event.actor"></span>
                            <span class="text-gray-500">captured</span>
                            <span class="text-white" x-text="event.detail || 'a base'"></span>
                        </p>
                    </template>
                    <template x-if="event.type === 'connection'">
                        <p class="text-sm truncate">
                            <span class="text-green-400 font-medium" x-text="event.actor"></span>
                            <span class="text-gray-500">joined the server</span>
                        </p>
                    </template>
                </div>
                {{-- Time --}}
                <span class="text-xs text-gray-600 flex-shrink-0" x-text="timeAgo(event.occurred_at)"></span>
            </div>
        </template>
        <div x-show="events.length === 0 && !loading" class="text-center text-gray-500 py-4 text-sm">
            No recent activity
        </div>
        <div x-show="loading" class="text-center text-gray-500 py-4 text-sm">Loading...</div>
    </div>
</div>

<script>
function activityFeed() {
    return {
        events: [],
        loading: true,
        async fetchEvents() {
            try {
                const res = await fetch('/api/activity-feed');
                this.events = await res.json();
            } catch(e) {} finally { this.loading = false; }
            let pollMs = 20000;
            if (window.Echo) {
                window.Echo.channel('server.global')
                    .listen('.activity.new', (e) => {
                        this.events.unshift({
                            type: e.type,
                            actor: e.data.killer_name || e.data.player_name || 'Unknown',
                            target: e.data.victim_name || e.data.base_name || null,
                            detail: e.data.base_name || e.data.weapon_name || null,
                            victim_type: e.data.victim_type || null,
                            is_headshot: e.data.is_headshot || false,
                            occurred_at: e.timestamp,
                        });
                        this.events = this.events.slice(0, 50);
                        pollMs = 60000;
                    });
            }
            setInterval(async () => {
                try {
                    const res = await fetch('/api/activity-feed');
                    this.events = await res.json();
                } catch(e) {}
            }, pollMs);
        },
        timeAgo(dateStr) {
            if (!dateStr) return '';
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
