@extends('admin.layout')

@section('title', 'Weapons Management')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Weapons Management</h1>
        <div class="flex gap-3">
            <form action="{{ route('admin.weapons.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sync from Kills
                </button>
            </form>
            <a href="{{ route('admin.weapons.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Weapon
            </a>
        </div>
    </div>

    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Weapons</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalWeapons) }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">With Images</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($withImages) }}</p>
                </div>
            </div>
        </div>

        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Missing Images</p>
                    <p class="text-2xl font-bold text-yellow-400">{{ number_format($withoutImages) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('admin.weapons.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search weapons..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="has_image" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Weapons</option>
                <option value="yes" {{ request('has_image') === 'yes' ? 'selected' : '' }}>With Image</option>
                <option value="no" {{ request('has_image') === 'no' ? 'selected' : '' }}>Without Image</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'has_image']))
            <a href="{{ route('admin.weapons.index') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Weapons Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($weapons as $weapon)
        <div class="glass-card rounded-xl overflow-hidden hover:border-white/10 transition">
            <div class="aspect-video bg-gray-900 flex items-center justify-center">
                @if($weapon->image_path)
                <img src="{{ Storage::url($weapon->image_path) }}" alt="{{ $weapon->display_name }}" class="w-full h-full object-contain p-4">
                @else
                <div class="text-gray-600">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-white truncate" title="{{ $weapon->name }}">{{ $weapon->display_name ?? $weapon->name }}</h3>
                <p class="text-sm text-gray-500 truncate" title="{{ $weapon->name }}">{{ $weapon->name }}</p>
                @if($weapon->weapon_type || $weapon->category)
                <div class="flex gap-2 mt-2">
                    @if($weapon->weapon_type)
                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded">{{ $weapon->weapon_type }}</span>
                    @endif
                    @if($weapon->category)
                    <span class="px-2 py-0.5 bg-purple-500/20 text-purple-400 text-xs rounded">{{ $weapon->category }}</span>
                    @endif
                </div>
                @endif
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('admin.weapons.edit', $weapon) }}" class="flex-1 px-3 py-1.5 bg-white/5 hover:bg-white/10 text-white text-sm rounded text-center transition">
                        Edit
                    </a>
                    <form action="{{ route('admin.weapons.destroy', $weapon) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this weapon?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm rounded transition">
                            Delete
                        </button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="glass-card rounded-xl p-8 text-center">
                <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                </svg>
                <p class="text-gray-400 mb-4">No weapons found</p>
                <a href="{{ route('admin.weapons.create') }}" class="inline-flex items-center gap-2 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add First Weapon
                </a>
            </div>
        </div>
        @endforelse
    </div>

    {{-- Pagination --}}
    @if($weapons->hasPages())
    <div class="flex justify-center">
        {{ $weapons->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
