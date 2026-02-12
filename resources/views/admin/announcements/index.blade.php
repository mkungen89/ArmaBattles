@extends('admin.layout')

@section('admin-title', 'Announcements')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Site Announcements</h1>
        <a href="{{ route('admin.announcements.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition text-sm font-medium">
            Create Announcement
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Active</p>
                    <p class="text-2xl font-bold text-green-400">{{ $announcements->where('is_active', true)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-gray-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 14l2-2m0 0l2-2m-2 2l-2-2m2 2l2 2m7-2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Inactive</p>
                    <p class="text-2xl font-bold text-gray-400">{{ $announcements->where('is_active', false)->count() }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total</p>
                    <p class="text-2xl font-bold text-blue-400">{{ $announcements->total() }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Message</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Schedule</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Created</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($announcements as $announcement)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3">
                        @if($announcement->title)
                        <p class="text-xs font-medium text-white mb-1">{{ $announcement->title }}</p>
                        @endif
                        <p class="text-sm text-gray-400">{{ Str::limit($announcement->message, 100) }}</p>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 rounded-full text-xs font-medium {{ match($announcement->type) {
                            'info' => 'bg-blue-500/20 text-blue-400',
                            'warning' => 'bg-yellow-500/20 text-yellow-400',
                            'success' => 'bg-green-500/20 text-green-400',
                            'danger' => 'bg-red-500/20 text-red-400',
                            default => 'bg-blue-500/20 text-blue-400'
                        } }}">
                            {{ ucfirst($announcement->type) }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        @if($announcement->is_active)
                        <span class="px-2 py-1 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Active</span>
                        @else
                        <span class="px-2 py-1 bg-gray-500/20 text-gray-400 rounded-full text-xs font-medium">Inactive</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        @if($announcement->starts_at || $announcement->ends_at)
                            <div class="text-xs">
                                @if($announcement->starts_at)
                                <p>Start: {{ $announcement->starts_at->format('M d, Y H:i') }}</p>
                                @endif
                                @if($announcement->ends_at)
                                <p>End: {{ $announcement->ends_at->format('M d, Y H:i') }}</p>
                                @endif
                            </div>
                        @else
                            <span class="text-gray-500">Always</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $announcement->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1">
                            <form action="{{ route('admin.announcements.toggle', $announcement) }}" method="POST" class="inline">
                                @csrf
                                <button type="submit" class="px-2 py-1 text-xs rounded transition {{ $announcement->is_active ? 'bg-gray-600/20 text-gray-400 hover:bg-gray-600/40' : 'bg-green-600/20 text-green-400 hover:bg-green-600/40' }}">
                                    {{ $announcement->is_active ? 'Deactivate' : 'Activate' }}
                                </button>
                            </form>
                            <a href="{{ route('admin.announcements.edit', $announcement) }}" class="px-2 py-1 text-xs bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 rounded transition">Edit</a>
                            <form action="{{ route('admin.announcements.destroy', $announcement) }}" method="POST" class="inline" onsubmit="return confirm('Delete this announcement?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-400">No announcements found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $announcements->links() }}</div>
</div>
@endsection
