@extends('admin.layout')

@section('title', 'Scheduled Restarts')

@section('admin-content')
<div x-data="scheduledRestarts()" class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Scheduled Restarts</h1>
            <p class="text-sm text-gray-500 mt-1">Configure automated server restart schedules</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">
                <span class="flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                    </svg>
                    Dashboard
                </span>
            </a>
            <button @click="showForm = !showForm" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Schedule
            </button>
        </div>
    </div>

    {{-- Create Form (collapsible) --}}
    <div x-show="showForm" x-transition:enter="transition ease-out duration-200" x-transition:enter-start="opacity-0 -translate-y-2" x-transition:enter-end="opacity-100 translate-y-0" x-transition:leave="transition ease-in duration-150" x-transition:leave-start="opacity-100 translate-y-0" x-transition:leave-end="opacity-0 -translate-y-2" x-cloak>
        <form method="POST" action="{{ route('admin.server.scheduled-restarts.store') }}" class="glass-card rounded-xl p-6">
            @csrf
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">New Restart Schedule</h2>

            <div class="grid lg:grid-cols-2 gap-6">
                {{-- Left Column --}}
                <div class="space-y-4">
                    {{-- Server Selection --}}
                    <div>
                        <label for="server_id" class="block text-sm text-gray-400 mb-1">Server</label>
                        <select name="server_id" id="server_id" required
                                class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                            <option value="">Select a server...</option>
                            @foreach($servers as $server)
                                <option value="{{ $server->id }}">{{ $server->name }}</option>
                            @endforeach
                        </select>
                    </div>

                    {{-- Schedule Type --}}
                    <div>
                        <label class="block text-sm text-gray-400 mb-2">Schedule Type</label>
                        <div class="flex items-center gap-4">
                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                <input type="radio" name="schedule_type" value="daily" x-model="scheduleType"
                                       class="w-4 h-4 bg-white/5 border-white/10 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                Daily
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                <input type="radio" name="schedule_type" value="weekly" x-model="scheduleType"
                                       class="w-4 h-4 bg-white/5 border-white/10 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                Weekly
                            </label>
                            <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                <input type="radio" name="schedule_type" value="custom" x-model="scheduleType"
                                       class="w-4 h-4 bg-white/5 border-white/10 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                Custom
                            </label>
                        </div>
                    </div>

                    {{-- Restart Time (daily/weekly) --}}
                    <div x-show="scheduleType === 'daily' || scheduleType === 'weekly'" x-transition>
                        <label for="restart_time" class="block text-sm text-gray-400 mb-1">Restart Time</label>
                        <input type="time" name="restart_time" id="restart_time"
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                    </div>

                    {{-- Day Checkboxes (weekly) --}}
                    <div x-show="scheduleType === 'weekly'" x-transition>
                        <label class="block text-sm text-gray-400 mb-2">Days of the Week</label>
                        <div class="flex flex-wrap gap-3">
                            @php
                                $days = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                            @endphp
                            @foreach($days as $value => $label)
                                <label class="flex items-center gap-2 text-sm text-gray-300 cursor-pointer">
                                    <input type="checkbox" name="days[]" value="{{ $value }}"
                                           class="w-4 h-4 rounded bg-white/5 border-white/10 text-green-500 focus:ring-green-500 focus:ring-offset-gray-800">
                                    {{ $label }}
                                </label>
                            @endforeach
                        </div>
                    </div>

                    {{-- Cron Expression (custom) --}}
                    <div x-show="scheduleType === 'custom'" x-transition>
                        <label for="cron_expression" class="block text-sm text-gray-400 mb-1">Cron Expression</label>
                        <input type="text" name="cron_expression" id="cron_expression" placeholder="0 4 * * *"
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white font-mono focus:outline-none focus:border-green-500/50">
                        <p class="text-xs text-gray-500 mt-1">Standard cron format: minute hour day month weekday</p>
                    </div>
                </div>

                {{-- Right Column --}}
                <div class="space-y-4">
                    {{-- Warning Minutes --}}
                    <div>
                        <label for="warning_minutes" class="block text-sm text-gray-400 mb-1">Warning Minutes</label>
                        <input type="number" name="warning_minutes" id="warning_minutes" value="5" min="0" max="60"
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                        <p class="text-xs text-gray-500 mt-1">Minutes before restart to warn players. Set to 0 to disable.</p>
                    </div>

                    {{-- Warning Message --}}
                    <div>
                        <label for="warning_message" class="block text-sm text-gray-400 mb-1">Warning Message</label>
                        <input type="text" name="warning_message" id="warning_message" placeholder="Server will restart in {minutes} minutes."
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/10 rounded-lg text-sm text-white focus:outline-none focus:border-green-500/50">
                        <p class="text-xs text-gray-500 mt-1">Message sent to players before restart.</p>
                    </div>

                    {{-- Submit --}}
                    <div class="pt-4">
                        <button type="submit" class="w-full px-4 py-3 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm font-medium transition flex items-center justify-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                            </svg>
                            Create Schedule
                        </button>
                    </div>
                </div>
            </div>
        </form>
    </div>

    {{-- Existing Schedules --}}
    @if($restarts->isEmpty())
        <div class="glass-card rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/>
            </svg>
            <p class="text-gray-400 mb-1">No scheduled restarts configured</p>
            <p class="text-sm text-gray-500">Click "Add Schedule" to create your first restart schedule.</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($restarts as $restart)
                <div class="glass-card rounded-xl p-6">
                    <div class="flex flex-col lg:flex-row lg:items-center lg:justify-between gap-4">
                        {{-- Schedule Info --}}
                        <div class="flex-1 min-w-0">
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-white font-semibold truncate">{{ $restart->server->name ?? 'Unknown Server' }}</h3>
                                @if($restart->schedule_type === 'daily')
                                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 rounded-full text-xs font-medium">Daily</span>
                                @elseif($restart->schedule_type === 'weekly')
                                    <span class="px-2 py-0.5 bg-purple-500/20 text-purple-400 rounded-full text-xs font-medium">Weekly</span>
                                @else
                                    <span class="px-2 py-0.5 bg-orange-500/20 text-orange-400 rounded-full text-xs font-medium">Custom</span>
                                @endif
                                @if($restart->is_enabled)
                                    <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Active</span>
                                @else
                                    <span class="px-2 py-0.5 bg-red-500/20 text-red-400 rounded-full text-xs font-medium">Disabled</span>
                                @endif
                            </div>

                            <div class="grid grid-cols-2 lg:grid-cols-4 gap-4 text-sm">
                                {{-- Time / Cron --}}
                                <div>
                                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">
                                        @if($restart->schedule_type === 'custom')
                                            Cron Expression
                                        @else
                                            Time
                                        @endif
                                    </span>
                                    <span class="text-gray-300 font-mono text-xs">
                                        @if($restart->schedule_type === 'custom')
                                            {{ $restart->cron_expression }}
                                        @else
                                            {{ $restart->restart_time }}
                                        @endif
                                    </span>
                                </div>

                                {{-- Days (weekly) --}}
                                <div>
                                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Days</span>
                                    <span class="text-gray-300 text-xs">
                                        @if($restart->schedule_type === 'weekly' && $restart->days)
                                            @php
                                                $dayNames = [1 => 'Mon', 2 => 'Tue', 3 => 'Wed', 4 => 'Thu', 5 => 'Fri', 6 => 'Sat', 7 => 'Sun'];
                                                $selectedDays = is_array($restart->days) ? $restart->days : json_decode($restart->days, true);
                                            @endphp
                                            {{ collect($selectedDays)->map(fn($d) => $dayNames[$d] ?? $d)->implode(', ') }}
                                        @elseif($restart->schedule_type === 'daily')
                                            Every day
                                        @else
                                            --
                                        @endif
                                    </span>
                                </div>

                                {{-- Warning Config --}}
                                <div>
                                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Warning</span>
                                    <span class="text-gray-300 text-xs">
                                        {{ $restart->warning_minutes ?? 0 }} min{{ ($restart->warning_minutes ?? 0) !== 1 ? 's' : '' }}
                                        @if($restart->warning_message)
                                            <span class="text-gray-500 block truncate max-w-[180px]" title="{{ $restart->warning_message }}">{{ $restart->warning_message }}</span>
                                        @endif
                                    </span>
                                </div>

                                {{-- Execution Times --}}
                                <div>
                                    <span class="text-xs text-gray-500 uppercase tracking-wider block mb-1">Execution</span>
                                    <div class="text-xs space-y-0.5">
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-500">Next:</span>
                                            <span class="text-gray-300">
                                                @if($restart->next_execution_at)
                                                    {{ $restart->next_execution_at->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-gray-500">Not scheduled</span>
                                                @endif
                                            </span>
                                        </div>
                                        <div class="flex items-center gap-1">
                                            <span class="text-gray-500">Last:</span>
                                            <span class="text-gray-300">
                                                @if($restart->last_executed_at)
                                                    {{ $restart->last_executed_at->format('M d, Y H:i') }}
                                                @else
                                                    <span class="text-gray-500">Never</span>
                                                @endif
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Actions --}}
                        <div class="flex items-center gap-3 flex-shrink-0">
                            {{-- Enable/Disable Toggle --}}
                            <form method="POST" action="{{ route('admin.server.scheduled-restarts.update', $restart) }}">
                                @csrf
                                @method('PUT')
                                <input type="hidden" name="is_enabled" value="{{ $restart->is_enabled ? '0' : '1' }}">
                                <button type="submit" title="{{ $restart->is_enabled ? 'Disable' : 'Enable' }} schedule">
                                    <label class="relative inline-flex items-center cursor-pointer">
                                        <input type="checkbox" class="sr-only peer" {{ $restart->is_enabled ? 'checked' : '' }}
                                               onclick="this.closest('form').submit()">
                                        <div class="w-11 h-6 bg-white/5 peer-focus:outline-none rounded-full peer peer-checked:after:translate-x-full rtl:peer-checked:after:-translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:start-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600"></div>
                                    </label>
                                </button>
                            </form>

                            {{-- Delete --}}
                            <form method="POST" action="{{ route('admin.server.scheduled-restarts.destroy', $restart) }}"
                                  onsubmit="return confirm('Are you sure you want to delete this restart schedule?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="p-2 bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg transition" title="Delete schedule">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                                    </svg>
                                </button>
                            </form>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif
</div>

@push('scripts')
<script>
function scheduledRestarts() {
    return {
        showForm: false,
        scheduleType: 'daily'
    };
}
</script>
@endpush
@endsection
