@extends('admin.layout')

@section('title', 'Admin Audit Log')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Audit Log</h1>
        <span class="text-sm text-gray-400">{{ $logs->total() }} total entries</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.audit-log') }}" method="GET" class="flex flex-wrap gap-4 items-end">
            <div>
                <label class="block text-xs text-gray-500 mb-1">Action</label>
                <select name="action" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Actions</option>
                    @foreach($actions as $action)
                    <option value="{{ $action }}" {{ request('action') === $action ? 'selected' : '' }}>{{ $action }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">User</label>
                <select name="user_id" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    <option value="">All Users</option>
                    @foreach($users as $user)
                    <option value="{{ $user->id }}" {{ request('user_id') == $user->id ? 'selected' : '' }}>{{ $user->name }}</option>
                    @endforeach
                </select>
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">From</label>
                <input type="date" name="date_from" value="{{ request('date_from') }}" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">To</label>
                <input type="date" name="date_to" value="{{ request('date_to') }}" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <div>
                <label class="block text-xs text-gray-500 mb-1">Search</label>
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search action..." class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">
                Filter
            </button>
            @if(request()->hasAny(['action', 'user_id', 'date_from', 'date_to', 'search']))
            <a href="{{ route('admin.audit-log') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
            <a href="{{ route('admin.audit-log', array_merge(request()->query(), ['export' => 'csv'])) }}" class="px-4 py-2 bg-blue-600/20 border border-blue-500/30 hover:bg-blue-600/30 text-blue-400 rounded-lg transition text-sm">
                Export CSV
            </a>
        </form>
    </div>

    {{-- Audit Log Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Target</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">IP Address</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Details</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($logs as $log)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3 text-sm text-gray-400 whitespace-nowrap" title="{{ $log->created_at->format('M j, Y g:i:s A') }}">
                        {{ $log->created_at->diffForHumans() }}
                    </td>
                    <td class="px-4 py-3">
                        @if($log->user)
                        <a href="{{ route('admin.users.edit', $log->user) }}" class="text-green-500 hover:text-green-400 font-medium transition">
                            {{ $log->user->name }}
                        </a>
                        @else
                        <span class="text-gray-500 italic">Deleted User</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $actionColor = match(true) {
                                $log->action === '2fa.enabled' || $log->action === '2fa.challenge-passed' => 'bg-green-500/20 text-green-400',
                                $log->action === '2fa.disabled' || $log->action === '2fa.admin-reset' => 'bg-red-500/20 text-red-400',
                                str_starts_with($log->action, '2fa.') => 'bg-yellow-500/20 text-yellow-400',
                                str_contains($log->action, 'ban') => 'bg-red-500/20 text-red-400',
                                str_contains($log->action, 'unban') || str_contains($log->action, 'restore') || str_contains($log->action, 'approve') => 'bg-green-500/20 text-green-400',
                                str_contains($log->action, 'delete') || str_contains($log->action, 'destroy') || str_contains($log->action, 'reject') || str_contains($log->action, 'disband') => 'bg-red-500/20 text-red-400',
                                str_contains($log->action, 'create') || str_contains($log->action, 'store') => 'bg-blue-500/20 text-blue-400',
                                str_contains($log->action, 'update') || str_contains($log->action, 'edit') => 'bg-yellow-500/20 text-yellow-400',
                                default => 'bg-white/5 text-gray-400',
                            };
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $actionColor }}">
                            {{ $log->action }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        @if($log->target_type)
                        <span class="text-gray-300">{{ class_basename($log->target_type) }}</span>
                        <span class="text-gray-500">#{{ $log->target_id }}</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <code class="text-xs text-gray-400">{{ $log->ip_address ?? '-' }}</code>
                    </td>
                    <td class="px-4 py-3">
                        @if($log->metadata && count($log->metadata) > 0)
                        <details class="group">
                            <summary class="cursor-pointer text-xs text-green-500 hover:text-green-400 transition select-none">
                                Show metadata
                                <svg class="w-3 h-3 inline-block ml-1 transition-transform group-open:rotate-90" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                                </svg>
                            </summary>
                            <pre class="mt-2 p-2 bg-gray-900/50 border border-white/5 rounded-lg text-xs text-gray-300 overflow-x-auto max-w-xs">{{ json_encode($log->metadata, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES) }}</pre>
                        </details>
                        @else
                        <span class="text-gray-500 text-xs">-</span>
                        @endif
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No audit log entries found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($logs->hasPages())
    <div class="flex justify-center">
        {{ $logs->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
