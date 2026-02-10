@extends('admin.layout')

@section('title', 'Settings')

@section('admin-content')
@php
    $groupIcons = [
        'General' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 12l2-2m0 0l7-7 7 7M5 10v10a1 1 0 001 1h3m10-11l2 2m-2-2v10a1 1 0 01-1 1h-3m-6 0a1 1 0 001-1v-4a1 1 0 011-1h2a1 1 0 011 1v4a1 1 0 001 1m-6 0h6"/>',
        'SEO' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 21l-6-6m2-5a7 7 0 11-14 0 7 7 0 0114 0z"/>',
        'Security' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>',
        'Server Tracking' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 12h14M5 12a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v4a2 2 0 01-2 2M5 12a2 2 0 00-2 2v4a2 2 0 002 2h14a2 2 0 002-2v-4a2 2 0 00-2-2"/>',
        'Leaderboard' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 19v-6a2 2 0 00-2-2H5a2 2 0 00-2 2v6a2 2 0 002 2h2a2 2 0 002-2zm0 0V9a2 2 0 012-2h2a2 2 0 012 2v10m-6 0a2 2 0 002 2h2a2 2 0 002-2m0 0V5a2 2 0 012-2h2a2 2 0 012 2v14a2 2 0 01-2 2h-2a2 2 0 01-2-2z"/>',
        'Tournaments' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"/>',
        'Moderation' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M18.364 18.364A9 9 0 005.636 5.636m12.728 12.728A9 9 0 015.636 5.636m12.728 12.728L5.636 5.636"/>',
        'Notifications' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 17h5l-1.405-1.405A2.032 2.032 0 0118 14.158V11a6.002 6.002 0 00-4-5.659V5a2 2 0 10-4 0v.341C7.67 6.165 6 8.388 6 11v3.159c0 .538-.214 1.055-.595 1.436L4 17h5m6 0v1a3 3 0 11-6 0v-1m6 0H9"/>',
        'Appearance' => '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M7 21a4 4 0 01-4-4V5a2 2 0 012-2h4a2 2 0 012 2v12a4 4 0 01-4 4zm0 0h12a2 2 0 002-2v-4a2 2 0 00-2-2h-2.343M11 7.343l1.657-1.657a2 2 0 012.828 0l2.829 2.829a2 2 0 010 2.828l-8.486 8.485M7 17h.01"/>',
    ];
    $groupDescriptions = [
        'General' => 'Site identity, maintenance mode',
        'SEO' => 'Search engines, social sharing, analytics',
        'Security' => 'Auth, sessions, two-factor',
        'Server Tracking' => 'BattleMetrics, A2S queries',
        'Leaderboard' => 'Rankings, categories, filters',
        'Tournaments' => 'Brackets, team limits, reminders',
        'Moderation' => 'Chat filters, auto-ban rules',
        'Notifications' => 'Discord, retention periods',
        'Appearance' => 'Logo, colors, custom CSS',
    ];
@endphp

