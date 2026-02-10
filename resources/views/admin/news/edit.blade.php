@extends('admin.layout')

@section('title', 'Edit Article')

@section('admin-content')
<div class="space-y-6">
    <div>
        <h1 class="text-2xl font-bold text-white">Edit Article</h1>
        <p class="text-gray-400 text-sm">{{ $article->title }}</p>
    </div>

    <form action="{{ route('admin.news.update', $article) }}" method="POST" enctype="multipart/form-data">
        @csrf
        @method('PUT')

        <div class="grid lg:grid-cols-3 gap-6">
            {{-- Main Content --}}
            <div class="lg:col-span-2 space-y-4">
                <div>
                    <label for="title" class="block text-sm font-medium text-gray-300 mb-1">Title</label>
                    <input type="text" name="title" id="title" value="{{ old('title', $article->title) }}" required
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500">
                    @error('title')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="content" class="block text-sm font-medium text-gray-300 mb-1">Content</label>
                    <textarea name="content" id="content" rows="20">{{ old('content', $article->content) }}</textarea>
                    @error('content')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>

                <div>
                    <label for="excerpt" class="block text-sm font-medium text-gray-300 mb-1">Excerpt <span class="text-gray-500">(optional)</span></label>
                    <textarea name="excerpt" id="excerpt" rows="2" maxlength="500"
                        class="w-full bg-white/5 border border-white/10 rounded-lg px-4 py-2 text-white focus:border-green-500 focus:ring-1 focus:ring-green-500 resize-none"
                        placeholder="Short summary shown on cards...">{{ old('excerpt', $article->excerpt) }}</textarea>
                    @error('excerpt')
                        <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                    @enderror
                </div>
            </div>

            {{-- Sidebar --}}
            <div class="space-y-4">
                <div class="glass-card rounded-xl p-4 space-y-4">
                    {{-- Current Image --}}
                    @if($article->featured_image_url)
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-1">Current Image</label>
                            <div class="relative">
                                <img src="{{ $article->featured_image_url }}" alt="" class="w-full h-32 object-cover rounded-lg">
                                <form action="{{ route('admin.news.delete-image', $article) }}" method="POST" class="absolute top-2 right-2">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" class="p-1 bg-red-600 hover:bg-red-700 rounded text-white transition" title="Remove image" onclick="return confirm('Remove this image?')">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                    </button>
                                </form>
                            </div>
                        </div>
                    @endif

                    <div>
                        <label for="featured_image" class="block text-sm font-medium text-gray-300 mb-1">{{ $article->featured_image ? 'Replace' : 'Featured' }} Image</label>
                        <p class="text-xs text-gray-500 mb-2">Recommended: 1200 x 630 px (16:9). Max 2 MB.</p>
                        <input type="file" name="featured_image" id="featured_image" accept="image/*"
                            class="w-full bg-white/5 border-white/10 rounded-lg px-3 py-2 text-sm text-white file:mr-3 file:px-3 file:py-1 file:rounded file:border-0 file:bg-green-600 file:text-white file:text-sm file:cursor-pointer">
                        @error('featured_image')
                            <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                        @enderror
                    </div>

                    <div>
                        <label for="status" class="block text-sm font-medium text-gray-300 mb-1">Status</label>
                        <select name="status" id="status" class="w-full bg-white/5 border-white/10 rounded-lg px-3 py-2 text-sm text-white">
                            <option value="draft" {{ old('status', $article->status) === 'draft' ? 'selected' : '' }}>Draft</option>
                            <option value="published" {{ old('status', $article->status) === 'published' ? 'selected' : '' }}>Published</option>
                        </select>
                    </div>

                    <div class="flex items-center gap-2">
                        <input type="checkbox" name="is_pinned" id="is_pinned" value="1" {{ old('is_pinned', $article->is_pinned) ? 'checked' : '' }}
                            class="rounded bg-white/5 border-white/10 text-green-500 focus:ring-green-500">
                        <label for="is_pinned" class="text-sm text-gray-300">Pin article</label>
                    </div>
                </div>

                <div class="flex gap-3">
                    <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-xl text-sm font-medium transition">
                        Update Article
                    </button>
                    <a href="{{ route('admin.news.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">
                        Cancel
                    </a>
                </div>
            </div>
        </div>
    </form>
</div>

@push('scripts')
<script src="https://cdn.jsdelivr.net/npm/tinymce@6/tinymce.min.js"></script>
<script>
    tinymce.init({
        selector: '#content',
        skin_url: 'https://cdn.jsdelivr.net/npm/tinymce@6/skins/ui/oxide-dark',
        content_css: 'https://cdn.jsdelivr.net/npm/tinymce@6/skins/content/dark/content.min.css',
        height: 500,
        menubar: false,
        plugins: 'lists link image code',
        toolbar: 'undo redo | blocks | bold italic underline | link image | bullist numlist | blockquote | code',
        block_formats: 'Paragraph=p; Heading 2=h2; Heading 3=h3; Heading 4=h4',
        content_style: 'body { font-family: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto, sans-serif; font-size: 14px; color: #e5e7eb; background: #1f2937; }',
        branding: false,
        promotion: false,
    });
</script>
@endpush
@endsection
