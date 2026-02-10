@extends('layouts.app')

@section('title', 'Weapon Statistics')

@section('content')
<div class="space-y-6">
    <div class="flex flex-col sm:flex-row items-start sm:items-center justify-between gap-4">
        <h1 class="text-3xl font-bold text-white">Weapon Statistics</h1>
        <p class="text-gray-400 text-sm">{{ $weapons->count() }} weapons tracked</p>
    </div>

    <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase w-16">#</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Weapon</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Kills</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Headshots</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">HS %</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Avg Distance</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Longest Kill</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($weapons as $index => $weapon)
                <tr class="{{ $loop->odd ? 'bg-white/3' : 'bg-white/[0.01]' }} hover:bg-white/5 transition-colors">
                    <td class="px-4 py-3">
                        @if($index === 0)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-yellow-500/20 text-yellow-400 font-bold text-sm">1</span>
                        @elseif($index === 1)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-gray-400/20 text-gray-300 font-bold text-sm">2</span>
                        @elseif($index === 2)
                        <span class="inline-flex items-center justify-center w-8 h-8 rounded-full bg-amber-700/20 text-amber-600 font-bold text-sm">3</span>
                        @else
                        <span class="text-gray-500 text-sm pl-2">{{ $index + 1 }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <div class="flex items-center gap-3">
                            @if(isset($weaponImages[$weapon->weapon_name]))
                            <img src="{{ Storage::url($weaponImages[$weapon->weapon_name]) }}" alt="{{ $weapon->weapon_name }}" class="h-8 w-auto object-contain max-w-[80px]">
                            @else
                            <div class="w-10 h-8 bg-white/3 rounded flex items-center justify-center">
                                <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14"/>
                                </svg>
                            </div>
                            @endif
                            <span class="text-white font-medium text-sm">{{ $weapon->weapon_name }}</span>
                        </div>
                    </td>
                    <td class="px-4 py-3 text-right text-sm text-green-400 font-bold">{{ number_format($weapon->total_kills) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-amber-400">{{ number_format($weapon->headshots) }}</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-400">{{ $weapon->headshot_pct }}%</td>
                    <td class="px-4 py-3 text-right text-sm text-gray-400">{{ $weapon->avg_distance ? number_format($weapon->avg_distance, 1) . 'm' : '-' }}</td>
                    <td class="px-4 py-3 text-right text-sm {{ ($weapon->max_distance ?? 0) >= 500 ? 'text-red-400 font-bold' : 'text-gray-400' }}">
                        {{ $weapon->max_distance ? number_format($weapon->max_distance, 0) . 'm' : '-' }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="7" class="px-4 py-8 text-center text-gray-400">No weapon data available yet.</td>
                </tr>
                @endforelse
            </tbody>
        </table>
        </div>
    </div>
</div>
@endsection
