@auth
@php
    $isFavorited = auth()->user()->hasFavorited($model);
@endphp
<form action="{{ route('favorites.toggle') }}" method="POST" class="inline">
    @csrf
    <input type="hidden" name="type" value="{{ $type }}">
    <input type="hidden" name="id" value="{{ $model->getKey() }}">
    <button type="submit" class="flex items-center gap-1 px-3 py-1.5 rounded-lg text-sm transition
        {{ $isFavorited ? 'bg-yellow-500/20 text-yellow-400 border border-yellow-500/30 hover:bg-yellow-500/30' : 'bg-white/5 text-gray-400 border border-white/10 hover:text-yellow-400 hover:border-yellow-500/30' }}"
        title="{{ $isFavorited ? 'Remove from favorites' : 'Add to favorites' }}">
        <svg class="w-4 h-4" fill="{{ $isFavorited ? 'currentColor' : 'none' }}" stroke="currentColor" viewBox="0 0 24 24">
            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/>
        </svg>
        {{ $isFavorited ? 'Favorited' : 'Favorite' }}
    </button>
</form>
@endauth
