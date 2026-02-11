@extends('admin.layout')

@section('title', 'Manage Users')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Users</h1>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.users') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by name or Steam ID..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="role" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Roles</option>
                <option value="user" {{ request('role') === 'user' ? 'selected' : '' }}>User</option>
                <option value="moderator" {{ request('role') === 'moderator' ? 'selected' : '' }}>Moderator</option>
                <option value="admin" {{ request('role') === 'admin' ? 'selected' : '' }}>Admin</option>
                <option value="gm" {{ request('role') === 'gm' ? 'selected' : '' }}>Game Master</option>
                <option value="referee" {{ request('role') === 'referee' ? 'selected' : '' }}>Referee</option>
                <option value="observer" {{ request('role') === 'observer' ? 'selected' : '' }}>Observer</option>
                <option value="caster" {{ request('role') === 'caster' ? 'selected' : '' }}>Caster</option>
            </select>
            <select name="banned" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Status</option>
                <option value="no" {{ request('banned') === 'no' ? 'selected' : '' }}>Active</option>
                <option value="yes" {{ request('banned') === 'yes' ? 'selected' : '' }}>Banned</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'role', 'banned']))
            <a href="{{ route('admin.users') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Users Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">User</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Steam ID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player UUID</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Role</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Joined</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($users as $user)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-8 h-8 rounded-full">
                            <span class="text-white font-medium">{{ $user->name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3">
                        <code class="text-sm text-gray-400">{{ $user->steam_id }}</code>
                    </td>
                    <td class="px-4 py-3">
                        @if($user->player_uuid)
                            <code class="text-xs text-gray-500">{{ Str::limit($user->player_uuid, 16) }}</code>
                        @else
                            <span class="text-xs text-gray-600">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        @php
                            $roleColors = [
                                'admin' => 'bg-red-500/20 text-red-400',
                                'moderator' => 'bg-yellow-500/20 text-yellow-400',
                                'gm' => 'bg-purple-500/20 text-purple-400',
                                'referee' => 'bg-blue-500/20 text-blue-400',
                                'observer' => 'bg-cyan-500/20 text-cyan-400',
                                'caster' => 'bg-pink-500/20 text-pink-400',
                                'user' => 'bg-white/5 text-gray-400',
                            ];
                            $roleLabels = [
                                'gm' => 'Game Master',
                                'referee' => 'Referee',
                                'observer' => 'Observer',
                                'caster' => 'Caster',
                            ];
                            $roleColor = $roleColors[$user->role] ?? $roleColors['user'];
                            $roleLabel = $roleLabels[$user->role] ?? ucfirst($user->role ?? 'user');
                        @endphp
                        <span class="px-2 py-1 text-xs rounded-full {{ $roleColor }}">
                            {{ $roleLabel }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex flex-wrap gap-1">
                            @if($user->is_banned)
                            <span class="px-2 py-1 text-xs rounded-full bg-red-500/20 text-red-400">Banned</span>
                            @else
                            <span class="px-2 py-1 text-xs rounded-full bg-green-500/20 text-green-400">Active</span>
                            @endif
                            @if($user->hasTwoFactorEnabled())
                            <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400" title="Two-factor authentication enabled">2FA</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        {{ $user->created_at->format('M j, Y') }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-2">
                            <a href="{{ route('admin.users.edit', $user) }}" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition" title="Edit">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                                </svg>
                            </a>
                            @if($user->hasTwoFactorEnabled())
                            <form action="{{ route('admin.users.reset-2fa', $user) }}" method="POST" class="inline" onsubmit="return confirm('Reset 2FA for this user? They will need to set it up again.')">
                                @csrf
                                <button type="submit" class="p-2 text-gray-400 hover:text-yellow-400 hover:bg-yellow-500/20 rounded-lg transition" title="Reset 2FA">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                            @if(!$user->is_banned && $user->id !== auth()->id())
                            <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="inline" onsubmit="return confirm('Ban this user?')">
                                @csrf
                                <button type="submit" class="p-2 text-gray-400 hover:text-red-400 hover:bg-red-500/20 rounded-lg transition" title="Ban">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                                    </svg>
                                </button>
                            </form>
                            @elseif($user->is_banned)
                            <form action="{{ route('admin.users.unban', $user) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="p-2 text-gray-400 hover:text-green-400 hover:bg-green-500/20 rounded-lg transition" title="Unban">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                                    </svg>
                                </button>
                            </form>
                            @endif
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-500">No users found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($users->hasPages())
    <div class="flex justify-center">
        {{ $users->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
