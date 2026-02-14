@extends('layouts.app')

@section('title', 'Ban Appeal #' . $appeal->id)

@section('content')
<div class="max-w-3xl mx-auto px-4 py-8">
    <div class="mb-6">
        <div class="flex items-center gap-3 mb-2">
            <h1 class="text-3xl font-bold text-white">Appeal #{{ $appeal->id }}</h1>
            <span class="px-3 py-1 rounded-lg text-sm font-medium
                {{ $appeal->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : '' }}
                {{ $appeal->status === 'approved' ? 'bg-green-900 text-green-300' : '' }}
                {{ $appeal->status === 'rejected' ? 'bg-red-900 text-red-300' : '' }}">
                {{ ucfirst($appeal->status) }}
            </span>
        </div>
        <p class="text-gray-400">Submitted {{ $appeal->created_at->format('M j, Y \a\t g:i A') }}</p>
    </div>

    <div class="space-y-6">
        <!-- Original Ban Info -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="shield-alert" class="w-5 h-5"></i>
                Original Ban Information
            </h2>
            <div class="space-y-2">
                <div>
                    <p class="text-xs text-gray-500">Reason:</p>
                    <p class="text-gray-300">{{ $appeal->reason }}</p>
                </div>
                @if(auth()->user()->banned_at)
                    <div>
                        <p class="text-xs text-gray-500">Banned on:</p>
                        <p class="text-gray-300">{{ auth()->user()->banned_at->format('M j, Y \a\t g:i A') }}</p>
                    </div>
                @endif
            </div>
        </div>

        <!-- Appeal Message -->
        <div class="bg-gray-800 rounded-lg p-6">
            <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                <i data-lucide="message-square" class="w-5 h-5"></i>
                Your Appeal
            </h2>
            <p class="text-gray-300 whitespace-pre-wrap">{{ $appeal->appeal_message }}</p>
        </div>

        <!-- Admin Response -->
        @if($appeal->reviewed_at)
            <div class="bg-gray-800 rounded-lg p-6">
                <h2 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <i data-lucide="user-check" class="w-5 h-5"></i>
                    Admin Response
                </h2>
                <div class="mb-4">
                    <p class="text-xs text-gray-500">Reviewed by:</p>
                    <p class="text-gray-300">{{ $appeal->reviewer->name }}</p>
                </div>
                <div class="mb-4">
                    <p class="text-xs text-gray-500">Reviewed on:</p>
                    <p class="text-gray-300">{{ $appeal->reviewed_at->format('M j, Y \a\t g:i A') }}</p>
                </div>
                @if($appeal->admin_response)
                    <div>
                        <p class="text-xs text-gray-500 mb-1">Response:</p>
                        <p class="text-gray-300 whitespace-pre-wrap">{{ $appeal->admin_response }}</p>
                    </div>
                @endif

                @if($appeal->status === 'approved')
                    <div class="mt-4 p-4 bg-green-900/20 border border-green-900 rounded-lg">
                        <p class="text-green-400 font-semibold">✓ Your appeal was approved and you have been unbanned.</p>
                    </div>
                @elseif($appeal->status === 'rejected')
                    <div class="mt-4 p-4 bg-red-900/20 border border-red-900 rounded-lg">
                        <p class="text-red-400 font-semibold">✗ Your appeal was rejected.</p>
                    </div>
                @endif
            </div>
        @else
            <div class="bg-blue-900/20 border border-blue-900 rounded-lg p-6">
                <div class="flex items-center gap-3">
                    <i data-lucide="clock" class="w-5 h-5 text-blue-400"></i>
                    <div>
                        <p class="text-blue-400 font-semibold">Appeal Pending Review</p>
                        <p class="text-sm text-gray-400 mt-1">
                            Your appeal is awaiting review by an administrator. You will be notified when a decision is made.
                        </p>
                    </div>
                </div>
            </div>
        @endif
    </div>

    <div class="mt-8">
        <a href="{{ route('ban-appeals.index') }}" class="text-gray-400 hover:text-gray-300 transition">
            ← Back to My Appeals
        </a>
    </div>
</div>
@endsection
