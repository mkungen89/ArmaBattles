@extends('admin.layout')

@section('admin-title', 'Flagged Chat Messages')

@section('admin-content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <h1 class="text-2xl font-bold text-white">Flagged Chat Messages</h1>
        <a href="{{ route('admin.moderation.flagged-chat', ['reviewed' => 1]) }}"
           class="px-4 py-2 {{ request('reviewed') ? 'bg-green-600' : 'bg-gray-700' }} text-white rounded-lg">
            Show Reviewed
        </a>
    </div>

    @if($messages->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <p class="text-gray-400">No flagged messages</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($messages as $message)
                <div class="bg-gray-800 rounded-lg p-6">
                    <div class="flex items-start justify-between mb-3">
                        <div>
                            <p class="text-sm text-gray-400 mb-1">Player: {{ $message->player_name }}</p>
                            <p class="text-white">{{ $message->message }}</p>
                            <p class="text-xs text-gray-500 mt-2">{{ \Carbon\Carbon::parse($message->created_at)->format('M j, Y \a\t g:i A') }}</p>
                        </div>
                        @if(!$message->reviewed_at)
                            <form action="{{ route('admin.moderation.review-chat', $message->id) }}" method="POST" class="flex gap-2">
                                @csrf
                                <button type="submit" name="action" value="dismiss" class="px-3 py-1 bg-gray-700 hover:bg-gray-600 text-white rounded text-sm">
                                    Dismiss
                                </button>
                                <button type="submit" name="action" value="warn" class="px-3 py-1 bg-yellow-600 hover:bg-yellow-700 text-white rounded text-sm">
                                    Warn
                                </button>
                                <button type="submit" name="action" value="ban" class="px-3 py-1 bg-red-600 hover:bg-red-700 text-white rounded text-sm">
                                    Ban
                                </button>
                            </form>
                        @else
                            <span class="text-sm text-green-400">✓ Reviewed</span>
                        @endif
                    </div>
                    @if($message->flag_reason)
                        <p class="text-xs text-yellow-400">Flag reason: {{ $message->flag_reason }}</p>
                    @endif
                </div>
            @endforeach
        </div>
        {{ $messages->links() }}
    @endif
</div>
@endsection
