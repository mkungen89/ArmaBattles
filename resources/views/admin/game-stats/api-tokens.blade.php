@extends('admin.layout')

@section('title', 'API Tokens - Game Statistics')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.game-stats.index') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold text-white">API Tokens</h1>
    </div>

    {{-- New Token Alert --}}
    @if(session('new_token'))
    <div class="bg-green-500/20 border border-green-500/50 rounded-xl p-6">
        <div class="flex items-start gap-4">
            <div class="p-2 bg-green-500/20 rounded-lg">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                </svg>
            </div>
            <div class="flex-1">
                <h3 class="text-lg font-semibold text-green-400 mb-2">New API Token Generated</h3>
                @if(session('token_type'))
                <div class="flex items-center gap-2 mb-3">
                    <span class="text-sm text-gray-400">Type:</span>
                    <span class="px-2 py-1 bg-{{ session('token_type') === 'premium' ? 'purple' : (session('token_type') === 'high-volume' ? 'blue' : 'gray') }}-500/20 text-{{ session('token_type') === 'premium' ? 'purple' : (session('token_type') === 'high-volume' ? 'blue' : 'gray') }}-400 text-xs font-medium rounded uppercase">{{ session('token_type') }}</span>
                    <span class="text-sm text-gray-400">Rate Limit:</span>
                    <span class="text-sm text-white font-medium">{{ session('rate_limit', 60) }} requests/minute</span>
                </div>
                @endif
                <p class="text-sm text-gray-300 mb-4">Make sure to copy this token now. You won't be able to see it again!</p>
                <div class="bg-gray-900/50 rounded-lg p-4">
                    <code class="text-green-400 break-all select-all">{{ session('new_token') }}</code>
                </div>
                <div class="mt-4">
                    <p class="text-sm text-gray-400">Use this header in your API requests:</p>
                    <code class="text-sm text-gray-300">Authorization: Bearer {{ session('new_token') }}</code>
                </div>
            </div>
        </div>
    </div>
    @endif

    {{-- Generate New Token --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Generate New Token</h2>
        <form action="{{ route('admin.game-stats.generate-token') }}" method="POST" class="space-y-4">
            @csrf
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Token Name (optional)</label>
                    <input type="text" name="token_name" placeholder="e.g., Production Server" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                </div>
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Token Type</label>
                    <select name="token_type" class="w-full bg-gray-700 border border-gray-600 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                        <option value="standard">Standard (60 req/min)</option>
                        <option value="high-volume">High Volume (180 req/min)</option>
                        <option value="premium">Premium (300 req/min)</option>
                    </select>
                </div>
            </div>
            <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition flex items-center gap-2">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                </svg>
                Generate Token
            </button>
        </form>

        {{-- Token Type Info --}}
        <div class="mt-6 pt-6 border-t border-gray-700">
            <h3 class="text-sm font-semibold text-white mb-3">Token Types</h3>
            <div class="space-y-2 text-sm">
                <div class="flex items-start gap-2">
                    <span class="px-2 py-1 bg-gray-500/20 text-gray-400 text-xs font-medium rounded uppercase mt-0.5">Standard</span>
                    <span class="text-gray-400">60 requests/minute - Suitable for development and low-traffic servers</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="px-2 py-1 bg-blue-500/20 text-blue-400 text-xs font-medium rounded uppercase mt-0.5">High Volume</span>
                    <span class="text-gray-400">180 requests/minute - For production servers with moderate traffic</span>
                </div>
                <div class="flex items-start gap-2">
                    <span class="px-2 py-1 bg-purple-500/20 text-purple-400 text-xs font-medium rounded uppercase mt-0.5">Premium</span>
                    <span class="text-gray-400">300 requests/minute - For high-traffic production servers</span>
                </div>
            </div>
        </div>
    </div>

    {{-- API Documentation --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">API Endpoints</h2>

        {{-- Rate Limiting Info --}}
        <div class="mb-6 p-4 bg-blue-500/10 border border-blue-500/30 rounded-lg">
            <h3 class="text-sm font-semibold text-blue-400 mb-2">Rate Limiting</h3>
            <p class="text-sm text-gray-300 mb-2">All API requests include rate limit headers:</p>
            <ul class="text-xs text-gray-400 space-y-1 ml-4">
                <li><code class="text-blue-400">X-RateLimit-Limit</code> - Maximum requests per minute for your token</li>
                <li><code class="text-blue-400">X-RateLimit-Remaining</code> - Remaining requests in current window</li>
                <li><code class="text-blue-400">X-RateLimit-Reset</code> - Unix timestamp when the limit resets</li>
                <li><code class="text-red-400">Retry-After</code> - Seconds to wait if rate limit exceeded (429 response)</li>
            </ul>
        </div>

        <div class="space-y-4">
            <div class="bg-gray-900/50 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-medium rounded">POST</span>
                    <code class="text-white">/api/player-stats</code>
                </div>
                <p class="text-sm text-gray-400 mb-2">Update or create player statistics</p>
                <pre class="text-xs text-gray-300 bg-gray-800 rounded p-2 overflow-x-auto">{
  "player_name": "John_Doe",
  "uuid": "a1b2c3d4-e5f6-7890-abcd-ef1234567890",
  "total_playtime": 7200,
  "last_seen": "2025-02-04T15:30:45.000Z"
}</pre>
            </div>

            <div class="bg-gray-900/50 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-medium rounded">POST</span>
                    <code class="text-white">/api/kills/batch</code>
                </div>
                <p class="text-sm text-gray-400 mb-2">Batch insert kills (timestamps in milliseconds)</p>
                <pre class="text-xs text-gray-300 bg-gray-800 rounded p-2 overflow-x-auto">{
  "data": [
    {
      "killer": "John_Doe",
      "victim": "Jane_Smith",
      "weapon": "M16A2",
      "timestamp": 1738665045000,
      "server_id": 1
    }
  ]
}</pre>
            </div>

            <div class="bg-gray-900/50 rounded-lg p-4">
                <div class="flex items-center gap-2 mb-2">
                    <span class="px-2 py-1 bg-green-500/20 text-green-400 text-xs font-medium rounded">POST</span>
                    <code class="text-white">/api/server-status</code>
                </div>
                <p class="text-sm text-gray-400 mb-2">Record server status (timestamp in milliseconds)</p>
                <pre class="text-xs text-gray-300 bg-gray-800 rounded p-2 overflow-x-auto">{
  "server_id": 1,
  "server_name": "ArmaBattles #1",
  "map": "Everon",
  "players": 32,
  "max_players": 64,
  "ping": 45,
  "timestamp": 1738665045000
}</pre>
            </div>
        </div>
    </div>

    {{-- Existing Tokens --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <div class="px-4 py-3 border-b border-gray-700">
            <h2 class="text-lg font-semibold text-white">Active Tokens</h2>
        </div>
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Name</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Type</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Rate Limit</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Created</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase tracking-wider">Last Used</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase tracking-wider">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @forelse($tokens as $token)
                <tr class="hover:bg-gray-700/30">
                    <td class="px-4 py-3">
                        <span class="text-white font-medium">{{ $token->name }}</span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="px-2 py-1 bg-{{ $token->badge_color }}-500/20 text-{{ $token->badge_color }}-400 text-xs font-medium rounded uppercase">
                            {{ $token->token_type }}
                        </span>
                    </td>
                    <td class="px-4 py-3">
                        <span class="text-sm text-white font-medium">{{ $token->rate_limit }}</span>
                        <span class="text-xs text-gray-400">/min</span>
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        {{ $token->created_at->format('M j, Y H:i') }}
                    </td>
                    <td class="px-4 py-3 text-sm text-gray-400">
                        {{ $token->last_used_at ? $token->last_used_at->diffForHumans() : 'Never' }}
                    </td>
                    <td class="px-4 py-3 text-right">
                        <form action="{{ route('admin.game-stats.revoke-token', $token->id) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure you want to revoke this token?')">
                            @csrf
                            @method('DELETE')
                            <button type="submit" class="px-3 py-1 bg-red-500/20 hover:bg-red-500/30 text-red-400 rounded-lg text-sm transition">
                                Revoke
                            </button>
                        </form>
                    </td>
                </tr>
                @empty
                <tr>
                    <td colspan="6" class="px-4 py-8 text-center text-gray-500">No API tokens generated yet</td>
                </tr>
                @endforelse
            </tbody>
        </table>
    </div>

    {{-- cURL Examples --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-semibold text-white mb-4">Test with cURL</h2>
        <div class="space-y-4">
            <div>
                <p class="text-sm text-gray-400 mb-2">Test player stats endpoint:</p>
                <pre class="text-xs text-gray-300 bg-gray-900/50 rounded p-3 overflow-x-auto">curl -X POST {{ url('/api/player-stats') }} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"player_name":"Test","uuid":"test-uuid","total_playtime":100,"last_seen":"2025-02-04T10:00:00.000Z"}'</pre>
            </div>
            <div>
                <p class="text-sm text-gray-400 mb-2">Test kills batch endpoint:</p>
                <pre class="text-xs text-gray-300 bg-gray-900/50 rounded p-3 overflow-x-auto">curl -X POST {{ url('/api/kills/batch') }} \
  -H "Authorization: Bearer YOUR_TOKEN" \
  -H "Content-Type: application/json" \
  -d '{"data":[{"killer":"Player1","victim":"Player2","weapon":"M16A2","timestamp":1738665045000,"server_id":1}]}'</pre>
            </div>
        </div>
    </div>
</div>
@endsection
