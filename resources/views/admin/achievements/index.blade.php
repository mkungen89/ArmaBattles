@extends('admin.layout')

@section('admin-title', 'Achievements')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Achievements</h1>
        <a href="{{ route('admin.achievements.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition flex items-center gap-2">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/></svg>
            Add Achievement
        </a>
    </div>

    {{-- Stats Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4">
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-yellow-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Achievements</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($stats['total']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 7h.01M7 3h5c.512 0 1.024.195 1.414.586l7 7a2 2 0 010 2.828l-7 7a2 2 0 01-2.828 0l-7-7A1.994 1.994 0 013 12V7a4 4 0 014-4z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Categories</p>
                    <p class="text-2xl font-bold text-blue-400">{{ number_format($stats['categories']) }}</p>
                </div>
            </div>
        </div>
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-green-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Unlocks</p>
                    <p class="text-2xl font-bold text-green-400">{{ number_format($stats['total_unlocks']) }}</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Filters --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('admin.achievements.index') }}" method="GET" class="flex flex-wrap items-center gap-3">
            <input type="text" name="search" value="{{ request('search') }}" placeholder="Search achievements..." class="flex-1 min-w-[200px] px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white text-sm placeholder-gray-400 focus:outline-none focus:border-green-500">
            <select name="category" class="px-3 py-2 bg-gray-700/50 border border-gray-600 rounded-lg text-white text-sm focus:outline-none focus:border-green-500">
                <option value="">All Categories</option>
                @foreach($categories as $category)
                    <option value="{{ $category }}" {{ request('category') === $category ? 'selected' : '' }}>{{ ucfirst($category) }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm transition">Filter</button>
            @if(request()->hasAny(['search', 'category']))
                <a href="{{ route('admin.achievements.index') }}" class="px-4 py-2 bg-gray-600 hover:bg-gray-500 text-white rounded-lg text-sm transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Achievement Cards --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
        @forelse($achievements as $achievement)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-5 hover:border-gray-600 transition">
            <div class="flex items-start gap-4">
                <div class="flex-shrink-0 w-12 h-12 rounded-lg flex items-center justify-center" style="background-color: {{ $achievement->color ?? '#6b7280' }}20;">
                    @if($achievement->badge_url)
                        <img src="{{ $achievement->badge_url }}" alt="" class="w-10 h-10 rounded">
                    @elseif($achievement->icon)
                        <i data-lucide="{{ $achievement->icon }}" class="w-6 h-6" style="color: {{ $achievement->color ?? '#9ca3af' }};"></i>
                    @else
                        <span class="text-2xl">?</span>
                    @endif
                </div>
                <div class="flex-1 min-w-0">
                    <h3 class="text-sm font-bold text-white">{{ $achievement->name }}</h3>
                    <p class="text-xs text-gray-400 mt-0.5">{{ Str::limit($achievement->description, 60) }}</p>
                    <div class="flex items-center gap-2 mt-2">
                        <span class="px-1.5 py-0.5 bg-gray-700 text-gray-300 rounded text-[10px]">{{ ucfirst($achievement->category) }}</span>
                        <span class="text-[10px] text-gray-500">{{ $achievement->threshold }} {{ $achievement->stat_field }}</span>
                    </div>
                </div>
            </div>
            <div class="flex items-center justify-between mt-4 pt-3 border-t border-gray-700">
                <div class="flex items-center gap-3 text-xs text-gray-400">
                    <span>{{ $achievement->players_count }} unlocks</span>
                    <span class="px-1.5 py-0.5 bg-gradient-to-r {{ $achievement->rarity_color }} text-white rounded text-[10px]">{{ $achievement->rarity_label }}</span>
                </div>
                <div class="flex items-center gap-2">
                    <a href="{{ route('admin.achievements.edit', $achievement) }}" class="px-2 py-1 text-xs bg-blue-600/20 text-blue-400 hover:bg-blue-600/40 rounded transition">Edit</a>
                    <form action="{{ route('admin.achievements.destroy', $achievement) }}" method="POST" class="inline" onsubmit="return confirm('Delete this achievement? Players will lose progress.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-2 py-1 text-xs bg-red-600/20 text-red-400 hover:bg-red-600/40 rounded transition">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full text-center py-8 text-gray-400">No achievements found.</div>
        @endforelse
    </div>

    <div class="mt-4">{{ $achievements->withQueryString()->links() }}</div>
</div>
@endsection

@push('scripts')
<script>document.addEventListener('DOMContentLoaded', () => { if (window.lucide) lucide.createIcons(); });</script>
@endpush
