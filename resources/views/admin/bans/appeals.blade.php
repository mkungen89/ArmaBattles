@extends('admin.layout')

@section('admin-title', 'Ban Appeals')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Ban Appeals</h1>
        <div class="flex items-center gap-3">
            <a href="{{ route('admin.bans.appeals', ['status' => 'pending']) }}"
               class="px-4 py-2 {{ !request('status') || request('status') === 'pending' ? 'bg-yellow-600' : 'bg-gray-700' }} text-white rounded-lg hover:opacity-90 transition">
                Pending
            </a>
            <a href="{{ route('admin.bans.appeals', ['status' => 'approved']) }}"
               class="px-4 py-2 {{ request('status') === 'approved' ? 'bg-green-600' : 'bg-gray-700' }} text-white rounded-lg hover:opacity-90 transition">
                Approved
            </a>
            <a href="{{ route('admin.bans.appeals', ['status' => 'rejected']) }}"
               class="px-4 py-2 {{ request('status') === 'rejected' ? 'bg-red-600' : 'bg-gray-700' }} text-white rounded-lg hover:opacity-90 transition">
                Rejected
            </a>
        </div>
    </div>

    @if($appeals->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <i data-lucide="inbox" class="w-16 h-16 mx-auto mb-4 text-gray-600"></i>
            <p class="text-gray-400">No {{ request('status', 'pending') }} appeals</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($appeals as $appeal)
                <div class="bg-gray-800 rounded-lg p-6 hover:bg-gray-750 transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex-1">
                            <div class="flex items-center gap-3 mb-2">
                                <img src="{{ $appeal->user->avatar }}" class="w-10 h-10 rounded-full" alt="">
                                <div>
                                    <h3 class="text-lg font-semibold text-white">{{ $appeal->user->name }}</h3>
                                    <p class="text-sm text-gray-400">Submitted {{ $appeal->created_at->diffForHumans() }}</p>
                                </div>
                            </div>
                        </div>
                        <a href="{{ route('admin.bans.appeals.show', $appeal) }}"
                           class="px-4 py-2 bg-green-600 hover:bg-green-700 text-white rounded-lg transition text-sm font-medium">
                            Review Appeal
                        </a>
                    </div>

                    <div class="grid grid-cols-2 gap-4 mb-4 text-sm">
                        <div>
                            <p class="text-gray-500 mb-1">Ban Reason:</p>
                            <p class="text-gray-300">{{ $appeal->reason }}</p>
                        </div>
                        <div>
                            <p class="text-gray-500 mb-1">Status:</p>
                            <span class="px-2 py-1 rounded text-xs font-medium inline-block
                                {{ $appeal->status === 'pending' ? 'bg-yellow-900 text-yellow-300' : '' }}
                                {{ $appeal->status === 'approved' ? 'bg-green-900 text-green-300' : '' }}
                                {{ $appeal->status === 'rejected' ? 'bg-red-900 text-red-300' : '' }}">
                                {{ ucfirst($appeal->status) }}
                            </span>
                        </div>
                    </div>

                    <div class="text-sm">
                        <p class="text-gray-500 mb-1">Appeal Message:</p>
                        <p class="text-gray-300 line-clamp-3">{{ $appeal->appeal_message }}</p>
                    </div>
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $appeals->links() }}
        </div>
    @endif
</div>
@endsection
