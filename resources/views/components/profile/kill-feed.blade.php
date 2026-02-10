@props(['recentKillEvents', 'weaponImages'])

@if($recentKillEvents->count() > 0)
<div class="glass-card overflow-hidden">
    <div class="px-5 py-3.5 border-b border-white/5 flex items-center justify-between">
        <div class="flex items-center gap-2">
            <svg class="w-4 h-4 text-red-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
            <h3 class="text-sm font-semibold text-white uppercase tracking-wider">Kill Feed</h3>
        </div>
        <span class="text-[10px] text-gray-600">{{ $recentKillEvents->count() }} recent</span>
    </div>

    <div class="max-h-[420px] overflow-y-auto">
        <table class="w-full">
            <thead class="bg-white/3 sticky top-0 z-10">
                <tr>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Victim</th>
                    <th class="px-4 py-2 text-left text-[10px] font-medium text-gray-500 uppercase tracking-wider">Weapon</th>
                    <th class="px-4 py-2 text-right text-[10px] font-medium text-gray-500 uppercase tracking-wider">Dist</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/3">
                @foreach($recentKillEvents as $kill)
                @php
                    $killedAt = $kill->killed_at ? \Carbon\Carbon::parse($kill->killed_at) : null;
                    $distance = $kill->kill_distance ? round($kill->kill_distance) : null;
                @endphp
                <tr class="hover:bg-white/3 transition-colors">
                    <td class="px-4 py-2 text-xs text-gray-500 whitespace-nowrap">
                        {{ $killedAt ? $killedAt->diffForHumans() : '-' }}
                    </td>
                    <td class="px-4 py-2">
                        @if($kill->victim_type === 'AI')
                        <span class="px-1.5 py-0.5 bg-yellow-500/15 text-yellow-400 text-[10px] font-semibold rounded">AI</span>
                        @else
                        <span class="text-red-400 text-xs">{{ $kill->victim_name ?? 'Unknown' }}</span>
                        @endif
                    </td>
                    <td class="px-4 py-2">
                        <div class="flex items-center gap-1.5">
                            @if(isset($weaponImages[$kill->weapon_name]))
                            <img src="{{ Storage::url($weaponImages[$kill->weapon_name]) }}" alt="{{ $kill->weapon_name }}" class="h-3.5 w-auto object-contain flex-shrink-0 opacity-70">
                            @endif
                            <span class="text-[10px] text-gray-400 truncate max-w-[120px]">{{ $kill->weapon_name }}</span>
                            @if($kill->is_headshot)
                            <svg class="w-3 h-3 text-yellow-400 flex-shrink-0" fill="currentColor" viewBox="0 0 24 24"><path d="M12 2l3.09 6.26L22 9.27l-5 4.87 1.18 6.88L12 17.77l-6.18 3.25L7 14.14 2 9.27l6.91-1.01L12 2z"/></svg>
                            @endif
                            @if($kill->is_roadkill)
                            <span class="px-1 py-0.5 text-[8px] font-bold bg-violet-500/15 text-violet-400 rounded">RK</span>
                            @endif
                        </div>
                    </td>
                    <td class="px-4 py-2 text-right text-xs font-medium whitespace-nowrap
                        @if($distance)
                            {{ $distance < 50 ? 'text-green-400' : ($distance < 200 ? 'text-yellow-400' : 'text-red-400') }}
                        @else
                            text-gray-600
                        @endif">
                        {{ $distance ? $distance . 'm' : '-' }}
                    </td>
                </tr>
                @endforeach
            </tbody>
        </table>
    </div>
</div>
@endif
