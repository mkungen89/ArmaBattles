@if(isset($achievements) && $achievements->count() > 0)
<div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6" x-data="{ showAll: false }">
    <div class="flex items-center gap-3 mb-4">
        <h3 class="text-lg font-semibold text-white">Achievements</h3>
        <div class="flex-1 h-px bg-gray-700"></div>
        @php
            $earnedCount = $playerAchievements->count();
            $totalCount = $achievements->count();
        @endphp
        <span class="text-sm text-gray-400">{{ $earnedCount }}/{{ $totalCount }}</span>
    </div>

    <div class="grid grid-cols-2 sm:grid-cols-3 lg:grid-cols-4 gap-3">
        @foreach($achievements as $achievement)
        @php
            $earned = $playerAchievements->has($achievement->id);
            $pa = $playerAchievements->get($achievement->id);
            $currentValue = isset($gameStats) && $gameStats ? ($gameStats->{$achievement->stat_field} ?? 0) : 0;
            $progress = $achievement->threshold > 0 ? min(100, round(($currentValue / $achievement->threshold) * 100)) : 0;
        @endphp
        <div x-show="showAll || {{ $loop->index }} < 16" x-transition
             class="relative rounded-xl p-4 text-center transition {{ $earned ? 'bg-gray-700/50 border border-gray-600/50' : 'bg-gray-800/30 border border-gray-700/30 opacity-60' }}">
            <div class="flex items-center justify-center mb-2">
                <i data-lucide="{{ $achievement->icon }}" class="w-7 h-7" style="color: {{ $earned ? $achievement->color : '#6b7280' }};"></i>
            </div>
            <p class="text-sm font-medium {{ $earned ? 'text-white' : 'text-gray-500' }} truncate" title="{{ $achievement->name }}">
                {{ $achievement->name }}
            </p>
            <p class="text-xs text-gray-500 mt-1 line-clamp-2" title="{{ $achievement->description }}">
                {{ $achievement->description }}
            </p>
            @if($earned)
            <div class="mt-2">
                <span class="inline-block px-2 py-0.5 text-xs rounded-full font-medium" style="background: {{ $achievement->color }}20; color: {{ $achievement->color }};">
                    Earned
                </span>
            </div>
            @else
            <div class="mt-2">
                <div class="w-full bg-gray-700 rounded-full h-1.5">
                    <div class="h-1.5 rounded-full transition-all" style="width: {{ $progress }}%; background: {{ $achievement->color }}"></div>
                </div>
                <p class="text-xs text-gray-600 mt-1">{{ number_format($currentValue) }}/{{ number_format($achievement->threshold) }}</p>
            </div>
            @endif
        </div>
        @endforeach
    </div>

    @if($achievements->count() > 16)
    <div class="mt-4 text-center">
        <button x-show="!showAll" @click="showAll = true; $nextTick(() => { if (typeof lucide !== 'undefined') lucide.createIcons(); })"
                class="px-4 py-2 text-sm font-medium text-green-400 hover:text-green-300 bg-gray-700/50 hover:bg-gray-700 rounded-lg transition">
            View All ({{ $achievements->count() }})
        </button>
        <button x-show="showAll" @click="showAll = false" style="display:none;"
                class="px-4 py-2 text-sm font-medium text-gray-400 hover:text-white bg-gray-700/50 hover:bg-gray-700 rounded-lg transition">
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
