@extends('admin.layout')

@section('title', 'Manage Servers')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Servers</h1>
        <button onclick="document.getElementById('add-server-modal').classList.remove('hidden')" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
            </svg>
            Add Server
        </button>
    </div>

    {{-- Add Server Modal --}}
    <div id="add-server-modal" class="hidden fixed inset-0 z-50 overflow-y-auto">
        <div class="flex items-center justify-center min-h-screen px-4">
            <div class="fixed inset-0 bg-black/70" onclick="document.getElementById('add-server-modal').classList.add('hidden')"></div>
            <div class="relative bg-gray-800 border border-gray-700 rounded-xl p-6 w-full max-w-md">
                <h3 class="text-lg font-bold text-white mb-4">Add Server</h3>
                <form action="{{ route('admin.servers.store') }}" method="POST">
                    @csrf
                    <div class="space-y-4">
                        <div>
                            <label class="block text-sm font-medium text-gray-300 mb-2">BattleMetrics Server ID</label>
                            <input type="text" name="battlemetrics_id" required
                                class="w-full px-4 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white placeholder-gray-400 focus:outline-none focus:border-green-500"
                                placeholder="e.g., 37525380">
                            <p class="mt-1 text-xs text-gray-500">Find this in the BattleMetrics URL: battlemetrics.com/servers/arma-reforger/<strong>37525380</strong></p>
                        </div>
                        <div class="flex items-center gap-2">
                            <input type="checkbox" name="sync_mods" id="sync_mods" value="1" checked
                                class="w-4 h-4 rounded border-gray-600 bg-gray-700 text-green-500 focus:ring-green-500">
                            <label for="sync_mods" class="text-sm text-gray-300">Sync mods from workshop after adding</label>
                        </div>
                    </div>
                    <div class="flex justify-end gap-3 mt-6">
                        <button type="button" onclick="document.getElementById('add-server-modal').classList.add('hidden')"
                            class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition">
                            Cancel
                        </button>
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition">
                            Add Server
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    {{-- Servers Grid --}}
    <div class="grid gap-4">
        @forelse($servers as $server)
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
            <div class="flex flex-col lg:flex-row lg:items-center justify-between gap-4">
                <div class="flex-1">
                    <div class="flex items-center gap-3 mb-2">
                        <span class="relative flex h-3 w-3">
                            <span class="animate-ping absolute inline-flex h-full w-full rounded-full {{ $server->status === 'online' ? 'bg-green-500' : 'bg-red-500' }} opacity-75"></span>
                            <span class="relative inline-flex rounded-full h-3 w-3 {{ $server->status === 'online' ? 'bg-green-500' : 'bg-red-500' }}"></span>
                        </span>
                        <h2 class="text-lg font-bold text-white">{{ $server->name }}</h2>
                    </div>
                    <p class="text-sm text-gray-400 mb-2">{{ $server->ip }}:{{ $server->port }}</p>
                    <div class="flex flex-wrap gap-2">
                        <span class="px-2 py-1 text-xs bg-gray-700 text-gray-300 rounded">
                            @if($server->battlemetrics_id)
                                BattleMetrics ID: {{ $server->battlemetrics_id }}
                            @else
                                <span class="text-yellow-400">Local Server (No BattleMetrics)</span>
                            @endif
                        </span>
                        @if($server->game_version)
                        <span class="px-2 py-1 text-xs bg-green-500/20 text-green-400 rounded">
                            v{{ $server->game_version }}
                        </span>
                        @endif
                    </div>
                </div>

                <div class="grid grid-cols-4 gap-4 text-center">
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <p class="text-xl font-bold text-white">{{ $server->players }}</p>
                        <p class="text-xs text-gray-500">Players</p>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <p class="text-xl font-bold text-white">{{ $server->sessions_count }}</p>
                        <p class="text-xs text-gray-500">Sessions</p>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <p class="text-xl font-bold text-white">{{ number_format($server->statistics_count) }}</p>
                        <p class="text-xs text-gray-500">Stats</p>
                    </div>
                    <div class="bg-gray-700/50 rounded-lg p-3">
                        <p class="text-xl font-bold text-white">{{ $server->mods_count }}</p>
                        <p class="text-xs text-gray-500">Mods</p>
                    </div>
                </div>

                <div class="flex flex-col gap-2">
                    @if($server->battlemetrics_id)
                        <a href="{{ route('servers.show', $server->battlemetrics_id) }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm rounded-lg transition text-center">
                            View Details
                        </a>
                    @else
                        <span class="px-4 py-2 bg-gray-600 text-gray-400 text-sm rounded-lg text-center cursor-not-allowed" title="No BattleMetrics ID">
                            View Details
                        </span>
                    @endif
                    @if($server->battlemetrics_id)
                        <form action="{{ route('admin.servers.sync-mods', $server) }}" method="POST">
                            @csrf
                            <button type="submit" class="w-full px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white text-sm rounded-lg transition">
                                Sync Mods
                            </button>
                        </form>
                        <a href="{{ route('servers.debug', $server->battlemetrics_id) }}" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white text-sm rounded-lg transition text-center">
                            Debug API
                        </a>
                    @endif
                    <form action="{{ route('admin.servers.destroy', $server) }}" method="POST" onsubmit="return confirm('Are you sure you want to remove this server? This will also delete all statistics and sessions.')">
                        @csrf
                        @method('DELETE')
                        <button type="submit" class="w-full px-4 py-2 bg-red-600/20 hover:bg-red-600 text-red-400 hover:text-white text-sm rounded-lg transition">
                            Remove
                        </button>
                    </form>
                </div>
            </div>

            @if($server->last_updated_at)
            <div class="mt-4 pt-4 border-t border-gray-700 text-xs text-gray-500">
                Last updated: {{ $server->last_updated_at->diffForHumans() }}
            </div>
            @endif
        </div>
        @empty
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-8 text-center">
            <svg class="w-12 h-12 mx-auto text-gray-600 mb-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
            </svg>
            <p class="text-gray-400 font-medium">No servers tracked yet</p>
            <p class="text-sm text-gray-500 mt-2">Click "Add Server" to start tracking an Arma Reforger server.</p>
            <button onclick="document.getElementById('add-server-modal').classList.remove('hidden')" class="mt-4 px-4 py-2 bg-green-600 hover:bg-green-500 text-white text-sm font-medium rounded-lg transition inline-flex items-center gap-2">
                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Add Server
            </button>
        </div>
        @endforelse
    </div>
</div>
@endsection
