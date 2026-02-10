@extends('admin.layout')

@section('admin-title', 'Edit Achievement')

@section('admin-content')
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.achievements.index') }}" class="p-2 text-gray-400 hover:text-white bg-white/3 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit Achievement</h1>
    </div>

    {{-- Achievement Stats --}}
    <div class="glass-card rounded-xl p-4 flex items-center gap-6">
        <div class="flex-shrink-0 w-14 h-14 rounded-lg flex items-center justify-center" style="background-color: {{ $achievement->color ?? '#6b7280' }}20;">
            @if($achievement->badge_url)
                <img src="{{ $achievement->badge_url }}" alt="" class="w-12 h-12 rounded">
            @elseif($achievement->icon)
                <i data-lucide="{{ $achievement->icon }}" class="w-7 h-7" style="color: {{ $achievement->color ?? '#9ca3af' }};"></i>
            @else
                <span class="text-3xl">?</span>
            @endif
        </div>
        <div>
            <h2 class="text-lg font-bold text-white">{{ $achievement->name }}</h2>
            <p class="text-sm text-gray-400">{{ $achievement->players_count }} players unlocked &middot; {{ $achievement->rarity_label }}</p>
        </div>
    </div>

    <form action="{{ route('admin.achievements.update', $achievement) }}" method="POST" enctype="multipart/form-data" class="glass-card rounded-xl p-6 space-y-5">
        @csrf
        @method('PUT')

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name', $achievement->name) }}" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug', $achievement->slug) }}" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('slug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Description *</label>
            <textarea name="description" rows="3" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">{{ old('description', $achievement->description) }}</textarea>
            @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Icon (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon', $achievement->icon) }}" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Color</label>
                <input type="text" name="color" value="{{ old('color', $achievement->color) }}" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Category *</label>
                <input type="text" name="category" value="{{ old('category', $achievement->category) }}" list="category-list" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <datalist id="category-list">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
                @error('category') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Stat Field</label>
                <input type="text" name="stat_field" value="{{ old('stat_field', $achievement->stat_field) }}" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Threshold *</label>
                <input type="number" name="threshold" value="{{ old('threshold', $achievement->threshold) }}" min="1" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('threshold') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Points *</label>
                <input type="number" name="points" value="{{ old('points', $achievement->points) }}" min="0" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('points') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', $achievement->sort_order) }}" min="0" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Badge Image</label>
            @if($achievement->badge_path)
                <div class="flex items-center gap-3 mb-2">
                    <img src="{{ $achievement->badge_url }}" alt="" class="w-16 h-16 rounded-lg border border-white/10">
                    <form action="{{ route('admin.achievements.delete-badge', $achievement) }}" method="POST" onsubmit="return confirm('Remove badge image?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Remove</button>
                    </form>
                </div>
            @endif
            <input type="file" name="badge" accept="image/*" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:bg-green-600 file:text-white file:text-sm file:cursor-pointer">
            @error('badge') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-3 border-t border-white/5">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">Update Achievement</button>
            <a href="{{ route('admin.achievements.index') }}" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
