<!DOCTYPE html>
<html lang="en" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <title>Admin Panel - {{ site_setting('site_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <script>
        // Disable right-click context menu
        document.addEventListener('contextmenu', function(e) {
            e.preventDefault();
            return false;
        });

        // Disable common keyboard shortcuts for saving
        document.addEventListener('keydown', function(e) {
            // Ctrl+S / Cmd+S
            if ((e.ctrlKey || e.metaKey) && e.key === 's') {
                e.preventDefault();
                return false;
            }
        });
    </script>
</head>
<body class="min-h-full bg-gray-900 text-white" x-data="{
    sidebarOpen: false,
    expandedCategories: JSON.parse(localStorage.getItem('adminExpandedCategories') || '[]'),
    toggleCategory(name) {
        if (this.expandedCategories.includes(name)) {
            this.expandedCategories = this.expandedCategories.filter(c => c !== name);
        } else {
            this.expandedCategories.push(name);
        }
        localStorage.setItem('adminExpandedCategories', JSON.stringify(this.expandedCategories));
    },
    isCategoryExpanded(name) {
        return this.expandedCategories.includes(name);
    }
}">

    {{-- Mobile Overlay --}}
    <div x-show="sidebarOpen" x-transition:enter="transition-opacity ease-linear duration-200" x-transition:enter-start="opacity-0" x-transition:enter-end="opacity-100" x-transition:leave="transition-opacity ease-linear duration-200" x-transition:leave-start="opacity-100" x-transition:leave-end="opacity-0"
         @click="sidebarOpen = false"
         class="fixed inset-0 bg-black/60 z-40 lg:hidden" style="display: none;"></div>

    {{-- Sidebar --}}
    <aside :class="sidebarOpen ? 'translate-x-0' : '-translate-x-full'"
           class="fixed top-0 left-0 w-64 h-full bg-gray-800 border-r border-white/5 z-50 transition-transform duration-200 lg:translate-x-0 flex flex-col">

        {{-- Sidebar Header --}}
        <div class="h-16 flex items-center gap-3 px-4 border-b border-white/5 flex-shrink-0">
            <a href="{{ route('home') }}" class="flex-shrink-0">
                <img src="{{ site_setting('custom_logo_url') ?: '/images/logo-removebg-preview.png' }}" alt="{{ site_setting('site_name', config('app.name')) }}" class="h-10">
            </a>
            <span class="text-sm font-bold text-green-400 uppercase tracking-wider">Admin</span>
            {{-- Mobile close button --}}
            <button @click="sidebarOpen = false" class="ml-auto p-1 text-gray-400 hover:text-white lg:hidden">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                </svg>
            </button>
        </div>

        {{-- Navigation --}}
        <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
            {{-- Dashboard (Standalone) --}}
            <a href="{{ route('admin.dashboard') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm transition {{ request()->routeIs('admin.dashboard') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>
                </svg>
                Dashboard
            </a>

            <div class="border-t border-white/5 my-2"></div>

            {{-- CONTENT MANAGEMENT --}}
            <div>
                <button @click="toggleCategory('content')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        <span class="font-medium">Content</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="isCategoryExpanded('content') && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="isCategoryExpanded('content')" x-collapse class="ml-3 mt-1 space-y-1">
                    <a href="{{ route('admin.users') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.users*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4.354a4 4 0 110 5.292M15 21H3v-1a6 6 0 0112 0v1zm0 0h6v-1a6 6 0 00-9-5.197M13 7a4 4 0 11-8 0 4 4 0 018 0z"/>
                        </svg>
                        Users
                    </a>
                    <a href="{{ route('admin.news.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.news*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/>
                        </svg>
                        News
                    </a>
                    <a href="{{ route('admin.creators.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.creators*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                        </svg>
                        Creators
                        @php $pendingCreators = \App\Models\ContentCreator::where('is_approved', false)->count(); @endphp
                        @if($pendingCreators > 0)
                        <span class="ml-auto px-1.5 py-0.5 bg-orange-500/20 text-orange-400 rounded-full text-[10px] font-bold">{{ $pendingCreators }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.clips.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.clips*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/>
                        </svg>
                        Videos
                    </a>
                    <a href="{{ route('admin.announcements.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.announcements*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5.882V19.24a1.76 1.76 0 01-3.417.592l-2.147-6.15M18 13a3 3 0 100-6M5.436 13.683A4.001 4.001 0 017 6h1.832c4.1 0 7.625-1.234 9.168-3v14c-1.543-1.766-5.067-3-9.168-3H7a3.988 3.988 0 01-1.564-.317z"/>
                        </svg>
                        Announcements
                        @php $activeAnnouncements = \App\Models\Announcement::where('is_active', true)->count(); @endphp
                        @if($activeAnnouncements > 0)
                        <span class="ml-auto px-1.5 py-0.5 bg-blue-500/20 text-blue-400 rounded-full text-[10px] font-bold">{{ $activeAnnouncements }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.achievements.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.achievements*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/>
                        </svg>
                        Achievements
                    </a>
                </div>
            </div>

            {{-- GAME MANAGEMENT --}}
            <div>
                <button @click="toggleCategory('game')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="font-medium">Game</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="isCategoryExpanded('game') && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="isCategoryExpanded('game')" x-collapse class="ml-3 mt-1 space-y-1">
                    <a href="{{ route('admin.servers') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.servers*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>
                        </svg>
                        Servers
                    </a>
                    <a href="{{ route('admin.tournaments.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.tournaments*') || request()->routeIs('admin.matches*') || request()->routeIs('admin.registrations*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Tournaments
                    </a>
                    <a href="{{ route('admin.teams.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.teams*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/>
                        </svg>
                        Teams
                    </a>
                    <a href="{{ route('admin.scrims.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.scrims*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        Scrims
                    </a>
                    <a href="{{ route('admin.ranks.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.ranks*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                        </svg>
                        Ranks
                        <span class="ml-auto px-1.5 py-0.5 bg-purple-500/20 text-purple-400 rounded-full text-[10px] font-bold">50</span>
                    </a>
                    <a href="{{ route('admin.ranked.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.ranked*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/>
                        </svg>
                        Ranked Ratings
                    </a>
                </div>
            </div>

            {{-- GAME STATS --}}
            <div>
                <button @click="toggleCategory('stats')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        <span class="font-medium">Game Stats</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="isCategoryExpanded('stats') && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="isCategoryExpanded('stats')" x-collapse class="ml-3 mt-1 space-y-1">
                    <a href="{{ route('admin.game-stats.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.game-stats*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Dashboard
                    </a>
                    <a href="{{ route('gm.sessions') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('gm.sessions') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5.121 17.804A13.937 13.937 0 0112 16c2.5 0 4.847.655 6.879 1.804M15 10a3 3 0 11-6 0 3 3 0 016 0zm6 2a9 9 0 11-18 0 9 9 0 0118 0z"/>
                        </svg>
                        GM Sessions
                    </a>
                    <a href="{{ route('gm.editor-actions') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('gm.editor-actions') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
                        </svg>
                        Editor Actions
                    </a>
                    <a href="{{ route('admin.anticheat.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.anticheat*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20.618 5.984A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Anti-Cheat
                    </a>
                    <a href="{{ route('admin.weapons.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.weapons*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 11H5m14 0a2 2 0 012 2v6a2 2 0 01-2 2H5a2 2 0 01-2-2v-6a2 2 0 012-2m14 0V9a2 2 0 00-2-2M5 11V9a2 2 0 012-2m0 0V5a2 2 0 012-2h6a2 2 0 012 2v2M7 7h10"/>
                        </svg>
                        Weapons
                    </a>
                    <a href="{{ route('admin.vehicles.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.vehicles*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 7h8m-8 5h8m-4-10v2m0 12v2M5 21h14a2 2 0 002-2V5a2 2 0 00-2-2H5a2 2 0 00-2 2v14a2 2 0 002 2z"/>
                        </svg>
                        Vehicles
                    </a>
                </div>
            </div>

            {{-- MODERATION --}}
            <div>
                <button @click="toggleCategory('moderation')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        <span class="font-medium">Moderation</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="isCategoryExpanded('moderation') && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="isCategoryExpanded('moderation')" x-collapse class="ml-3 mt-1 space-y-1">
                    <a href="{{ route('admin.reports.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.reports*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 9v2m0 4h.01m-6.938 4h13.856c1.54 0 2.502-1.667 1.732-3L13.732 4c-.77-1.333-2.694-1.333-3.464 0L3.34 16c-.77 1.333.192 3 1.732 3z"/>
                        </svg>
                        Reports
                        @php $pendingReportCount = \App\Models\PlayerReport::pending()->count(); @endphp
                        @if($pendingReportCount > 0)
                        <span class="ml-auto px-1.5 py-0.5 bg-red-500/20 text-red-400 rounded-full text-[10px] font-bold">{{ $pendingReportCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.moderation.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.moderation*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Queue
                        @php
                            $flaggedChatCount = \App\Models\ChatEvent::whereNotNull('is_flagged')->whereNull('reviewed_at')->count();
                            $activeWarningsCount = \App\Models\PlayerWarning::active()->count();
                            $moderationQueueTotal = $flaggedChatCount + $activeWarningsCount;
                        @endphp
                        @if($moderationQueueTotal > 0)
                        <span class="ml-auto px-1.5 py-0.5 bg-yellow-500/20 text-yellow-400 rounded-full text-[10px] font-bold">{{ $moderationQueueTotal }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.bans.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.bans*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>
                        </svg>
                        Bans
                        @php $pendingAppealsCount = \App\Models\BanAppeal::pending()->count(); @endphp
                        @if($pendingAppealsCount > 0)
                        <span class="ml-auto px-1.5 py-0.5 bg-red-500/20 text-red-400 rounded-full text-[10px] font-bold">{{ $pendingAppealsCount }}</span>
                        @endif
                    </a>
                    <a href="{{ route('admin.reputation.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.reputation*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>
                        </svg>
                        Reputation
                        @php $flaggedCount = \App\Models\PlayerReputation::where('total_score', '<', -50)->count(); @endphp
                        @if($flaggedCount > 0)
                        <span class="ml-auto px-1.5 py-0.5 bg-red-500/20 text-red-400 rounded-full text-[10px] font-bold">{{ $flaggedCount }}</span>
                        @endif
                    </a>
                </div>
            </div>

            {{-- SERVER TOOLS --}}
            <div>
                <button @click="toggleCategory('servertools')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        <span class="font-medium">Server Tools</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="isCategoryExpanded('servertools') && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="isCategoryExpanded('servertools')" x-collapse class="ml-3 mt-1 space-y-1">
                    <a href="{{ route("admin.rcon.index") }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs("admin.rcon*") ? "bg-green-500/20 text-green-400" : "text-gray-400 hover:bg-white/5 hover:text-white" }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 9l3 3-3 3m5 0h3M5 20h14a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v12a2 2 0 002 2z"/>
                        </svg>
                        RCON Console
                    </a>
                    <a href="{{ route('admin.server.dashboard') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.server*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 3v2m6-2v2M9 19v2m6-2v2M5 9H3m2 6H3m18-6h-2m2 6h-2M7 19h10a2 2 0 002-2V7a2 2 0 00-2-2H7a2 2 0 00-2 2v10a2 2 0 002 2zM9 9h6v6H9V9z"/>
                        </svg>
                        Server Manager
                    </a>
                </div>
            </div>

            {{-- SYSTEM --}}
            <div>
                <button @click="toggleCategory('system')" class="w-full flex items-center justify-between px-3 py-2 rounded-lg text-sm text-gray-300 hover:bg-white/5 transition">
                    <div class="flex items-center gap-3">
                        <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                        </svg>
                        <span class="font-medium">System</span>
                    </div>
                    <svg class="w-4 h-4 transition-transform" :class="isCategoryExpanded('system') && 'rotate-90'" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"/>
                    </svg>
                </button>
                <div x-show="isCategoryExpanded('system')" x-collapse class="ml-3 mt-1 space-y-1">
                    <a href="{{ route('admin.metrics') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.metrics*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>
                        </svg>
                        Metrics
                    </a>
                    <a href="{{ route('admin.discord.index') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.discord*') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 8h2a2 2 0 012 2v6a2 2 0 01-2 2h-2v4l-4-4H9a1.994 1.994 0 01-1.414-.586m0 0L11 14h4a2 2 0 002-2V6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2v4l.586-.586z"/>
                        </svg>
                        Discord RPC
                    </a>
                    <a href="{{ route('admin.audit-log') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.audit-log') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/>
                        </svg>
                        Audit Log
                    </a>
                    <a href="{{ route('admin.settings') }}" class="flex items-center gap-2 px-3 py-2 pl-8 rounded-lg text-sm transition {{ request()->routeIs('admin.settings') ? 'bg-green-500/20 text-green-400' : 'text-gray-400 hover:bg-white/5 hover:text-white' }}">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>
                        </svg>
                        Settings
                    </a>
                </div>
            </div>

            <div class="border-t border-white/5 my-3"></div>

            <form action="{{ route('admin.cache.clear') }}" method="POST">
                @csrf
                <button type="submit" class="w-full flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-400 hover:bg-red-500/20 hover:text-red-400 transition">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                    </svg>
                    Clear Cache
                </button>
            </form>

            <a href="{{ route('home') }}" class="flex items-center gap-3 px-3 py-2 rounded-lg text-sm text-gray-400 hover:bg-white/5 hover:text-white transition">
                <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
                </svg>
                Back to Site
            </a>
        </nav>
    </aside>

    {{-- Main wrapper --}}
    <div class="lg:ml-64 min-h-screen flex flex-col">

        {{-- Top Bar --}}
        <header class="sticky top-0 z-30 h-16 bg-gray-800/80 backdrop-blur-sm border-b border-white/5 flex items-center justify-between px-4 sm:px-6">
            <div class="flex items-center gap-3">
                {{-- Mobile hamburger --}}
                <button @click="sidebarOpen = true" class="p-2 text-gray-400 hover:text-white lg:hidden">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                    </svg>
                </button>
                <h1 class="text-lg font-semibold text-white hidden sm:block">
                    @yield('admin-title', 'Admin Panel')
                </h1>
            </div>

            <div class="flex items-center gap-4">
                <a href="{{ route('home') }}" class="flex items-center gap-2 px-3 py-1.5 text-sm text-gray-400 hover:text-white bg-white/3 hover:bg-white/5 rounded-xl transition">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"/>
                    </svg>
                    View Site
                </a>
                @auth
                <div class="flex items-center gap-3">
                    <img src="{{ auth()->user()->avatar_display }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full">
                    <span class="text-sm text-gray-300 hidden sm:block">{{ auth()->user()->name }}</span>
                </div>
                @endauth
            </div>
        </header>

        {{-- Main Content --}}
        <main class="flex-1 p-4 sm:p-6">
            @if(session('success'))
            <div class="mb-4 p-4 bg-green-500/20 border border-green-500/30 rounded-lg text-green-400">
                {{ session('success') }}
            </div>
            @endif

            @if(session('error'))
            <div class="mb-4 p-4 bg-red-500/20 border border-red-500/30 rounded-lg text-red-400">
                {{ session('error') }}
            </div>
            @endif

            @yield('admin-content')
        </main>

        {{-- Footer --}}
        <footer class="border-t border-white/5 py-4 px-6 text-center">
            <p class="text-xs text-gray-500">&copy; {{ date('Y') }} {{ site_setting('site_name', config('app.name')) }} &mdash; Admin Panel</p>
        </footer>
    </div>

    @stack('scripts')
</body>
</html>
