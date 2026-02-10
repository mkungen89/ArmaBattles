@extends('layouts.app')

@section('title', 'News')

@section('content')
<div>

    {{-- Page Header --}}
    <div class="flex items-center justify-between mb-8">
        <h1 class="text-2xl font-bold text-white">News Hub</h1>
        @auth
            @if(auth()->user()->isGM())
            <a href="{{ route('admin.news.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium text-sm flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
                Write Article
            </a>
            @endif
        @endauth
    </div>

    <div>

    {{-- Unified news feed --}}
    <div class="space-y-3">

        @forelse($articles as $article)
            <a href="{{ route('news.show', $article) }}" class="group flex gap-0 rounded-xl overflow-hidden {{ $article->is_pinned ? 'glass-card border border-yellow-500/30 hover:border-yellow-500/50' : 'glass-card hover:border-white/10' }} hover:bg-white/5 transition-all">
                {{-- Image --}}
                <div class="relative w-72 md:w-96 h-36 flex-shrink-0 overflow-hidden">
                    @if($article->featured_image_url)
                        <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-[1.03] transition-transform duration-500">
                    @else
                        <div class="w-full h-full bg-gradient-to-br {{ $article->isOfficial() ? 'from-blue-900/30' : 'from-green-900/20' }} to-gray-800 flex items-center justify-center">
                            <svg class="w-10 h-10 {{ $article->isOfficial() ? 'text-blue-500/20' : 'text-green-500/15' }}" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                        </div>
                    @endif
                    @if($article->is_pinned)
                        <span class="absolute top-2 left-2 px-1.5 py-0.5 bg-yellow-500 text-yellow-950 rounded-sm text-[9px] font-bold uppercase">Pinned</span>
                    @endif
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0 p-5 flex flex-col justify-center">
                    <div class="flex items-center gap-2 mb-2">
                        @if($article->isOfficial())
                            <span class="px-1.5 py-0.5 bg-blue-500/20 text-blue-400 rounded-sm text-[10px] font-bold uppercase">Official</span>
                            @if($article->category)
                                <span class="px-1.5 py-0.5 bg-white/5 text-gray-300 rounded-sm text-[10px] font-medium uppercase">{{ $article->category }}</span>
                            @endif
                        @else
                            <span class="px-1.5 py-0.5 bg-green-500/15 text-green-400 rounded-sm text-[10px] font-bold uppercase">Community</span>
                        @endif
                    </div>
                    <span class="text-xs text-gray-500 mb-1.5">{{ $article->published_at->format('j M Y') }}</span>
                    <h3 class="text-lg font-semibold text-white mb-2 line-clamp-2 {{ $article->isOfficial() ? 'group-hover:text-blue-400' : 'group-hover:text-green-400' }} transition-colors">{{ $article->title }}</h3>
                    <p class="text-gray-400 text-sm line-clamp-2">
                        {{ $article->excerpt ?: Str::limit(strip_tags($article->content ?? ''), 160) }}
                    </p>
                    <div class="mt-3 flex items-center gap-4">
                        @if($article->author)
                            <div class="flex items-center gap-2">
                                <img src="{{ $article->author->avatar_display }}" alt="" class="w-5 h-5 rounded-full">
                                <span class="text-xs text-gray-400">{{ $article->author->name }}</span>
                            </div>
                        @elseif($article->isOfficial())
                            <span class="text-xs text-gray-500">reforger.armaplatform.com</span>
                        @endif
                        <div class="flex items-center gap-3 text-xs text-gray-500">
                            <span class="flex items-center gap-1">
                                <svg class="w-3 h-3" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                {{ $article->comments_count }}
                            </span>
                            <span class="flex items-center gap-1 {{ $article->isOfficial() ? 'text-blue-400' : 'text-green-400' }}">
                                <svg class="w-3 h-3" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                {{ $article->hoorahs_count }}
                            </span>
                        </div>
                    </div>
                </div>
            </a>
        @empty
            <div class="bg-white/3 rounded-lg p-12 text-center">
                <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                </svg>
                <p class="text-gray-400">No news articles yet.</p>
            </div>
        @endforelse
    </div>

    @if($articles->hasPages())
    <div class="mt-8">
        {{ $articles->links() }}
    </div>
    @endif

    </div>

</div>
@endsection
