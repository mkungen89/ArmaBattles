@extends('admin.layout')

@section('title', $tournament->name)

@section('admin-content')
<div class="mb-6">
    <a href="{{ route('admin.tournaments.index') }}" class="text-gray-400 hover:text-white transition text-sm">
        &larr; Back to tournaments
    </a>
</div>

<!-- Header -->
<div class="flex flex-col sm:flex-row sm:items-center sm:justify-between gap-4 mb-6">
    <div>
        <div class="flex items-center gap-3 mb-1">
            <h1 class="text-2xl font-bold text-white">{{ $tournament->name }}</h1>
            <span class="px-3 py-1 text-sm rounded-full {{ $tournament->status_badge }}">
                {{ $tournament->status_text }}
            </span>
        </div>
        <p class="text-gray-400">{{ $tournament->format_text }}</p>
    </div>
    <div class="flex gap-2">
        <a href="{{ route('tournaments.show', $tournament) }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition" target="_blank">
            View public
        </a>
        <a href="{{ route('admin.tournaments.edit', $tournament) }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
            Edit
        </a>
    </div>
</div>

<!-- Stats -->
<div class="grid grid-cols-2 md:grid-cols-5 gap-4 mb-6">
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-white">{{ $stats['total_registrations'] }}</div>
        <div class="text-sm text-gray-400">Registrations</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-green-400">{{ $stats['approved_teams'] }}</div>
        <div class="text-sm text-gray-400">Approved</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-yellow-400">{{ $stats['pending_teams'] }}</div>
        <div class="text-sm text-gray-400">Pending</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-white">{{ $stats['total_matches'] }}</div>
        <div class="text-sm text-gray-400">Matches</div>
    </div>
    <div class="glass-card rounded-xl p-4 text-center">
        <div class="text-2xl font-bold text-blue-400">{{ $stats['completed_matches'] }}</div>
        <div class="text-sm text-gray-400">Completed</div>
    </div>
</div>

