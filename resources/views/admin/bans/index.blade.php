@extends('admin.layout')

@section('admin-title', 'Ban Management')

@section('admin-content')
<div class="space-y-6">
    <!-- Stats Cards -->
    <div class="grid grid-cols-1 md:grid-cols-3 gap-6">
        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Pending Appeals</p>
                    <p class="text-3xl font-bold text-yellow-400">{{ $pendingAppeals }}</p>
                </div>
                <i data-lucide="file-text" class="w-12 h-12 text-yellow-400 opacity-50"></i>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Banned Users</p>
                    <p class="text-3xl font-bold text-red-400">{{ $bannedUsers }}</p>
                </div>
                <i data-lucide="user-x" class="w-12 h-12 text-red-400 opacity-50"></i>
            </div>
        </div>

        <div class="bg-gray-800 rounded-lg p-6">
            <div class="flex items-center justify-between">
                <div>
                    <p class="text-sm text-gray-400 mb-1">Temporary Bans</p>
                    <p class="text-3xl font-bold text-blue-400">{{ $tempBannedUsers }}</p>
                </div>
                <i data-lucide="clock" class="w-12 h-12 text-blue-400 opacity-50"></i>
            </div>
        </div>
    </div>

    <!-- Quick Actions -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Quick Actions</h2>
        <div class="grid grid-cols-2 md:grid-cols-4 gap-4">
            <a href="{{ route('admin.bans.appeals') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg transition">
                <i data-lucide="inbox" class="w-5 h-5 text-yellow-400"></i>
                <span class="text-white font-medium">Review Appeals</span>
            </a>
            <a href="{{ route('admin.bans.users') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg transition">
                <i data-lucide="users" class="w-5 h-5 text-red-400"></i>
                <span class="text-white font-medium">Banned Users</span>
            </a>
            <a href="{{ route('admin.bans.hardware') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg transition">
                <i data-lucide="cpu" class="w-5 h-5 text-purple-400"></i>
                <span class="text-white font-medium">Hardware Bans</span>
            </a>
            <a href="{{ route('admin.bans.import') }}" class="flex items-center gap-3 p-4 bg-gray-900 hover:bg-gray-750 rounded-lg transition">
                <i data-lucide="upload" class="w-5 h-5 text-blue-400"></i>
                <span class="text-white font-medium">Import Bans</span>
            </a>
        </div>
    </div>

    <!-- Recent Ban History -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Recent Ban History</h2>
        @if($recentBans->isEmpty())
            <p class="text-gray-400 text-center py-8">No recent ban activity</p>
        @else
            <div class="overflow-x-auto">
                <table class="w-full">
                    <thead>
                        <tr class="text-left text-sm text-gray-400 border-b border-gray-700">
                            <th class="pb-3">User</th>
                            <th class="pb-3">Action</th>
                            <th class="pb-3">Type</th>
                            <th class="pb-3">Admin</th>
                            <th class="pb-3">Date</th>
                        </tr>
                    </thead>
                    <tbody class="text-sm">
                        @foreach($recentBans as $ban)
                            <tr class="border-b border-gray-700/50">
                                <td class="py-3">
                                    @if($ban->user)
                                        <div class="flex items-center gap-2">
                                            <img src="{{ $ban->user->avatar }}" class="w-8 h-8 rounded-full" alt="">
                                            <span class="text-white">{{ $ban->user->name }}</span>
                                        </div>
                                    @else
                                        <span class="text-gray-500">Unknown User</span>
                                    @endif
                                </td>
                                <td class="py-3">
                                    <span class="px-2 py-1 rounded text-xs font-medium
                                        {{ $ban->action === 'banned' ? 'bg-red-900 text-red-300' : '' }}
                                        {{ $ban->action === 'unbanned' ? 'bg-green-900 text-green-300' : '' }}
                                        {{ $ban->action === 'temp_ban_expired' ? 'bg-blue-900 text-blue-300' : '' }}">
                                        {{ $ban->action_label }}
                                    </span>
                                </td>
                                <td class="py-3 text-gray-300">
                                    {{ $ban->ban_type_label ?? '-' }}
                                </td>
                                <td class="py-3 text-gray-300">
                                    {{ $ban->admin->name ?? 'System' }}
                                </td>
                                <td class="py-3 text-gray-400">
                                    {{ $ban->created_at->diffForHumans() }}
                                </td>
                            </tr>
                        @endforeach
                    </tbody>
                </table>
            </div>
        @endif
    </div>
</div>
@endsection
