@extends('layouts.app')

@section('title', 'My Ban Appeals')

@section('content')
<div class="max-w-4xl mx-auto px-4 py-8">
    <div class="mb-6">
        <h1 class="text-3xl font-bold text-white mb-2">My Ban Appeals</h1>
        <p class="text-gray-400">View your submitted ban appeals and their status</p>
    </div>

    @if($appeals->isEmpty())
        <div class="bg-gray-800 rounded-lg p-8 text-center">
            <i data-lucide="file-text" class="w-16 h-16 mx-auto mb-4 text-gray-600"></i>
            <p class="text-gray-400 mb-4">You have no ban appeals</p>
            @if(auth()->user()->is_banned)
                <a href="{{ route('ban-appeals.create') }}" class="inline-flex items-center px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition">
                    <i data-lucide="plus" class="w-4 h-4 mr-2"></i>
                    Submit Appeal
                </a>
            @endif
        </div>
    @else
        <div class="space-y-4">
            @foreach($appeals as $appeal)
                <div class="bg-gray-800 rounded-lg p-6 hover:bg-gray-750 transition">
                    <div class="flex items-start justify-between mb-4">
                        <div>
                            <div class="flex items-center gap-3 mb-2">
                                <h3 class="text-lg font-semibold text-white">Appeal #{{ $appeal->id }}</h3>
                                <span class="px-2 py-1 rounded text-xs font-medium
                                    {{ $appeal->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                    {{ $appeal->status === 'approved' ? 'bg-green-900 text-green-300' : '' }}
                                    {{ $appeal->status === 'rejected' ? 'bg-red-900 text-red-300' : '' }}">
                                    {{ ucfirst($appeal->status) }}
                                </span>
                            </div>
                            <p class="text-sm text-gray-400">
                                Submitted {{ $appeal->created_at->diffForHumans() }}
                            </p>
                        </div>
                        <a href="{{ route('ban-appeals.show', $appeal) }}" class="text-green-400 hover:text-green-300 text-sm font-medium">
                            View Details →
                        </a>
                    </div>

                    <div class="mb-3">
                        <p class="text-xs text-gray-500 mb-1">Original Ban Reason:</p>
                        <p class="text-gray-300">{{ $appeal->reason }}</p>
                    </div>

                    <div>
                        <p class="text-xs text-gray-500 mb-1">Your Appeal:</p>
                        <p class="text-gray-300 line-clamp-2">{{ $appeal->appeal_message }}</p>
                    </div>

                    @if($appeal->reviewed_at)
                        <div class="mt-4 pt-4 border-t border-gray-700">
                            <p class="text-xs text-gray-500 mb-1">
                                Reviewed by {{ $appeal->reviewer->name }} on {{ $appeal->reviewed_at->format('M j, Y') }}
                            </p>
                            @if($appeal->admin_response)
                                <p class="text-sm text-gray-300">{{ $appeal->admin_response }}</p>
                            @endif
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $appeals->links() }}
        </div>
    @endif
</div>
@endsection
