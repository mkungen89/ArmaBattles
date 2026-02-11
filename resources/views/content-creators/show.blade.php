@extends('layouts.app')
@section('title', $contentCreator->channel_name . ' - Content Creator')
@section('content')
<div class="py-12">
    <div class="max-w-7xl mx-auto px-4 sm:px-6 lg:px-8">
        <div class="bg-gray-800 rounded-xl p-6 mb-6">
            <h1 class="text-3xl font-bold text-white mb-2">{{ $contentCreator->channel_name }}</h1>
            <p class="text-gray-400">{{ $contentCreator->bio }}</p>

            @if($contentCreator->channel_url)
                <a href="{{ $contentCreator->channel_url }}" target="_blank" class="inline-block mt-4 text-green-400 hover:text-green-300">
                    Visit {{ ucfirst($contentCreator->platform) }} Channel
                </a>
            @endif
        </div>

        @if($clips->count() > 0)
            <h2 class="text-2xl font-bold text-white mb-4">Highlight Clips</h2>
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($clips as $clip)
                    <div class="bg-gray-800 rounded-xl p-4">
                        <h3 class="text-white font-semibold">{{ $clip->title }}</h3>
                        <p class="text-gray-400 text-sm">{{ $clip->votes }} votes</p>
                    </div>
                @endforeach
            </div>
        @endif
    </div>
</div>
@endsection
