@php
    $vs = $vehicleStats;
    $roadkills = $gameStats->total_roadkills ?? 0;
    $totalDist = $vs['totalWalkingDistance'] + $vs['totalVehicleDistance'];
    $walkPct = $totalDist > 0 ? round(($vs['totalWalkingDistance'] / $totalDist) * 100, 1) : 0;
    $vehiclePct = $totalDist > 0 ? round(($vs['totalVehicleDistance'] / $totalDist) * 100, 1) : 0;
    $walkHours = floor($vs['totalWalkingTime'] / 3600);
    $walkMin = floor(($vs['totalWalkingTime'] % 3600) / 60);
    $vehHours = floor($vs['totalVehicleTime'] / 3600);
    $vehMin = floor(($vs['totalVehicleTime'] % 3600) / 60);
@endphp

@if($totalDist > 0 || $vs['topVehicles']->count() > 0 || $roadkills > 0)
<div class="glass-card p-5 sm:p-6">
    <h3 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Vehicle Stats</h3>

    {{-- Distance Breakdown + Roadkills --}}
    @if($totalDist > 0 || $roadkills > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-3 mb-5">
        @if($totalDist > 0)
        {{-- On Foot --}}
        <div class="bg-white/3 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="w-9 h-9 rounded-lg bg-blue-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-blue-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] text-gray-500 uppercase">On Foot</p>
                    <p class="text-lg font-bold text-blue-400">{{ number_format($vs['totalWalkingDistance'] / 1000, 1) }} km</p>
                </div>
                <span class="text-xs text-gray-600">{{ $walkPct }}%</span>
            </div>
            <div class="w-full bg-white/5 rounded-full h-1.5 mb-1.5">
                <div class="bg-blue-500/70 h-1.5 rounded-full" style="width: {{ $walkPct }}%"></div>
            </div>
            <p class="text-[10px] text-gray-600">{{ $walkHours }}h {{ $walkMin }}m walking</p>
        </div>

        {{-- In Vehicles --}}
        <div class="bg-white/3 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="w-9 h-9 rounded-lg bg-emerald-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-emerald-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] text-gray-500 uppercase">In Vehicles</p>
                    <p class="text-lg font-bold text-emerald-400">{{ number_format($vs['totalVehicleDistance'] / 1000, 1) }} km</p>
                </div>
                <span class="text-xs text-gray-600">{{ $vehiclePct }}%</span>
            </div>
            <div class="w-full bg-white/5 rounded-full h-1.5 mb-1.5">
                <div class="bg-emerald-500/70 h-1.5 rounded-full" style="width: {{ $vehiclePct }}%"></div>
            </div>
            <p class="text-[10px] text-gray-600">{{ $vehHours }}h {{ $vehMin }}m driving</p>
        </div>
        @endif

        @if($roadkills > 0)
        <div class="bg-white/3 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-2.5">
                <div class="w-9 h-9 rounded-lg bg-violet-500/15 flex items-center justify-center flex-shrink-0">
                    <svg class="w-4 h-4 text-violet-400" fill="none" stroke="currentColor" stroke-width="2" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                </div>
                <div class="flex-1">
                    <p class="text-[10px] text-gray-500 uppercase">Roadkills</p>
                    <p class="text-lg font-bold text-violet-400">{{ number_format($roadkills) }}</p>
                </div>
            </div>
            @if(($gameStats->kills ?? 0) > 0)
            <p class="text-[10px] text-gray-600">{{ number_format(($roadkills / $gameStats->kills) * 100, 1) }}% of total kills</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Top Vehicles --}}
    @if($vs['topVehicles']->count() > 0)
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-2">
        @foreach($vs['topVehicles'] as $index => $vehicle)
        @php
            $vehKm = number_format($vehicle['distance'] / 1000, 1);
            $vehH = floor($vehicle['time'] / 3600);
            $vehM = floor(($vehicle['time'] % 3600) / 60);
        @endphp
        <div class="relative bg-white/3 rounded-xl p-3 text-center card-hover group">
            @if($index < 3)
            <span class="absolute top-1.5 right-1.5 w-4 h-4 rounded-full flex items-center justify-center text-[9px] font-bold
                {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-400' : ($index === 1 ? 'bg-gray-400/20 text-gray-300' : 'bg-amber-700/20 text-amber-600') }}">
                {{ $index + 1 }}
            </span>
            @endif
            <div class="h-10 flex items-center justify-center mb-2">
                @if(isset($vs['vehicleImages'][$vehicle['name']]))
                <x-blur-image src="{{ Storage::url($vs['vehicleImages'][$vehicle['name']]) }}" alt="{{ $vehicle['name'] }}" class="max-h-10 max-w-full object-contain opacity-70 group-hover:opacity-100 transition" />
                @else
                <div class="w-8 h-8 bg-white/5 rounded-lg flex items-center justify-center">
                    <svg class="w-4 h-4 text-gray-600" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/></svg>
                </div>
                @endif
            </div>
            <p class="text-[10px] text-gray-400 truncate mb-0.5" title="{{ $vehicle['name'] }}">{{ $vehicle['name'] }}</p>
            <p class="text-sm font-bold text-emerald-400">{{ $vehKm }} km</p>
            <p class="text-[9px] text-gray-600">{{ $vehH }}h {{ $vehM }}m</p>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif
