@extends('admin.layout')

@section('title', 'Report #' . $report->id)

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ route('admin.reports.index') }}" class="p-2 bg-gray-700 hover:bg-gray-600 text-gray-400 hover:text-white rounded-lg transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
                </svg>
            </a>
            <div>
                <h1 class="text-2xl font-bold text-white">Report #{{ $report->id }}</h1>
                <p class="text-sm text-gray-500 mt-1">
                    {{ $report->reporter_name }} reported {{ $report->target_name }}
                </p>
            </div>
        </div>
        <div>
            @switch($report->status)
                @case('pending')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-yellow-500/20 text-yellow-400">Pending</span>
                    @break
                @case('reviewed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-blue-500/20 text-blue-400">Reviewed</span>
                    @break
                @case('resolved')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-green-500/20 text-green-400">Resolved</span>
                    @break
                @case('dismissed')
                    <span class="inline-flex items-center px-3 py-1 rounded-full text-sm font-medium bg-gray-500/20 text-gray-400">Dismissed</span>
                    @break
            @endswitch
        </div>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- Report Details --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Report Info --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Report Details</h2>
                <div class="grid grid-cols-2 gap-6">
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reported Player</p>
                            <p class="text-white font-medium text-lg">{{ $report->target_name }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reporter</p>
                            <p class="text-white">{{ $report->reporter_name }}</p>
                            @if($report->reporter_uuid)
                            <code class="text-xs text-gray-500 select-all">{{ $report->reporter_uuid }}</code>
                            @endif
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Channel</p>
                            <p class="text-gray-400">{{ $report->channel ?? '—' }}</p>
                        </div>
                    </div>
                    <div class="space-y-4">
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Server ID</p>
                            <p class="text-gray-400">{{ $report->server_id }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reporter In-Game ID</p>
                            <p class="text-gray-400">{{ $report->reporter_id ?? '—' }}</p>
                        </div>
                        <div>
                            <p class="text-xs text-gray-500 uppercase tracking-wider mb-1">Reported At</p>
                            <p class="text-gray-400">{{ $report->reported_at->format('Y-m-d H:i:s') }}</p>
                            <p class="text-xs text-gray-600">{{ $report->reported_at->diffForHumans() }}</p>
                        </div>
                    </div>
                </div>

                <div class="mt-6 pt-4 border-t border-gray-700">
                    <p class="text-xs text-gray-500 uppercase tracking-wider mb-2">Reason</p>
                    <div class="bg-gray-900/50 rounded-lg p-4">
                        <p class="text-gray-300 whitespace-pre-wrap">{{ $report->reason ?: 'No reason provided' }}</p>
                    </div>
                </div>
            </div>

            {{-- Admin Action Form --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Admin Action</h2>
                <form method="POST" action="{{ route('admin.reports.update', $report) }}">
                    @csrf
                    @method('PUT')

                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm text-gray-400 mb-2">Status</label>
                            <div class="flex flex-wrap gap-2">
                                @foreach(['pending' => ['Pending', 'bg-yellow-600/20 border-yellow-500/30 text-yellow-400'], 'reviewed' => ['Reviewed', 'bg-blue-600/20 border-blue-500/30 text-blue-400'], 'resolved' => ['Resolved', 'bg-green-600/20 border-green-500/30 text-green-400'], 'dismissed' => ['Dismissed', 'bg-gray-600/20 border-gray-500/30 text-gray-400']] as $val => [$label, $classes])
                                <label class="cursor-pointer">
                                    <input type="radio" name="status" value="{{ $val }}" {{ $report->status === $val ? 'checked' : '' }} class="sr-only peer">
                                    <span class="inline-flex items-center px-4 py-2 rounded-lg text-sm font-medium border transition peer-checked:ring-2 peer-checked:ring-offset-1 peer-checked:ring-offset-gray-800 {{ $classes }} peer-checked:ring-current opacity-60 peer-checked:opacity-100">
                                        {{ $label }}
                                    </span>
                                </label>
                                @endforeach
                            </div>
                        </div>

                        <div>
                            <label class="block text-sm text-gray-400 mb-1">Admin Notes</label>
                            <textarea name="admin_notes" rows="4" maxlength="5000" placeholder="Add notes about actions taken..."
                                      class="w-full px-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50 resize-y">{{ old('admin_notes', $report->admin_notes) }}</textarea>
                        </div>

                        @if($report->handled_by)
                        <div class="text-xs text-gray-600">
                            Last handled by {{ $report->handler->name ?? 'Unknown' }} at {{ $report->handled_at?->format('Y-m-d H:i') }}
                        </div>
                        @endif

                        <button type="submit" class="px-6 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-medium transition">
                            Update Report
                        </button>
                    </div>
                </form>
            </div>

            {{-- Quick Actions --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
                <p class="text-sm text-gray-500 mb-4">Take action against the reported player via the Server Manager.</p>
                <div class="flex flex-wrap gap-3">
                    <a href="{{ route('admin.server.players') }}" class="px-4 py-2 bg-yellow-600/20 border border-yellow-500/30 hover:bg-yellow-600/30 text-yellow-400 rounded-lg text-sm transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                        Kick Player
                    </a>
                    <a href="{{ route('admin.server.players') }}" class="px-4 py-2 bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg text-sm transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/></svg>
                        Ban Player
                    </a>
                    <a href="{{ route('admin.rcon.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                        RCON Console
                    </a>
                </div>
            </div>
        </div>

        {{-- Right Column --}}
        <div class="space-y-6">
            {{-- Report History for Target --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-1">Report History</h2>
                <p class="text-xs text-gray-500 mb-4">Other reports against <span class="text-white">{{ $report->target_name }}</span></p>

                @if($history->isEmpty())
                <div class="text-center py-6">
                    <p class="text-sm text-gray-600">No other reports found for this player</p>
                </div>
                @else
                <div class="space-y-3 max-h-96 overflow-y-auto">
                    @foreach($history as $prev)
                    <a href="{{ route('admin.reports.show', $prev) }}" class="block p-3 bg-gray-900/30 rounded-lg hover:bg-gray-700/30 transition">
                        <div class="flex items-center justify-between mb-1">
                            <code class="text-xs text-gray-600">#{{ $prev->id }}</code>
                            @switch($prev->status)
                                @case('pending')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-yellow-500/20 text-yellow-400">Pending</span>
                                    @break
                                @case('reviewed')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-blue-500/20 text-blue-400">Reviewed</span>
                                    @break
                                @case('resolved')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-green-500/20 text-green-400">Resolved</span>
                                    @break
                                @case('dismissed')
                                    <span class="px-1.5 py-0.5 rounded text-[10px] font-medium bg-gray-500/20 text-gray-400">Dismissed</span>
                                    @break
                            @endswitch
                        </div>
                        <p class="text-xs text-gray-400 truncate">{{ $prev->reason ?: 'No reason' }}</p>
                        <p class="text-xs text-gray-600 mt-1">by {{ $prev->reporter_name }} &middot; {{ $prev->reported_at->diffForHumans() }}</p>
                    </a>
                    @endforeach
                </div>
                @endif

                <div class="mt-3 pt-3 border-t border-gray-700 text-center">
                    <a href="{{ route('admin.reports.index', ['search' => $report->target_name]) }}" class="text-xs text-green-400 hover:text-green-300 transition">
                        View all reports for this player
                    </a>
                </div>
            </div>

            {{-- Timeline --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Timeline</h2>
                <div class="space-y-4">
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-yellow-400 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm text-white">Report created</p>
                            <p class="text-xs text-gray-500">{{ $report->reported_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>

                    @if($report->handled_at)
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-blue-400 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm text-white">Handled by {{ $report->handler->name ?? 'Unknown' }}</p>
                            <p class="text-xs text-gray-500">{{ $report->handled_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    @endif

                    @if($report->status === 'resolved')
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-green-400 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm text-white">Resolved</p>
                            <p class="text-xs text-gray-500">{{ $report->updated_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    @elseif($report->status === 'dismissed')
                    <div class="flex items-start gap-3">
                        <div class="w-2 h-2 mt-1.5 rounded-full bg-gray-400 flex-shrink-0"></div>
                        <div>
                            <p class="text-sm text-white">Dismissed</p>
                            <p class="text-xs text-gray-500">{{ $report->updated_at->format('M d, Y H:i:s') }}</p>
                        </div>
                    </div>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
