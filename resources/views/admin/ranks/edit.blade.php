@extends('admin.layout')
@section('title', 'Edit Rank: ' . $rank->name)
@section('admin-content')
<div class="mb-6">
    <div class="flex items-center gap-3 mb-2">
        <a href="{{ route('admin.ranks.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 19l-7-7m0 0l7-7m-7 7h18"/>
            </svg>
        </a>
        <h1 class="text-3xl font-bold text-white">Edit Rank: {{ $rank->name }}</h1>
    </div>
    <p class="text-gray-400">Rank {{ $rank->rank }} • Era {{ $rank->era }} • Levels {{ $rank->min_level }}-{{ $rank->max_level }}</p>
</div>

{{-- Errors --}}
@if($errors->any())
    <div class="bg-red-500/20 border border-red-500/50 text-red-400 px-4 py-3 rounded-xl mb-6">
        <ul class="list-disc list-inside">
            @foreach($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
    {{-- Edit Form --}}
    <div class="glass-card p-6">
        <h2 class="text-xl font-bold text-white mb-4">Rank Details</h2>
        <form action="{{ route('admin.ranks.update', $rank) }}" method="POST" enctype="multipart/form-data">
            @csrf
            @method('PUT')

            {{-- Name --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Rank Name</label>
                <input type="text" name="name" value="{{ old('name', $rank->name) }}"
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white focus:border-green-500 focus:ring-1 focus:ring-green-500"
                       required>
                <p class="text-xs text-gray-500 mt-1">The display name for this rank</p>
            </div>

            {{-- Color --}}
            <div class="mb-4">
                <label class="block text-sm font-medium text-gray-300 mb-2">Era Color</label>
                <div class="flex items-center gap-3">
                    <input type="color" id="colorPicker" value="{{ old('color', $rank->color) }}"
                           class="w-16 h-10 rounded-lg border border-white/10 cursor-pointer">
                    <input type="text" id="colorHex" name="color" value="{{ old('color', $rank->color) }}"
                           pattern="^#[0-9A-Fa-f]{6}$"
                           class="flex-1 px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white font-mono text-sm focus:border-green-500"
                           placeholder="#22c55e"
                           oninput="document.getElementById('colorPicker').value = this.value">
                </div>
                <p class="text-xs text-gray-500 mt-1">Hex color code for this rank (affects progress bar and badges)</p>
            </div>

            {{-- Logo Upload --}}
            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Rank Logo</label>
                <input type="file" name="logo" accept="image/*"
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white file:mr-4 file:py-2 file:px-4 file:rounded-lg file:border-0 file:bg-green-600 file:text-white hover:file:bg-green-500">
                <p class="text-xs text-gray-500 mt-1">Upload a PNG, JPG, SVG or WebP image (max 2MB). Recommended size: 256x256px</p>
            </div>

            {{-- Submit --}}
            <div class="flex gap-3">
                <button type="submit"
                        class="flex-1 px-6 py-3 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition">
                    Save Changes
                </button>
                <a href="{{ route('admin.ranks.index') }}"
                   class="px-6 py-3 bg-gray-700 hover:bg-gray-600 text-white font-semibold rounded-xl transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>

    {{-- Preview --}}
    <div class="glass-card p-6">
        <h2 class="text-xl font-bold text-white mb-4">Current Rank Preview</h2>

        {{-- Current Logo --}}
        <div class="bg-white/3 border border-white/10 rounded-xl p-6 mb-4">
            <div class="flex items-center justify-center mb-4">
                @if($rank->logo_url)
                    <img src="{{ $rank->logo_url }}" alt="{{ $rank->name }}"
                         class="w-32 h-32 object-contain"
                         style="filter: drop-shadow(0 0 10px {{ $rank->color }}80);">
                @else
                    <div class="w-32 h-32 rounded-full flex items-center justify-center text-white font-bold text-3xl"
                         style="background: linear-gradient(135deg, {{ $rank->color }}, {{ $rank->color }}80); box-shadow: 0 0 20px {{ $rank->color }}60;">
                        {{ $rank->rank }}
                    </div>
                @endif
            </div>
            <div class="text-center">
                <h3 class="text-2xl font-bold text-white mb-1" style="color: {{ $rank->color }};">{{ $rank->name }}</h3>
                <p class="text-sm text-gray-400">Rank {{ $rank->rank }} of 50 • Era {{ $rank->era }}</p>
                <p class="text-xs text-gray-500 mt-1">Levels {{ $rank->min_level }}-{{ $rank->max_level }}</p>
            </div>
        </div>

        {{-- Color Swatch --}}
        <div class="bg-white/3 border border-white/10 rounded-xl p-4">
            <p class="text-xs text-gray-400 mb-2">Era Color</p>
            <div class="flex items-center gap-3">
                <div class="w-12 h-12 rounded-lg" style="background: {{ $rank->color }}; box-shadow: 0 0 15px {{ $rank->color }}60;"></div>
                <div>
                    <p class="text-white font-bold">{{ $rank->color }}</p>
                    <p class="text-xs text-gray-500">Currently assigned color</p>
                </div>
            </div>
        </div>

        @if($rank->logo_url)
            <form action="{{ route('admin.ranks.delete-logo', $rank) }}" method="POST" class="mt-4"
                  onsubmit="return confirm('Are you sure you want to delete this logo? The rank will show a default badge instead.')">
                @csrf
                @method('DELETE')
                <button type="submit"
                        class="w-full px-4 py-2 bg-red-600 hover:bg-red-500 text-white font-medium rounded-xl transition">
                    Delete Current Logo
                </button>
            </form>
        @endif
    </div>
</div>

<script>
    // Sync color inputs
    const colorPicker = document.getElementById('colorPicker');
    const colorHex = document.getElementById('colorHex');

    if (colorPicker && colorHex) {
        colorPicker.addEventListener('input', function() {
            colorHex.value = this.value;
        });
    }
</script>

@endsection
