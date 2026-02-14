@extends('layouts.app')

@section('title', 'Submit Ban Appeal')

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white mb-2">Submit Ban Appeal</h1>
        <p class="text-gray-400">Explain why you believe your ban should be reconsidered</p>
    </div>

    <div class="bg-red-900/20 border border-red-900 rounded-lg p-4 mb-6">
        <div class="flex items-start gap-3">
            <i data-lucide="alert-circle" class="w-5 h-5 text-red-400 mt-0.5"></i>
            <div>
                <h3 class="text-red-400 font-semibold mb-1">You are currently banned</h3>
                <p class="text-sm text-gray-300 mb-2">
                    <strong>Reason:</strong> {{ auth()->user()->ban_reason ?? 'No reason provided' }}
                </p>
                <p class="text-sm text-gray-300">
                    <strong>Banned on:</strong> {{ auth()->user()->banned_at->format('M j, Y \a\t g:i A') }}
                </p>
                @if(auth()->user()->banned_until)
                    <p class="text-sm text-gray-300">
                        <strong>Expires:</strong> {{ auth()->user()->banned_until->format('M j, Y \a\t g:i A') }}
                    </p>
                @else
                    <p class="text-sm text-red-300">
                        <strong>Type:</strong> Permanent Ban
                    </p>
                @endif
            </div>
        </div>
    </div>

    <div class="bg-gray-800 rounded-lg p-6">
        <form action="{{ route('ban-appeals.store') }}" method="POST">
            @csrf

            <div class="mb-6">
                <label for="appeal_message" class="block text-sm font-medium text-gray-300 mb-2">
                    Appeal Message <span class="text-red-400">*</span>
                </label>
                <p class="text-xs text-gray-500 mb-2">
                    Explain your situation clearly and honestly. Minimum 50 characters.
                </p>
                <textarea
                    id="appeal_message"
                    name="appeal_message"
                    rows="8"
                    class="w-full bg-gray-900 border border-gray-700 rounded-lg px-4 py-3 text-white placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent"
                    placeholder="Explain why you believe your ban should be reconsidered. Be honest and respectful."
                    required
                >{{ old('appeal_message') }}</textarea>
                @error('appeal_message')
                    <p class="text-red-400 text-sm mt-1">{{ $message }}</p>
                @enderror
                <p class="text-xs text-gray-500 mt-1" x-data="{ count: 0 }">
                    <span x-text="count"></span> characters (minimum 50)
                    <script>
                        document.getElementById('appeal_message').addEventListener('input', function() {
                            Alpine.store('count', this.value.length);
                        });
                    </script>
                </p>
            </div>

            <div class="bg-blue-900/20 border border-blue-900 rounded-lg p-4 mb-6">
                <div class="flex items-start gap-3">
                    <i data-lucide="info" class="w-5 h-5 text-blue-400 mt-0.5"></i>
                    <div class="text-sm text-gray-300 space-y-1">
                        <p><strong>Appeal Tips:</strong></p>
                        <ul class="list-disc list-inside space-y-1 text-gray-400">
                            <li>Be honest about what happened</li>
                            <li>Take responsibility if you made a mistake</li>
                            <li>Explain what you've learned</li>
                            <li>Show that you understand the rules</li>
                            <li>Avoid making excuses or blaming others</li>
                        </ul>
                    </div>
                </div>
            </div>

            <div class="flex items-center justify-between">
                <a href="{{ route('profile.show') }}" class="text-gray-400 hover:text-gray-300 transition">
                    Cancel
                </a>
                <button type="submit" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                    Submit Appeal
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
