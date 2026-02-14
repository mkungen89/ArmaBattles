@extends('admin.layout')

@section('admin-title', 'Player Warnings')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Player Warnings</h1>
        <div class="flex gap-3">
            <a href="{{ route('admin.moderation.warnings', ['active_only' => 1]) }}"
               class="px-4 py-2 {{ request('active_only') ? 'bg-orange-600' : 'bg-gray-700' }} text-white rounded-lg">
                Active Only
            </a>
            <select onchange="window.location.href='?severity='+this.value" class="bg-gray-700 px-4 py-2 rounded-lg text-white">
                <option value="">All Severities</option>
                <option value="low" {{ request('severity') === 'low' ? 'selected' : '' }}>Low</option>
                <option value="medium" {{ request('severity') === 'medium' ? 'selected' : '' }}>Medium</option>
                <option value="high" {{ request('severity') === 'high' ? 'selected' : '' }}>High</option>
                <option value="critical" {{ request('severity') === 'critical' ? 'selected' : '' }}>Critical</option>
            </select>
        </div>
    </div>

    @if($warnings->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <p class="text-gray-400">No warnings found</p>
        </div>
    @else
        <div class="bg-gray-800 rounded-lg overflow-hidden">
            <table class="w-full">
                <thead>
                    <tr class="text-left text-sm text-gray-400 bg-gray-900">
                        <th class="p-4">User</th>
                        <th class="p-4">Type</th>
                        <th class="p-4">Severity</th>
                        <th class="p-4">Reason</th>
                        <th class="p-4">Moderator</th>
                        <th class="p-4">Date</th>
                        <th class="p-4">Expires</th>
                    </tr>
                </thead>
                <tbody>
                    @foreach($warnings as $warning)
                        <tr class="border-t border-gray-700">
                            <td class="p-4">
                                <div class="flex items-center gap-2">
                                    <img src="{{ $warning->user->avatar }}" class="w-8 h-8 rounded-full" alt="">
                                    <div>
                                        <p class="text-white">{{ $warning->user->name }}</p>
                                        <p class="text-xs text-gray-500">{{ $warning->user->warnings()->active()->count() }} active</p>
                                    </div>
                                </div>
                            </td>
                            <td class="p-4 text-gray-300">{{ ucfirst(str_replace('_', ' ', $warning->warning_type)) }}</td>
                            <td class="p-4">
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $warning->severity === 'low' ? 'bg-blue-900 text-blue-300' : '' }}
                                    {{ $warning->severity === 'medium' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                    {{ $warning->severity === 'high' ? 'bg-orange-900 text-orange-300' : '' }}
                                    {{ $warning->severity === 'critical' ? 'bg-red-900 text-red-300' : '' }}">
                                    {{ ucfirst($warning->severity) }}
                                </span>
                            </td>
                            <td class="p-4 text-gray-300 max-w-md truncate">{{ $warning->reason }}</td>
                            <td class="p-4 text-gray-300">{{ $warning->moderator->name }}</td>
                            <td class="p-4 text-gray-400 text-sm">{{ $warning->created_at->format('M j, Y') }}</td>
                            <td class="p-4 text-gray-400 text-sm">{{ $warning->expires_at ? $warning->expires_at->format('M j, Y') : 'Never' }}</td>
                        </tr>
                    @endforeach
                </tbody>
            </table>
        </div>
        {{ $warnings->links() }}
    @endif
</div>
@endsection
