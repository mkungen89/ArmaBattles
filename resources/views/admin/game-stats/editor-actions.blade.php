@extends('admin.layout')

@section('title', 'Editor Actions - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div class="flex items-center gap-4">
            <a href="{{ auth()->user()->isAdmin() ? route('admin.game-stats.index') : route('gm.editor-actions') }}" class="text-gray-400 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
                </svg>
            </a>
            <h1 class="text-2xl font-bold text-white">Editor Actions</h1>
            <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs rounded">GM/Admin Only</span>
        </div>
        <span class="text-gray-400">{{ $actions->total() }} actions</span>
    </div>

    {{-- Filters --}}
    <div class="glass-card rounded-xl p-4">
        <form action="{{ route('gm.editor-actions') }}" method="GET" class="flex flex-wrap gap-4">
            <div class="flex-1 min-w-[200px]">
                <input type="text" name="search" value="{{ request('search') }}" placeholder="Search player or action..." class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-purple-500 focus:border-purple-500">
            </div>
            <select name="action" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">All Actions</option>
                @foreach($actionTypes as $actionType)
                <option value="{{ $actionType }}" {{ request('action') == $actionType ? 'selected' : '' }}>{{ $actionType }}</option>
                @endforeach
            </select>
            <select name="server_id" class="bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-purple-500 focus:border-purple-500">
                <option value="">All Servers</option>
                @foreach($serverIds as $serverId)
                <option value="{{ $serverId }}" {{ request('server_id') == $serverId ? 'selected' : '' }}>Server #{{ $serverId }}</option>
                @endforeach
            </select>
            <button type="submit" class="px-4 py-2 bg-purple-600 hover:bg-purple-500 text-white rounded-xl transition">
                Filter
            </button>
            @if(request()->hasAny(['search', 'action', 'server_id']))
            <a href="{{ route('gm.editor-actions') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                Clear
            </a>
            @endif
        </form>
    </div>

    {{-- Editor Actions Table --}}
    <div class="glass-card rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-white/3">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Time</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Player</th>
                    <th class="px-4 py-3 text-center text-xs font-medium text-gray-400 uppercase tracking-wider">Action</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Target Entity</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Server</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-white/5">
                @forelse($actions as $action)
                @php
                    $occurredAt = $action->occurred_at ? \Carbon\Carbon::parse($action->occurred_at) : null;
                @endphp
                <tr class="hover:bg-white/5">
                    <td class="px-4 py-3">
                        @if($occurredAt)
                        <div class="text-sm text-white">{{ $occurredAt->format('M j, Y') }}</div>
                        <div class="text-xs text-gray-400">{{ $occurredAt->format('H:i:s') }}</div>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-purple-400 font-medium">{{ $action->player_name }}</span>
                        @if($action->player_uuid)
                        <div class="text-xs text-gray-500 truncate max-w-[150px]">{{ $action->player_uuid }}</div>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-center">
                        @php
                            $actionColors = [
                                'SPAWN_ENTITY' => 'bg-green-500/20 text-green-400',
                                'DELETE_ENTITY' => 'bg-red-500/20 text-red-400',
                                'TELEPORT' => 'bg-blue-500/20 text-blue-400',
                                'HEAL' => 'bg-pink-500/20 text-pink-400',
                                'KILL' => 'bg-red-500/20 text-red-400',
                            ];
                            $colorClass = $actionColors[$action->action] ?? 'bg-gray-500/20 text-gray-400';
                        @endphp
                        <span class="px-2 py-1 {{ $colorClass }} text-xs rounded">{{ $action->action }}</span>
                    </td>
                    <td class="px-4 py-3">
                        @if($action->hovered_entity_component_name)
                        <span class="text-white text-sm">{{ $action->hovered_entity_component_name }}</span>
                        @if($action->hovered_entity_component_owner_id)
                        <div class="text-xs text-gray-500">ID: {{ $action->hovered_entity_component_owner_id }}</div>
                        @endif
                        @elseif($action->selected_entity_components_names)
                        <span class="text-white text-sm truncate max-w-[200px] block">{{ $action->selected_entity_components_names }}</span>
                        @else
                        <span class="text-gray-500">-</span>
                        @endif
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        Server #{{ $action->server_id }}
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="5" class="px-4 py-8 text-center text-gray-500">No editor actions found</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- Pagination --}}
    @if($actions->hasPages())
    <div class="flex justify-center">
        {{ $actions->withQueryString()->links() }}
    </div>
    @endif
</div>
@endsection
