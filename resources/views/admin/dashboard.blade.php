@extends('admin.layout')

@section('title', 'Admin Dashboard')

@section('admin-content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Dashboard</h1>

    {{-- Stats Grid --}}
    <div class="grid grid-cols-2 md:grid-cols-3 lg:grid-cols-6 gap-4">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-blue-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_users'] }}</p>
                    <p class="text-xs text-gray-500">Total Users</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-green-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['admin_users'] }}</p>
                    <p class="text-xs text-gray-500">Admins</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-red-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['banned_users'] }}</p>
                    <p class="text-xs text-gray-500">Banned</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-purple-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_servers'] }}</p>
                    <p class="text-xs text-gray-500">Servers</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-yellow-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ $stats['total_sessions'] }}</p>
                    <p class="text-xs text-gray-500">Sessions</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
            <div class="flex items-center gap-3">
                <div class="w-10 h-10 bg-cyan-500/20 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-cyan-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total_statistics']) }}</p>
                    <p class="text-xs text-gray-500">Data Points</p>
                </div>
            </div>
        </div>
    </div>

    <div class="grid lg:grid-cols-2 gap-6">
        {{-- Recent Users --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl">
            <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Recent Users</h2>
                <a href="{{ route('admin.users') }}" class="text-sm text-green-400 hover:text-green-300">View All</a>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($recentUsers as $user)
                <div class="p-4 flex items-center justify-between">
                    <div class="flex items-center gap-3">
                        <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-10 h-10 rounded-full">
                        <div>
                            <p class="text-white font-medium">{{ $user->name }}</p>
                            <p class="text-xs text-gray-500">{{ $user->steam_id }}</p>
                        </div>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs rounded-full {{ $user->role === 'admin' ? 'bg-green-500/20 text-green-400' : ($user->role === 'moderator' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-700 text-gray-400') }}">
                            {{ ucfirst($user->role ?? 'user') }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $user->created_at->diffForHumans() }}</p>
                    </div>
                </div>
                @empty
                <p class="p-4 text-gray-500 text-center">No users yet</p>
                @endforelse
            </div>
        </div>

        {{-- Servers --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl">
            <div class="p-4 border-b border-gray-700 flex items-center justify-between">
                <h2 class="text-lg font-semibold text-white">Tracked Servers</h2>
                <a href="{{ route('admin.servers') }}" class="text-sm text-green-400 hover:text-green-300">View All</a>
            </div>
            <div class="divide-y divide-gray-700">
                @forelse($servers as $server)
                <div class="p-4 flex items-center justify-between">
                    <div>
                        <p class="text-white font-medium">{{ $server->name }}</p>
                        <p class="text-xs text-gray-500">{{ $server->ip }}:{{ $server->port }}</p>
                    </div>
                    <div class="text-right">
                        <span class="px-2 py-1 text-xs rounded-full {{ $server->status === 'online' ? 'bg-green-500/20 text-green-400' : 'bg-red-500/20 text-red-400' }}">
                            {{ ucfirst($server->status) }}
                        </span>
                        <p class="text-xs text-gray-500 mt-1">{{ $server->players }}/{{ $server->max_players }} players</p>
                    </div>
                </div>
                @empty
                <p class="p-4 text-gray-500 text-center">No servers tracked</p>
                @endforelse
            </div>
        </div>
    </div>
</div>
@endsection
