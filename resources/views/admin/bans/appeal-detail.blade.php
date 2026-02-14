@extends('admin.layout')

@section('admin-title', 'Review Appeal')

@section('admin-content')
<div class="max-w-4xl space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Appeal #{{ $appeal->id }}</h1>
        <span class="px-3 py-1 rounded-lg text-sm font-medium
            {{ $appeal->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : '' }}
            {{ $appeal->status === 'approved' ? 'bg-green-900 text-green-300' : '' }}
            {{ $appeal->status === 'rejected' ? 'bg-red-900 text-red-300' : '' }}">
            {{ ucfirst($appeal->status) }}
        </span>
    </div>

    <!-- User Info -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">User Information</h2>
        <div class="flex items-center gap-4 mb-4">
            <img src="{{ $appeal->user->avatar }}" class="w-16 h-16 rounded-full" alt="">
            <div>
                <h3 class="text-xl font-semibold text-white">{{ $appeal->user->name }}</h3>
                <p class="text-sm text-gray-400">{{ $appeal->user->email }}</p>
                <p class="text-sm text-gray-400">Player UUID: {{ $appeal->user->player_uuid ?? 'Not linked' }}</p>
            </div>
        </div>
        <div class="grid grid-cols-3 gap-4 text-sm">
            <div>
                <p class="text-gray-500">Ban Count:</p>
                <p class="text-white font-semibold">{{ $appeal->user->ban_count }}</p>
            </div>
            <div>
                <p class="text-gray-500">Currently Banned:</p>
                <p class="text-white font-semibold">{{ $appeal->user->is_banned ? 'Yes' : 'No' }}</p>
            </div>
            <div>
                <p class="text-gray-500">Account Created:</p>
                <p class="text-white">{{ $appeal->user->created_at->format('M j, Y') }}</p>
            </div>
        </div>
    </div>

    <!-- Ban Info -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Ban Information</h2>
        <div class="space-y-3 text-sm">
            <div>
                <p class="text-gray-500">Reason:</p>
                <p class="text-white">{{ $appeal->reason }}</p>
            </div>
            <div>
                <p class="text-gray-500">Ban Type:</p>
                <p class="text-white">{{ $appeal->user->banned_until ? 'Temporary' : 'Permanent' }}</p>
            </div>
            @if($appeal->user->banned_until)
                <div>
                    <p class="text-gray-500">Expires:</p>
                    <p class="text-white">{{ $appeal->user->banned_until->format('M j, Y \a\t g:i A') }}</p>
                </div>
            @endif
        </div>
    </div>

    <!-- Appeal Message -->
    <div class="bg-gray-800 rounded-lg p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Appeal Message</h2>
        <p class="text-sm text-gray-400 mb-2">Submitted {{ $appeal->created_at->format('M j, Y \a\t g:i A') }}</p>
        <p class="text-gray-300 whitespace-pre-wrap">{{ $appeal->appeal_message }}</p>
    </div>

    <!-- Review Form (if pending) -->
    @if($appeal->isPending())
        <div class="bg-gray-800 rounded-lg p-6" x-data="{ action: 'approve' }">
            <h2 class="text-lg font-semibold text-white mb-4">Review Appeal</h2>

            <div class="flex gap-4 mb-6">
                <button @click="action = 'approve'" :class="action === 'approve' ? 'bg-green-600' : 'bg-gray-700'"
                        class="flex-1 px-4 py-3 text-white rounded-lg hover:opacity-90 transition">
                    <i data-lucide="check" class="w-5 h-5 inline mr-2"></i>
                    Approve & Unban
                </button>
                <button @click="action = 'reject'" :class="action === 'reject' ? 'bg-red-600' : 'bg-gray-700'"
                        class="flex-1 px-4 py-3 text-white rounded-lg hover:opacity-90 transition">
                    <i data-lucide="x" class="w-5 h-5 inline mr-2"></i>
                    Reject Appeal
                </button>
            </div>

            <!-- Approve Form -->
            <form x-show="action === 'approve'" action="{{ route('admin.bans.appeals.approve', $appeal) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Response to User</label>
                    <textarea name="admin_response" rows="4" required
                              class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                              placeholder="Explain why the appeal was approved..."></textarea>
                </div>
                <button type="submit" class="w-full px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                    Approve Appeal & Unban User
                </button>
            </form>

            <!-- Reject Form -->
            <form x-show="action === 'reject'" action="{{ route('admin.bans.appeals.reject', $appeal) }}" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Response to User</label>
                    <textarea name="admin_response" rows="4" required
                              class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                              placeholder="Explain why the appeal was rejected..."></textarea>
                </div>
                <button type="submit" class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                    Reject Appeal
                </button>
            </form>
        </div>
    @else
        <!-- Already Reviewed -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-white mb-4">Admin Response</h2>
            <p class="text-sm text-gray-400 mb-2">
                Reviewed by {{ $appeal->reviewer->name }} on {{ $appeal->reviewed_at->format('M j, Y') }}
            </p>
            <p class="text-gray-300 whitespace-pre-wrap">{{ $appeal->admin_response }}</p>
        </div>
    @endif

    <a href="{{ route('admin.bans.appeals') }}" class="text-gray-400 hover:text-gray-300">← Back to Appeals</a>
</div>
@endsection