<div class="grid lg:grid-cols-3 gap-6">
    <!-- Main Actions -->
    <div class="lg:col-span-2 space-y-6">
        <!-- Status Management -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Status</h2>

            <div class="flex flex-wrap gap-2">
                @php
                    $transitions = [
                        'draft' => ['registration_open', 'cancelled'],
                        'registration_open' => ['registration_closed', 'cancelled'],
                        'registration_closed' => ['in_progress', 'registration_open', 'cancelled'],
                        'in_progress' => ['completed', 'cancelled'],
                        'completed' => [],
                        'cancelled' => ['draft'],
                    ];
                    $statusLabels = [
                        'draft' => 'Draft',
                        'registration_open' => 'Open Registration',
                        'registration_closed' => 'Close Registration',
                        'in_progress' => 'Start Tournament',
                        'completed' => 'Mark Completed',
                        'cancelled' => 'Cancel',
                    ];
                @endphp

                @foreach($transitions[$tournament->status] ?? [] as $newStatus)
                    <form action="{{ route('admin.tournaments.status', $tournament) }}" method="POST" class="inline">
                        @csrf
                        <input type="hidden" name="status" value="{{ $newStatus }}">
                        <button type="submit" class="px-4 py-2 rounded-lg transition text-sm
                            {{ $newStatus === 'cancelled' ? 'bg-red-600 hover:bg-red-500' : 'bg-white/5 hover:bg-white/10' }} text-white"
                            onclick="{{ $newStatus === 'cancelled' ? 'return confirm(\'Are you sure?\')' : '' }}">
                            {{ $statusLabels[$newStatus] }}
                        </button>
                    </form>
                @endforeach
            </div>
        </div>

        <!-- Bracket Management -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Bracket</h2>

            @if($tournament->matches->count() > 0)
                <p class="text-gray-400 mb-4">
                    Bracket generated with {{ $tournament->matches->count() }} matches.
                </p>
                <div class="flex gap-2">
                    <a href="{{ route('admin.tournaments.matches', $tournament) }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                        Manage matches
                    </a>
                    <a href="{{ route('tournaments.bracket', $tournament) }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition" target="_blank">
                        View bracket
                    </a>
                    @if($tournament->status !== 'completed')
                        <form action="{{ route('admin.tournaments.reset-bracket', $tournament) }}" method="POST" class="inline" onsubmit="return confirm('Are you sure? All match results will be lost!')">
                            @csrf
                            <button type="submit" class="px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition">
                                Reset bracket
                            </button>
                        </form>
                    @endif
                </div>

                @if($tournament->format === 'swiss')
                    <form action="{{ route('admin.tournaments.next-swiss-round', $tournament) }}" method="POST" class="mt-4">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-blue-600 hover:bg-blue-500 text-white rounded-xl transition">
                            Generate next Swiss round
                        </button>
                    </form>
                @endif
            @else
                <p class="text-gray-400 mb-4">
                    No bracket has been generated yet.
                    @if($stats['approved_teams'] < $tournament->min_teams)
                        <span class="text-yellow-400">Need at least {{ $tournament->min_teams }} approved platoons (have {{ $stats['approved_teams'] }}).</span>
                    @endif
                </p>
                @if($stats['approved_teams'] >= $tournament->min_teams)
                    <form action="{{ route('admin.tournaments.generate-bracket', $tournament) }}" method="POST">
                        @csrf
                        <button type="submit" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition">
                            Generate bracket
                        </button>
                    </form>
                @endif
            @endif
        </div>

        <!-- Recent Registrations -->
        <div class="glass-card rounded-xl p-6">
            <div class="flex items-center justify-between mb-4">
                <h2 class="text-lg font-semibold text-white">Recent registrations</h2>
                <a href="{{ route('admin.tournaments.registrations', $tournament) }}" class="text-sm text-green-400 hover:text-green-300">
                    View all &rarr;
                </a>
            </div>

            @if($tournament->registrations->count() > 0)
                <div class="space-y-2">
                    @foreach($tournament->registrations->take(5) as $registration)
                        <div class="flex items-center justify-between bg-white/3 rounded-lg p-3">
                            <div class="flex items-center gap-3">
                                <span class="text-white">{{ $registration->team->name }}</span>
                                <span class="px-2 py-0.5 text-xs rounded-full {{ $registration->status_badge }}">
                                    {{ $registration->status_text }}
                                </span>
                            </div>
                            @if($registration->status === 'pending')
                                <div class="flex gap-2">
                                    <form action="{{ route('admin.registrations.approve', $registration) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-green-400 hover:text-green-300">Approve</button>
                                    </form>
                                    <form action="{{ route('admin.registrations.reject', $registration) }}" method="POST">
                                        @csrf
                                        <button type="submit" class="text-sm text-red-400 hover:text-red-300">Reject</button>
                                    </form>
                                </div>
                            @endif
                        </div>
                    @endforeach
                </div>
            @else
                <p class="text-gray-400">No registrations yet.</p>
            @endif
        </div>
    </div>

    <!-- Sidebar -->
    <div class="space-y-6">
        <!-- Info -->
        <div class="glass-card rounded-xl p-6">
            <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Information</h2>
            <dl class="space-y-3 text-sm">
                <div class="flex justify-between">
                    <dt class="text-gray-400">Max platoons</dt>
                    <dd class="text-white">{{ $tournament->max_teams }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Min platoons</dt>
                    <dd class="text-white">{{ $tournament->min_teams }}</dd>
                </div>
                <div class="flex justify-between">
                    <dt class="text-gray-400">Platoon size</dt>
                    <dd class="text-white">{{ $tournament->team_size }}</dd>
                </div>
                @if($tournament->registration_starts_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Registration opens</dt>
                        <dd class="text-white">{{ $tournament->registration_starts_at->format('d M H:i') }}</dd>
                    </div>
                @endif
                @if($tournament->registration_ends_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Registration closes</dt>
                        <dd class="text-white">{{ $tournament->registration_ends_at->format('d M H:i') }}</dd>
                    </div>
                @endif
                @if($tournament->starts_at)
                    <div class="flex justify-between">
                        <dt class="text-gray-400">Start</dt>
                        <dd class="text-white">{{ $tournament->starts_at->format('d M H:i') }}</dd>
                    </div>
                @endif
                <div class="flex justify-between">
                    <dt class="text-gray-400">Requires approval</dt>
                    <dd class="text-white">{{ $tournament->require_approval ? 'Yes' : 'No' }}</dd>
                </div>
            </dl>
        </div>

        <!-- Winner -->
        @if($tournament->winner)
            <div class="bg-gradient-to-br from-yellow-500/20 to-orange-500/20 border border-yellow-500/30 rounded-xl p-6">
                <h2 class="text-lg font-semibold text-yellow-400 mb-4">Winner</h2>
                <div class="flex items-center gap-3">
                    @if($tournament->winner->logo_url)
                        <img src="{{ $tournament->winner->logo_url }}" alt="{{ $tournament->winner->name }}" class="w-12 h-12 rounded-lg object-cover">
                    @endif
                    <div>
                        <div class="text-white font-semibold">{{ $tournament->winner->name }}</div>
                        <div class="text-sm text-yellow-400/70">[{{ $tournament->winner->tag }}]</div>
                    </div>
                </div>
            </div>
        @endif

        <!-- Danger Zone -->
        <div class="bg-red-500/10 border border-red-500/30 rounded-xl p-6">
            <h2 class="text-lg font-semibold text-red-400 mb-4">Danger Zone</h2>
            <form action="{{ route('admin.tournaments.destroy', $tournament) }}" method="POST" onsubmit="return confirm('Are you ABSOLUTELY sure? This will permanently delete the tournament and all matches!')">
                @csrf
                @method('DELETE')
                <button type="submit" class="w-full px-4 py-2 bg-red-600 hover:bg-red-500 text-white rounded-xl transition">
                    Delete tournament
                </button>
            </form>
        </div>
    </div>
</div>
@endsection
