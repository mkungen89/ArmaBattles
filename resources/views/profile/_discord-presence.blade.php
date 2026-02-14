{{-- Discord Rich Presence Section --}}
@if($discordPresence && $discordPresence->enabled && $discordPresence->current_activity)
<div class="glass rounded-2xl p-6 card-hover"
     x-data="{
         activityStatus: @js($discordPresence->getActivityStatus()),
         activityState: @js($discordPresence->getActivityState()),
         startedAt: @js($discordPresence->started_at ? $discordPresence->started_at->toIso8601String() : null),
         enabled: @js($discordPresence->enabled),
         elapsedSeconds: @js($discordPresence->getElapsedTime()),
         get elapsedFormatted() {
             if (!this.elapsedSeconds) return '';
             const hours = Math.floor(this.elapsedSeconds / 3600);
             const minutes = Math.floor((this.elapsedSeconds % 3600) / 60);
             if (hours > 0) return `${hours}h ${minutes}m elapsed`;
             return `${minutes} minutes elapsed`;
         }
     }"
     x-init="
         @auth
         if (window.Echo && {{ auth()->id() }} === {{ $user->id }}) {
             window.Echo.private('App.Models.User.{{ $user->id }}')
                 .listen('.presence.updated', (e) => {
                     activityStatus = e.activity_status;
                     activityState = e.activity_state;
                     startedAt = e.started_at;
                     enabled = e.enabled;
                     elapsedSeconds = 0;

                     // Hide card if presence disabled or no activity
                     if (!enabled || !e.activity_status) {
                         $el.style.display = 'none';
                     } else {
                         $el.style.display = 'block';
                     }
                 });
         }
         @endauth

         // Update elapsed time every second
         if (startedAt) {
             setInterval(() => {
                 elapsedSeconds++;
             }, 1000);
         }
     ">
    <div class="flex items-center justify-between mb-4">
        <div class="flex items-center gap-3">
            <div class="w-10 h-10 bg-gradient-to-br from-indigo-500 to-purple-600 rounded-xl flex items-center justify-center">
                <svg class="w-6 h-6 text-white" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189z"/>
                </svg>
            </div>
            <div>
                <h3 class="text-white font-bold text-sm">Discord Activity</h3>
                <p class="text-gray-400 text-xs">Live presence</p>
            </div>
        </div>
        <span class="flex items-center gap-2 px-3 py-1 bg-green-500/20 text-green-400 text-xs font-medium rounded-full animate-live-glow">
            <span class="w-2 h-2 bg-green-400 rounded-full"></span>
            Live
        </span>
    </div>

    <div class="bg-white/5 rounded-xl p-4 border border-white/10">
        <div class="flex items-center gap-4">
            {{-- Activity Icon --}}
            <div class="w-14 h-14 bg-gradient-to-br from-blue-500 to-purple-600 rounded-lg flex items-center justify-center text-white font-bold text-xl flex-shrink-0">
                AR
            </div>

            {{-- Activity Info --}}
            <div class="flex-1 min-w-0">
                <p class="font-bold text-white text-sm mb-1 truncate" x-text="activityStatus">
                    {{ $discordPresence->getActivityStatus() }}
                </p>
                <p class="text-xs text-gray-400 truncate" x-show="activityState" x-text="activityState">
                    {{ $discordPresence->getActivityState() }}
                </p>
                <div class="flex items-center gap-2 mt-2" x-show="startedAt">
                    <svg class="w-3 h-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    <p class="text-xs text-gray-500" x-text="elapsedFormatted">
                        {{ $discordPresence->started_at?->diffForHumans(null, true) ?? '0 minutes' }} elapsed
                    </p>
                </div>
            </div>
        </div>
    </div>

    @if($discordPresence->discord_user_id)
    <div class="mt-3 pt-3 border-t border-white/5 flex items-center gap-2">
        <svg class="w-4 h-4 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/>
        </svg>
        <p class="text-xs text-gray-500">
            Discord ID: <span class="text-gray-400 font-mono">{{ $discordPresence->discord_user_id }}</span>
        </p>
    </div>
    @endif
</div>
@endif
