<div class="activity-feed-wrapper glass rounded-xl p-5" x-data="activityFeed()" x-init="fetchEvents()">
    {{-- Header --}}
    <div class="flex items-center gap-3 mb-5 flex-shrink-0">
        <div class="flex items-center gap-2.5">
            <span class="relative flex h-2 w-2">
                <span class="animate-ping absolute inline-flex h-full w-full rounded-full bg-green-400 opacity-75"></span>
                <span class="relative inline-flex rounded-full h-2 w-2 bg-green-500 animate-live-glow"></span>
            </span>
            <h3 class="text-sm font-semibold text-gray-300 uppercase tracking-wider">Live Activity</h3>
        </div>
        <div class="flex-1 glow-line"></div>
        <span class="text-[10px] text-gray-600 font-medium tabular-nums" x-text="events.length + ' events'"></span>
    </div>

    {{-- Event list --}}
    <div class="activity-feed-scroll space-y-1 max-h-96 overflow-y-auto">
        <template x-for="event in events" :key="event.occurred_at + event.actor + event.type">
            <div class="group relative flex items-start gap-3 py-2.5 px-3 rounded-lg hover:bg-white/[0.03] transition-all duration-200">
                {{-- Left accent + icon --}}
                <div class="flex flex-col items-center flex-shrink-0 pt-0.5">
                    {{-- Kill icon --}}
                    <template x-if="event.type === 'kill'">
                        <div class="w-7 h-7 rounded-lg bg-red-500/10 border border-red-500/20 flex items-center justify-center group-hover:bg-red-500/20 group-hover:border-red-500/30 transition-all">
                            <svg class="w-3.5 h-3.5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 2a4 4 0 014 4c0 2-2 4-4 6-2-2-4-4-4-6a4 4 0 014-4zM5 21l2-6 5 4 5-4 2 6H5z"/>
                            </svg>
                        </div>
                    </template>
                    {{-- Base capture icon --}}
                    <template x-if="event.type === 'base_capture'">
                        <div class="w-7 h-7 rounded-lg bg-amber-500/10 border border-amber-500/20 flex items-center justify-center group-hover:bg-amber-500/20 group-hover:border-amber-500/30 transition-all">
                            <svg class="w-3.5 h-3.5 text-amber-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 21v-4m0 0V5a2 2 0 012-2h6.5l1 1H21l-3 6 3 6h-8.5l-1-1H5a2 2 0 00-2 2zm9-13.5V9"/>
                            </svg>
                        </div>
                    </template>
                    {{-- Connection icon --}}
                    <template x-if="event.type === 'connection'">
                        <div class="w-7 h-7 rounded-lg bg-green-500/10 border border-green-500/20 flex items-center justify-center group-hover:bg-green-500/20 group-hover:border-green-500/30 transition-all">
                            <svg class="w-3.5 h-3.5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 16l-4-4m0 0l4-4m-4 4h14m-5 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h7a3 3 0 013 3v1"/>
                            </svg>
                        </div>
                    </template>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    {{-- Kill event --}}
                    <template x-if="event.type === 'kill'">
                        <div>
                            <p class="text-[13px] leading-snug">
                                <template x-if="event.actor_profile_url">
                                    <a :href="event.actor_profile_url" class="text-green-400 font-medium hover:text-green-300 hover:underline transition" x-text="event.actor" @click.stop></a>
                                </template>
                                <template x-if="!event.actor_profile_url">
                                    <span class="text-gray-300 font-medium" x-text="event.actor"></span>
                                </template>
                                <span class="text-gray-600 mx-0.5">killed</span>
                                <template x-if="event.victim_type === 'AI'">
                                    <span class="text-yellow-500/80 font-medium">AI</span>
                                </template>
                                <template x-if="event.victim_type !== 'AI' && event.target_profile_url">
                                    <a :href="event.target_profile_url" class="text-red-400 font-medium hover:text-red-300 hover:underline transition" x-text="event.target" @click.stop></a>
                                </template>
                                <template x-if="event.victim_type !== 'AI' && !event.target_profile_url">
                                    <span class="text-gray-300 font-medium" x-text="event.target"></span>
                                </template>
                                <template x-if="event.is_headshot">
                                    <span class="inline-flex items-center ml-1 px-1 py-0.5 rounded bg-yellow-500/10 border border-yellow-500/20 text-yellow-400 text-[9px] font-bold uppercase">HS</span>
                                </template>
                            </p>
                            <p class="text-[11px] text-gray-600 mt-0.5" x-show="event.detail" x-text="event.detail"></p>
                        </div>
                    </template>

                    {{-- Base capture event --}}
                    <template x-if="event.type === 'base_capture'">
                        <div>
                            <p class="text-[13px] leading-snug">
                                <template x-if="event.actor_profile_url">
                                    <a :href="event.actor_profile_url" class="text-amber-400 font-medium hover:text-amber-300 hover:underline transition" x-text="event.actor" @click.stop></a>
                                </template>
                                <template x-if="!event.actor_profile_url">
                                    <span class="text-gray-300 font-medium" x-text="event.actor"></span>
                                </template>
                                <span class="text-gray-600 mx-0.5">captured</span>
                                <span class="text-amber-200/70" x-text="event.detail || 'a base'"></span>
                            </p>
                        </div>
                    </template>

                    {{-- Connection event --}}
                    <template x-if="event.type === 'connection'">
                        <div>
                            <p class="text-[13px] leading-snug">
                                <template x-if="event.actor_profile_url">
                                    <a :href="event.actor_profile_url" class="text-green-400 font-medium hover:text-green-300 hover:underline transition" x-text="event.actor" @click.stop></a>
                                </template>
                                <template x-if="!event.actor_profile_url">
                                    <span class="text-gray-300 font-medium" x-text="event.actor"></span>
                                </template>
                                <span class="text-gray-600 mx-0.5">joined the server</span>
                            </p>
                        </div>
                    </template>
                </div>

                {{-- Timestamp --}}
                <span class="text-[10px] text-gray-600 flex-shrink-0 tabular-nums pt-0.5" x-text="timeAgo(event.occurred_at)"></span>
            </div>
        </template>

        {{-- Empty state --}}
        <div x-show="events.length === 0 && !loading" class="text-center py-10">
            <svg class="w-8 h-8 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M13 10V3L4 14h7v7l9-11h-7z"/>
            </svg>
            <p class="text-gray-600 text-xs">No recent activity</p>
        </div>

        {{-- Loading skeleton --}}
        <div x-show="loading" class="space-y-1">
            <template x-for="i in 8" :key="'skel-'+i">
                <div class="flex items-start gap-3 py-2.5 px-3">
                    <div class="skeleton w-7 h-7 rounded-lg flex-shrink-0"></div>
                    <div class="flex-1 space-y-1.5 pt-0.5">
                        <div class="skeleton skeleton-text w-4/5"></div>
                        <div class="skeleton skeleton-text w-2/5" style="height:0.5rem"></div>
                    </div>
                    <div class="skeleton w-8 h-3 rounded mt-1"></div>
                </div>
            </template>
        </div>
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
                            actor_profile_url: e.data.actor_profile_url || null,
                            target_profile_url: e.data.target_profile_url || null,
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
            if (seconds < 60) return seconds + 's';
            if (seconds < 3600) return Math.floor(seconds / 60) + 'm';
            if (seconds < 86400) return Math.floor(seconds / 3600) + 'h';
            return Math.floor(seconds / 86400) + 'd';
        }
    };
}
</script>
