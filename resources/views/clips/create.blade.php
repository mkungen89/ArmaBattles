@extends('layouts.app')
@section('title', 'Submit Video')
@section('content')
<div class="py-12">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white mb-2">Submit Video</h1>
        <p class="text-gray-400">Share your best moments with the community</p>
    </div>

    <div class="glass-card rounded-xl p-6" x-data="videoSubmit()">
        <form action="{{ route('clips.store') }}" method="POST" class="space-y-6" @submit="handleSubmit">
            @csrf

            {{-- Video URL Input --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">Video URL *</label>
                <div class="relative">
                    <input
                        type="url"
                        name="url"
                        required
                        placeholder="https://youtube.com/watch?v=... or https://clips.twitch.tv/..."
                        value="{{ old('url') }}"
                        x-model="url"
                        @input.debounce.500ms="fetchMetadata()"
                        class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2 pr-10"
                    >
                    {{-- Loading spinner --}}
                    <div x-show="loading" class="absolute right-3 top-1/2 -translate-y-1/2">
                        <svg class="animate-spin h-5 w-5 text-green-400" fill="none" viewBox="0 0 24 24">
                            <circle class="opacity-25" cx="12" cy="12" r="10" stroke="currentColor" stroke-width="4"></circle>
                            <path class="opacity-75" fill="currentColor" d="M4 12a8 8 0 018-8V0C5.373 0 0 5.373 0 12h4zm2 5.291A7.962 7.962 0 014 12H0c0 3.042 1.135 5.824 3 7.938l3-2.647z"></path>
                        </svg>
                    </div>
                </div>
                @error('url')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
                <p class="mt-1 text-xs text-gray-500">Supported: YouTube, Twitch, TikTok, Kick</p>

                {{-- Success message --}}
                <div x-show="fetchSuccess" x-transition class="mt-2 flex items-center gap-2 text-sm text-green-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zm3.707-9.293a1 1 0 00-1.414-1.414L9 10.586 7.707 9.293a1 1 0 00-1.414 1.414l2 2a1 1 0 001.414 0l4-4z" clip-rule="evenodd"/>
                    </svg>
                    <span>Video metadata loaded! You can edit the details below.</span>
                </div>

                {{-- Error message --}}
                <div x-show="fetchError" x-transition class="mt-2 flex items-center gap-2 text-sm text-red-400">
                    <svg class="w-4 h-4" fill="currentColor" viewBox="0 0 20 20">
                        <path fill-rule="evenodd" d="M10 18a8 8 0 100-16 8 8 0 000 16zM8.707 7.293a1 1 0 00-1.414 1.414L8.586 10l-1.293 1.293a1 1 0 101.414 1.414L10 11.414l1.293 1.293a1 1 0 001.414-1.414L11.414 10l1.293-1.293a1 1 0 00-1.414-1.414L10 8.586 8.707 7.293z" clip-rule="evenodd"/>
                    </svg>
                    <span x-text="fetchError"></span>
                </div>
            </div>

            {{-- Hidden Platform Field --}}
            <input type="hidden" name="platform" x-model="platform">

            {{-- Title (auto-filled) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Title <span class="text-gray-500">(auto-filled, editable)</span>
                </label>
                <input
                    type="text"
                    name="title"
                    placeholder="Video title will appear here..."
                    x-model="title"
                    value="{{ old('title') }}"
                    class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2"
                >
                @error('title')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            {{-- Author/Channel (auto-filled) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Channel/Creator <span class="text-gray-500">(auto-filled, editable)</span>
                </label>
                <input
                    type="text"
                    name="author"
                    placeholder="Channel or creator name will appear here..."
                    x-model="author"
                    value="{{ old('author') }}"
                    class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2"
                >
                @error('author')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            {{-- Description (auto-filled) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Description <span class="text-gray-500">(optional, editable)</span>
                </label>
                <textarea
                    name="description"
                    rows="4"
                    placeholder="Video description will appear here, or add your own..."
                    x-model="description"
                    class="w-full bg-gray-900/50 border border-white/5 text-white rounded-xl px-4 py-2"
                >{{ old('description') }}</textarea>
                @error('description')<p class="mt-1 text-sm text-red-400">{{ $message }}</p>@enderror
            </div>

            {{-- Hidden Thumbnail URL --}}
            <input type="hidden" name="thumbnail_url" x-model="thumbnailUrl">

            {{-- Preview (if metadata loaded) --}}
            <div x-show="thumbnailUrl" x-transition class="bg-white/5 rounded-xl p-4 border border-white/10">
                <p class="text-sm font-medium text-gray-300 mb-2">Preview</p>
                <div class="flex gap-4">
                    <img :src="thumbnailUrl" alt="Thumbnail" class="w-32 h-24 object-cover rounded">
                    <div>
                        <p class="text-sm text-white font-medium" x-text="title || 'No title'"></p>
                        <p class="text-xs text-gray-400 mt-1" x-text="platform ? platform.toUpperCase() : ''"></p>
                    </div>
                </div>
            </div>

            {{-- Guidelines --}}
            <div class="bg-blue-500/10 border border-blue-500/30 rounded-xl p-4">
                <p class="text-sm text-blue-300">
                    <strong>Guidelines:</strong> Submit videos of impressive gameplay, funny moments, or epic plays. Low-effort or spam submissions may be removed.
                </p>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-3">
                <button
                    type="submit"
                    class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition disabled:opacity-50 disabled:cursor-not-allowed"
                    :disabled="loading"
                >
                    <span x-show="!loading">Submit Video</span>
                    <span x-show="loading">Fetching metadata...</span>
                </button>
                <a href="{{ route('clips.index') }}" class="px-6 py-3 bg-white/5 hover:bg-white/10 text-white font-semibold rounded-xl transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>

@push('scripts')
<script>
function videoSubmit() {
    return {
        url: '{{ old('url') }}',
        title: '{{ old('title') }}',
        author: '{{ old('author') }}',
        description: '{{ old('description') }}',
        platform: '{{ old('platform') }}',
        thumbnailUrl: '',
        loading: false,
        fetchSuccess: false,
        fetchError: null,

        async fetchMetadata() {
            if (!this.url || this.url.length < 10) {
                return;
            }

            this.loading = true;
            this.fetchSuccess = false;
            this.fetchError = null;

            try {
                const response = await fetch('{{ route('clips.fetch-metadata') }}', {
                    method: 'POST',
                    headers: {
                        'Content-Type': 'application/json',
                        'X-CSRF-TOKEN': document.querySelector('meta[name="csrf-token"]').content,
                        'Accept': 'application/json'
                    },
                    body: JSON.stringify({ url: this.url })
                });

                const data = await response.json();

                if (data.success && data.data) {
                    this.title = data.data.title || this.title;
                    this.author = data.data.author || this.author;
                    this.description = data.data.description || this.description;
                    this.platform = data.data.platform || this.platform;
                    this.thumbnailUrl = data.data.thumbnail_url || '';
                    this.fetchSuccess = true;
                } else {
                    this.fetchError = data.message || 'Could not fetch video metadata';
                }
            } catch (error) {
                console.error('Fetch metadata error:', error);
                this.fetchError = 'Network error. Please check the URL and try again.';
            } finally {
                this.loading = false;
            }
        },

        handleSubmit(e) {
            if (this.loading) {
                e.preventDefault();
                return false;
            }
        }
    }
}
</script>
@endpush
@endsection
