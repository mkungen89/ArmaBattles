@props(['achievements', 'playerAchievements', 'gameStats'])

@if(isset($achievements) && $achievements->count() > 0)
@php
    $earnedCount = $playerAchievements->count();
    $totalCount = $achievements->count();
    $categories = $achievements->pluck('category')->unique()->sort()->values();
@endphp

<div class="glass-card p-5 sm:p-6" x-data="{ showAll: false, filter: 'all' }">
    <div class="flex flex-col sm:flex-row sm:items-center justify-between gap-3 mb-4">
        <div class="flex items-center gap-3">
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Achievements</h3>
            <span class="text-xs text-gray-500">{{ $earnedCount }}/{{ $totalCount }}</span>
            {{-- Progress bar --}}
            <div class="hidden sm:block w-24 bg-white/5 rounded-full h-1.5">
                <div class="bg-green-500 h-1.5 rounded-full" style="width: {{ $totalCount > 0 ? round(($earnedCount / $totalCount) * 100) : 0 }}%"></div>
            </div>
        </div>
        {{-- Category filter --}}
        <div class="flex flex-wrap gap-1">
            <button @click="filter = 'all'" :class="filter === 'all' ? 'bg-white/10 text-white' : 'text-gray-500 hover:text-gray-300'" class="px-2 py-0.5 text-[10px] font-medium rounded transition">All</button>
            @foreach($categories as $cat)
            <button @click="filter = '{{ $cat }}'" :class="filter === '{{ $cat }}' ? 'bg-white/10 text-white' : 'text-gray-500 hover:text-gray-300'" class="px-2 py-0.5 text-[10px] font-medium rounded transition capitalize">{{ $cat }}</button>
            @endforeach
        </div>
    </div>

    <div class="grid grid-cols-3 sm:grid-cols-4 lg:grid-cols-6 gap-2">
        @foreach($achievements as $achievement)
        @php
            $earned = $playerAchievements->has($achievement->id);
            $currentValue = isset($gameStats) && $gameStats ? ($gameStats->{$achievement->stat_field} ?? 0) : 0;
            $progress = $achievement->threshold > 0 ? min(100, round(($currentValue / $achievement->threshold) * 100)) : 0;
        @endphp
        <div x-show="(showAll || {{ $loop->index }} < 18) && (filter === 'all' || filter === '{{ $achievement->category }}')"
             x-transition
             class="relative rounded-xl p-3 text-center transition group {{ $earned ? '' : 'achievement-locked' }}"
             title="{{ $achievement->name }}: {{ $achievement->description }}">
            <div class="flex items-center justify-center mb-1.5">
                @if($achievement->badge_url)
                    <img src="{{ $achievement->badge_url }}" alt="{{ $achievement->name }}" class="w-10 h-10 object-contain">
                @else
                    <div class="w-10 h-10 rounded-full flex items-center justify-center" style="background: {{ $earned ? $achievement->color : '#374151' }}15;">
                        <i data-lucide="{{ $achievement->icon }}" class="w-5 h-5" style="color: {{ $earned ? $achievement->color : '#6b7280' }};"></i>
                    </div>
                @endif
            </div>
            <p class="text-[10px] font-medium {{ $earned ? 'text-white' : 'text-gray-600' }} truncate mb-0.5">{{ $achievement->name }}</p>
            <p class="text-[9px] text-gray-500 line-clamp-2 leading-tight">{{ $achievement->description }}</p>
            @if(!$earned && $progress > 0)
            <div class="mt-1 w-full bg-white/5 rounded-full h-1">
                <div class="h-1 rounded-full" style="width: {{ $progress }}%; background: {{ $achievement->color }}"></div>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    @if($achievements->count() > 18)
    <div class="mt-4 text-center">
        <button x-show="!showAll" @click="showAll = true; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); })"
                class="px-4 py-1.5 text-xs font-medium text-green-400 hover:text-green-300 bg-white/5 hover:bg-white/10 rounded-lg transition">
            Show All ({{ $achievements->count() }})
        </button>
        <button x-show="showAll" @click="showAll = false" x-cloak
                class="px-4 py-1.5 text-xs font-medium text-gray-400 hover:text-white bg-white/5 hover:bg-white/10 rounded-lg transition">
            Show Less
        </button>
    </div>
    @endif
</div>

@push('scripts')
<script>
    document.addEventListener('DOMContentLoaded', function() {
        if (typeof lucide !== 'undefined') lucide.createIcons();
    });
</script>
@endpush
@endif
