@extends('admin.layout')

@section('title', 'Player Reports')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Player Reports</h1>
            <p class="text-sm text-gray-500 mt-1">
                {{ $pendingCount }} pending report{{ $pendingCount !== 1 ? 's' : '' }} awaiting review
            </p>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form method="GET" action="{{ route('admin.reports.index') }}" class="flex flex-wrap items-center gap-4">
            {{-- Status Filter --}}
            <div class="flex items-center gap-1">
                @foreach(['all' => 'All', 'pending' => 'Pending', 'reviewed' => 'Reviewed', 'resolved' => 'Resolved', 'dismissed' => 'Dismissed'] as $val => $label)
                <a href="{{ route('admin.reports.index', array_merge(request()->except('status', 'page'), $val !== 'all' ? ['status' => $val] : [])) }}"
                   class="px-3 py-1.5 rounded-lg text-xs font-medium transition {{ (request('status', 'all') === $val) ? 'bg-green-500/20 text-green-400 border border-green-500/30' : 'bg-gray-700/50 text-gray-400 hover:text-white border border-transparent' }}">
                    {{ $label }}
                    @if($val === 'pending' && $pendingCount > 0)
                    <span class="ml-1 px-1.5 py-0.5 bg-red-500/20 text-red-400 rounded-full text-[10px]">{{ $pendingCount }}</span>
                    @endif
                </a>
                @endforeach
            </div>

            {{-- Search --}}
            <div class="flex-1 min-w-[200px]">
                <div class="relative">
                    <input type="text" name="search" value="{{ request('search') }}" placeholder="Search player name or reason..."
                           class="w-full pl-9 pr-3 py-2 bg-gray-900/50 border border-gray-700 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
                    <svg class="absolute left-3 top-2.5 w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                    </svg>
                </div>
            </div>

            @if(request('status'))
            <input type="hidden" name="status" value="{{ request('status') }}">
            @endif
            <button type="submit" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition">Search</button>
            @if(request('search'))
            <a href="{{ route('admin.reports.index', request()->only('status')) }}" class="text-xs text-gray-500 hover:text-white transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Reports Table --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        @if($reports->isEmpty())
        <div class="p-12 text-center">
            <svg class="w-12 h-12 text-gray-700 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
            </svg>
            <p class="text-gray-500">No reports found</p>
        </div>
        @else
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-gray-700 bg-gray-900/30">
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">ID</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Status</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Reported Player</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Reason</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Reported By</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Date</th>
                        <th class="text-right text-xs text-gray-500 uppercase tracking-wider px-4 py-3">Action</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($reports as $report)
                    <tr class="border-b border-gray-700/50 hover:bg-gray-700/20 transition {{ $report->status === 'pending' ? 'bg-yellow-500/5' : '' }}">
                        <td class="px-4 py-3">
                            <code class="text-xs text-gray-500">#{{ $report->id }}</code>
                        </td>
                        <td class="px-4 py-3">
                            @switch($report->status)
                                @case('pending')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">Pending</span>
                                    @break
                                @case('reviewed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-blue-500/20 text-blue-400">Reviewed</span>
                                    @break
                                @case('resolved')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-green-500/20 text-green-400">Resolved</span>
                                    @break
                                @case('dismissed')
                                    <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium bg-gray-500/20 text-gray-400">Dismissed</span>
                                    @break
                            @endswitch
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-white font-medium">{{ $report->target_name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-400">{{ Str::limit($report->reason, 80) ?: '—' }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-400">{{ $report->reporter_name }}</span>
                        </td>
                        <td class="px-4 py-3">
                            <span class="text-sm text-gray-500" title="{{ $report->reported_at->format('Y-m-d H:i:s') }}">
                                {{ $report->reported_at->diffForHumans() }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-right">
                            <a href="{{ route('admin.reports.show', $report) }}"
                               class="px-3 py-1.5 text-xs bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                                View
                            </a>
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>

        @if($reports->hasPages())
        <div class="px-4 py-3 border-t border-gray-700">
            {{ $reports->links() }}
        </div>
        @endif
        @endif
    </div>
</div>
@endsection
