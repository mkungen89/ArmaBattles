<!DOCTYPE html>
<html lang="en" class="min-h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <meta name="csrf-token" content="{{ csrf_token() }}">
    <meta name="view-transition" content="same-origin">
    <meta name="description" content="{{ site_setting('meta_description', '') }}">
    @if(site_setting('meta_keywords'))
    <meta name="keywords" content="{{ site_setting('meta_keywords') }}">
    @endif
    <meta name="robots" content="{{ site_setting('robots_meta', 'index, follow') }}">
    <meta property="og:title" content="{{ site_setting('site_name', config('app.name')) }} - @yield('title', 'Home')">
    <meta property="og:description" content="{{ site_setting('meta_description', '') }}">
    <meta property="og:type" content="website">
    @if(site_setting('og_image_url'))
    <meta property="og:image" content="{{ site_setting('og_image_url') }}">
    @endif
    <link rel="icon" type="image/x-icon" href="/favicon.ico">
    <link rel="icon" type="image/png" sizes="32x32" href="/favicon-32x32.png">
    <link rel="icon" type="image/png" sizes="16x16" href="/favicon-16x16.png">
    <link rel="apple-touch-icon" sizes="180x180" href="/apple-touch-icon.png">
    <title>{{ site_setting('site_name', config('app.name')) }} - @yield('title', 'Home')</title>
    @vite(['resources/css/app.css', 'resources/js/app.js'])
    <script src="https://unpkg.com/lucide@latest"></script>
    <script src="https://cdn.jsdelivr.net/npm/alpinejs@3.14.1/dist/cdn.min.js" defer></script>
    <script>
        // Simple sidebar toggle without Alpine store
        function setSidebarOpen(isOpen, options = {}) {
            const sidebar = document.getElementById('sidebar');
            const overlay = document.getElementById('sidebar-overlay');
            const hamburger = document.getElementById('hamburger-icon');
            const spacer = document.getElementById('left-sidebar-spacer');
            const isDesktop = window.innerWidth >= 1024;
            const silent = options.silent === true;

            if (!sidebar) return;

            sidebar.dataset.open = isOpen ? 'true' : 'false';
            sidebar.classList.remove('translate-x-0', '-translate-x-full');
            sidebar.classList.add(isOpen ? 'translate-x-0' : '-translate-x-full');

            if (spacer) {
                if (isOpen && isDesktop) {
                    spacer.classList.remove('hidden');
                    spacer.classList.add('block');
                } else {
                    spacer.classList.add('hidden');
                    spacer.classList.remove('block');
                }
            }

            if (overlay) {
                if (silent || isDesktop) {
                    overlay.classList.add('hidden');
                } else {
                    overlay.classList.toggle('hidden', !isOpen);
                }
            }

            if (hamburger) {
                const openIcon = hamburger.querySelector('[data-icon="open"]');
                const closeIcon = hamburger.querySelector('[data-icon="close"]');
                if (openIcon && closeIcon) {
                    openIcon.classList.toggle('hidden', isOpen);
                    closeIcon.classList.toggle('hidden', !isOpen);
                }
                hamburger.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

        }

        function toggleSidebar() {
            const sidebar = document.getElementById('sidebar');
            const isOpen = sidebar?.dataset.open === 'true';
            setSidebarOpen(!isOpen);
        }

        function setRightSidebarOpen(isOpen, options = {}) {
            const sidebar = document.getElementById('right-sidebar');
            const overlay = document.getElementById('right-sidebar-overlay');
            const toggle = document.getElementById('right-sidebar-toggle');
            const spacer = document.getElementById('right-sidebar-spacer');
            const isWide = window.innerWidth >= 1280;
            const silent = options.silent === true;

            if (!sidebar) return;

            sidebar.dataset.open = isOpen ? 'true' : 'false';
            sidebar.classList.remove('translate-x-0', 'translate-x-full');
            sidebar.classList.add(isOpen ? 'translate-x-0' : 'translate-x-full');
            sidebar.classList.toggle('hidden', !isOpen);

            if (overlay) {
                if (silent || isWide) {
                    overlay.classList.add('hidden');
                } else {
                    overlay.classList.toggle('hidden', !isOpen);
                }
            }

            if (spacer) {
                if (isOpen && isWide) {
                    spacer.classList.remove('hidden');
                    spacer.classList.add('block');
                } else {
                    spacer.classList.add('hidden');
                    spacer.classList.remove('block');
                }
            }

            if (toggle) {
                toggle.setAttribute('aria-expanded', isOpen ? 'true' : 'false');
            }

        }

        function toggleRightSidebar() {
            const sidebar = document.getElementById('right-sidebar');
            const isOpen = sidebar?.dataset.open === 'true';
            setRightSidebarOpen(!isOpen);
        }

        // Initialize sidebar state on page load
        document.addEventListener('DOMContentLoaded', function() {
            setSidebarOpen(window.innerWidth >= 1024, { silent: true });
            setRightSidebarOpen(window.innerWidth >= 1280, { silent: true });
        });

        // Keep overlay state sane on resize
        window.addEventListener('resize', function() {
            const isDesktop = window.innerWidth >= 1024;
            const sidebar = document.getElementById('sidebar');
            if (!sidebar) return;
            if (isDesktop) {
                setSidebarOpen(true, { silent: true });
            } else {
                setSidebarOpen(false, { silent: true });
            }

            const rightSidebar = document.getElementById('right-sidebar');
            if (!rightSidebar) return;
            const isWide = window.innerWidth >= 1280;
            if (isWide) {
                const wasOpen = rightSidebar.dataset.open === 'true';
                setRightSidebarOpen(wasOpen, { silent: true });
            } else {
                setRightSidebarOpen(false, { silent: true });
            }

        });
    </script>
    @if(site_setting('analytics_code'))
    {!! site_setting('analytics_code') !!}
    @endif
    @if(site_setting('custom_head_html'))
    {!! site_setting('custom_head_html') !!}
    @endif
    @if(site_setting('custom_css'))
    <style>{!! site_setting('custom_css') !!}</style>
    @endif
</head>
<body class="min-h-full bg-gray-900 text-white">
    {{-- Full Page Background --}}
    <div class="fixed inset-0 z-0">
        <img src="https://images.wallpapersden.com/image/download/arma-reforger-4k-gaming_bWhrbm6UmZqaraWkpJRobWllrWdma2U.jpg" alt="" class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/40 via-gray-900/70 to-gray-900/90"></div>
    </div>

    <div class="relative z-10 flex flex-col min-h-screen">
    @auth
    {{-- Sidebar Navigation (Only for logged-in users) --}}

    {{-- Mobile Overlay --}}
    <div id="sidebar-overlay"
         onclick="toggleSidebar()"
         class="fixed inset-0 bg-black/50 z-30 lg:hidden hidden"></div>

    {{-- Right Sidebar Overlay --}}
    <div id="right-sidebar-overlay"
         onclick="toggleRightSidebar()"
         class="fixed inset-0 bg-black/50 z-30 xl:hidden hidden"></div>

    <aside id="sidebar" class="fixed top-16 left-0 w-64 h-[calc(100vh-4rem)] flex-shrink-0 z-40 transition-transform duration-300 -translate-x-full">
        <div class="w-64 flex flex-col h-full glass-dark border-r border-white/5">
            {{-- Navigation Links --}}
            <nav class="flex-1 overflow-y-auto py-4 px-3 space-y-1">
                <p class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Navigation</p>
                <a href="{{ route('home') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('home') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/></svg>
                    Home
                </a>
                <a href="{{ route('servers.show', config('services.battlemetrics.server_id', '0')) }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('servers.show') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2m-2-4h.01M17 16h.01"/></svg>
                    Server
                </a>
                <a href="{{ route('news.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('news.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 20H5a2 2 0 01-2-2V6a2 2 0 012-2h10a2 2 0 012 2v1m2 13a2 2 0 01-2-2V7m2 13a2 2 0 002-2V9a2 2 0 00-2-2h-2m-4-3H9M7 16h6M7 8h6v4H7V8z"/></svg>
                    News
                </a>
                <a href="{{ route('tournaments.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('tournaments.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4M7.835 4.697a3.42 3.42 0 001.946-.806 3.42 3.42 0 014.438 0 3.42 3.42 0 001.946.806 3.42 3.42 0 013.138 3.138 3.42 3.42 0 00.806 1.946 3.42 3.42 0 010 4.438 3.42 3.42 0 00-.806 1.946 3.42 3.42 0 01-3.138 3.138 3.42 3.42 0 00-1.946.806 3.42 3.42 0 01-4.438 0 3.42 3.42 0 00-1.946-.806 3.42 3.42 0 01-3.138-3.138 3.42 3.42 0 00-.806-1.946 3.42 3.42 0 010-4.438 3.42 3.42 0 00.806-1.946 3.42 3.42 0 013.138-3.138z"/></svg>
                    Tournaments
                </a>
                <a href="{{ route('teams.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('teams.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0zm6 3a2 2 0 11-4 0 2 2 0 014 0zM7 10a2 2 0 11-4 0 2 2 0 014 0z"/></svg>
                    Platoons
                </a>
                <a href="{{ route('scrims.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('scrims.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Scrims
                </a>

                <div class="glow-line mx-3"></div>
                <p class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Stats & Community</p>

                <a href="{{ route('leaderboard') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('leaderboard') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/></svg>
                    Leaderboard
                </a>
                <a href="{{ route('levels.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('levels.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/></svg>
                    Levels
                </a>
                <a href="{{ route('ranked.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('ranked.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 7h8m0 0v8m0-8l-8 8-4-4-6 6"/></svg>
                    Ranked
                </a>
                <a href="{{ route('achievements.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('achievements.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 3v4M3 5h4M6 17v4m-2-2h4m5-16l2.286 6.857L21 12l-5.714 2.143L13 21l-2.286-6.857L5 12l5.714-2.143L13 3z"/></svg>
                    Achievements
                </a>
                <a href="{{ route('reputation.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('reputation.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Reputation
                </a>
                <a href="{{ route('favorites.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('favorites.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z"/></svg>
                    Favorites
                </a>
                <a href="{{ route('content-creators.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('content-creators.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 10l4.553-2.276A1 1 0 0121 8.618v6.764a1 1 0 01-1.447.894L15 14M5 18h8a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v8a2 2 0 002 2z"/></svg>
                    Creators
                </a>
                <a href="{{ route('clips.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('clips.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 4v16M17 4v16M3 8h4m10 0h4M3 12h18M3 16h4m10 0h4M4 20h16a1 1 0 001-1V5a1 1 0 00-1-1H4a1 1 0 00-1 1v14a1 1 0 001 1z"/></svg>
                    Clips
                </a>
                <a href="{{ route('weapons.index') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('weapons.*') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M14 10l-2 1m0 0l-2-1m2 1v2.5M20 7l-2 1m2-1l-2-1m2 1v2.5M14 4l-2-1-2 1M4 7l2-1M4 7l2 1M4 7v2.5M12 21l-2-1m2 1l2-1m-2 1v-2.5M6 18l-2-1v-2.5M18 18l2-1v-2.5"/></svg>
                    Weapons
                </a>
                <a href="{{ route('rules') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('rules') ? 'nav-link-active text-white' : 'text-gray-300' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"/></svg>
                    Rules
                </a>

                @if(auth()->user()->isAdmin())
                <div class="glow-line mx-3"></div>
                <p class="px-3 mb-2 text-xs font-semibold text-gray-500 uppercase tracking-wider">Management</p>
                <a href="{{ route('admin.dashboard') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('admin.*') ? 'bg-green-500/10 text-green-400 border border-green-500/20 glow-green-sm' : 'text-green-400' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                    Admin Panel
                </a>
                @endif
                @if(auth()->user()->isReferee())
                <a href="{{ route('referee.dashboard') }}" class="group flex items-center px-3 py-2 rounded-lg text-sm font-medium hover:bg-white/5 transition-all duration-200 {{ request()->routeIs('referee.*') ? 'bg-blue-500/10 text-blue-400 border border-blue-500/20' : 'text-blue-400' }}" style="{{ request()->routeIs('referee.*') ? 'box-shadow: 0 0 10px rgba(59, 130, 246, 0.1);' : '' }}">
                    <svg class="w-5 h-5 mr-3 transition-transform duration-200 group-hover:scale-110" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                    Referee
                </a>
                @endif
            </nav>
        </div>
    </aside>

    {{-- Right Sidebar: Live Activity Feed (desktop only, fixed like left sidebar) --}}
    <aside id="right-sidebar" class="fixed top-16 right-0 w-80 h-[calc(100vh-4rem)] flex-shrink-0 z-40 transition-transform duration-300 translate-x-full hidden glass-dark border-l border-white/5 p-4">
        @include('partials._activity-feed')
    </aside>
    @endauth

    {{-- Top Navigation Bar (full width, above everything) --}}
    <nav class="sticky top-0 z-50 glass border-b border-white/5 relative">
        <div class="absolute bottom-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-green-500/40 to-transparent"></div>
        <div class="px-4 sm:px-6 lg:px-8">
            <div class="flex items-center justify-between h-16">
                @guest
                {{-- Guest: Logo only --}}
                <div class="flex items-center min-w-0">
                    <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center hover:drop-shadow-[0_0_12px_rgba(34,197,94,0.4)] transition-all duration-300">
                        <img src="{{ site_setting('custom_logo_url') ?: '/images/logo-removebg-preview.png' }}" alt="{{ site_setting('site_name', config('app.name')) }}" class="h-38 -mb-8 -mt-1">
                    </a>
                </div>
                @endguest

                @auth
                {{-- Logged In: Hamburger + Logo + Centered Search --}}
                <div class="flex items-center gap-6">
                    {{-- Hamburger Menu (Toggle Sidebar) --}}
                    <button id="hamburger-icon" onclick="toggleSidebar()"
                            class="p-2 rounded-md text-gray-400 hover:text-white hover:bg-white/5 transition">
                        <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path data-icon="open" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M4 6h16M4 12h16M4 18h16"/>
                            <path data-icon="close" class="hidden" stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                        </svg>
                    </button>

                    {{-- Logo --}}
                    <a href="{{ route('home') }}" class="flex-shrink-0 flex items-center ml-12 hover:drop-shadow-[0_0_12px_rgba(34,197,94,0.4)] transition-all duration-300">
                        <img src="{{ site_setting('custom_logo_url') ?: '/images/logo-removebg-preview.png' }}" alt="{{ site_setting('site_name', config('app.name')) }}" class="h-38 -mb-8 -mt-1">
                    </a>
                </div>

                {{-- Centered Search --}}
                <div class="flex-1 flex justify-center px-4">
                    <div class="w-full max-w-md">
                        <div class="relative" x-data="{
                            searchOpen: false,
                            query: '',
                            results: [],
                            recentSearches: JSON.parse(localStorage.getItem('recentSearches') || '[]'),
                            showRecent: false,
                            loading: false,
                            async search() {
                                if (this.query.length < 2) { this.results = []; return; }
                                this.loading = true;
                                try {
                                    const res = await fetch('/api/player-search?q=' + encodeURIComponent(this.query));
                                    this.results = await res.json();
                                } catch(e) {} finally { this.loading = false; }
                            },
                            saveRecent(name, url, avatar) {
                                let recent = JSON.parse(localStorage.getItem('recentSearches') || '[]');
                                recent = recent.filter(r => r.name !== name);
                                recent.unshift({ name, url, avatar });
                                if (recent.length > 5) recent = recent.slice(0, 5);
                                localStorage.setItem('recentSearches', JSON.stringify(recent));
                                this.recentSearches = recent;
                            },
                            clearRecent() {
                                localStorage.removeItem('recentSearches');
                                this.recentSearches = [];
                            }
                        }">
                            <input x-ref="searchInput" type="text" x-model="query" @input.debounce.300ms="search()"
                                   @focus="if(query.length < 2) showRecent = true"
                                   @blur="setTimeout(() => showRecent = false, 200)"
                                   @keydown.enter="if(query.length >= 2) window.location.href='/players?q='+encodeURIComponent(query)"
                                   placeholder="Search players..."
                                   class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm placeholder-gray-400 focus-glow">
                            {{-- Recent searches --}}
                            <div x-show="showRecent && query.length < 2 && recentSearches.length > 0" class="absolute w-full mt-2 glass rounded-xl shadow-xl z-50" style="display:none;">
                                <div class="px-4 py-2 flex items-center justify-between border-b border-white/5">
                                    <span class="text-xs text-gray-400">Recent searches</span>
                                    <button @click="clearRecent()" class="text-xs text-gray-500 hover:text-red-400">Clear</button>
                                </div>
                                <template x-for="r in recentSearches" :key="r.name">
                                    <a :href="r.url || '/players?q='+encodeURIComponent(r.name)"
                                       class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition">
                                        <template x-if="r.avatar">
                                            <img :src="r.avatar" class="w-6 h-6 rounded-full">
                                        </template>
                                        <template x-if="!r.avatar">
                                            <div class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </template>
                                        <span class="text-sm text-gray-300 truncate" x-text="r.name"></span>
                                        <svg class="w-3 h-3 text-gray-600 ml-auto" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    </a>
                                </template>
                            </div>
                            {{-- Search results --}}
                            <div x-show="results.length > 0" class="absolute w-full mt-2 glass rounded-xl shadow-xl z-50 max-h-60 overflow-y-auto" style="display:none;">
                                <template x-for="r in results" :key="r.uuid">
                                    <a :href="r.url || '/players?q='+encodeURIComponent(r.name)"
                                       @click="saveRecent(r.name, r.url, r.avatar)"
                                       class="flex items-center gap-3 px-4 py-2 hover:bg-white/5 transition">
                                        <template x-if="r.avatar">
                                            <img :src="r.avatar" class="w-6 h-6 rounded-full">
                                        </template>
                                        <template x-if="!r.avatar">
                                            <div class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </template>
                                        <span class="text-sm text-white truncate" x-text="r.name"></span>
                                    </a>
                                </template>
                            </div>
                            <div x-show="loading" class="absolute w-full mt-2 glass rounded-xl shadow-xl z-50 p-2" style="display:none;">
                                <template x-for="i in 3" :key="'search-skel-'+i">
                                    <div class="flex items-center gap-3 px-4 py-2">
                                        <div class="skeleton skeleton-circle w-6 h-6 flex-shrink-0"></div>
                                        <div class="skeleton skeleton-text flex-1"></div>
                                    </div>
                                </template>
                            </div>
                        </div>
                    </div>
                </div>
                @endauth

                <div class="flex items-center space-x-4 shrink-0">
                    @guest
                    {{-- Guest Player Search Icon --}}
                    <div class="relative" x-data="{
                        searchOpen: false,
                        query: '',
                        results: [],
                        loading: false,
                        async search() {
                            if (this.query.length < 2) { this.results = []; return; }
                            this.loading = true;
                            try {
                                const res = await fetch('/api/player-search?q=' + encodeURIComponent(this.query));
                                this.results = await res.json();
                            } catch(e) {} finally { this.loading = false; }
                        }
                    }">
                        <button @click="searchOpen = !searchOpen; $nextTick(() => { if(searchOpen) $refs.searchInput.focus(); })"
                                class="p-2 text-gray-400 hover:text-white transition">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>
                            </svg>
                        </button>
                        <div x-show="searchOpen" @click.outside="searchOpen = false" x-transition
                             class="absolute right-0 mt-2 w-80 glass-card backdrop-blur-xl rounded-xl shadow-xl z-50 p-3" style="display:none;">
                            <input x-ref="searchInput" type="text" x-model="query" @input.debounce.300ms="search()"
                                   @keydown.enter="if(query.length >= 2) window.location.href='/players?q='+encodeURIComponent(query)"
                                   placeholder="Search players..."
                                   class="w-full px-3 py-2 bg-white/5 border border-white/10 rounded-lg text-white text-sm placeholder-gray-500 focus:outline-none focus:border-green-500">
                            <div x-show="results.length > 0" class="mt-2 max-h-60 overflow-y-auto">
                                <template x-for="r in results" :key="r.uuid">
                                    <a :href="r.url || '/players?q='+encodeURIComponent(r.name)"
                                       class="flex items-center gap-3 px-3 py-2 rounded-lg hover:bg-white/5 transition">
                                        <template x-if="r.avatar">
                                            <img :src="r.avatar" class="w-6 h-6 rounded-full">
                                        </template>
                                        <template x-if="!r.avatar">
                                            <div class="w-6 h-6 rounded-full bg-white/5 flex items-center justify-center">
                                                <svg class="w-3 h-3 text-gray-500" fill="currentColor" viewBox="0 0 20 20"><path fill-rule="evenodd" d="M10 9a3 3 0 100-6 3 3 0 000 6zm-7 9a7 7 0 1114 0H3z" clip-rule="evenodd"/></svg>
                                            </div>
                                        </template>
                                        <span class="text-sm text-white truncate" x-text="r.name"></span>
                                    </a>
                                </template>
                            </div>
                            <div x-show="loading" class="mt-2 space-y-2">
                                <template x-for="i in 3" :key="'gsearch-skel-'+i">
                                    <div class="flex items-center gap-3 px-3 py-2">
                                        <div class="skeleton skeleton-circle w-6 h-6 flex-shrink-0"></div>
                                        <div class="skeleton skeleton-text flex-1"></div>
                                    </div>
                                </template>
                            </div>
                            <a href="/players" class="block mt-2 text-center text-xs text-green-400 hover:text-green-300">Full search &rarr;</a>
                        </div>
                    </div>
                    @endguest

                    @auth
                        {{-- Notifications --}}
                        <div class="relative" x-data="{
                            open: false,
                            notifications: [],
                            unread: 0,
                            loading: false,
                            filter: 'all',
                            fetchNotifications() {
                                this.loading = true;
                                fetch('{{ route('notifications.index') }}')
                                    .then(r => r.json())
                                    .then(data => { this.notifications = data; this.loading = false; })
                                    .catch(() => { this.loading = false; });
                            },
                            fetchUnread() {
                                fetch('{{ route('notifications.unread-count') }}')
                                    .then(r => r.json())
                                    .then(data => {
                                        if (data.count > this.unread && this.unread >= 0 && Notification.permission === 'granted') {
                                            new Notification('{{ site_setting('site_name', config('app.name')) }}', { body: 'You have new notifications', icon: '/favicon-32x32.png' });
                                        }
                                        this.unread = data.count;
                                    });
                            },
                            markRead(notification) {
                                if (notification.read_at) return;
                                fetch('/notifications/' + notification.id + '/read', {
                                    method: 'POST',
                                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                                }).then(() => {
                                    notification.read_at = 'now';
                                    this.unread = Math.max(0, this.unread - 1);
                                });
                            },
                            markAllRead() {
                                fetch('{{ route('notifications.mark-all-read') }}', {
                                    method: 'POST',
                                    headers: {'X-CSRF-TOKEN': '{{ csrf_token() }}'}
                                }).then(() => {
                                    this.unread = 0;
                                    this.notifications.forEach(n => n.read_at = 'now');
                                });
                            },
                            enableDesktop() {
                                if ('Notification' in window && Notification.permission === 'default') {
                                    Notification.requestPermission();
                                }
                            },
                            getCategory(n) {
                                const type = n.data?.type || n.type || 'general';
                                if (['team_invitation','team_application','application_result'].includes(type)) return 'team';
                                if (['match_scheduled','match_reminder'].includes(type)) return 'match';
                                if (['achievement_unlocked','achievement','achievement_earned','level_up'].includes(type)) return 'achievement';
                                return 'general';
                            },
                            getCategoryIcon(cat) {
                                const icons = {
                                    team: '<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M17 20h5v-2a3 3 0 00-5.356-1.857M17 20H7m10 0v-2c0-.656-.126-1.283-.356-1.857M7 20H2v-2a3 3 0 015.356-1.857M7 20v-2c0-.656.126-1.283.356-1.857m0 0a5.002 5.002 0 019.288 0M15 7a3 3 0 11-6 0 3 3 0 016 0z&quot;/>',
                                    match: '<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z&quot;/>',
                                    achievement: '<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M11.049 2.927c.3-.921 1.603-.921 1.902 0l1.519 4.674a1 1 0 00.95.69h4.915c.969 0 1.371 1.24.588 1.81l-3.976 2.888a1 1 0 00-.363 1.118l1.518 4.674c.3.922-.755 1.688-1.538 1.118l-3.976-2.888a1 1 0 00-1.176 0l-3.976 2.888c-.783.57-1.838-.197-1.538-1.118l1.518-4.674a1 1 0 00-.363-1.118l-3.976-2.888c-.784-.57-.38-1.81.588-1.81h4.914a1 1 0 00.951-.69l1.519-4.674z&quot;/>',
                                    general: '<path stroke-linecap=&quot;round&quot; stroke-linejoin=&quot;round&quot; stroke-width=&quot;2&quot; d=&quot;M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9&quot;/>'
                                };
                                return icons[cat] || icons.general;
                            },
                            getCategoryColor(cat) {
                                return { team: 'text-blue-400', match: 'text-orange-400', achievement: 'text-yellow-400', general: 'text-gray-400' }[cat] || 'text-gray-400';
                            },
                            get filtered() {
                                if (this.filter === 'all') return this.notifications;
                                return this.notifications.filter(n => this.getCategory(n) === this.filter);
                            },
                            getLink(n) {
                                if (n.data && n.data.action_url) return n.data.action_url;
                                if (!n.data || !n.data.type) return null;
                                if (n.data.type === 'team_invitation' || n.data.type === 'team_application' || n.data.type === 'application_result') {
                                    return n.data.team_id ? '/teams/' + n.data.team_id : null;
                                }
                                if (n.data.type === 'match_scheduled') {
                                    return n.data.match_id ? '/matches/' + n.data.match_id : null;
                                }
                                return null;
                            }
                        }" x-init="
                            fetchUnread();
                            let notifPollMs = 60000;
                            setInterval(() => fetchUnread(), notifPollMs);
                            enableDesktop();
                            if (window.Echo) {
                                window.Echo.private('App.Models.User.{{ auth()->id() }}')
                                    .listen('.notification.new', (e) => {
                                        this.unread++;
                                        if (Notification.permission === 'granted') {
                                            new Notification('{{ site_setting('site_name', config('app.name')) }}', { body: e.message || 'New notification', icon: '/favicon-32x32.png' });
                                        }
                                        if (this.open) { this.fetchNotifications(); }
                                        if (e.category === 'achievement' && e.metadata) {
                                            window.dispatchEvent(new CustomEvent('achievement-unlocked', { detail: e.metadata }));
                                        }
                                    });
                            }
                        ">
                            <button @click="open = !open; if(open) fetchNotifications()"
                                    class="relative p-2 text-gray-400 hover:text-white transition">
                                <svg class="w-6 h-6" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>
                                </svg>
                                <span x-show="unread > 0" x-text="unread > 9 ? '9+' : unread"
                                      class="absolute -top-1 -right-1 bg-red-500 text-white text-xs rounded-full w-5 h-5 flex items-center justify-center animate-pulse-glow"></span>
                            </button>

                            <div x-show="open" @click.outside="open = false" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95"
                                 class="absolute right-0 mt-2 w-96 glass-card backdrop-blur-xl rounded-xl shadow-xl z-50 overflow-hidden" style="display: none;">
                                <div class="p-3 border-b border-white/5 flex items-center justify-between">
                                    <span class="font-semibold text-white">Notifications</span>
                                    <button x-show="unread > 0" @click="markAllRead()"
                                            class="text-xs text-green-400 hover:text-green-300">Mark all read</button>
                                </div>
                                {{-- Category filters --}}
                                <div class="flex items-center gap-1 px-3 py-2 border-b border-white/5">
                                    <template x-for="cat in ['all','team','match','achievement','general']" :key="cat">
                                        <button @click="filter = cat"
                                                :class="filter === cat ? 'bg-green-500/20 text-green-400 border-green-500/30' : 'bg-white/3 text-gray-400 border-white/10 hover:text-white'"
                                                class="px-2 py-1 text-xs rounded-md border capitalize transition" x-text="cat"></button>
                                    </template>
                                </div>
                                <div class="max-h-96 overflow-y-auto">
                                    <div x-show="loading" class="p-3 space-y-3">
                                        <template x-for="i in 4" :key="'notif-skel-'+i">
                                            <div class="flex items-start gap-3">
                                                <div class="skeleton skeleton-circle w-4 h-4 flex-shrink-0 mt-0.5"></div>
                                                <div class="flex-1 space-y-2">
                                                    <div class="skeleton skeleton-text w-full"></div>
                                                    <div class="skeleton skeleton-text w-1/3" style="height:0.625rem"></div>
                                                </div>
                                            </div>
                                        </template>
                                    </div>
                                    <template x-for="notification in filtered" :key="notification.id">
                                        <a :href="getLink(notification) || 'javascript:void(0)'"
                                           @click="markRead(notification)"
                                           :class="[
                                               notification.read_at ? 'bg-transparent' : 'bg-white/3',
                                               getLink(notification) ? 'cursor-pointer' : 'cursor-default'
                                           ]"
                                           class="block p-3 border-b border-white/5 last:border-0 hover:bg-white/5 transition">
                                            <div class="flex items-start gap-3">
                                                <div class="shrink-0 mt-0.5" :class="getCategoryColor(getCategory(notification))">
                                                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24" x-html="getCategoryIcon(getCategory(notification))"></svg>
                                                </div>
                                                <div class="min-w-0 flex-1">
                                                    <p class="text-sm text-white" x-text="notification.message"></p>
                                                    <div class="flex items-center gap-2 mt-1">
                                                        <span class="text-xs text-gray-500" x-text="notification.created_at"></span>
                                                        <span x-show="!notification.read_at" class="w-1.5 h-1.5 rounded-full bg-blue-500"></span>
                                                    </div>
                                                </div>
                                            </div>
                                        </a>
                                    </template>
                                    <div x-show="!loading && filtered.length === 0" class="p-4 text-center text-gray-400 text-sm">
                                        <span x-text="filter === 'all' ? 'No notifications' : 'No ' + filter + ' notifications'"></span>
                                    </div>
                                </div>
                            </div>
                        </div>

                        {{-- Live Activity Toggle --}}
                        <button id="right-sidebar-toggle" onclick="toggleRightSidebar()"
                                class="p-2 rounded-md text-gray-400 hover:text-white hover:bg-white/5 transition"
                                title="Live Activity" aria-expanded="false">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12h4l3-6 4 12 3-6h4"/>
                            </svg>
                        </button>

                        <div x-data="{ profileOpen: false }" class="relative">
                            <button @click="profileOpen = !profileOpen" @click.outside="profileOpen = false" class="flex items-center space-x-3 hover:opacity-80 transition">
                                <img src="{{ auth()->user()->avatar_display }}" alt="{{ auth()->user()->name }}" class="w-8 h-8 rounded-full">
                                <span class="text-sm">{{ auth()->user()->name }}</span>
                                <svg class="w-4 h-4 text-gray-400 transition-transform" :class="profileOpen ? 'rotate-180' : ''" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                                </svg>
                            </button>
                            <div x-show="profileOpen" x-transition:enter="transition ease-out duration-100" x-transition:enter-start="opacity-0 scale-95" x-transition:enter-end="opacity-100 scale-100" x-transition:leave="transition ease-in duration-75" x-transition:leave-start="opacity-100 scale-100" x-transition:leave-end="opacity-0 scale-95" class="absolute right-0 mt-2 w-56 glass-card backdrop-blur-xl rounded-lg shadow-xl z-50 py-1" style="display: none;">
                                <div class="px-4 py-3 border-b border-white/5">
                                    <p class="text-sm font-medium text-white">{{ auth()->user()->name }}</p>
                                    <p class="text-xs text-gray-400 capitalize">{{ auth()->user()->role }}</p>
                                </div>
                                <a href="{{ route('profile') }}" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                                    <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M16 7a4 4 0 11-8 0 4 4 0 018 0zM12 14a7 7 0 00-7 7h14a7 7 0 00-7-7z"/></svg>
                                    My Profile
                                </a>
                                <a href="{{ route('profile.settings') }}" class="flex items-center px-4 py-2 text-sm text-gray-300 hover:bg-white/5 hover:text-white transition">
                                    <svg class="w-4 h-4 mr-3 text-gray-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Settings
                                </a>
                                <a href="{{ route('discord.presence.settings') }}" class="flex items-center px-4 py-2 text-sm text-purple-400 hover:bg-white/5 hover:text-purple-300 transition">
                                    <svg class="w-4 h-4 mr-3 text-purple-500" fill="currentColor" viewBox="0 0 24 24"><path d="M20.317 4.3698a19.7913 19.7913 0 00-4.8851-1.5152.0741.0741 0 00-.0785.0371c-.211.3753-.4447.8648-.6083 1.2495-1.8447-.2762-3.68-.2762-5.4868 0-.1636-.3933-.4058-.8742-.6177-1.2495a.077.077 0 00-.0785-.037 19.7363 19.7363 0 00-4.8852 1.515.0699.0699 0 00-.0321.0277C.5334 9.0458-.319 13.5799.0992 18.0578a.0824.0824 0 00.0312.0561c2.0528 1.5076 4.0413 2.4228 5.9929 3.0294a.0777.0777 0 00.0842-.0276c.4616-.6304.8731-1.2952 1.226-1.9942a.076.076 0 00-.0416-.1057c-.6528-.2476-1.2743-.5495-1.8722-.8923a.077.077 0 01-.0076-.1277c.1258-.0943.2517-.1923.3718-.2914a.0743.0743 0 01.0776-.0105c3.9278 1.7933 8.18 1.7933 12.0614 0a.0739.0739 0 01.0785.0095c.1202.099.246.1981.3728.2924a.077.077 0 01-.0066.1276 12.2986 12.2986 0 01-1.873.8914.0766.0766 0 00-.0407.1067c.3604.698.7719 1.3628 1.225 1.9932a.076.076 0 00.0842.0286c1.961-.6067 3.9495-1.5219 6.0023-3.0294a.077.077 0 00.0313-.0552c.5004-5.177-.8382-9.6739-3.5485-13.6604a.061.061 0 00-.0312-.0286zM8.02 15.3312c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9555-2.4189 2.157-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.9555 2.4189-2.1569 2.4189zm7.9748 0c-1.1825 0-2.1569-1.0857-2.1569-2.419 0-1.3332.9554-2.4189 2.1569-2.4189 1.2108 0 2.1757 1.0952 2.1568 2.419 0 1.3332-.946 2.4189-2.1568 2.4189z"/></svg>
                                    Discord Presence
                                </a>
                                @if(auth()->user()->isAdmin())
                                <a href="{{ route('admin.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-green-400 hover:bg-white/5 hover:text-green-300 transition">
                                    <svg class="w-4 h-4 mr-3 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.066 2.573c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.573 1.066c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.066-2.573c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/></svg>
                                    Admin Panel
                                </a>
                                @endif
                                @if(auth()->user()->isReferee())
                                <a href="{{ route('referee.dashboard') }}" class="flex items-center px-4 py-2 text-sm text-blue-400 hover:bg-white/5 hover:text-blue-300 transition">
                                    <svg class="w-4 h-4 mr-3 text-blue-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m6 2a9 9 0 11-18 0 9 9 0 0118 0z"/></svg>
                                    Referee Dashboard
                                </a>
                                @endif
                                <div class="border-t border-white/5 mt-1 pt-1">
                                    <form method="POST" action="{{ route('logout') }}">
                                        @csrf
                                        <button type="submit" class="flex items-center w-full px-4 py-2 text-sm text-red-400 hover:bg-white/5 hover:text-red-300 transition">
                                            <svg class="w-4 h-4 mr-3 text-red-500" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M17 16l4-4m0 0l-4-4m4 4H7m6 4v1a3 3 0 01-3 3H6a3 3 0 01-3-3V7a3 3 0 013-3h4a3 3 0 013 3v1"/></svg>
                                            Logout
                                        </button>
                                    </form>
                                </div>
                            </div>
                        </div>
                    @else
                        <a href="{{ route('login') }}" class="px-3 py-2 text-sm font-medium text-gray-300 hover:text-white transition">
                            Login
                        </a>
                        <a href="{{ route('register') }}" class="px-4 py-2 text-sm font-medium bg-green-600 hover:bg-green-700 rounded-md transition btn-glow">
                            Register
                        </a>
                        <a href="{{ route('auth.steam') }}" class="p-2 bg-white/5 hover:bg-white/10 rounded-md transition" title="Login with Steam">
                            <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                                <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174l2.896-4.143c-.468-.116-.91-.293-1.317-.525L4.5 21.75c-.913-.288-1.772-.684-2.563-1.176l4.707-3.308c-.155-.369-.277-.758-.359-1.162L0 19.293V12C0 5.373 5.373 0 12 0zm0 4.5c-4.136 0-7.5 3.364-7.5 7.5 0 .768.115 1.509.328 2.206l3.908-2.745c.493-2.293 2.535-4.011 4.997-4.011 2.795 0 5.067 2.272 5.067 5.067 0 2.462-1.758 4.514-4.089 4.977l-2.725 3.896C9.788 22.285 10.869 22.5 12 22.5c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
                            </svg>
                        </a>
                    @endauth
                </div>
            </div>
        </div>
    </nav>

    {{-- Content row: sidebar spacer + main --}}
    <div class="flex flex-1">
    @auth
    <div id="left-sidebar-spacer" class="hidden w-64 flex-shrink-0"></div>
    @endauth

    <main class="flex-1 min-w-0 overflow-x-hidden relative py-6 px-4 sm:px-6 lg:px-8">
        <div id="main-inner" class="max-w-6xl mx-auto w-full">
        @if(session('success'))
            <div class="mb-4 p-4 bg-green-600 rounded-md">
                {{ session('success') }}
            </div>
        @endif

        @if(session('error'))
            <div class="mb-4 p-4 bg-red-600 rounded-md">
                {{ session('error') }}
            </div>
        @endif

        @yield('content')
        </div>
    </main>

    @auth
    <div id="right-sidebar-spacer" class="hidden w-80 flex-shrink-0"></div>
    @endauth
    </div>

    {{-- Footer: full width, outside sidebar+content wrapper --}}
    <footer class="relative glass-dark border-t border-white/10 mt-auto">
        <div class="absolute top-0 left-0 right-0 h-px bg-gradient-to-r from-transparent via-green-500/30 to-transparent"></div>
        <div class="py-8 px-4 sm:px-6 lg:px-8">
            <div class="max-w-6xl mx-auto w-full">
                <div class="flex flex-wrap items-center justify-center gap-5 text-sm text-gray-400">
                    <a href="{{ route('faq') }}" class="hover:text-green-400 transition">FAQ</a>
                    <span class="text-gray-700">•</span>
                    <a href="{{ route('rules') }}" class="hover:text-green-400 transition">Rules</a>
                    <span class="text-gray-700">•</span>
                    <a href="{{ route('privacy') }}" class="hover:text-green-400 transition">Privacy Policy</a>
                    <span class="text-gray-700">•</span>
                    <a href="{{ route('terms') }}" class="hover:text-green-400 transition">Terms of Service</a>
                </div>

                <div class="glow-line my-4"></div>

                @if(site_setting('custom_footer_text'))
                <p class="text-center text-gray-300 text-sm mb-2">
                    {{ site_setting('custom_footer_text') }}
                </p>
                @endif
                <p class="text-center text-gray-500 text-xs mb-3">
                    Built with ❤️ for <a href="https://reforger.armaplatform.com" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition">Arma Reforger</a> and <a href="https://www.bohemia.net" target="_blank" rel="noopener noreferrer" class="text-gray-400 hover:text-white transition">Bohemia Interactive</a> for creating amazing games.
                </p>
                <p class="text-center text-gray-400 text-sm mb-1">
                    &copy; {{ date('Y') }} {{ site_setting('site_name', config('app.name')) }}. All rights reserved.
                </p>
                <p class="text-center text-gray-600 text-xs">
                    Not affiliated with Bohemia Interactive.
                </p>
            </div>
        </div>
    </footer>
    </div>

    {{-- Achievement Unlocked Popup --}}
    @auth
    <div x-data="{
        popups: [],
        add(detail) {
            const id = Date.now() + Math.random();
            const popup = {
                id,
                name: detail.achievement_name || 'Achievement',
                icon: detail.achievement_icon || '🏆',
                color: detail.achievement_color || '#22c55e',
                progress: 100,
            };
            this.popups.push(popup);
            this.$nextTick(() => {
                const el = this.$refs['popup-' + id];
                if (el && window.animateAchievementPopup) {
                    window.animateAchievementPopup(el, popup.color);
                }
                this.startDismissTimer(id);
            });
        },
        startDismissTimer(id) {
            const duration = 6000;
            const interval = 50;
            const popup = this.popups.find(p => p.id === id);
            if (!popup) return;
            const timer = setInterval(() => {
                const p = this.popups.find(p => p.id === id);
                if (!p) { clearInterval(timer); return; }
                p.progress -= (interval / duration) * 100;
                if (p.progress <= 0) {
                    clearInterval(timer);
                    this.dismiss(id);
                }
            }, interval);
        },
        dismiss(id) {
            const el = this.$refs['popup-' + id];
            if (el && window.dismissAchievementPopup) {
                window.dismissAchievementPopup(el, () => {
                    this.popups = this.popups.filter(p => p.id !== id);
                });
            } else {
                this.popups = this.popups.filter(p => p.id !== id);
            }
        },
        navigate() {
            window.location.href = '/achievements';
        }
    }" @achievement-unlocked.window="add($event.detail)"
       class="fixed bottom-6 right-6 z-[60] flex flex-col-reverse gap-3 pointer-events-none">
        <template x-for="popup in popups" :key="popup.id">
            <div :x-ref="'popup-' + popup.id"
                 @click="navigate()"
                 class="achievement-popup pointer-events-auto cursor-pointer w-80 rounded-xl overflow-hidden shadow-2xl border border-white/10"
                 :style="'background: linear-gradient(135deg, rgba(17,24,39,0.95), rgba(31,41,55,0.95)); border-left: 3px solid ' + popup.color">
                <div class="achievement-popup-shine"></div>
                <div class="relative p-4 flex items-center gap-3">
                    <div class="text-3xl shrink-0" x-text="popup.icon"></div>
                    <div class="min-w-0 flex-1">
                        <p class="text-xs font-bold uppercase tracking-widest" :style="'color: ' + popup.color">Achievement Unlocked!</p>
                        <p class="text-sm font-semibold text-white truncate mt-0.5" x-text="popup.name"></p>
                    </div>
                    <button @click.stop="dismiss(popup.id)" class="shrink-0 text-gray-500 hover:text-white transition p-1">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24"><path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/></svg>
                    </button>
                </div>
                <div class="h-0.5 bg-white/3">
                    <div class="h-full transition-all duration-100 ease-linear rounded-full" :style="'width: ' + popup.progress + '%; background: ' + popup.color"></div>
                </div>
            </div>
        </template>
    </div>
    @endauth

    @stack('scripts')
</body>
</html>
