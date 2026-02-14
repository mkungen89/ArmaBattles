@extends('layouts.app')

@section('title', 'Recruitment Board')

@section('content')
<div class="max-w-7xl mx-auto px-4 py-8">
    <div class="mb-8">
        <div class="flex items-center justify-between mb-4">
            <div>
                <h1 class="text-3xl font-bold text-white mb-2">Recruitment Board</h1>
                <p class="text-gray-400">Find players or teams looking to squad up</p>
            </div>
            @auth
                @if(!auth()->user()->hasActiveRecruitmentListing())
                    <a href="{{ route('recruitment.create') }}" class="px-6 py-3 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                        <i data-lucide="plus" class="w-4 h-4 inline mr-2"></i>
                        Create Listing
                    </a>
                @else
                    <a href="{{ route('recruitment.my-listing') }}" class="px-6 py-3 bg-blue-600 hover:bg-blue-700 text-white font-semibold rounded-lg transition">
                        View My Listing
                    </a>
                @endif
            @endauth
        </div>

        <!-- Filters -->
        <form method="GET" class="bg-gray-800 rounded-lg p-4">
            <div class="grid grid-cols-1 md:grid-cols-4 gap-4">
                <select name="type" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <option value="">All Types</option>
                    <option value="players" {{ request('type') === 'players' ? 'selected' : '' }}>Players</option>
                    <option value="teams" {{ request('type') === 'teams' ? 'selected' : '' }}>Teams</option>
                </select>
                <select name="region" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <option value="">All Regions</option>
                    <option value="NA">North America</option>
                    <option value="EU">Europe</option>
                    <option value="APAC">Asia-Pacific</option>
                    <option value="SA">South America</option>
                    <option value="OCE">Oceania</option>
                </select>
                <select name="playstyle" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <option value="">All Playstyles</option>
                    <option value="casual">Casual</option>
                    <option value="competitive">Competitive</option>
                    <option value="milsim">MilSim</option>
                </select>
                <select name="role" onchange="this.form.submit()" class="bg-gray-900 border border-gray-700 rounded-lg px-4 py-2 text-white">
                    <option value="">All Roles</option>
                    @foreach($roles->groupBy('category') as $category => $categoryRoles)
                        <optgroup label="{{ ucfirst($category) }}">
                            @foreach($categoryRoles as $role)
                                <option value="{{ $role->id }}" {{ request('role') == $role->id ? 'selected' : '' }}>
                                    {{ $role->display_name }}
                                </option>
                            @endforeach
                        </optgroup>
                    @endforeach
                </select>
            </div>
        </form>
    </div>

    <!-- Featured Players -->
    @if($featuredPlayers->isNotEmpty())
        <div class="mb-8">
            <h2 class="text-xl font-bold text-white mb-4 flex items-center gap-2">
                <i data-lucide="star" class="w-5 h-5 text-yellow-400"></i>
                Featured Players
            </h2>
            <div class="grid grid-cols-1 md:grid-cols-5 gap-4">
                @foreach($featuredPlayers as $featured)
                    <div class="bg-gradient-to-br from-yellow-900/20 to-gray-800 border border-yellow-900 rounded-lg p-4 hover:border-yellow-700 transition">
                        <div class="flex items-center gap-3 mb-2">
                            <img src="{{ $featured->user->avatar }}" class="w-10 h-10 rounded-full" alt="">
                            <div>
                                <h3 class="font-semibold text-white">{{ $featured->user->name }}</h3>
                                <p class="text-xs text-gray-400">{{ $featured->region }}</p>
                            </div>
                        </div>
                        <div class="flex gap-1 text-xs">
                            <span class="px-2 py-1 bg-gray-900 rounded">{{ ucfirst($featured->playstyle) }}</span>
                            <span class="px-2 py-1 bg-gray-900 rounded">{{ ucfirst($featured->availability) }}</span>
                        </div>
                    </div>
                @endforeach
            </div>
        </div>
    @endif

    <!-- Listings -->
    @if($listings->isEmpty())
        <div class="bg-gray-800 rounded-lg p-12 text-center">
            <i data-lucide="users" class="w-16 h-16 mx-auto mb-4 text-gray-600"></i>
            <p class="text-gray-400">No recruitment listings found</p>
        </div>
    @else
        <div class="space-y-4">
            @foreach($listings as $listing)
                <div class="bg-gray-800 rounded-lg p-6 hover:bg-gray-750 transition">
                    <div class="flex items-start justify-between mb-4">
                        <div class="flex items-center gap-4">
                            <img src="{{ $listing->user->avatar }}" class="w-16 h-16 rounded-full" alt="">
                            <div>
                                <div class="flex items-center gap-2 mb-1">
                                    <h3 class="text-xl font-semibold text-white">{{ $listing->user->name }}</h3>
                                    @if($listing->is_featured)
                                        <i data-lucide="star" class="w-4 h-4 text-yellow-400"></i>
                                    @endif
                                </div>
                                <div class="flex gap-2 text-sm">
                                    <span class="text-gray-400">{{ $listing->region }}</span>
                                    <span class="text-gray-600">•</span>
                                    <span class="text-gray-400">{{ ucfirst($listing->playstyle) }}</span>
                                    <span class="text-gray-600">•</span>
                                    <span class="text-gray-400">{{ ucfirst($listing->availability) }}</span>
                                </div>
                            </div>
                        </div>
                        <span class="text-xs text-gray-500">{{ $listing->created_at->diffForHumans() }}</span>
                    </div>

                    <p class="text-gray-300 mb-4">{{ $listing->message }}</p>

                    @if($listing->preferred_roles)
                        <div class="flex flex-wrap gap-2">
                            @foreach($listing->preferred_roles as $roleId)
                                @php $role = $roles->find($roleId); @endphp
                                @if($role)
                                    <span class="px-3 py-1 bg-gray-900 text-gray-300 rounded-lg text-sm flex items-center gap-2">
                                        <i data-lucide="{{ $role->icon }}" class="w-3 h-3"></i>
                                        {{ $role->display_name }}
                                    </span>
                                @endif
                            @endforeach
                        </div>
                    @endif
                </div>
            @endforeach
        </div>

        <div class="mt-6">
            {{ $listings->links() }}
        </div>
    @endif
</div>
@endsection
