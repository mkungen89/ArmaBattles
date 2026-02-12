@extends('admin.layout')
@section('title', 'Rank Management')
@section('admin-content')
<div class="mb-6">
    <h1 class="text-3xl font-bold text-white mb-2">Rank Management</h1>
    <p class="text-gray-400">Manage all 50 ranks across 10 military eras (Level 1-500 system)</p>
</div>

{{-- Success Message --}}
@if(session('success'))
    <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-xl mb-6">
        {{ session('success') }}
    </div>
@endif

{{-- Ranks by Era --}}
@foreach($ranks as $eraNum => $eraRanks)
    @php
        $eraColors = [
            1 => '#22c55e', 2 => '#22c55e',
            3 => '#3b82f6', 4 => '#3b82f6',
            5 => '#a855f7', 6 => '#a855f7',
            7 => '#f97316', 8 => '#f97316',
            9 => '#ef4444', 10 => '#fbbf24',
        ];
        $color = $eraColors[$eraNum] ?? '#22c55e';
    @endphp

    <div class="glass-card p-6 mb-6" style="border-color: {{ $color }};">
        {{-- Era Header --}}
        <div class="flex items-center justify-between mb-4 pb-3 border-b border-white/10">
            <h2 class="text-xl font-bold text-white" style="color: {{ $color }};">
                {{ $eraNames[$eraNum] ?? "Era $eraNum" }}
            </h2>
            <span class="text-sm text-gray-400">Levels {{ $eraRanks->first()->min_level }}-{{ $eraRanks->last()->max_level }}</span>
        </div>

        {{-- Ranks Grid --}}
        <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 xl:grid-cols-5 gap-4">
            @foreach($eraRanks as $rank)
                <div class="bg-white/3 border border-white/10 rounded-xl p-4 hover:border-white/20 transition group">
                    {{-- Rank Badge --}}
                    <div class="flex items-center justify-center mb-3">
                        @if($rank->logo_url)
                            <img src="{{ $rank->logo_url }}" alt="{{ $rank->name }}"
                                 class="w-16 h-16 object-contain group-hover:scale-110 transition"
                                 style="filter: drop-shadow(0 0 8px {{ $rank->color }}80);">
                        @else
                            <div class="w-16 h-16 rounded-full flex items-center justify-center text-white font-bold text-xl"
                                 style="background: linear-gradient(135deg, {{ $rank->color }}, {{ $rank->color }}80);">
                                {{ $rank->rank }}
                            </div>
                        @endif
                    </div>

                    {{-- Rank Info --}}
                    <div class="text-center mb-3">
                        <h3 class="text-sm font-bold text-white mb-1">{{ $rank->name }}</h3>
                        <p class="text-xs text-gray-500">Rank {{ $rank->rank }} • Levels {{ $rank->min_level }}-{{ $rank->max_level }}</p>
                        <div class="flex items-center justify-center gap-1 mt-1">
                            <div class="w-4 h-4 rounded-full" style="background: {{ $rank->color }};"></div>
                            <span class="text-[10px] text-gray-500 font-mono">{{ $rank->color }}</span>
                        </div>
                    </div>

                    {{-- Actions --}}
                    <div class="flex gap-2">
                        <a href="{{ route('admin.ranks.edit', $rank) }}"
                           class="flex-1 px-3 py-1.5 bg-blue-600 hover:bg-blue-500 text-white text-xs font-medium rounded-lg transition text-center">
                            Edit
                        </a>
                        @if($rank->logo_url)
                            <form action="{{ route('admin.ranks.delete-logo', $rank) }}" method="POST" class="flex-1"
                                  onsubmit="return confirm('Delete logo for {{ $rank->name }}?')">
                                @csrf
                                @method('DELETE')
                                <button type="submit"
                                        class="w-full px-3 py-1.5 bg-red-600 hover:bg-red-500 text-white text-xs font-medium rounded-lg transition">
                                    Delete Logo
                                </button>
                            </form>
                        @endif
                    </div>
                </div>
            @endforeach
        </div>
    </div>
@endforeach

@endsection
