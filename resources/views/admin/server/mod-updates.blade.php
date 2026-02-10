@extends('admin.layout')

@section('title', 'Mod Update Check')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Mod Update Check</h1>
            <p class="text-sm text-gray-500 mt-1">Compare installed mod versions against the Workshop</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Back
            </a>
            <a href="{{ route('admin.server.mod-updates') }}" class="px-3 py-2 bg-green-600/20 border border-green-500/30 hover:bg-green-600/30 text-green-400 rounded-lg text-sm transition">
                <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 4v5h.582m15.356 2A8.001 8.001 0 004.582 9m0 0H9m11 11v-5h-.581m0 0a8.003 8.003 0 01-15.357-2m15.357 2H15"/>
                </svg>
                Refresh
            </a>
        </div>
    </div>

    @if(count($mods) === 0)
    {{-- Empty State --}}
    <div class="glass-card rounded-xl p-12 text-center">
        <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M20 7l-8-4-8 4m16 0l-8 4m8-4v10l-8 4m0-10L4 7m8 4v10M4 7v10l8 4"/>
        </svg>
        <p class="text-gray-400 mb-1">No mod data available</p>
        <p class="text-sm text-gray-600">Make sure the server is reachable.</p>
    </div>
    @else
    @php
        $updatesAvailable = collect($mods)->filter(fn($mod) => $mod['has_update'] ?? false)->count();
    @endphp

    {{-- Summary --}}
    <div class="text-sm text-gray-400">
        {{ count($mods) }} {{ Str::plural('mod', count($mods)) }} checked, <span class="{{ $updatesAvailable > 0 ? 'text-yellow-400 font-medium' : 'text-green-400 font-medium' }}">{{ $updatesAvailable }} {{ Str::plural('update', $updatesAvailable) }} available</span>
    </div>

    {{-- Mods Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <div class="overflow-x-auto">
            <table class="w-full">
                <thead>
                    <tr class="border-b border-white/5">
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-6 py-3">Mod Name</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-6 py-3">Mod ID</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-6 py-3">Installed Version</th>
                        <th class="text-left text-xs text-gray-500 uppercase tracking-wider px-6 py-3">Latest Version</th>
                        <th class="text-center text-xs text-gray-500 uppercase tracking-wider px-6 py-3">Status</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($mods as $mod)
                    <tr class="border-b border-white/5 hover:bg-white/3 transition">
                        {{-- Mod Name --}}
                        <td class="px-6 py-3">
                            <a href="{{ $mod['workshop_url'] }}" target="_blank" class="text-sm text-white hover:text-green-400 transition font-medium">
                                {{ $mod['name'] }}
                                <svg class="w-3 h-3 inline ml-1 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                                </svg>
                            </a>
                        </td>

                        {{-- Mod ID --}}
                        <td class="px-6 py-3">
                            <code class="text-xs text-gray-400 font-mono select-all">{{ $mod['mod_id'] }}</code>
                        </td>

                        {{-- Installed Version --}}
                        <td class="px-6 py-3">
                            <span class="text-sm text-gray-300 font-mono">{{ $mod['installed_version'] ?? '&mdash;' }}</span>
                        </td>

                        {{-- Latest Version --}}
                        <td class="px-6 py-3">
                            <span class="text-sm text-gray-300 font-mono">{{ $mod['latest_version'] ?? '&mdash;' }}</span>
                        </td>

                        {{-- Status --}}
                        <td class="px-6 py-3 text-center">
                            @if(!empty($mod['error']))
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-red-500/20 text-red-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                                    </svg>
                                    Error
                                </span>
                            @elseif($mod['has_update'])
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-yellow-500/20 text-yellow-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 16v1a3 3 0 003 3h10a3 3 0 003-3v-1m-4-4l-4 4m0 0l-4-4m4 4V4"/>
                                    </svg>
                                    Update available
                                </span>
                            @else
                                <span class="inline-flex items-center gap-1 px-2.5 py-1 rounded-full text-xs font-medium bg-green-500/20 text-green-400">
                                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                                    </svg>
                                    Up to date
                                </span>
                            @endif
                        </td>
                    </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
    </div>
    @endif
</div>
@endsection
