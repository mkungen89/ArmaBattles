@extends('layouts.app')

@section('title', 'Embed Widget - ' . $server->name)

@section('content')
<div class="space-y-6">
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Server Status Widget</h1>
            <p class="text-gray-400 mt-1">Embed {{ $server->name }} status on your website</p>
        </div>
        <a href="{{ route('servers.show', $server) }}" class="px-4 py-2 bg-gray-700 text-gray-300 rounded-lg hover:bg-gray-600 transition text-sm">
            Back to Server
        </a>
    </div>

    <div class="grid grid-cols-1 lg:grid-cols-2 gap-6">
        {{-- Preview --}}
        <div class="space-y-4">
            <h2 class="text-lg font-semibold text-white">Preview</h2>
            <div class="bg-gray-900 rounded-xl p-6 border border-gray-700/50">
                <iframe id="widgetPreview"
                        src="{{ route('servers.widget', $server) }}?theme=dark&accent=%2322c55e"
                        style="width: 100%; height: 200px; border: none; border-radius: 12px;"
                        loading="lazy"></iframe>
            </div>

            {{-- Compact preview --}}
            <div class="bg-gray-900 rounded-xl p-6 border border-gray-700/50">
                <p class="text-sm text-gray-400 mb-3">Compact version</p>
                <iframe id="widgetPreviewCompact"
                        src="{{ route('servers.widget', $server) }}?theme=dark&accent=%2322c55e&compact=1"
                        style="width: 100%; height: 120px; border: none; border-radius: 12px;"
                        loading="lazy"></iframe>
            </div>
        </div>

        {{-- Configuration --}}
        <div class="space-y-4" x-data="{
            theme: 'dark',
            accent: '#22c55e',
            compact: false,
            width: '400',
            height: '200',
            get widgetUrl() {
                let url = '{{ route('servers.widget', $server) }}?theme=' + this.theme + '&accent=' + encodeURIComponent(this.accent);
                if (this.compact) url += '&compact=1';
                return url;
            },
            get embedCode() {
                let h = this.compact ? '130' : this.height;
                return '<iframe src=&quot;' + this.widgetUrl + '&quot; width=&quot;' + this.width + '&quot; height=&quot;' + h + '&quot; style=&quot;border:none;border-radius:12px;&quot; loading=&quot;lazy&quot;></iframe>';
            },
            get apiUrl() {
                return '{{ route('servers.widget.api', $server) }}';
            },
            updatePreview() {
                document.getElementById('widgetPreview').src = this.widgetUrl;
                document.getElementById('widgetPreviewCompact').src = this.widgetUrl + '&compact=1';
            },
            copyToClipboard(text) {
                navigator.clipboard.writeText(text.replace(/&quot;/g, '\"').replace(/&amp;/g, '&'));
            }
        }">
            <h2 class="text-lg font-semibold text-white">Configuration</h2>

            <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-6 space-y-4">
                {{-- Theme --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Theme</label>
                    <div class="flex gap-3">
                        <button @click="theme = 'dark'; updatePreview()"
                                :class="theme === 'dark' ? 'ring-2 ring-green-500' : ''"
                                class="px-4 py-2 bg-gray-900 text-white rounded-lg text-sm border border-gray-600">Dark</button>
                        <button @click="theme = 'light'; updatePreview()"
                                :class="theme === 'light' ? 'ring-2 ring-green-500' : ''"
                                class="px-4 py-2 bg-white text-gray-900 rounded-lg text-sm border border-gray-300">Light</button>
                    </div>
                </div>

                {{-- Accent Color --}}
                <div>
                    <label class="block text-sm font-medium text-gray-300 mb-2">Accent Color</label>
                    <div class="flex items-center gap-3">
                        <input type="color" x-model="accent" @change="updatePreview()"
                               class="w-10 h-10 rounded-lg cursor-pointer border-0 bg-transparent">
                        <input type="text" x-model="accent" @change="updatePreview()"
                               class="px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm w-28">
                        <div class="flex gap-2">
                            <button @click="accent = '#22c55e'; updatePreview()" class="w-6 h-6 rounded-full bg-green-500 border-2 border-gray-600 hover:border-white transition"></button>
                            <button @click="accent = '#3b82f6'; updatePreview()" class="w-6 h-6 rounded-full bg-blue-500 border-2 border-gray-600 hover:border-white transition"></button>
                            <button @click="accent = '#a855f7'; updatePreview()" class="w-6 h-6 rounded-full bg-purple-500 border-2 border-gray-600 hover:border-white transition"></button>
                            <button @click="accent = '#ef4444'; updatePreview()" class="w-6 h-6 rounded-full bg-red-500 border-2 border-gray-600 hover:border-white transition"></button>
                            <button @click="accent = '#f59e0b'; updatePreview()" class="w-6 h-6 rounded-full bg-amber-500 border-2 border-gray-600 hover:border-white transition"></button>
                        </div>
                    </div>
                </div>

                {{-- Compact --}}
                <div>
                    <label class="flex items-center gap-3 cursor-pointer">
                        <input type="checkbox" x-model="compact" @change="updatePreview()"
                               class="w-4 h-4 text-green-500 bg-gray-700 border-gray-600 rounded focus:ring-green-500">
                        <span class="text-sm text-gray-300">Compact mode</span>
                    </label>
                </div>

                {{-- Size --}}
                <div class="grid grid-cols-2 gap-4">
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Width (px)</label>
                        <input type="number" x-model="width" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm">
                    </div>
                    <div>
                        <label class="block text-sm font-medium text-gray-300 mb-1">Height (px)</label>
                        <input type="number" x-model="height" class="w-full px-3 py-2 bg-gray-700 border border-gray-600 rounded-lg text-white text-sm">
                    </div>
                </div>
            </div>

            {{-- Embed Code --}}
            <h2 class="text-lg font-semibold text-white">Embed Code</h2>
            <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-400">HTML Iframe</span>
                    <button @click="copyToClipboard(embedCode)" class="text-xs text-green-400 hover:text-green-300">Copy</button>
                </div>
                <pre class="bg-gray-900 rounded-lg p-3 text-xs text-gray-300 overflow-x-auto"><code x-text="embedCode.replace(/&quot;/g, '\"').replace(/&amp;/g, '&')"></code></pre>
            </div>

            {{-- JSON API --}}
            <div class="bg-gray-800 rounded-xl border border-gray-700/50 p-4">
                <div class="flex items-center justify-between mb-2">
                    <span class="text-xs font-medium text-gray-400">JSON API Endpoint</span>
                    <button @click="copyToClipboard(apiUrl)" class="text-xs text-green-400 hover:text-green-300">Copy</button>
                </div>
                <pre class="bg-gray-900 rounded-lg p-3 text-xs text-gray-300 overflow-x-auto"><code x-text="apiUrl"></code></pre>
            </div>
        </div>
    </div>
</div>
@endsection
