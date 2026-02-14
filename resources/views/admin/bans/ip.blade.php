@extends('admin.layout')

@section('admin-title', 'IP Address Ban')

@section('admin-content')
<div class="max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold text-white">IP Address Ban</h1>

    <div class="bg-yellow-900/20 border border-yellow-900 rounded-lg p-4">
        <p class="text-yellow-400 text-sm">
            <strong>Warning:</strong> IP bans can affect multiple users sharing the same network (VPN, school, office). Use with caution.
        </p>
    </div>

    <div class="bg-gray-800 rounded-lg p-6">
        <form action="{{ route('admin.bans.ip.ban') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">IP Address or Range</label>
                <input type="text" name="ip_address" required
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                       placeholder="e.g., 192.168.1.100 or 192.168.1.0/24">
                <p class="text-xs text-gray-500 mt-1">You can specify a single IP or a CIDR range</p>
                @error('ip_address')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Reason</label>
                <textarea name="reason" rows="4" required
                          class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                          placeholder="Explain why this IP is being banned..."></textarea>
                @error('reason')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="flex items-center gap-2">
                    <input type="checkbox" name="permanent" value="1" class="w-4 h-4 text-green-600">
                    <span class="text-sm text-gray-300">Permanent ban (otherwise 30 days)</span>
                </label>
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.bans.index') }}" class="text-gray-400 hover:text-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">
                    Ban IP Address
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