<div x-data="{ activeTab: '{{ array_key_first($groupedSettings) }}' }" class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Site Settings</h1>
            <p class="text-sm text-gray-500 mt-1">Manage your community configuration</p>
        </div>
    </div>

    <form action="{{ route('admin.settings.update') }}" method="POST">
        @csrf

        <div class="flex gap-6">
            {{-- Sidebar Navigation --}}
            <div class="w-56 flex-shrink-0">
                <nav class="sticky top-24 space-y-1">
                    @foreach($groupedSettings as $group => $settings)
                    <button type="button" @click="activeTab = '{{ $group }}'"
                            :class="activeTab === '{{ $group }}'
                                ? 'bg-green-500/10 border-green-500/30 text-green-400'
                                : 'border-transparent text-gray-400 hover:bg-white/5 hover:text-white'"
                            class="w-full flex items-center gap-3 px-3 py-2.5 rounded-lg border transition text-left">
                        <svg class="w-4 h-4 flex-shrink-0" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            {!! $groupIcons[$group] ?? '<path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6V4m0 2a2 2 0 100 4m0-4a2 2 0 110 4m-6 8a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4m6 6v10m6-2a2 2 0 100-4m0 4a2 2 0 110-4m0 4v2m0-6V4"/>' !!}
                        </svg>
                        <div class="min-w-0">
                            <span class="text-sm font-medium block truncate">{{ $group }}</span>
                            <span class="text-[10px] text-gray-500 block truncate" x-show="activeTab === '{{ $group }}'">{{ $groupDescriptions[$group] ?? '' }}</span>
                        </div>
                    </button>
                    @endforeach

                    <div class="border-t border-white/5 my-3"></div>

                    {{-- Save Button in sidebar --}}
                    <button type="submit"
                            class="w-full flex items-center justify-center gap-2 px-3 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                        </svg>
                        Save Settings
                    </button>
                </nav>
            </div>

            {{-- Settings Content --}}
            <div class="flex-1 min-w-0">
                @foreach($groupedSettings as $group => $settings)
                <div x-show="activeTab === '{{ $group }}'" x-cloak>
                    {{-- Group Header --}}
                    <div class="flex items-center gap-3 mb-5">
                        <div class="p-2 bg-green-500/10 rounded-lg">
                            <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                {!! $groupIcons[$group] ?? '' !!}
                            </svg>
                        </div>
                        <div>
                            <h2 class="text-sm font-semibold text-white uppercase tracking-wider">{{ $group }}</h2>
                            <p class="text-xs text-gray-500">{{ $groupDescriptions[$group] ?? '' }}</p>
                        </div>
                    </div>

                    {{-- Settings List --}}
                    <div class="space-y-4">
                        @foreach($settings as $setting)
                        <div class="glass-card rounded-xl p-5 hover:border-white/10 transition">
                            <div class="flex flex-col lg:flex-row lg:items-start gap-4">
                                <div class="lg:w-2/5">
                                    <label for="{{ $setting['key'] }}" class="block text-sm font-medium text-white">{{ $setting['label'] }}</label>
                                    @if($setting['description'])
                                    <p class="text-xs text-gray-500 mt-1 leading-relaxed">{{ $setting['description'] }}</p>
                                    @endif
                                    <code class="text-[10px] text-gray-600 mt-2 block">{{ $setting['key'] }}</code>
                                </div>
                                <div class="lg:w-3/5">
                                    @if($setting['type'] === 'boolean')
                                        {{-- Toggle Switch --}}
                                        <label class="relative inline-flex items-center cursor-pointer" x-data="{ on: {{ $setting['value'] ? 'true' : 'false' }} }">
                                            <input type="checkbox" name="{{ $setting['key'] }}" value="1" class="sr-only peer"
                                                   x-model="on" :checked="on">
                                            <div class="w-11 h-6 bg-white/5 rounded-full peer peer-checked:after:translate-x-full peer-checked:after:border-white after:content-[''] after:absolute after:top-[2px] after:left-[2px] after:bg-white after:border-gray-300 after:border after:rounded-full after:h-5 after:w-5 after:transition-all peer-checked:bg-green-600 transition-colors"></div>
                                            <span class="ml-3 text-sm" :class="on ? 'text-green-400' : 'text-gray-500'" x-text="on ? 'Enabled' : 'Disabled'"></span>
                                        </label>

                                    @elseif($setting['type'] === 'text')
                                        {{-- Textarea --}}
                                        <textarea name="{{ $setting['key'] }}" id="{{ $setting['key'] }}" rows="3"
                                            class="w-full bg-gray-900/50 border border-white/10 text-white rounded-lg px-4 py-2.5 focus:ring-green-500 focus:border-green-500 text-sm placeholder-gray-600 font-mono"
                                            placeholder="{{ $setting['label'] }}">{{ $setting['value'] }}</textarea>

                                    @elseif($setting['type'] === 'integer')
                                        {{-- Number Input --}}
                                        <input type="number" name="{{ $setting['key'] }}" id="{{ $setting['key'] }}" value="{{ $setting['value'] }}"
                                            class="w-full lg:w-40 bg-gray-900/50 border border-white/10 text-white rounded-lg px-4 py-2.5 focus:ring-green-500 focus:border-green-500 text-sm tabular-nums">

                                    @elseif($setting['type'] === 'color')
                                        {{-- Color Picker --}}
                                        <div class="flex items-center gap-3" x-data="{ color: '{{ $setting['value'] ?? '#22c55e' }}' }">
                                            <input type="color" name="{{ $setting['key'] }}" id="{{ $setting['key'] }}"
                                                x-model="color"
                                                class="h-10 w-14 bg-transparent border-2 border-white/10 rounded-lg cursor-pointer hover:border-white/10 transition">
                                            <input type="text" x-model="color" readonly
                                                class="w-28 bg-gray-900/50 border border-white/10 text-white rounded-lg px-3 py-2.5 text-sm font-mono text-center">
                                            <div class="w-8 h-8 rounded-full border-2 border-white/10" :style="'background-color: ' + color"></div>
                                        </div>

                                    @elseif($setting['type'] === 'json' && $setting['options'])
                                        {{-- Multi-Checkboxes --}}
                                        @php
                                            $options = json_decode($setting['options'], true) ?? [];
                                            $selectedValues = json_decode($setting['value'], true) ?? [];
                                        @endphp
                                        <div class="grid grid-cols-2 xl:grid-cols-3 gap-2">
                                            @foreach($options as $option)
                                            <label class="flex items-center gap-2.5 text-sm text-gray-300 cursor-pointer px-3 py-2 rounded-lg hover:bg-white/3 transition">
                                                <input type="checkbox" name="{{ $setting['key'] }}[]" value="{{ $option }}"
                                                       {{ in_array($option, $selectedValues) ? 'checked' : '' }}
                                                       class="rounded bg-white/5 border-white/10 text-green-500 focus:ring-green-500 focus:ring-offset-0">
                                                <span>{{ str_replace('_', ' ', ucfirst($option)) }}</span>
                                            </label>
                                            @endforeach
                                        </div>

                                    @elseif($setting['type'] === 'string' && $setting['options'])
                                        {{-- Select Dropdown --}}
                                        @php $options = json_decode($setting['options'], true) ?? []; @endphp
                                        <select name="{{ $setting['key'] }}" id="{{ $setting['key'] }}"
                                            class="w-full lg:w-auto bg-gray-900/50 border border-white/10 text-white rounded-lg px-4 py-2.5 focus:ring-green-500 focus:border-green-500 text-sm">
                                            @foreach($options as $option)
                                            <option value="{{ $option }}" {{ $setting['value'] === $option ? 'selected' : '' }}>{{ $option }}</option>
                                            @endforeach
                                        </select>

                                    @else
                                        {{-- Default Text Input --}}
                                        <input type="text" name="{{ $setting['key'] }}" id="{{ $setting['key'] }}" value="{{ $setting['value'] }}"
                                            class="w-full bg-gray-900/50 border border-white/10 text-white rounded-lg px-4 py-2.5 focus:ring-green-500 focus:border-green-500 text-sm placeholder-gray-600"
                                            placeholder="{{ $setting['label'] }}">
                                    @endif
                                </div>
                            </div>
                        </div>
                        @endforeach
                    </div>

                    {{-- Bottom save for long groups --}}
                    @if(count($settings) > 3)
                    <div class="flex justify-end mt-6">
                        <button type="submit"
                                class="flex items-center gap-2 px-6 py-2.5 bg-green-600 hover:bg-green-500 text-white rounded-lg transition font-medium text-sm">
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                            </svg>
                            Save Settings
                        </button>
                    </div>
                    @endif
                </div>
                @endforeach
            </div>
        </div>
    </form>

    {{-- System Information --}}
    <div class="glass-card rounded-xl p-6 mt-8">
        <details class="group">
            <summary class="flex items-center justify-between cursor-pointer">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"/>
                    </svg>
                    System Information
                </h2>
                <svg class="w-5 h-5 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="grid md:grid-cols-3 gap-3 mt-4">
                <div class="bg-white/3 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">PHP</p>
                    <p class="text-sm font-medium text-white mt-0.5">{{ phpversion() }}</p>
                </div>
                <div class="bg-white/3 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Laravel</p>
                    <p class="text-sm font-medium text-white mt-0.5">{{ app()->version() }}</p>
                </div>
                <div class="bg-white/3 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Environment</p>
                    <p class="text-sm font-medium mt-0.5 {{ config('app.env') === 'production' ? 'text-green-400' : 'text-yellow-400' }}">{{ config('app.env') }}</p>
                </div>
                <div class="bg-white/3 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Server Time</p>
                    <p class="text-sm font-medium text-white mt-0.5">{{ now()->format('Y-m-d H:i:s') }}</p>
                </div>
                <div class="bg-white/3 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Timezone</p>
                    <p class="text-sm font-medium text-white mt-0.5">{{ config('app.timezone') }}</p>
                </div>
                <div class="bg-white/3 rounded-lg p-3">
                    <p class="text-xs text-gray-500 uppercase tracking-wider">Cache</p>
                    <p class="text-sm font-medium text-white mt-0.5">{{ config('cache.default') }}</p>
                </div>
            </div>
        </details>
    </div>

    {{-- Quick Actions --}}
    <div class="glass-card rounded-xl p-6">
        <details class="group">
            <summary class="flex items-center justify-between cursor-pointer">
                <h2 class="text-sm font-semibold text-white uppercase tracking-wider flex items-center gap-2">
                    <svg class="w-5 h-5 text-gray-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 10V3L4 14h7v7l9-11h-7z"/>
                    </svg>
                    Quick Actions
                </h2>
                <svg class="w-5 h-5 text-gray-400 transition-transform group-open:rotate-180" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 9l-7 7-7-7"/>
                </svg>
            </summary>

            <div class="flex flex-wrap gap-3 mt-4">
                <form action="{{ route('admin.cache.clear') }}" method="POST">
                    @csrf
                    <button type="submit" class="flex items-center gap-2 px-4 py-2 bg-red-600/20 border border-red-500/30 hover:bg-red-600/30 text-red-400 rounded-lg transition text-sm">
                        <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M19 7l-.867 12.142A2 2 0 0116.138 21H7.862a2 2 0 01-1.995-1.858L5 7m5 4v6m4-6v6m1-10V4a1 1 0 00-1-1h-4a1 1 0 00-1 1v3M4 7h16"/>
                        </svg>
                        Clear All Cache
                    </button>
                </form>
            </div>
        </details>
    </div>
</div>
@endsection
