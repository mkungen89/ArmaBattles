@extends('admin.layout')

@section('admin-title', 'Edit Announcement')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('admin.announcements.index') }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-lg transition">
            <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/></svg>
        </a>
        <h1 class="text-2xl font-bold text-white">Edit Announcement</h1>
    </div>

    <div class="glass-card rounded-xl p-6">
        <form action="{{ route('admin.announcements.update', $announcement) }}" method="POST" class="space-y-6">
            @csrf
            @method('PUT')

            {{-- Title (Optional) --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Title <span class="text-gray-500">(optional, for admin reference)</span>
                </label>
                <input
                    type="text"
                    name="title"
                    value="{{ old('title', $announcement->title) }}"
                    placeholder="e.g., Server Maintenance Notice"
                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-green-500"
                >
                @error('title')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Message --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Message <span class="text-red-400">*</span>
                </label>
                <textarea
                    name="message"
                    rows="3"
                    required
                    placeholder="This will be displayed to all users at the top of the site..."
                    class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-green-500"
                >{{ old('message', $announcement->message) }}</textarea>
                @error('message')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                <p class="mt-1 text-xs text-gray-500">Maximum 1000 characters</p>
            </div>

            {{-- Type --}}
            <div>
                <label class="block text-sm font-medium text-gray-300 mb-2">
                    Type <span class="text-red-400">*</span>
                </label>
                <div class="grid grid-cols-2 md:grid-cols-4 gap-3">
                    <label class="relative flex items-center gap-3 px-4 py-3 bg-blue-500/10 border border-blue-500/30 rounded-xl cursor-pointer hover:bg-blue-500/20 transition has-[:checked]:border-blue-500 has-[:checked]:bg-blue-500/20">
                        <input type="radio" name="type" value="info" {{ old('type', $announcement->type) === 'info' ? 'checked' : '' }} required class="text-blue-500 focus:ring-blue-500">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-blue-400">Info</span>
                            <p class="text-xs text-gray-400 mt-0.5">General information</p>
                        </div>
                    </label>

                    <label class="relative flex items-center gap-3 px-4 py-3 bg-yellow-500/10 border border-yellow-500/30 rounded-xl cursor-pointer hover:bg-yellow-500/20 transition has-[:checked]:border-yellow-500 has-[:checked]:bg-yellow-500/20">
                        <input type="radio" name="type" value="warning" {{ old('type', $announcement->type) === 'warning' ? 'checked' : '' }} required class="text-yellow-500 focus:ring-yellow-500">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-yellow-400">Warning</span>
                            <p class="text-xs text-gray-400 mt-0.5">Important notice</p>
                        </div>
                    </label>

                    <label class="relative flex items-center gap-3 px-4 py-3 bg-green-500/10 border border-green-500/30 rounded-xl cursor-pointer hover:bg-green-500/20 transition has-[:checked]:border-green-500 has-[:checked]:bg-green-500/20">
                        <input type="radio" name="type" value="success" {{ old('type', $announcement->type) === 'success' ? 'checked' : '' }} required class="text-green-500 focus:ring-green-500">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-green-400">Success</span>
                            <p class="text-xs text-gray-400 mt-0.5">Good news</p>
                        </div>
                    </label>

                    <label class="relative flex items-center gap-3 px-4 py-3 bg-red-500/10 border border-red-500/30 rounded-xl cursor-pointer hover:bg-red-500/20 transition has-[:checked]:border-red-500 has-[:checked]:bg-red-500/20">
                        <input type="radio" name="type" value="danger" {{ old('type', $announcement->type) === 'danger' ? 'checked' : '' }} required class="text-red-500 focus:ring-red-500">
                        <div class="flex-1">
                            <span class="text-sm font-medium text-red-400">Danger</span>
                            <p class="text-xs text-gray-400 mt-0.5">Critical alert</p>
                        </div>
                    </label>
                </div>
                @error('type')
                <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            {{-- Schedule --}}
            <div class="grid grid-cols-1 md:grid-cols-2 gap-4">
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        Start Date & Time <span class="text-gray-500">(optional)</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="starts_at"
                        value="{{ old('starts_at', $announcement->starts_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-green-500"
                    >
                    @error('starts_at')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Leave empty to show immediately</p>
                </div>

                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">
                        End Date & Time <span class="text-gray-500">(optional)</span>
                    </label>
                    <input
                        type="datetime-local"
                        name="ends_at"
                        value="{{ old('ends_at', $announcement->ends_at?->format('Y-m-d\TH:i')) }}"
                        class="w-full bg-white/5 border border-white/10 text-white rounded-xl px-4 py-2 focus:outline-none focus:border-green-500"
                    >
                    @error('ends_at')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                    @enderror
                    <p class="mt-1 text-xs text-gray-500">Leave empty to show indefinitely</p>
                </div>
            </div>

            {{-- Active Toggle --}}
            <div>
                <label class="flex items-center gap-3 cursor-pointer">
                    <input
                        type="checkbox"
                        name="is_active"
                        value="1"
                        {{ old('is_active', $announcement->is_active) ? 'checked' : '' }}
                        class="w-5 h-5 rounded bg-white/5 border-white/20 text-green-500 focus:ring-green-500 focus:ring-offset-0"
                    >
                    <div>
                        <span class="text-sm font-medium text-white">Active</span>
                        <p class="text-xs text-gray-500">Uncheck to save as draft without displaying</p>
                    </div>
                </label>
            </div>

            {{-- Submit Buttons --}}
            <div class="flex gap-3 pt-4 border-t border-white/10">
                <button
                    type="submit"
                    class="px-6 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium"
                >
                    Update Announcement
                </button>
                <a
                    href="{{ route('admin.announcements.index') }}"
                    class="px-6 py-2.5 bg-white/5 hover:bg-white/10 text-white rounded-xl transition font-medium"
                >
                    Cancel
                </a>
            </div>
        </form>
    </div>
</div>
@endsection
