@extends('admin.layout')

@section('admin-title', 'Videos')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Videos</h1>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Pending</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ number_format($stats['pending']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Approved</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($stats['approved']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Featured</p>
                    <p class="text-2xl font-bold text-blue-400">{{ number_format($stats['featured']) }}</p>
                </div>
            </div>
        </div>
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-purple-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10h4.764a2 2 0 011.789 2.894l-3.5 7A2 2 0 0115.263 21h-4.017c-.163 0-.326-.02-.485-.06L7 20m7-10V5a2 2 0 00-2-2h-.095c-.5 0-.905.405-.905.905a3.61 3.61 0 01-.608 2.006L7 11v9m7-10h-2M7 20H5a2 2 0 01-2-2v-6a2 2 0 012-2h2.5"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Votes</p>
                    <p class="text-2xl font-bold text-purple-400">{{ number_format($stats['total_votes']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.clips.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search by title..." class="flex-1 min-w-[200px] px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:border-green-500">
            <select name="platform" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Platforms</option>
                <option value="twitch" {{ request('platform') === 'twitch' ? 'selected' : '' }}>Twitch</option>
                <option value="youtube" {{ request('platform') === 'youtube' ? 'selected' : '' }}>YouTube</option>
                <option value="tiktok" {{ request('platform') === 'tiktok' ? 'selected' : '' }}>TikTok</option>
                <option value="kick" {{ request('platform') === 'kick' ? 'selected' : '' }}>Kick</option>
            </select>
            <select name="status" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Status</option>
                <option value="pending" {{ request('status') === 'pending' ? 'selected' : '' }}>Pending</option>
                <option value="approved" {{ request('status') === 'approved' ? 'selected' : '' }}>Approved</option>
                <option value="rejected" {{ request('status') === 'rejected' ? 'selected' : '' }}>Rejected</option>
            </select>
            <select name="featured" class="px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Videos</option>
                <option value="yes" {{ request('featured') === 'yes' ? 'selected' : '' }}>Featured</option>
                <option value="no" {{ request('featured') === 'no' ? 'selected' : '' }}>Not Featured</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl text-sm transition">Filter</button>
            @if(request()->hasAny(['search', 'platform', 'status', 'featured']))
                <a href="{{ route('admin.clips.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg text-sm transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Title</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Channel/Creator</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Platform</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Submitted By</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Votes</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Featured</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Date</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($clips as $clip)
                <tr class="hover:bg-white/3">
                    <td class="px-4 py-3">
                        <a href="{{ route('clips.show', $clip) }}" target="_blank" class="text-sm font-medium text-blue-400 hover:text-blue-300">{{ Str::limit($clip->title, 40) }}</a>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        {{ $clip->author ?? '-' }}
                    </td>
                    <td class="px-4 py-3">
                        <span class="inline-flex items-center px-2 py-0.5 rounded-full text-xs font-medium {{ $clip->platform_color }} bg-white/5">
                            {{ $clip->platform_name }}
                        </span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">{{ $clip->user->name ?? 'N/A' }}</td>
                    <td class="px-4 py-3">
                        @if($clip->status === 'approved')
                            <span class="px-2 py-0.5 bg-green-500/20 text-green-400 rounded-full text-xs font-medium">Approved</span>
                        @elseif($clip->status === 'pending')
                            <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 rounded-full text-xs font-medium">Pending</span>
                        @else
                            <span class="px-2 py-0.5 bg-red-500/20 text-red-400 rounded-full text-xs font-medium">Rejected</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-300">{{ number_format($clip->votes) }}</td>
                    <td class="px-4 py-3">
                        @if($clip->is_featured)
                            <span class="px-2 py-0.5 bg-yellow-500/20 text-yellow-400 rounded-full text-xs">Featured</span>
                        @else
                            <span class="text-xs text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">{{ $clip->created_at->format('M d, Y') }}</td>
                    <td class="px-4 py-3 text-right">
                        <div class="flex items-center justify-end gap-1 flex-wrap">
                            {{-- Approval buttons --}}
                            @if($clip->status === 'pending')
                                <form action="{{ route('admin.clips.approve', $clip) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-600/20 text-green-400 hover:bg-green-600/40 rounded transition">Approve</button>
                                </form>
                                <button
                                    type="button"
                                    onclick="showRejectModal({{ $clip->id }}, '{{ addslashes($clip->title) }}')"
                                    class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">
                                    Reject
                                </button>
                            @elseif($clip->status === 'rejected')
                                <form action="{{ route('admin.clips.approve', $clip) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="px-2 py-1 text-xs bg-green-600/20 text-green-400 hover:bg-green-600/40 rounded transition">Approve</button>
                                </form>
                            @endif

                            {{-- Feature/Unfeature --}}
                            @if($clip->status === 'approved')
                                @if($clip->is_featured)
                                    <form action="{{ route('admin.clips.unfeature', $clip) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-xs bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded transition">Unfeature</button>
                                    </form>
                                @else
                                    <form action="{{ route('admin.clips.feature', $clip) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-2 py-1 text-xs bg-yellow-600/20 text-yellow-400 hover:bg-yellow-600/40 rounded transition">Feature</button>
                                    </form>
                                @endif
                            @endif

                            {{-- Delete --}}
                            <form action="{{ route('admin.clips.destroy', $clip) }}" method="POST" class="inline" onsubmit="return confirm('Delete this video?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                            </form>
                        </div>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="9" class="px-4 py-8 text-center text-gray-400">No videos found.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">{{ $clips->withQueryString()->links() }}</div>
</div>

{{-- Reject Modal --}}
<div x-data="{ open: false, clipId: null, clipTitle: '' }"
     x-show="open"
     x-cloak
     @reject-modal.window="open = true; clipId = $event.detail.id; clipTitle = $event.detail.title"
     class="fixed inset-0 z-50 overflow-y-auto"
     style="display: none;">
    <div class="flex items-center justify-center min-h-screen px-4">
        {{-- Backdrop --}}
        <div class="fixed inset-0 bg-black/70 transition-opacity" @click="open = false"></div>

        {{-- Modal --}}
        <div class="relative bg-gray-900 rounded-2xl max-w-lg w-full p-6 shadow-2xl border border-red-500/30">
            <h3 class="text-xl font-bold text-white mb-4">Reject Video</h3>
            <p class="text-sm text-gray-400 mb-4">You are about to reject: <strong class="text-white" x-text="clipTitle"></strong></p>

            <form :action="`{{ route('admin.clips.index') }}/${clipId}/reject`" method="POST">
                @csrf
                <div class="mb-4">
                    <label class="block text-sm font-medium text-gray-300 mb-2">Rejection Reason (Optional)</label>
                    <textarea
                        name="reason"
                        rows="3"
                        placeholder="E.g., Low quality, inappropriate content, spam..."
                        class="w-full bg-gray-800 border border-white/10 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-red-500"
                    ></textarea>
                    <p class="mt-1 text-xs text-gray-500">This reason will be sent to the user in their notification.</p>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-semibold rounded-xl transition">
                        Reject Video
                    </button>
                    <button type="button" @click="open = false" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                        Cancel
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
function showRejectModal(id, title) {
    window.dispatchEvent(new CustomEvent('reject-modal', {
        detail: { id, title }
    }));
}
</script>
@endpush
@endsection
