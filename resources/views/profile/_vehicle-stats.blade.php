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
<div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
    <div class="flex items-center gap-3 mb-4">
        <h3 class="text-lg font-semibold text-white">Vehicle Stats</h3>
        <div class="flex-1 h-px bg-gray-700"></div>
    </div>

    {{-- Distance Breakdown + Roadkills --}}
    @if($totalDist > 0 || $roadkills > 0)
    <div class="grid grid-cols-1 md:grid-cols-3 gap-4 mb-6">
        {{-- On Foot --}}
        @if($totalDist > 0)
        <div class="bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-400">On Foot</p>
                    <p class="text-xl font-bold text-blue-400">{{ number_format($vs['totalWalkingDistance'] / 1000, 1) }} km</p>
                </div>
                <span class="text-sm text-gray-500">{{ $walkPct }}%</span>
            </div>
            <div class="w-full bg-gray-600 rounded-full h-2 mb-2">
                <div class="bg-blue-500 h-2 rounded-full" style="width: {{ $walkPct }}%"></div>
            </div>
            <p class="text-xs text-gray-500">{{ $walkHours }}h {{ $walkMin }}m walking</p>
        </div>

        {{-- In Vehicles --}}
        <div class="bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-emerald-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-emerald-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-400">In Vehicles</p>
                    <p class="text-xl font-bold text-emerald-400">{{ number_format($vs['totalVehicleDistance'] / 1000, 1) }} km</p>
                </div>
                <span class="text-sm text-gray-500">{{ $vehiclePct }}%</span>
            </div>
            <div class="w-full bg-gray-600 rounded-full h-2 mb-2">
                <div class="bg-emerald-500 h-2 rounded-full" style="width: {{ $vehiclePct }}%"></div>
            </div>
            <p class="text-xs text-gray-500">{{ $vehHours }}h {{ $vehMin }}m driving</p>
        </div>
        @endif

        {{-- Roadkills --}}
        @if($roadkills > 0)
        <div class="bg-gray-700/30 rounded-xl p-4">
            <div class="flex items-center gap-3 mb-3">
                <div class="w-10 h-10 rounded-lg bg-violet-500/20 flex items-center justify-center flex-shrink-0">
                    <svg class="w-5 h-5 text-violet-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                </div>
                <div class="flex-1">
                    <p class="text-sm text-gray-400">Roadkills</p>
                    <p class="text-xl font-bold text-violet-400">{{ number_format($roadkills) }}</p>
                </div>
            </div>
            @if(($gameStats->kills ?? 0) > 0)
            <p class="text-xs text-gray-500">{{ number_format(($roadkills / $gameStats->kills) * 100, 1) }}% of total kills</p>
            @endif
        </div>
        @endif
    </div>
    @endif

    {{-- Top Vehicles --}}
    @if($vs['topVehicles']->count() > 0)
    <div class="grid grid-cols-2 lg:grid-cols-4 gap-3">
        @foreach($vs['topVehicles'] as $index => $vehicle)
        @php
            $vehKm = number_format($vehicle['distance'] / 1000, 1);
            $vehH = floor($vehicle['time'] / 3600);
            $vehM = floor(($vehicle['time'] % 3600) / 60);
        @endphp
        <div class="relative bg-gray-700/30 border border-gray-600/50 rounded-xl p-3 text-center hover:border-gray-500/50 transition">
            @if($index < 3)
            <span class="absolute top-2 right-2 w-5 h-5 rounded-full flex items-center justify-center text-[10px] font-bold
                {{ $index === 0 ? 'bg-yellow-500/20 text-yellow-400' : ($index === 1 ? 'bg-gray-400/20 text-gray-300' : 'bg-amber-700/20 text-amber-600') }}">
                #{{ $index + 1 }}
            </span>
            @endif
            <div class="h-12 flex items-center justify-center mb-2">
                @if(isset($vs['vehicleImages'][$vehicle['name']]))
                <x-blur-image src="{{ Storage::url($vs['vehicleImages'][$vehicle['name']]) }}" alt="{{ $vehicle['name'] }}" class="max-h-12 max-w-full object-contain" />
                @else
                <div class="w-10 h-10 bg-gray-600/50 rounded-lg flex items-center justify-center">
                    <svg class="w-5 h-5 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                    </svg>
                </div>
                @endif
            </div>
            <p class="text-xs text-gray-300 truncate mb-1" title="{{ $vehicle['name'] }}">{{ $vehicle['name'] }}</p>
            <p class="text-sm font-bold text-emerald-400">{{ $vehKm }} km</p>
            <p class="text-[10px] text-gray-500">{{ $vehH }}h {{ $vehM }}m</p>
        </div>
        @endforeach
    </div>
    @endif
</div>
@endif
