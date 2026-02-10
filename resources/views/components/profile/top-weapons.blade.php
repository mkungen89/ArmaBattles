@props(['topWeapons', 'weaponImages'])

@if($topWeapons->count() > 0)
<div class="glass-card p-5 sm:p-6">
    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Top Weapons</h3>

    <div class="grid grid-cols-2 lg:grid-cols-3 gap-3">
        @foreach($topWeapons as $index => $weapon)
        <div class="relative glass-card p-4 text-center card-hover group
            {{ $index === 0 ? 'border-yellow-500/20' : '' }}">

            {{-- Rank badge --}}
            @if($index < 3)
            <span class="absolute top-2 right-2 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold
                {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-400' : ($index === 1 ? 'bg-gray-400/20 text-gray-300' : 'bg-amber-700/20 text-amber-600') }}">
                {{ $index + 1 }}
            </span>
            @endif

            {{-- Weapon image --}}
            <div class="h-14 flex items-center justify-center mb-3">
                @if(isset($weaponImages[$weapon->weapon_name]))
                <x-blur-image src="{{ Storage::url($weaponImages[$weapon->weapon_name]) }}" alt="{{ $weapon->weapon_name }}" class="max-h-14 max-w-full object-contain opacity-80 group-hover:opacity-100 transition" />
                @else
                <div class="w-10 h-10 bg-white/5 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16l4.586-4.586a2 2 0 012.828 0L16 16m-2-2l1.586-1.586a2 2 0 012.828 0L20 14m-6-6h.01M6 20h12a2 2 0 002-2V6a2 2 0 00-2-2H6a2 2 0 00-2 2v12a2 2 0 002 2z"/></svg>
                </div>
                @endif
            </div>

            <p class="text-xs text-gray-400 truncate mb-1">{{ $weapon->weapon_name }}</p>
            <p class="text-lg font-bold text-green-400">{{ number_format($weapon->total) }}</p>
            <p class="text-[10px] text-gray-600">kills</p>
        </div>
        @endforeach
    </div>
</div>
@endif
