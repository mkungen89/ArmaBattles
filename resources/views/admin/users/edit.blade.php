@extends('admin.layout')

@section('title', 'Edit User')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.users') }}" class="p-2 text-gray-400 hover:text-white hover:bg-gray-700 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit User</h1>
    </div>

    <div class="grid lg:grid-cols-3 gap-6">
        {{-- User Info Card --}}
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex flex-col items-center text-center">
                <img src="{{ $user->avatar_display }}" alt="{{ $user->name }}" class="w-24 h-24 rounded-full mb-4">
                <h2 class="text-xl font-bold text-white">{{ $user->name }}</h2>
                <p class="text-sm text-gray-400 mb-4">{{ $user->steam_id }}</p>

                <div class="flex items-center gap-2 mb-4">
                    <span class="px-3 py-1 text-xs rounded-full {{ $user->role === 'admin' ? 'bg-green-500/20 text-green-400' : ($user->role === 'moderator' ? 'bg-blue-500/20 text-blue-400' : 'bg-gray-700 text-gray-400') }}">
                        {{ ucfirst($user->role ?? 'user') }}
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

            <div class="border-t border-gray-700 mt-6 pt-6 space-y-3 text-sm">
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
        <div class="lg:col-span-2 bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <h3 class="text-lg font-semibold text-white mb-6">Edit User Details</h3>

            <form action="{{ route('admin.users.update', $user) }}" method="POST" class="space-y-6">
                @csrf
                @method('PUT')

                <div>
                    <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Display Name</label>
                    <input type="text" name="name" id="name" value="{{ old('name', $user->name) }}" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                    @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="role" class="block text-sm font-medium text-gray-400 mb-2">Role</label>
                    <select name="role" id="role" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="user" {{ old('role', $user->role) === 'user' ? 'selected' : '' }}>User</option>
                        <option value="moderator" {{ old('role', $user->role) === 'moderator' ? 'selected' : '' }}>Moderator</option>
                        <option value="admin" {{ old('role', $user->role) === 'admin' ? 'selected' : '' }}>Admin</option>
                    </select>
                    @error('role')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                </div>

                <div class="flex items-center gap-4 pt-4">
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium">
                        Save Changes
                    </button>
                    <a href="{{ route('admin.users') }}" class="px-6 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                        Cancel
                    </a>
                </div>
            </form>

            {{-- Two-Factor Authentication Section --}}
            <div class="border-t border-gray-700 mt-8 pt-8">
                <h3 class="text-lg font-semibold text-white mb-4">Two-Factor Authentication</h3>

                @if($user->hasTwoFactorEnabled())
                <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4 mb-4">
                    <div class="flex items-center gap-2">
                        <span class="px-2 py-1 text-xs rounded-full bg-yellow-500/20 text-yellow-400">2FA Enabled</span>
                        <span class="text-sm text-gray-400">since {{ $user->two_factor_confirmed_at->format('M j, Y g:i A') }}</span>
                    </div>
                </div>
                <form action="{{ route('admin.users.reset-2fa', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition text-sm"
                            onclick="return confirm('This will disable 2FA for this user. They will need to set it up again. Continue?')">
                        Reset 2FA
                    </button>
                </form>
                @else
                <p class="text-gray-500 text-sm">This user does not have two-factor authentication enabled.</p>
                @endif
            </div>

            {{-- Ban/Unban Section --}}
            <div class="border-t border-gray-700 mt-8 pt-8">
                <h3 class="text-lg font-semibold text-white mb-4">Account Status</h3>

                @if($user->is_banned)
                <div class="bg-red-500/10 border border-red-500/30 rounded-lg p-4 mb-4">
                    <p class="text-red-400">This user is currently banned.</p>
                    @if($user->ban_reason)
                    <p class="text-sm text-gray-400 mt-1">Reason: {{ $user->ban_reason }}</p>
                    @endif
                </div>
                <form action="{{ route('admin.users.unban', $user) }}" method="POST">
                    @csrf
                    <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">
                        Unban User
                    </button>
                </form>
                @else
                @if($user->id !== auth()->id())
                <form action="{{ route('admin.users.ban', $user) }}" method="POST" class="space-y-4">
                    @csrf
                    <div>
                        <label for="reason" class="block text-sm font-medium text-gray-400 mb-2">Ban Reason (optional)</label>
                        <textarea name="reason" id="reason" rows="2" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-red-500 focus:border-red-500" placeholder="Enter reason for ban..."></textarea>
                    </div>
                    <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-lg transition" onclick="return confirm('Are you sure you want to ban this user?')">
                        Ban User
                    </button>
                </form>
                @else
                <p class="text-gray-500">You cannot ban yourself.</p>
                @endif
                @endif
            </div>
        </div>
    </div>
</div>
@endsection
