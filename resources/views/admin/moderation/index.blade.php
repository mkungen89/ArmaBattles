@extends('admin.layout')

@section('admin-title', 'Moderation')

@section('admin-content')
<div class="space-y-6">
    <!-- Stats -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Flagged Chat</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ $queue['flagged_chat'] }}</p>
                </div>
                <i data-lucide="message-square-warning" class="w-12 h-12 text-yellow-400 opacity-50"></i>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Pending Reports</p>
                    <p class="text-3xl font-bold text-red-400">{{ $queue['pending_reports'] }}</p>
                </div>
                <i data-lucide="flag" class="w-12 h-12 text-red-400 opacity-50"></i>
            </div>
        </div>
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Active Warnings</p>
                    <p class="text-3xl font-bold text-orange-400">{{ $queue['active_warnings'] }}</p>
                </div>
                <i data-lucide="alert-triangle" class="w-12 h-12 text-orange-400 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.moderation.flagged-chat') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg">
                <i data-lucide="message-square" class="w-5 h-5 text-yellow-400"></i>
                <span class="text-white font-medium">Flagged Chat</span>
            </a>
            <a href="{{ route('admin.moderation.warnings') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg">
                <i data-lucide="alert-triangle" class="w-5 h-5 text-orange-400"></i>
                <span class="text-white font-medium">Warnings</span>
            </a>
            <a href="{{ route('admin.moderation.notes') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg">
                <i data-lucide="sticky-note" class="w-5 h-5 text-blue-400"></i>
                <span class="text-white font-medium">Notes</span>
            </a>
            <a href="{{ route('admin.bans.import') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg">
                <i data-lucide="upload" class="w-5 h-5 text-purple-400"></i>
                <span class="text-white font-medium">Import Bans</span>
            </a>
        </div>
    </div>

    <!-- Recent Warnings -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Recent Warnings</h2>
        @if($recentWarnings->isEmpty())
            <p class="text-gray-400 text-center py-8">No recent warnings</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-gray-400 border-b border-gray-700">
                            <th class="pb-3">User</th>
                            <th class="pb-3">Type</th>
                            <th class="pb-3">Severity</th>
                            <th class="pb-3">Moderator</th>
                            <th class="pb-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($recentWarnings as $warning)
                            <tr class="border-b border-gray-700/50">
                                <td class="py-3">
                                    <div class="flex items-center gap-2">
                                        <img src="{{ $warning->user->avatar }}" class="w-8 h-8 rounded-full" alt="">
                                        <span class="text-white">{{ $warning->user->name }}</span>
                                    </div>
                                </td>
                                <td class="py-3 text-gray-300">{{ ucfirst(str_replace('_', ' ', $warning->warning_type)) }}</td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        {{ $warning->severity === 'low' ? 'bg-blue-900 text-blue-300' : '' }}
                                        {{ $warning->severity === 'medium' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                        {{ $warning->severity === 'high' ? 'bg-orange-900 text-orange-300' : '' }}
                                        {{ $warning->severity === 'critical' ? 'bg-red-900 text-red-300' : '' }}">
                                        {{ ucfirst($warning->severity) }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-300">{{ $warning->moderator->name }}</td>
                                <td class="py-3 text-gray-400">{{ $warning->created_at->diffForHumans() }}</td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
