@extends('admin.layout')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">RCON Console</h1>
            <p class="text-gray-400 mt-1">Manage your game server remotely</p>
        </div>
        <div id="connection-status" class="flex items-center gap-2 px-4 py-2 rounded-lg bg-gray-800/50 border border-gray-700">
            <span class="w-3 h-3 rounded-full bg-gray-500" id="status-indicator"></span>
            <span class="text-gray-400" id="status-text">Checking...</span>
        </div>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-3 gap-6">
        {{-- Left Column: Players & Commands --}}
        <div class="lg:col-span-2 space-y-6">
            {{-- Online Players --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <div class="flex items-center justify-between mb-4">
                    <h2 class="text-lg font-semibold text-white flex items-center gap-2">
                        <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Online Players
                        <span id="player-count" class="text-sm text-gray-500">(0)</span>
                    </h2>
                    <button onclick="refreshPlayers()" class="px-3 py-1 text-sm bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                        Refresh
                    </button>
                </div>
                <div id="players-list" class="space-y-2">
                    <p class="text-gray-500 text-center py-4">Loading players...</p>
                </div>
            </div>

            {{-- Command Console --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                    </svg>
                    Command Console
                </h2>
                <div id="console-output" class="bg-gray-900 rounded-lg p-4 h-64 overflow-y-auto font-mono text-sm text-gray-300 mb-4">
                    <p class="text-gray-500">Welcome to RCON Console. Type a command below.</p>
                </div>
                <form id="command-form" class="flex gap-2">
                    <input type="text" id="command-input" placeholder="Enter RCON command..."
                           class="flex-1 px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500">
                    <button type="submit" class="px-6 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                        Send
                    </button>
                </form>
            </div>
        </div>

        {{-- Right Column: Quick Actions --}}
        <div class="space-y-6">
            {{-- Broadcast Message --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                    </svg>
                    Broadcast Message
                </h2>
                <form id="broadcast-form">
                    <textarea id="broadcast-message" rows="3" placeholder="Message to all players..."
                              class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500 mb-3"></textarea>
                    <button type="submit" class="w-full px-4 py-2 bg-yellow-600 hover:bg-yellow-700 text-white rounded-lg transition">
                        Send Broadcast
                    </button>
                </form>
            </div>

            {{-- Quick Commands --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Quick Commands</h2>
                <div class="space-y-2">
                    <button onclick="quickCommand('players')" class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition text-left">
                        List Players
                    </button>
                    <button onclick="quickCommand('bans')" class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition text-left">
                        List Bans
                    </button>
                    <button onclick="quickCommand('admins')" class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition text-left">
                        List Admins
                    </button>
                    <button onclick="quickCommand('missions')" class="w-full px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition text-left">
                        List Missions
                    </button>
                </div>
            </div>

            {{-- Server Info --}}
            <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-white mb-4">Server Info</h2>
                <div id="server-info" class="space-y-2 text-sm">
                    <p class="text-gray-500">Loading...</p>
                </div>
            </div>
        </div>
    </div>

    {{-- Kick/Ban Modal --}}
    <div id="action-modal" class="fixed inset-0 bg-black/50 hidden items-center justify-center z-50">
        <div class="bg-gray-800 border border-gray-700 rounded-xl p-6 w-full max-w-md">
            <h3 id="modal-title" class="text-lg font-semibold text-white mb-4">Action</h3>
            <form id="action-form">
                <input type="hidden" id="action-type">
                <input type="hidden" id="action-player-id">
                <div class="mb-4">
                    <label class="block text-sm text-gray-400 mb-2">Player</label>
                    <input type="text" id="action-player-name" readonly class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white">
                </div>
                <div class="mb-4">
                    <label class="block text-sm text-gray-400 mb-2">Reason</label>
                    <input type="text" id="action-reason" placeholder="Enter reason..." class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white placeholder-gray-500 focus:outline-none focus:border-green-500">
                </div>
                <div id="ban-duration-field" class="mb-4 hidden">
                    <label class="block text-sm text-gray-400 mb-2">Duration (minutes, 0 = permanent)</label>
                    <input type="number" id="action-duration" value="0" min="0" class="w-full px-4 py-2 bg-gray-900 border border-gray-700 rounded-lg text-white focus:outline-none focus:border-green-500">
                </div>
                <div class="flex gap-3">
                    <button type="button" onclick="closeModal()" class="flex-1 px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition">
                        Cancel
                    </button>
                    <button type="submit" id="modal-submit" class="flex-1 px-4 py-2 bg-red-600 hover:bg-red-700 text-white rounded-lg transition">
                        Confirm
                    </button>
                </div>
            </form>
        </div>
    </div>
</div>

@push('scripts')
<script>
const csrfToken = '{{ csrf_token() }}';
const routes = {
    status: '{{ route("admin.rcon.status") }}',
    players: '{{ route("admin.rcon.players") }}',
    command: '{{ route("admin.rcon.command") }}',
    say: '{{ route("admin.rcon.say") }}',
    kick: '{{ route("admin.rcon.kick") }}',
    ban: '{{ route("admin.rcon.ban") }}'
};

async function checkStatus() {
    try {
        const response = await fetch(routes.status);
        const data = await response.json();

        const indicator = document.getElementById('status-indicator');
        const text = document.getElementById('status-text');
        const serverInfo = document.getElementById('server-info');

        if (data.connected) {
            indicator.className = 'w-3 h-3 rounded-full bg-green-500';
            text.textContent = 'Connected';
            text.className = 'text-green-400';

            serverInfo.innerHTML = `
                <div class="flex justify-between"><span class="text-gray-500">Server ID:</span><span class="text-white">${data.server.id}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Name:</span><span class="text-white">${data.server.name}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">Host:</span><span class="text-white">${data.server.host}</span></div>
                <div class="flex justify-between"><span class="text-gray-500">RCON Port:</span><span class="text-white">${data.server.rconPort}</span></div>
            `;
        } else {
            indicator.className = 'w-3 h-3 rounded-full bg-red-500';
            text.textContent = 'Disconnected';
            text.className = 'text-red-400';
        }
    } catch (error) {
        console.error('Status check failed:', error);
    }
}

async function refreshPlayers() {
    try {
        const response = await fetch(routes.players);
        const data = await response.json();

        const container = document.getElementById('players-list');
        const countEl = document.getElementById('player-count');

        if (data.players && data.players.length > 0) {
            countEl.textContent = `(${data.players.length})`;
            container.innerHTML = data.players.map(player => `
                <div class="flex items-center justify-between p-3 bg-gray-900 rounded-lg">
                    <div>
                        <span class="text-white font-medium">${escapeHtml(player.name)}</span>
                        <span class="text-gray-500 text-sm ml-2">ID: ${player.id}</span>
                    </div>
                    <div class="flex gap-2">
                        <button onclick="openKickModal(${player.id}, '${escapeHtml(player.name).replace(/'/g, "\\'")}')" class="px-3 py-1 text-sm bg-yellow-600 hover:bg-yellow-700 text-white rounded transition">
                            Kick
                        </button>
                        <button onclick="openBanModal(${player.id}, '${escapeHtml(player.name).replace(/'/g, "\\'")}')" class="px-3 py-1 text-sm bg-red-600 hover:bg-red-700 text-white rounded transition">
                            Ban
                        </button>
                    </div>
                </div>
            `).join('');
        } else {
            countEl.textContent = '(0)';
            container.innerHTML = '<p class="text-gray-500 text-center py-4">No players online</p>';
        }
    } catch (error) {
        console.error('Failed to refresh players:', error);
    }
}

async function sendCommand(event) {
    event.preventDefault();
    const input = document.getElementById('command-input');
    const command = input.value.trim();
    if (!command) return;

    appendToConsole(`> ${command}`, 'text-green-400');
    input.value = '';

    try {
        const response = await fetch(routes.command, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ command })
        });
        const data = await response.json();

        if (data.response) {
            appendToConsole(data.response);
        } else if (data.error) {
            appendToConsole(`Error: ${data.error}`, 'text-red-400');
        }
    } catch (error) {
        appendToConsole(`Error: ${error.message}`, 'text-red-400');
    }
}

function quickCommand(cmd) {
    document.getElementById('command-input').value = cmd;
    document.getElementById('command-form').dispatchEvent(new Event('submit'));
}

async function broadcastMessage(event) {
    event.preventDefault();
    const textarea = document.getElementById('broadcast-message');
    const message = textarea.value.trim();
    if (!message) return;

    try {
        const response = await fetch(routes.say, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify({ message })
        });
        const data = await response.json();

        if (data.success) {
            appendToConsole(`[BROADCAST] ${message}`, 'text-yellow-400');
            textarea.value = '';
        } else {
            appendToConsole(`Broadcast failed: ${data.error}`, 'text-red-400');
        }
    } catch (error) {
        appendToConsole(`Error: ${error.message}`, 'text-red-400');
    }
}

function openKickModal(playerId, playerName) {
    document.getElementById('modal-title').textContent = 'Kick Player';
    document.getElementById('action-type').value = 'kick';
    document.getElementById('action-player-id').value = playerId;
    document.getElementById('action-player-name').value = playerName;
    document.getElementById('ban-duration-field').classList.add('hidden');
    document.getElementById('modal-submit').textContent = 'Kick';
    document.getElementById('action-modal').classList.remove('hidden');
    document.getElementById('action-modal').classList.add('flex');
}

function openBanModal(playerId, playerName) {
    document.getElementById('modal-title').textContent = 'Ban Player';
    document.getElementById('action-type').value = 'ban';
    document.getElementById('action-player-id').value = playerId;
    document.getElementById('action-player-name').value = playerName;
    document.getElementById('ban-duration-field').classList.remove('hidden');
    document.getElementById('modal-submit').textContent = 'Ban';
    document.getElementById('action-modal').classList.remove('hidden');
    document.getElementById('action-modal').classList.add('flex');
}

function closeModal() {
    document.getElementById('action-modal').classList.add('hidden');
    document.getElementById('action-modal').classList.remove('flex');
    document.getElementById('action-reason').value = '';
    document.getElementById('action-duration').value = '0';
}

async function submitAction(event) {
    event.preventDefault();
    const actionType = document.getElementById('action-type').value;
    const playerId = document.getElementById('action-player-id').value;
    const reason = document.getElementById('action-reason').value;

    const url = actionType === 'kick' ? routes.kick : routes.ban;
    const body = {
        player_id: parseInt(playerId),
        reason: reason || (actionType === 'kick' ? 'Kicked by admin' : 'Banned by admin')
    };

    if (actionType === 'ban') {
        body.minutes = parseInt(document.getElementById('action-duration').value) || 0;
    }

    try {
        const response = await fetch(url, {
            method: 'POST',
            headers: {
                'Content-Type': 'application/json',
                'X-CSRF-TOKEN': csrfToken
            },
            body: JSON.stringify(body)
        });
        const data = await response.json();

        if (data.success) {
            appendToConsole(`[${actionType.toUpperCase()}] Player ${playerId}: ${body.reason}`, 'text-yellow-400');
            closeModal();
            refreshPlayers();
        } else {
            appendToConsole(`${actionType} failed: ${data.error}`, 'text-red-400');
        }
    } catch (error) {
        appendToConsole(`Error: ${error.message}`, 'text-red-400');
    }
}

function appendToConsole(text, className = 'text-gray-300') {
    const consoleEl = document.getElementById('console-output');
    const line = document.createElement('p');
    line.className = className;
    line.textContent = text;
    consoleEl.appendChild(line);
    consoleEl.scrollTop = consoleEl.scrollHeight;
}

function escapeHtml(text) {
    const div = document.createElement('div');
    div.textContent = text;
    return div.innerHTML;
}

document.addEventListener('DOMContentLoaded', function() {
    checkStatus();
    refreshPlayers();

    document.getElementById('command-form').addEventListener('submit', sendCommand);
    document.getElementById('broadcast-form').addEventListener('submit', broadcastMessage);
    document.getElementById('action-form').addEventListener('submit', submitAction);

    setInterval(checkStatus, 30000);
    setInterval(refreshPlayers, 30000);
});
</script>
@endpush
@endsection
