@extends('layouts.app')
@section('title', $article->title)
@section('content')
    {{-- Draft banner --}}
    @if($article->status === 'draft')
        <div class="mb-4 p-3 bg-yellow-500/20 border border-yellow-500/30 rounded-lg text-yellow-400 text-sm">
            This article is a draft and is only visible to GMs and admins.
        </div>
    @endif
    {{-- Featured Image Hero --}}
    @if($article->featured_image_url)
        <div class="relative h-64 md:h-80 rounded-xl overflow-hidden mb-8">
            <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover">
            <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/40 to-transparent"></div>
        </div>
    @endif
    {{-- Article Header --}}
    <div class="mb-8">
        <div class="flex items-center gap-2 mb-3">
            @if($article->is_pinned)
                <span class="inline-flex items-center px-2 py-1 bg-yellow-500/20 text-yellow-400 rounded text-xs font-bold uppercase">Pinned</span>
            @endif
            @if($article->isOfficial())
                <span class="inline-flex items-center px-2 py-1 bg-blue-500/20 text-blue-400 rounded text-xs font-bold uppercase">Official</span>
                @if($article->category)
                    <span class="inline-flex items-center px-2 py-1 bg-white/5 text-gray-300 rounded text-xs font-medium uppercase">{{ $article->category }}</span>
                @endif
            @else
                <span class="inline-flex items-center px-2 py-1 bg-green-500/15 text-green-400 rounded text-xs font-bold uppercase">Community</span>
            @endif
        </div>
        <h1 class="text-3xl md:text-4xl font-bold text-white mb-4">{{ $article->title }}</h1>
        <div class="flex items-center gap-4 text-sm text-gray-400">
            @if($article->author)
                <div class="flex items-center gap-2">
                    <img src="{{ $article->author->avatar_display }}" alt="" class="w-8 h-8 rounded-full">
                    <span>{{ $article->author->name }}</span>
                </div>
            @elseif($article->isOfficial())
                <span class="text-blue-400">Arma Platform</span>
            @endif
            <span>{{ $article->published_at?->format('M j, Y') ?? $article->created_at->format('M j, Y') }}</span>
            <span>{{ $article->reading_time }} min read</span>
        </div>
    </div>
    {{-- External link for official articles --}}
    @if($article->isOfficial() && $article->external_url)
        <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/20 rounded-lg flex items-center justify-between">
            <span class="text-sm text-blue-300">This article is sourced from the official Arma Platform.</span>
            <a href="{{ $article->external_url }}" target="_blank" rel="noopener" class="text-sm text-blue-400 hover:text-blue-300 font-medium flex items-center gap-1 transition">
                View original
                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/></svg>
            </a>
        </div>
    @endif
    {{-- Article Content --}}
    <div class="article-content mb-8 glass-card rounded-xl p-6 md:p-8">
        {!! $article->content !!}
    </div>
    <style>
        .article-content { color: #d1d5db; line-height: 1.75; font-size: 1rem; }
        .article-content h2 { color: #f3f4f6; font-size: 1.5rem; font-weight: 700; margin-top: 2rem; margin-bottom: 0.75rem; }
        .article-content h3 { color: #e5e7eb; font-size: 1.25rem; font-weight: 600; margin-top: 1.5rem; margin-bottom: 0.5rem; }
        .article-content h4 { color: #e5e7eb; font-size: 1.1rem; font-weight: 600; margin-top: 1.25rem; margin-bottom: 0.5rem; }
        .article-content p { margin-bottom: 1rem; }
        .article-content ul, .article-content ol { margin-bottom: 1rem; padding-left: 1.5rem; }
        .article-content ul { list-style-type: disc; }
        .article-content ol { list-style-type: decimal; }
        .article-content li { margin-bottom: 0.25rem; }
        .article-content a { color: #4ade80; text-decoration: underline; }
        .article-content a:hover { color: #86efac; }
        .article-content strong { color: #f3f4f6; font-weight: 600; }
        .article-content em { font-style: italic; }
        .article-content blockquote { border-left: 4px solid #4ade80; padding-left: 1rem; margin: 1rem 0; color: #9ca3af; font-style: italic; }
        .article-content img { max-width: 100%; height: auto; border-radius: 0.5rem; margin: 1rem 0; }
        .article-content hr { border-color: #374151; margin: 1.5rem 0; }
        .article-content code { background: #1f2937; padding: 0.15rem 0.4rem; border-radius: 0.25rem; font-size: 0.875rem; color: #f3f4f6; }
        .article-content pre { background: #1f2937; padding: 1rem; border-radius: 0.5rem; overflow-x: auto; margin: 1rem 0; }
        .article-content pre code { background: none; padding: 0; }
        .article-content h2:first-child, .article-content h3:first-child { margin-top: 0; }
    </style>
    {{-- Hoorah Button --}}
    <div class="mb-10 flex items-center gap-4" x-data="{ hoorahed: {{ $hasHoorahed ? 'true' : 'false' }}, count: {{ $article->hoorahs_count }} }">
        @auth
            <button @click="
                fetch('{{ route('news.hoorah', $article) }}', {
                    method: 'POST',
                    headers: {
                        'X-CSRF-TOKEN': '{{ csrf_token() }}',
                        'Accept': 'application/json',
                        'Content-Type': 'application/json'
                    }
                })
                .then(r => r.json())
                .then(data => { hoorahed = data.hoorahed; count = data.count; })
            "
            :class="hoorahed ? 'bg-green-600 hover:bg-green-700 text-white' : 'bg-white/5 hover:bg-white/10 text-gray-300'"
            class="inline-flex items-center gap-2 px-6 py-3 rounded-xl font-semibold transition-all duration-200">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                <span x-text="hoorahed ? 'Hoorahed!' : 'Hoorah!'"></span>
            </button>
        @else
            <a href="{{ route('login') }}" class="inline-flex items-center gap-2 px-6 py-3 bg-white/5 hover:bg-white/10 text-gray-300 rounded-xl font-semibold transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                Hoorah!
            </a>
        @endauth
        <span class="text-gray-400 text-sm" x-text="count + ' Hoorah' + (count !== 1 ? 's' : '')"></span>
    </div>
    {{-- Comments Section --}}
    <div class="border-t border-white/5 pt-8">
        <h2 class="text-xl font-bold text-white mb-6">Comments ({{ $article->comments->count() }})</h2>
        {{-- Comment Form --}}
        @auth
            <form action="{{ route('news.comment', $article) }}" method="POST" class="mb-8">
                @csrf
                <div class="mb-3">
                    <textarea name="body" rows="3" maxlength="2000" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:border-green-500 focus:ring-1 focus:ring-green-500 resize-none"
                        placeholder="Write a comment...">{{ old('body') }}</textarea>
                    @error('body')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition">
                    Post Comment
                </button>
            </form>
        @else
            <div class="mb-8 p-4 glass-card rounded-lg text-center">
                <a href="{{ route('login') }}" class="text-green-400 hover:text-green-300">Log in</a> to leave a comment.
            </div>
        @endauth
        {{-- Comments List --}}
        <div class="space-y-4">
            @forelse($article->comments->sortBy('created_at') as $comment)
                <div class="glass-card rounded-lg p-4">
                    <div class="flex items-center justify-between mb-2">
                        <div class="flex items-center gap-2">
                            <img src="{{ $comment->user->avatar_display }}" alt="" class="w-6 h-6 rounded-full">
                            <span class="text-sm font-medium text-white">{{ $comment->user->name }}</span>
                            <span class="text-xs text-gray-500">{{ $comment->created_at->diffForHumans() }}</span>
                        </div>
                        @if(auth()->check() && (auth()->id() === $comment->user_id || auth()->user()->isAdmin()))
                            <form action="{{ route('news.comment.destroy', $comment) }}" method="POST" onsubmit="return confirm('Delete this comment?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit" class="text-gray-500 hover:text-red-400 transition">
                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/></svg>
                                </button>
                            </form>
                        @endif
                    </div>
                    <p class="text-gray-300 text-sm whitespace-pre-line">{{ $comment->body }}</p>
                </div>
            @empty
                <p class="text-gray-500 text-sm text-center py-4">No comments yet. Be the first to comment!</p>
            @endforelse
        </div>
    </div>
    {{-- Back link --}}
    <div class="mt-8">
        <a href="{{ route('news.index') }}" class="text-gray-400 hover:text-white text-sm transition flex items-center gap-1">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/></svg>
            Back to News
        </a>
    </div>

@endsection
