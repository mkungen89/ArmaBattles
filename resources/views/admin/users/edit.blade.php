@extends('admin.layout')

@section('title', 'Edit User')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users') }}" class="p-2 text-gray-400 hover:text-white hover:bg-white/5 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit User</h1>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- User Info Card --}}
        <div class="glass-card rounded-xl p-6">
            <div class="flex flex-col items-center text-center">
                <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full mb-4">
                <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-sm text-gray-400 mb-4">{{ $user->steam_id }}</p>

                <div class="flex items-center gap-2 mb-4">
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
                    <span class="px-3 py-1 text-xs rounded-full {{ $roleColor }}">
                        {{ $roleLabel }}
                    </span>
                    @if($user->is_banned)
                    <span class="px-3 py-1 text-xs rounded-full bg-red-500/20 text-red-400">Banned</span>
                    @endif
                </div>

                @if($user->profile_url)
                <a href="{{ $user->profile_url }}" target="_blank" class="text-sm text-green-400 hover:text-green-300">
                    View Steam Profile
                </a>
                @endif
            </div>

            <div class="border-t border-white/5 mt-6 pt-6 space-y-3 text-sm">
                <div class="flex justify-between">
                    <span class="text-gray-500">Joined</span>
                    <span class="text-white">{{ $user->created_at->format('M j, Y') }}</span>
                </div>
                <div class="flex justify-between">
                    <span class="text-gray-500">Last Updated</span>
                    <span class="text-white">{{ $user->updated_at->diffForHumans() }}</span>
                </div>
                @if($user->is_banned && $user->banned_at)
                <div class="flex justify-between">
                    <span class="text-gray-500">Banned At</span>
                    <span class="text-red-400">{{ $user->banned_at->format('M j, Y') }}</span>
                </div>
                @if($user->ban_reason)
                <div>
                    <span class="text-gray-500">Ban Reason</span>
                    <p class="text-red-400 mt-1">{{ $user->ban_reason }}</p>
                </div>
                @endif
                @endif
            </div>
        </div>

        {{-- Edit Form --}}
        <div class="lg:col-span-2 glass-card rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-6">Edit User Details</h3>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Display Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-400 mb-2">Role</label>
                    <select name="role" id="role" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="moderator" {{ old('role', $user->role) === 'moderator' ? 'selected' : '' }}>Moderator</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                        <option value="gm" {{ old('role', $user->role) === 'gm' ? 'selected' : '' }}>Game Master</option>
                        <option value="referee" {{ old('role', $user->role) === 'referee' ? 'selected' : '' }}>Referee</option>
                        <option value="observer" {{ old('role', $user->role) === 'observer' ? 'selected' : '' }}>Observer</option>
                        <option value="caster" {{ old('role', $user->role) === 'caster' ? 'selected' : '' }}>Caster</option>
                    </select>
                    @error('role')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="email" class="block text-sm font-medium text-gray-400 mb-2">Email (optional)</label>
                    <input type="email" name="email" id="email" value="{{ old('email', $user->email) }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500" placeholder="user@example.com">
                    @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="player_uuid" class="block text-sm font-medium text-gray-400 mb-2">Player UUID</label>
                    <input type="text" name="player_uuid" id="player_uuid" value="{{ old('player_uuid', $user->player_uuid) }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm" placeholder="Link to game stats">
                    <p class="mt-1 text-xs text-gray-500">Connect this user to their in-game statistics</p>
                    @error('player_uuid')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label for="discord_id" class="block text-sm font-medium text-gray-400 mb-2">Discord ID</label>
                        <input type="text" name="discord_id" id="discord_id" value="{{ old('discord_id', $user->discord_id) }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 font-mono text-sm">
                        @error('discord_id')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                    <div>
                        <label for="discord_username" class="block text-sm font-medium text-gray-400 mb-2">Discord Username</label>
                        <input type="text" name="discord_username" id="discord_username" value="{{ old('discord_username', $user->discord_username) }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        @error('discord_username')
                        <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                        @enderror
                    </div>
                </div>

                <div>
                    <label for="profile_visibility" class="block text-sm font-medium text-gray-400 mb-2">Profile Visibility</label>
                    <select name="profile_visibility" id="profile_visibility" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="public" {{ old('profile_visibility', $user->profile_visibility) === 'public' ? 'selected' : '' }}>Public</option>
                        <option value="private" {{ old('profile_visibility', $user->profile_visibility) === 'private' ? 'selected' : '' }}>Private</option>
                    </select>
                    @error('profile_visibility')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="custom_avatar" class="block text-sm font-medium text-gray-400 mb-2">Custom Avatar URL (optional)</label>
                    <input type="url" name="custom_avatar" id="custom_avatar" value="{{ old('custom_avatar', $user->custom_avatar) }}" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500" placeholder="https://example.com/avatar.jpg">
                    <p class="mt-1 text-xs text-gray-500">Override Steam avatar with a custom image URL</p>
                    @error('custom_avatar')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users') }}" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                        Cancel
                    </a>
                </div>
            </form>

            {{-- Two-Factor Authentication Section --}}
            <div class="border-t border-white/5 mt-8 pt-8">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Two-Factor Authentication</h3>

                @if($user->hasTwoFactorEnabled())
                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">2FA Enabled</span>
                        <span class="text-sm text-gray-400">since {{ $user->two_factor_confirmed_at->format('M j, Y g:i A') }}</span>
                    </div>
                </div>
                <form action="{{ route('admin.users.reset-2fa', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition text-sm"
                            onclick="return confirm('This will disable 2FA for this user. They will need to set it up again. Continue?')">
                        Reset 2FA
                    </button>
                </form>
                @else
                <p class="text-gray-500 text-sm">This user does not have two-factor authentication enabled.</p>
                @endif
            </div>

            {{-- Ban/Unban Section --}}
            <div class="border-t border-white/5 mt-8 pt-8">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Account Status</h3>

                @if($user->is_banned)
                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-4">
                    <p class="text-red-400">This user is currently banned.</p>
                    @if($user->ban_reason)
                    <p class="text-sm text-gray-400 mt-1">Reason: {{ $user->ban_reason }}</p>
                    @endif
                </div>
                <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                        Unban User
                    </button>
                </form>
                @else
                @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-400 mb-2">Ban Reason (optional)</label>
                        <textarea name="reason" id="reason" rows="2" class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-red-500 focus:border-red-500" placeholder="Enter reason for ban..."></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition" onclick="return confirm('Are you sure you want to ban this user?')">
                        Ban User
                    </button>
                </form>
                @else
                <p class="text-gray-500">You cannot ban yourself.</p>
                @endif
                @endif
            </div>

            {{-- User Activity & Quick Links --}}
            <div class="border-t border-white/5 mt-8 pt-8">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">User Activity</h3>
                <div class="grid grid-cols-2 gap-3">
                    @if($user->player_uuid)
                    <a href="{{ route('players.show', $user) }}" class="flex items-center gap-2 px-4 py-3 bg-white/5 hover:bg-white/10 rounded-lg transition text-sm text-gray-300">
                        <svg class="w-4 h-4 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Game Stats
                    </a>
                    @endif

                    @php
                        $userTeams = $user->teams()->count();
                    @endphp
                    @if($userTeams > 0)
                    <a href="{{ route('teams.index') }}?member={{ $user->id }}" class="flex items-center gap-2 px-4 py-3 bg-white/5 hover:bg-white/10 rounded-lg transition text-sm text-gray-300">
                        <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Teams ({{ $userTeams }})
                    </a>
                    @endif

                    @php
                        // Count tournaments through user's teams
                        $tournamentCount = 0;
                        foreach ($user->teams as $team) {
                            $tournamentCount += $team->registrations()->count();
                        }
                    @endphp
                    @if($tournamentCount > 0)
                    <a href="{{ route('tournaments.index') }}" class="flex items-center gap-2 px-4 py-3 bg-white/5 hover:bg-white/10 rounded-lg transition text-sm text-gray-300">
                        <svg class="w-4 h-4 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v13m0-13V6a2 2 0 112 2h-2zm0 0V5.5A2.5 2.5 0 109.5 8H12zm-7 4h14M5 12a2 2 0 110-4h14a2 2 0 110 4M5 12v7a2 2 0 002 2h10a2 2 0 002-2v-7"/>
                        </svg>
                        Tournaments ({{ $tournamentCount }})
                    </a>
                    @endif

                    <a href="{{ route('admin.audit-log') }}?user_id={{ $user->id }}" class="flex items-center gap-2 px-4 py-3 bg-white/5 hover:bg-white/10 rounded-lg transition text-sm text-gray-300">
                        <svg class="w-4 h-4 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Audit Log
                    </a>
                </div>
            </div>

            {{-- Admin Actions --}}
            <div class="border-t border-white/5 mt-8 pt-8">
                <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Admin Actions</h3>
                <div class="flex flex-wrap gap-3">
                    @if($user->id !== auth()->id())
                    <form action="{{ route('admin.users.impersonate', $user) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl transition text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h12m0 0l-4-4m4 4l-4 4m0 6H4m0 0l4 4m-4-4l4-4"/>
                            </svg>
                            Impersonate User
                        </button>
                    </form>

                    <form action="{{ route('admin.users.destroy', $user) }}" method="POST" onsubmit="return confirm('Are you sure you want to permanently delete this user? This action cannot be undone!')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition text-sm flex items-center gap-2">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                            </svg>
                            Delete User
                        </button>
                    </form>
                    @else
                    <p class="text-gray-500 text-sm">You cannot impersonate or delete yourself.</p>
                    @endif
                </div>
            </div>
        </div>
    </div>
</div>
@endsection
