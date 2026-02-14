@extends('admin.layout')

@section('admin-title', 'Hardware ID Ban')

@section('admin-content')
<div class="max-w-3xl space-y-6">
    <h1 class="text-2xl font-bold text-white">Hardware ID Ban</h1>

    <div class="bg-yellow-900/20 border border-yellow-900 rounded-lg p-4">
        <p class="text-yellow-400 text-sm">
            <strong>Warning:</strong> Hardware bans are persistent across accounts. Use this only for severe cases like repeated cheating or ban evasion.
        </p>
    </div>

    <div class="bg-gray-800 rounded-lg p-6">
        <form action="{{ route('admin.bans.hardware.ban') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Hardware ID</label>
                <input type="text" name="hardware_id" required
                       class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                       placeholder="Enter hardware ID to ban...">
                @error('hardware_id')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="mb-6">
                <label class="block text-sm font-medium text-gray-300 mb-2">Reason</label>
                <textarea name="reason" rows="4" required
                          class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white"
                          placeholder="Explain why this hardware ID is being banned..."></textarea>
                @error('reason')<p class="text-red-400 text-sm mt-1">{{ $message }}</p>@enderror
            </div>

            <div class="flex justify-between">
                <a href="{{ route('admin.bans.index') }}" class="text-gray-400 hover:text-gray-300">Cancel</a>
                <button type="submit" class="px-6 py-3 bg-red-600 hover:bg-red-700 text-white font-semibold rounded-lg">
                    Ban Hardware ID
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
