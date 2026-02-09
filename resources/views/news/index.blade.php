@extends('layouts.app')

@section('title', 'News')

@section('content')


    @if($articles->isEmpty())
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-12 text-center">
            <svg class="w-12 h-12 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
            </svg>
            <p class="text-gray-400">No news articles yet.</p>
        </div>
    @else
        <div class="space-y-0 divide-y divide-gray-700/50">
            @foreach($articles as $index => $article)
                @if($loop->first && $articles->currentPage() == 1)
                    {{-- Hero card for first article --}}
                    <a href="{{ route('news.show', $article) }}" class="group block pb-8">
                        <div class="relative rounded-xl overflow-hidden">
                            <div class="relative h-72 md:h-96 overflow-hidden rounded-xl">
                                @if($article->featured_image_url)
                                    <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-[1.02] transition-transform duration-500">
                                @else
                                    <div class="w-full h-full bg-gradient-to-br from-green-600/30 via-gray-800 to-emerald-600/20"></div>
                                @endif
                                <div class="absolute inset-0 bg-gradient-to-t from-gray-900 via-gray-900/50 to-transparent"></div>

                                <div class="absolute bottom-0 left-0 right-0 p-6 md:p-8">
                                    <div class="flex items-center gap-2 mb-3">
                                        @if($article->is_pinned)
                                            <span class="px-2 py-0.5 bg-yellow-500/90 text-yellow-900 rounded text-[10px] font-bold uppercase">Pinned</span>
                                        @endif
                                        <span class="text-xs text-gray-400">{{ $article->published_at->format('j M, Y') }}</span>
                                    </div>
                                    <h2 class="text-2xl md:text-3xl font-bold text-white mb-2 group-hover:text-green-400 transition-colors">{{ $article->title }}</h2>
                                    <p class="text-gray-300 text-sm md:text-base line-clamp-2 max-w-2xl">
                                        {{ $article->excerpt ?: Str::limit(strip_tags($article->content), 200) }}
                                    </p>
                                    <div class="flex items-center gap-4 mt-4">
                                        <div class="flex items-center gap-2">
                                            <img src="{{ $article->author->avatar_display }}" alt="" class="w-6 h-6 rounded-full">
                                            <span class="text-sm text-gray-300">{{ $article->author->name }}</span>
                                        </div>
                                        <div class="flex items-center gap-3 text-xs text-gray-400">
                                            <span class="flex items-center gap-1">
                                                <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                                {{ $article->comments_count }}
                                            </span>
                                            <span class="flex items-center gap-1 text-green-400">
                                                <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                                {{ $article->hoorahs_count }}
                                            </span>
                                        </div>
                                    </div>
                                </div>
                            </div>
                        </div>
                    </a>
                @else
                    {{-- Feed cards --}}
                    <a href="{{ route('news.show', $article) }}" class="group flex gap-5 py-5 hover:bg-gray-800/30 -mx-4 px-4 rounded-lg transition-colors">
                        {{-- Thumbnail --}}
                        <div class="relative w-48 md:w-64 h-28 md:h-36 flex-shrink-0 rounded-lg overflow-hidden">
                            @if($article->featured_image_url)
                                <img src="{{ $article->featured_image_url }}" alt="{{ $article->title }}" class="w-full h-full object-cover group-hover:scale-105 transition-transform duration-300">
                            @else
                                <div class="w-full h-full bg-gradient-to-br from-green-500/15 to-gray-800 flex items-center justify-center">
                                    <svg class="w-8 h-8 text-green-500/20" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                                    </svg>
                                </div>
                            @endif
                            @if($article->is_pinned)
                                <span class="absolute top-2 left-2 px-1.5 py-0.5 bg-yellow-500/90 text-yellow-900 rounded text-[9px] font-bold uppercase">Pinned</span>
                            @endif
                        </div>

                        {{-- Content --}}
                        <div class="flex-1 min-w-0 flex flex-col justify-between py-0.5">
                            <div>
                                <div class="flex items-center gap-2 mb-1.5">
                                    <span class="text-xs text-gray-500">{{ $article->published_at->format('j M, Y') }}</span>
                                </div>
                                <h3 class="text-base md:text-lg font-semibold text-white mb-1.5 line-clamp-2 group-hover:text-green-400 transition-colors">{{ $article->title }}</h3>
                                <p class="text-gray-400 text-sm line-clamp-2 hidden sm:block">
                                    {{ $article->excerpt ?: Str::limit(strip_tags($article->content), 150) }}
                                </p>
                            </div>

                            <div class="flex items-center gap-4 mt-2">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $article->author->avatar_display }}" alt="" class="w-5 h-5 rounded-full">
                                    <span class="text-xs text-gray-400">{{ $article->author->name }}</span>
                                </div>
                                <div class="flex items-center gap-3 text-xs text-gray-500">
                                    <span class="flex items-center gap-1">
                                        <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 12h.01M12 12h.01M16 12h.01M21 12c0 4.418-4.03 8-9 8a9.863 9.863 0 01-4.255-.949L3 20l1.395-3.72C3.512 15.042 3 13.574 3 12c0-4.418 4.03-8 9-8s9 3.582 9 8z"/></svg>
                                        {{ $article->comments_count }}
                                    </span>
                                    <span class="flex items-center gap-1 text-green-400">
                                        <svg class="w-3.5 h-3.5" fill="currentColor" viewBox="0 0 24 24"><path d="M1 21h4V9H1v12zm22-11c0-1.1-.9-2-2-2h-6.31l.95-4.57.03-.32c0-.41-.17-.79-.44-1.06L14.17 1 7.59 7.59C7.22 7.95 7 8.45 7 9v10c0 1.1.9 2 2 2h9c.83 0 1.54-.5 1.84-1.22l3.02-7.05c.09-.23.14-.47.14-.73v-2z"/></svg>
                                        {{ $article->hoorahs_count }}
                                    </span>
                                </div>
                            </div>
                        </div>
                    </a>
                @endif
            @endforeach
        </div>

        <div class="mt-8">
            {{ $articles->links() }}
        </div>
    @endif
@endsection
