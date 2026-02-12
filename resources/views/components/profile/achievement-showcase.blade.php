@props(['showcaseAchievements'])

@if(isset($showcaseAchievements) && $showcaseAchievements->count() > 0)
<div class="glass-card glow-gold-sm overflow-hidden">
    <div class="relative p-5 sm:p-6">
        {{-- Background shimmer --}}
        <div class="absolute inset-0 bg-gradient-to-r from-amber-500/5 via-transparent to-yellow-500/5 pointer-events-none"></div>

        <div class="relative">
            <div class="flex items-center gap-2.5 mb-4">
                <svg class="w-5 h-5 text-amber-400" fill="currentColor" viewBox="0 0 20 20">
                    <path d="M9.049 2.927c.3-.921 1.603-.921 1.902 0l1.07 3.292a1 1 0 00.95.69h3.462c.969 0 1.371 1.24.588 1.81l-2.8 2.034a1 1 0 00-.364 1.118l1.07 3.292c.3.921-.755 1.688-1.538 1.118l-2.8-2.034a1 1 0 00-1.176 0l-2.8 2.034c-.783.57-1.838-.197-1.538-1.118l1.07-3.292a1 1 0 00-.364-1.118l-2.8-2.034c-.783-.57-.38-1.81.588-1.81h3.461a1 1 0 00.951-.69l1.07-3.292z"/>
                </svg>
                <h3 class="text-sm font-semibold text-amber-300 uppercase tracking-wider">Achievement Showcase</h3>
            </div>

            <div class="showcase-scroll flex gap-4 overflow-x-auto pb-2 sm:grid sm:grid-cols-3 sm:overflow-visible sm:pb-0">
                @foreach($showcaseAchievements as $achievement)
                <div class="flex-shrink-0 w-48 sm:w-auto glass-card glow-gold p-4 text-center group">
                    <div class="relative flex items-center justify-center mb-3">
                        @if($achievement->badge_url)
                            <img src="{{ $achievement->badge_url }}" alt="{{ $achievement->name }}" class="w-14 h-14 object-contain drop-shadow-lg">
                        @else
                            <div class="w-14 h-14 rounded-full flex items-center justify-center" style="background: {{ $achievement->color }}15; box-shadow: 0 0 20px {{ $achievement->color }}30;">
                                <i data-lucide="{{ $achievement->icon }}" class="w-7 h-7" style="color: {{ $achievement->color }};"></i>
                            </div>
                        @endif
                    </div>
                    <p class="text-sm font-bold text-white mb-1 truncate">{{ $achievement->name }}</p>
                    <p class="text-xs text-gray-400 line-clamp-2 mb-2">{{ $achievement->description }}</p>
                    <span class="inline-block px-2.5 py-0.5 text-xs rounded-full font-semibold" style="background: {{ $achievement->color }}20; color: {{ $achievement->color }};">
                        {{ $achievement->points }} pts
                    </span>
                </div>
                @endforeach
            </div>
        </div>
    </div>
</div>
@endif
