@extends('admin.layout')

@section('title', 'News Management')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">News</h1>
            <p class="text-gray-400 text-sm">Manage news articles</p>
        </div>
        <a href="{{ route('admin.news.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Create Article
        </a>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.news.index') }}" method="GET" class="flex flex-wrap gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search articles..."
                class="flex-1 min-w-[200px] bg-white/5 border-white/10 rounded-lg px-3 py-2 text-sm text-white placeholder-gray-400">
            <select name="status" class="bg-white/5 border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                <option value="">All Statuses</option>
                <option value="draft" {{ request('status') === 'draft' ? 'selected' : '' }}>Draft</option>
                <option value="published" {{ request('status') === 'published' ? 'selected' : '' }}>Published</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-white/5 hover:bg-white/10 rounded-lg text-sm text-white transition">Filter</button>
        </form>
    </div>

    {{-- Articles Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full text-sm">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Title</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Status</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Author</th>
                    <th class="px-4 py-3 text-left text-gray-400 font-medium">Date</th>
                    <th class="px-4 py-3 text-center text-gray-400 font-medium">Comments</th>
                    <th class="px-4 py-3 text-center text-gray-400 font-medium">Hoorahs</th>
                    <th class="px-4 py-3 text-right text-gray-400 font-medium">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($articles as $article)
                    <tr class="hover:bg-white/3">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-2">
                                @if($article->is_pinned)
                                    <svg class="w-4 h-4 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M16 12V4h1V2H7v2h1v8l-2 2v2h5.2v6h1.6v-6H18v-2l-2-2z"/></svg>
                                @endif
                                <span class="text-white font-medium truncate max-w-xs">{{ $article->title }}</span>
                            </div>
                        </td>
                        <td class="px-4 py-3">
                            <span class="inline-flex px-2 py-0.5 rounded-full text-xs font-medium {{ $article->status_badge }}">
                                {{ ucfirst($article->status) }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $article->author->name }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $article->published_at?->format('M j, Y') ?? $article->created_at->format('M j, Y') }}</td>
                        <td class="px-4 py-3 text-center text-gray-400">{{ $article->comments_count }}</td>
                        <td class="px-4 py-3 text-center text-green-400">{{ $article->hoorahs_count }}</td>
                        <td class="px-4 py-3">
                            <div class="flex items-center justify-end gap-2">
                                <a href="{{ route('news.show', $article) }}" class="text-gray-400 hover:text-white transition" title="View">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M2.458 12C3.732 7.943 7.523 5 12 5c4.478 0 8.268 2.943 9.542 7-1.274 4.057-5.064 7-9.542 7-4.477 0-8.268-2.943-9.542-7z"/></svg>
                                </a>
                                @if(auth()->user()->isAdmin() || $article->author_id === auth()->id())
                                    <a href="{{ route('admin.news.edit', $article) }}" class="text-gray-400 hover:text-blue-400 transition" title="Edit">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/></svg>
                                    </a>
                                @endif
                                <form action="{{ route('admin.news.toggle-pin', $article) }}" method="POST" class="inline">
                                    @csrf
                                    <button type="submit" class="text-gray-400 hover:text-yellow-400 transition" title="{{ $article->is_pinned ? 'Unpin' : 'Pin' }}">
                                        <svg class="w-4 h-4" fill="{{ $article->is_pinned ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 5a2 2 0 012-2h10a2 2 0 012 2v16l-7-3.5L5 21V5z"/></svg>
                                    </button>
                                </form>
                                @if(auth()->user()->isAdmin() || $article->author_id === auth()->id())
                                    <form action="{{ route('admin.news.destroy', $article) }}" method="POST" class="inline" onsubmit="return confirm('Delete this article?')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="text-gray-400 hover:text-red-400 transition" title="Delete">
                                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                        </button>
                                    </form>
                                @endif
                            </div>
                        </td>
                    </tr>
                @empty
                    <tr>
                        <td colspan="7" class="px-4 py-8 text-center text-gray-500">No articles found.</td>
                    </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    <div class="mt-4">
        {{ $articles->withQueryString()->links() }}
    </div>
</div>
@endsection
