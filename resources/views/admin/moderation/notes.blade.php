@extends('admin.layout')

@section('admin-title', 'Moderator Notes')

@section('admin-content')
<div class="space-y-6">
    <h1 class="text-2xl font-bold text-white">Moderator Notes</h1>

    @if($notes->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <p class="text-gray-400">No moderator notes</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($notes as $note)
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div class="flex items-center gap-3">
                            <img src="{{ $note->user->avatar }}" class="w-10 h-10 rounded-full" alt="">
                            <div>
                                <h3 class="text-white font-semibold">{{ $note->user->name }}</h3>
                                <p class="text-xs text-gray-400">{{ $note->created_at->format('M j, Y \a\t g:i A') }}</p>
                            </div>
                        </div>
                        <div class="flex items-center gap-2">
                            @if($note->is_flagged)
                                <i data-lucide="flag" class="w-4 h-4 text-red-400"></i>
                            @endif
                            <span class="px-2 py-1 rounded text-xs font-medium
                                {{ $note->category === 'positive' ? 'bg-green-900 text-green-300' : '' }}
                                {{ $note->category === 'negative' ? 'bg-red-900 text-red-300' : '' }}
                                {{ $note->category === 'neutral' ? 'bg-gray-700 text-gray-300' : '' }}
                                {{ $note->category === 'watchlist' ? 'bg-yellow-900 text-yellow-300' : '' }}">
                                {{ ucfirst($note->category) }}
                            </span>
                        </div>
                    </div>
                    <p class="text-gray-300 mb-2">{{ $note->note }}</p>
                    <p class="text-xs text-gray-500">By {{ $note->moderator->name }}</p>
                </div>
            @endforeach
        </div>
        {{ $notes->links() }}
    @endif
</div>
@endsection
