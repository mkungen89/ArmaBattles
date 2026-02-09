@extends('admin.layout')

@section('title', 'Registrations - ' . $tournament->name)

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.tournaments.show', $tournament) }}" class="text-gray-400 hover:text-white transition text-sm">
        &larr; Back to {{ $tournament->name }}
    </a>
</div>

<div class="flex items-center justify-between mb-6">
    <h1 class="text-2xl font-bold text-white">Registrations</h1>
    <span class="text-gray-400">{{ $registrations->where('status', 'approved')->count() }}/{{ $tournament->max_teams }} approved</span>
</div>

@if($registrations->count() > 0)
    <!-- Seeding Form -->
    @if($registrations->where('status', 'approved')->count() > 1 && !$tournament->matches()->exists())
        <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6 mb-6">
            <h2 class="text-lg font-semibold text-white mb-4">Seeding</h2>
            <p class="text-gray-400 text-sm mb-4">Drag and drop to change the order. Seed 1 faces the lowest seeded platoon in the bracket.</p>

            <form action="{{ route('admin.tournaments.seeding', $tournament) }}" method="POST" id="seeding-form">
                @csrf
                <div id="seeding-list" class="space-y-2 mb-4">
                    @foreach($registrations->where('status', 'approved')->sortBy('seed') as $registration)
                        <div class="flex items-center gap-3 bg-gray-700/50 rounded-lg p-3 cursor-move" draggable="true" data-id="{{ $registration->id }}">
                            <span class="w-6 text-center text-gray-400 font-mono seeding-number">{{ $registration->seed ?? '-' }}</span>
                            <input type="hidden" name="seeds[]" value="{{ $registration->id }}">
                            <span class="text-white">{{ $registration->team->name }}</span>
                            <span class="text-gray-500 text-sm">[{{ $registration->team->tag }}]</span>
                        </div>
                    @endforeach
                </div>
                <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition">
                    Save seeding
                </button>
            </form>
        </div>
    @endif

    <!-- Registrations List -->
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl overflow-hidden">
        <table class="w-full">
            <thead class="bg-gray-700/50">
                <tr>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Platoon</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Captain</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Members</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Status</th>
                    <th class="px-4 py-3 text-left text-xs font-medium text-gray-400 uppercase">Registered</th>
                    <th class="px-4 py-3 text-right text-xs font-medium text-gray-400 uppercase">Actions</th>
                </tr>
            </thead>
            <tbody class="divide-y divide-gray-700">
                @foreach($registrations as $registration)
                    <tr class="hover:bg-gray-700/30">
                        <td class="px-4 py-3">
                            <div class="flex items-center gap-3">
                                @if($registration->team->avatar_url)
                                    <img src="{{ $registration->team->avatar_url }}" class="w-8 h-8 rounded object-cover">
                                @endif
                                <div>
                                    <div class="text-white font-medium">{{ $registration->team->name }}</div>
                                    <div class="text-xs text-gray-500">[{{ $registration->team->tag }}]</div>
                                </div>
                            </div>
                        </td>
                        <td class="px-4 py-3 text-gray-400">{{ $registration->team->captain->name }}</td>
                        <td class="px-4 py-3 text-gray-400">{{ $registration->team->activeMembers->count() }}</td>
                        <td class="px-4 py-3">
                            <span class="px-2 py-1 text-xs rounded-full {{ $registration->status_badge }}">
                                {{ $registration->status_text }}
                            </span>
                        </td>
                        <td class="px-4 py-3 text-gray-400 text-sm">{{ $registration->created_at->format('d M H:i') }}</td>
                        <td class="px-4 py-3 text-right">
                            @if($registration->status === 'pending')
                                <div class="flex justify-end gap-2">
                                    <form action="{{ route('admin.registrations.approve', $registration) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-green-600 hover:bg-green-500 text-white rounded text-sm">
                                            Approve
                                        </button>
                                    </form>
                                    <form action="{{ route('admin.registrations.reject', $registration) }}" method="POST" class="inline">
                                        @csrf
                                        <button type="submit" class="px-3 py-1 bg-red-600 hover:bg-red-500 text-white rounded text-sm"
                                            onclick="return confirm('Reject registration?')">
                                            Reject
                                        </button>
                                    </form>
                                </div>
                            @elseif($registration->status === 'rejected' && $registration->rejection_reason)
                                <span class="text-xs text-red-400">{{ Str::limit($registration->rejection_reason, 30) }}</span>
                            @endif
                        </td>
                    </tr>
                @endforeach
            </tbody>
        </table>
    </div>
@else
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-12 text-center">
        <p class="text-gray-400">No registrations yet.</p>
    </div>
@endif

@push('scripts')
<script>
    // Simple drag and drop for seeding
    const list = document.getElementById('seeding-list');
    if (list) {
        let draggedItem = null;

        list.querySelectorAll('[draggable]').forEach(item => {
            item.addEventListener('dragstart', function() {
                draggedItem = this;
                this.classList.add('opacity-50');
            });

            item.addEventListener('dragend', function() {
                this.classList.remove('opacity-50');
                updateSeedingNumbers();
            });

            item.addEventListener('dragover', function(e) {
                e.preventDefault();
            });

            item.addEventListener('drop', function(e) {
                e.preventDefault();
                if (draggedItem !== this) {
                    const allItems = [...list.children];
                    const draggedIdx = allItems.indexOf(draggedItem);
                    const targetIdx = allItems.indexOf(this);

                    if (draggedIdx < targetIdx) {
                        this.after(draggedItem);
                    } else {
                        this.before(draggedItem);
                    }
                }
            });
        });

        function updateSeedingNumbers() {
            list.querySelectorAll('.seeding-number').forEach((el, idx) => {
                el.textContent = idx + 1;
            });
        }
    }
</script>
@endpush
@endsection
