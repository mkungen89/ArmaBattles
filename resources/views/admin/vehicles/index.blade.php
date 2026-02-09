@extends('admin.layout')

@section('title', 'Vehicles Management')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Vehicles Management</h1>
        <div class="flex gap-3">
            <form action="{{ route('admin.vehicles.sync') }}" method="POST" class="inline">
                @csrf
                <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition flex items-center gap-2">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                    </svg>
                    Sync from Distance Data
                </button>
            </form>
            <a href="{{ route('admin.vehicles.create') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Vehicle
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
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex items-center gap-4">
                <div class="p-3 bg-blue-500/20 rounded-lg">
                    <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div>
                    <p class="text-sm text-gray-400">Total Vehicles</p>
                    <p class="text-2xl font-bold text-white">{{ number_format($totalVehicles) }}</p>
                </div>
            </div>
        </div>

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
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

        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
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
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-4">
        <form action="{{ route('admin.vehicles.index') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search vehicles..." class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
            </div>
            <select name="has_image" class="bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                <option value="">All Vehicles</option>
                <option value="yes" {{ request('has_image') === 'yes' ? 'selected' : '' }}>With Image</option>
                <option value="no" {{ request('has_image') === 'no' ? 'selected' : '' }}>Without Image</option>
            </select>
            <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">Filter</button>
            @if(request()->hasAny(['search', 'has_image']))
            <a href="{{ route('admin.vehicles.index') }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">Clear</a>
            @endif
        </form>
    </div>

    {{-- Vehicles Grid --}}
    <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-4 gap-4">
        @forelse($vehicles as $vehicle)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden hover:border-gray-600 transition">
            <div class="aspect-video bg-gray-900 flex items-center justify-center">
                @if($vehicle->image_path)
                <img src="{{ Storage::url($vehicle->image_path) }}" alt="{{ $vehicle->display_name }}" class="w-full h-full object-contain p-4">
                @else
                <div class="text-gray-600">
                    <svg class="w-16 h-16" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
            </div>
            <div class="p-4">
                <h3 class="font-semibold text-white truncate" title="{{ $vehicle->name }}">{{ $vehicle->display_name ?? $vehicle->name }}</h3>
                <p class="text-sm text-gray-500 truncate" title="{{ $vehicle->name }}">{{ $vehicle->name }}</p>
                @if($vehicle->vehicle_type)
                <div class="mt-2">
                    <span class="px-2 py-0.5 bg-blue-500/20 text-blue-400 text-xs rounded">{{ $vehicle->vehicle_type }}</span>
                </div>
                @endif
                <div class="mt-3 flex gap-2">
                    <a href="{{ route('admin.vehicles.edit', $vehicle) }}" class="flex-1 px-3 py-1.5 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded text-center transition">Edit</a>
                    <form action="{{ route('admin.vehicles.destroy', $vehicle) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to delete this vehicle?')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="px-3 py-1.5 bg-red-600/20 hover:bg-red-600/40 text-red-400 text-sm rounded transition">Delete</button>
                    </form>
                </div>
            </div>
        </div>
        @empty
        <div class="col-span-full">
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-8 text-center">
                <svg class="w-16 h-16 text-gray-600 mx-auto mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                </svg>
                <p class="text-gray-400 mb-4">No vehicles found. Sync from distance data to populate.</p>
                <form action="{{ route('admin.vehicles.sync') }}" method="POST" class="inline">
                    @csrf
                    <button type="submit" class="inline-flex items-center gap-2 px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-lg transition">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                        </svg>
                        Sync from Distance Data
                    </button>
                </form>
            </div>
        </div>
        @endforelse
    </div>

    @if($vehicles->hasPages())
    <div class="flex justify-center">
        {{ $vehicles->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
