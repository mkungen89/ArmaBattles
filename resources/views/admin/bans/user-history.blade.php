@extends('admin.layout')

@section('admin-title', 'Ban History - ' . $user->name)

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Ban History</h1>
            <p class="text-gray-400">{{ $user->name }}</p>
        </div>
        <a href="{{ route('admin.bans.users') }}" class="text-gray-400 hover:text-white">
            &larr; Back to Banned Users
        </a>
    </div>

    <!-- User Info Card -->
    <div class="bg-gray-800 rounded-lg p-6">
        <div class="flex items-center gap-4">
            <img src="{{ $user->avatar }}" class="w-16 h-16 rounded-full" alt="">
            <div>
                <h3 class="text-xl text-white font-semibold">{{ $user->name }}</h3>
                <p class="text-sm text-gray-400">Steam ID: {{ $user->steam_id }}</p>
                <p class="text-sm text-gray-400">Total Bans: {{ $user->ban_count }}</p>
                @if($user->is_banned)
                    <span class="inline-block mt-2 px-2 py-1 bg-red-900 text-red-300 rounded text-xs font-medium">Currently Banned</span>
                @endif
            </div>
        </div>
    </div>

    <!-- Ban History -->
    @if($history->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <p class="text-gray-400">No ban history</p>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-400 bg-gray-900">
                        <th class="p-4">Action</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Reason</th>
                        <th class="p-4">Admin</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Duration</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($history as $entry)
                        <tr class="border-t border-gray-700">
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $entry->action === 'banned' ? 'bg-red-900 text-red-300' : '' }}
                                    {{ $entry->action === 'unbanned' ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $entry->action === 'temp_ban_expired' ? 'bg-blue-900 text-blue-300' : '' }}">
                                    {{ $entry->actionLabel }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-300">{{ $entry->banTypeLabel }}</td>
                            <td class="p-4 text-gray-300 max-w-md truncate">{{ $entry->reason ?: 'No reason provided' }}</td>
                            <td class="p-4 text-gray-300">{{ $entry->admin?->name ?: 'System' }}</td>
                            <td class="p-4 text-gray-400 text-sm">{{ $entry->created_at->format('M j, Y g:i A') }}</td>
                            <td class="p-4 text-gray-400 text-sm">
                                @if($entry->banned_until)
                                    Until {{ $entry->banned_until->format('M j, Y') }}
                                @else
                                    —
                                @endif
                            </td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $history->links() }}
    @endif
</div>
@endsection
