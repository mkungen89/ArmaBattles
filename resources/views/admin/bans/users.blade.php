@extends('admin.layout')

@section('admin-title', 'Banned Users')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Banned Users</h1>
        <div class="flex items-center gap-3">
            <select onchange="window.location.href='?type='+this.value" class="bg-gray-700 px-4 py-2 rounded-lg text-white">
                <option value="">All Bans</option>
                <option value="permanent" {{ request('type') === 'permanent' ? 'selected' : '' }}>Permanent</option>
                <option value="temporary" {{ request('type') === 'temporary' ? 'selected' : '' }}>Temporary</option>
                <option value="expired" {{ request('type') === 'expired' ? 'selected' : '' }}>Expired</option>
            </select>
            <form method="GET" class="flex gap-2">
                <input type="text" name="search" value="{{ request('search') }}"
                       placeholder="Search by name, Steam ID, UUID..."
                       class="bg-gray-700 px-4 py-2 rounded-lg text-white">
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg">
                    Search
                </button>
            </form>
        </div>
    </div>

    @if($users->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <p class="text-gray-400">No banned users found</p>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-400 bg-gray-900">
                        <th class="p-4">User</th>
                        <th class="p-4">Ban Type</th>
                        <th class="p-4">Reason</th>
                        <th class="p-4">Banned At</th>
                        <th class="p-4">Expires</th>
                        <th class="p-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($users as $user)
                        <tr class="border-t border-gray-700">
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $user->avatar }}" class="w-8 h-8 rounded-full" alt="">
                                    <div>
                                        <p class="text-white">{{ $user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $user->steam_id }}</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4">
                                @if($user->banned_until)
                                    <span class="px-2 py-1 bg-yellow-900 text-yellow-300 rounded text-xs font-medium">Temporary</span>
                                @else
                                    <span class="px-2 py-1 bg-red-900 text-red-300 rounded text-xs font-medium">Permanent</span>
                                @endif
                            </td>
                            <td class="p-4 text-gray-300 max-w-md truncate">{{ $user->ban_reason ?: 'No reason provided' }}</td>
                            <td class="p-4 text-gray-400 text-sm">{{ $user->banned_at?->format('M j, Y') }}</td>
                            <td class="p-4 text-gray-400 text-sm">
                                @if($user->banned_until)
                                    {{ $user->banned_until->format('M j, Y') }}
                                    @if($user->banned_until->isPast())
                                        <span class="text-red-400">(Expired)</span>
                                    @endif
                                @else
                                    Never
                                @endif
                            </td>
                            <td class="p-4">
                                <div class="flex gap-2">
                                    <a href="{{ route('admin.bans.user-history', $user) }}" class="px-3 py-1 bg-blue-600 hover:bg-blue-700 text-white rounded text-sm">
                                        History
                                    </a>
                                    <form action="{{ route('admin.bans.unban-user', $user) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-700 text-white rounded text-sm">
                                            Unban
                                        </button>
                                    </form>
                                </div>
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $users->links() }}
    @endif
</div>
@endsection
