@extends('admin.layout')

@section('title', 'Server Mods')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Mod Management</h1>
            <p class="text-sm text-gray-500 mt-1">Manage installed mods and addon directories</p>
        </div>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">Dashboard</a>
            <a href="{{ route('admin.server.players') }}" class="px-3 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">Players</a>
            <a href="{{ route('admin.server.logs') }}" class="px-3 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">Logs</a>
            <a href="{{ route('admin.server.config') }}" class="px-3 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl text-sm transition">Config</a>
        </div>
    </div>

    {{-- Notice --}}
    <div class="p-3 bg-blue-500/10 border border-blue-500/30 rounded-lg flex items-center gap-3">
        <svg class="w-5 h-5 text-blue-400 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
        </svg>
        <p class="text-sm text-blue-400">Changes require a server restart to take effect.</p>
    </div>

    @if(!$data)
    <div class="glass-card rounded-xl p-12 text-center">
        <svg class="w-12 h-12 text-red-500/50 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="1.5" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
        </svg>
        <p class="text-gray-400 mb-1">Could not connect to game server</p>
        <p class="text-sm text-gray-600">The server manager API is not reachable. Make sure the service is running.</p>
    </div>
    @else
    @php
        $mods = $data['mods'] ?? [];
        $addonDirs = $data['addonDirectories'] ?? [];
    @endphp

    <div class="grid lg:grid-cols-3 gap-6">
        <div class="lg:col-span-2 space-y-6">
            {{-- Installed Mods --}}
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-sm font-semibold text-white uppercase tracking-wider">
                        Installed Mods
                        <span class="ml-2 text-sm font-normal text-gray-500">({{ count($mods) }})</span>
                    </h2>
                </div>

                @if(count($mods) === 0)
                <div class="text-center py-8">
                    <p class="text-gray-500">No mods installed</p>
                </div>
                @else
                <div class="overflow-x-auto">
                    <table class="w-full">
                        <thead>
                            <tr class="border-b border-white/5">
                                <th class="text-left text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">Mod ID</th>
                                <th class="text-left text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">Name</th>
                                <th class="text-left text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">Version</th>
                                <th class="text-center text-xs text-gray-500 uppercase tracking-wider pb-3 pr-4">On Disk</th>
                                <th class="text-right text-xs text-gray-500 uppercase tracking-wider pb-3">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                            @foreach($mods as $mod)
                            @php
                                $modId = $mod['modId'] ?? '';
                                $onDisk = collect($addonDirs)->contains(fn($dir) => str_contains($dir, $modId));
                            @endphp
                            <tr class="border-b border-white/5 hover:bg-white/3 transition">
                                <td class="py-3 pr-4">
                                    <code class="text-xs text-gray-400 select-all">{{ $modId }}</code>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="text-sm text-white font-medium">{{ $mod['name'] ?? '—' }}</span>
                                </td>
                                <td class="py-3 pr-4">
                                    <span class="text-sm text-gray-400">{{ $mod['version'] ?? '—' }}</span>
                                </td>
                                <td class="py-3 pr-4 text-center">
                                    @if($onDisk)
                                    <span class="inline-flex items-center gap-1 text-xs text-green-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/></svg>
                                        Found
                                    </span>
                                    @else
                                    <span class="inline-flex items-center gap-1 text-xs text-red-400">
                                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                                        Missing
                                    </span>
                                    @endif
                                </td>
                                <td class="py-3 text-right">
                                    <form method="POST" action="{{ route('admin.server.mods.remove', $modId) }}"
                                          onsubmit="return confirm('Remove {{ addslashes($mod['name'] ?? $modId) }} from the config? A server restart will be needed.')">
                                        @csrf
                                        @method('DELETE')
                                        <button type="submit" class="px-2.5 py-1 text-xs bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg transition">
                                            Remove
                                        </button>
                                    </form>
                                </td>
                            </tr>
                            @endforeach
                        </tbody>
                    </table>
                </div>
                @endif
            </div>

            {{-- Addon Directories --}}
            <div x-data="{ open: false }" class="glass-card rounded-xl p-6">
                <div class="flex items-center justify-between cursor-pointer" @click="open = !open">
                    <h2 class="text-sm font-semibold text-white uppercase tracking-wider">
                        Addon Directories
                        <span class="ml-2 text-sm font-normal text-gray-500">({{ count($addonDirs) }})</span>
                    </h2>
                    <svg class="w-5 h-5 text-gray-400 transition-transform" :class="open && 'rotate-180'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                    </svg>
                </div>
                <div x-show="open" x-collapse class="mt-4">
                    <div class="bg-gray-900/50 rounded-lg p-3 space-y-1 font-mono text-xs text-gray-400 max-h-64 overflow-y-auto">
                        @foreach($addonDirs as $dir)
                        <div class="px-2 py-1 hover:bg-white/3 rounded">{{ $dir }}</div>
                        @endforeach
                        @if(count($addonDirs) === 0)
                        <div class="text-gray-600">No addon directories found</div>
                        @endif
                    </div>
                </div>
            </div>
        </div>

        {{-- Add Mod Form --}}
        <div>
            <div class="glass-card rounded-xl p-6 sticky top-24">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-4">Add Mod</h2>
                <form method="POST" action="{{ route('admin.server.mods.add') }}" class="space-y-4">
                    @csrf
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Workshop Mod ID <span class="text-red-400">*</span></label>
                        <input type="text" name="mod_id" required maxlength="50" placeholder="e.g. 5AAAC70D754245DD"
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/5 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Mod Name <span class="text-red-400">*</span></label>
                        <input type="text" name="name" required maxlength="255" placeholder="e.g. Server Admin Tools"
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/5 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
                    </div>
                    <div>
                        <label class="block text-sm text-gray-400 mb-1">Version <span class="text-xs text-gray-600">(optional)</span></label>
                        <input type="text" name="version" maxlength="50" placeholder="e.g. 1.6.2"
                               class="w-full px-3 py-2 bg-gray-900/50 border border-white/5 rounded-lg text-sm text-white placeholder-gray-600 focus:outline-none focus:border-green-500/50">
                    </div>
                    <button type="submit" class="w-full px-4 py-2.5 bg-green-600/20 border border-green-500/30 hover:bg-green-600/30 text-green-400 rounded-lg text-sm font-medium transition">
                        <svg class="w-4 h-4 inline mr-1" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6v6m0 0v6m0-6h6m-6 0H6"/>
                        </svg>
                        Add Mod
                    </button>
                </form>
            </div>
        </div>
    </div>
    @endif
</div>
@endsection
