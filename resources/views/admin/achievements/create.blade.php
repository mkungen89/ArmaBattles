@extends('admin.layout')

@section('admin-title', 'Create Achievement')

@section('admin-content')
<div class="space-y-6 max-w-2xl">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.achievements.index') }}" class="p-2 text-gray-400 hover:text-white bg-white/3 rounded-lg transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Create Achievement</h1>
    </div>

    <form action="{{ route('admin.achievements.store') }}" method="POST" enctype="multipart/form-data" class="glass-card rounded-xl p-6 space-y-5">
        @csrf

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Name *</label>
                <input type="text" name="name" value="{{ old('name') }}" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('name') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Slug</label>
                <input type="text" name="slug" value="{{ old('slug') }}" placeholder="Auto-generated from name" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('slug') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Description *</label>
            <textarea name="description" rows="3" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">{{ old('description') }}</textarea>
            @error('description') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Icon (emoji)</label>
                <input type="text" name="icon" value="{{ old('icon') }}" placeholder="e.g. ?" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Color</label>
                <input type="text" name="color" value="{{ old('color') }}" placeholder="e.g. from-green-500 to-emerald-500" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-2 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Category *</label>
                <input type="text" name="category" value="{{ old('category') }}" list="category-list" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <datalist id="category-list">
                    @foreach($categories as $cat)
                        <option value="{{ $cat }}">
                    @endforeach
                </datalist>
                @error('category') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Stat Field</label>
                <input type="text" name="stat_field" value="{{ old('stat_field') }}" placeholder="e.g. kills, headshots" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div class="grid grid-cols-1 md:grid-cols-3 gap-5">
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Threshold *</label>
                <input type="number" name="threshold" value="{{ old('threshold', 1) }}" min="1" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('threshold') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Points *</label>
                <input type="number" name="points" value="{{ old('points', 10) }}" min="0" required class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                @error('points') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
            </div>
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-1">Sort Order</label>
                <input type="number" name="sort_order" value="{{ old('sort_order', 0) }}" min="0" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            </div>
        </div>

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Badge SVG URL</label>
            <input type="url" name="badge_svg_url" value="{{ old('badge_svg_url') }}" placeholder="https://example.com/badge.svg" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
            <p class="text-xs text-gray-500 mt-1">Highest priority - external SVG link</p>
            @error('badge_svg_url') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        @if(count($presetBadges) > 0)
        <div>
            <label class="block text-sm font-medium text-gray-300 mb-2">Preset Badges</label>
            <div class="grid grid-cols-4 sm:grid-cols-6 md:grid-cols-8 gap-3">
                @foreach($presetBadges as $badge)
                <label class="relative cursor-pointer group">
                    <input type="radio" name="preset_badge" value="{{ $badge }}" {{ old('preset_badge') === $badge ? 'checked' : '' }} class="peer sr-only">
                    <div class="w-full aspect-square bg-white/3 border-2 border-white/10 peer-checked:border-green-500 rounded-lg p-2 hover:border-white/30 transition flex items-center justify-center overflow-hidden">
                        <img src="{{ asset('images/Achivements/' . $badge) }}" alt="{{ $badge }}" class="max-w-full max-h-full object-contain">
                    </div>
                    <p class="text-xs text-gray-500 mt-1 truncate text-center">{{ pathinfo($badge, PATHINFO_FILENAME) }}</p>
                </label>
                @endforeach
                <label class="relative cursor-pointer group">
                    <input type="radio" name="preset_badge" value="" {{ old('preset_badge') === '' || !old('preset_badge') ? 'checked' : '' }} class="peer sr-only">
                    <div class="w-full aspect-square bg-white/3 border-2 border-white/10 peer-checked:border-green-500 rounded-lg p-2 hover:border-white/30 transition flex items-center justify-center">
                        <svg class="w-8 h-8 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </div>
                    <p class="text-xs text-gray-500 mt-1 text-center">None</p>
                </label>
            </div>
            <p class="text-xs text-gray-500 mt-2">Select a pre-uploaded badge (used if SVG URL is empty)</p>
            @error('preset_badge') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>
        @endif

        <div>
            <label class="block text-sm font-medium text-gray-300 mb-1">Badge Image Upload</label>
            <input type="file" name="badge" accept="image/*" class="w-full px-3 py-2 bg-white/3 border border-white/10 rounded-lg text-white text-sm file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:bg-green-600 file:text-white file:text-sm file:cursor-pointer">
            <p class="text-xs text-gray-500 mt-1">Lowest priority - upload custom image (used if SVG URL and preset are empty)</p>
            @error('badge') <p class="text-red-400 text-xs mt-1">{{ $message }}</p> @enderror
        </div>

        <div class="flex items-center gap-3 pt-3 border-t border-white/5">
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">Create Achievement</button>
            <a href="{{ route('admin.achievements.index') }}" class="px-6 py-2 bg-white/5 hover:bg-white/10 text-white rounded-lg transition">Cancel</a>
        </div>
    </form>
</div>
@endsection
