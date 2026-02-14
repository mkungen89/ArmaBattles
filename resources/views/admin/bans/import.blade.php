@extends('admin.layout')

@section('admin-title', 'Mass Ban Import')

@section('admin-content')
<div class="max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold text-white">Mass Ban Import</h1>

    <div class="bg-blue-900/20 border border-blue-900 rounded-lg p-4">
        <h3 class="text-blue-400 font-semibold mb-2">Import Format</h3>
        <p class="text-sm text-gray-300 mb-2">One ban per line in the format:</p>
        <code class="block bg-gray-900 p-2 rounded text-sm text-green-400 mb-2">
            type:value:reason
        </code>
        <p class="text-sm text-gray-300 mb-1"><strong>Types:</strong> steam_id, hardware, ip</p>
        <p class="text-sm text-gray-300"><strong>Example:</strong></p>
        <code class="block bg-gray-900 p-2 rounded text-xs text-gray-400">
            steam_id:76561198012345678:Cheating detected<br>
            hardware:ABC123456789:Ban evasion<br>
            ip:192.168.1.100:Toxic behavior
        </code>
    </div>

    <div class="bg-gray-800 rounded-lg p-6">
        <form action="{{ route('admin.bans.import.process') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Ban Source</label>
                <input type="text" name="source" required
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                       placeholder="e.g., BattleEye Global Banlist, Community Reports">
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Ban List</label>
                <textarea name="ban_list" rows="12" required
                          class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white font-mono text-sm"
                          placeholder="steam_id:76561198012345678:Reason here&#10;hardware:ABC123:Another reason"></textarea>
            </div>

            <button type="submit" class="w-full px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg transition">
                Import Bans
            </button>
        </form>
    </div>
</div>
@endsection
