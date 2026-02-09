@extends('admin.layout')

@section('title', 'Quick Messages')

@section('admin-content')
<div class="space-y-6">
    {{-- Header --}}
    <div class="flex items-center justify-between">
        <div>
            <h1 class="text-2xl font-bold text-white">Quick Messages</h1>
            <p class="text-sm text-gray-500 mt-1">Send pre-configured messages to the server</p>
        </div>
        <a href="{{ route('admin.server.dashboard') }}" class="px-3 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm transition flex items-center gap-2">
            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 17l-5-5m0 0l5-5m-5 5h12"/>
            </svg>
            Back to Dashboard
        </a>
    </div>

    {{-- Section 1: Send Quick Message --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 10h.01M12 10h.01M16 10h.01M9 16H5a2 2 0 01-2-2V6a2 2 0 012-2h14a2 2 0 012 2v8a2 2 0 01-2 2h-5l-5 5v-5z"/>
            </svg>
            Send Quick Message
        </h2>

        @if(!empty($templates) && count($templates) > 0)
            <div class="grid grid-cols-1 md:grid-cols-2 lg:grid-cols-3 gap-4">
                @foreach($templates as $template)
                    <div class="bg-gray-900/50 border border-gray-700 rounded-lg p-4 flex flex-col justify-between">
                        <div class="mb-3">
                            <p class="font-bold text-white mb-1">{{ $template['label'] }}</p>
                            <p class="text-sm text-gray-400">{{ $template['message'] }}</p>
                        </div>
                        <form action="{{ route('admin.server.quick-messages.send') }}" method="POST">
                            @csrf
                            <input type="hidden" name="message" value="{{ $template['message'] }}">
                            <button type="submit" class="w-full px-3 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-medium transition flex items-center justify-center gap-2">
                                <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 19l9 2-9-18-9 18 9-2zm0 0v-8"/>
                                </svg>
                                Send
                            </button>
                        </form>
                    </div>
                @endforeach
            </div>
        @else
            <div class="text-center py-8">
                <svg class="w-12 h-12 text-gray-600 mx-auto mb-3" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M20 13V6a2 2 0 00-2-2H6a2 2 0 00-2 2v7m16 0v5a2 2 0 01-2 2H6a2 2 0 01-2-2v-5m16 0h-2.586a1 1 0 00-.707.293l-2.414 2.414a1 1 0 01-.707.293h-3.172a1 1 0 01-.707-.293l-2.414-2.414A1 1 0 006.586 13H4"/>
                </svg>
                <p class="text-gray-500">No templates configured yet</p>
                <p class="text-sm text-gray-600 mt-1">Add templates below to get started</p>
            </div>
        @endif
    </div>

    {{-- Section 2: Manage Templates --}}
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6" x-data="{ templates: @js($templates ?? []) }">
        <h2 class="text-lg font-bold text-white mb-4 flex items-center gap-2">
            <svg class="w-5 h-5 text-green-500" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M11 5H6a2 2 0 00-2 2v11a2 2 0 002 2h11a2 2 0 002-2v-5m-1.414-9.414a2 2 0 112.828 2.828L11.828 15H9v-2.828l8.586-8.586z"/>
            </svg>
            Manage Templates
        </h2>

        <form action="{{ route('admin.server.quick-messages.save') }}" method="POST">
            @csrf

            <div class="space-y-3 mb-4">
                <template x-for="(template, index) in templates" :key="index">
                    <div class="flex items-center gap-3 bg-gray-900/50 border border-gray-700 rounded-lg p-3">
                        <div class="flex-1 grid grid-cols-1 md:grid-cols-2 gap-3">
                            <div>
                                <label class="block text-xs text-gray-500 mb-1" x-text="'Label'"></label>
                                <input type="text"
                                       :name="'templates[' + index + '][label]'"
                                       x-model="template.label"
                                       placeholder="Template label"
                                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-500 focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none">
                            </div>
                            <div>
                                <label class="block text-xs text-gray-500 mb-1" x-text="'Message'"></label>
                                <input type="text"
                                       :name="'templates[' + index + '][message]'"
                                       x-model="template.message"
                                       placeholder="Message text"
                                       class="w-full bg-gray-800 border border-gray-600 rounded-lg px-3 py-2 text-white text-sm placeholder-gray-500 focus:border-green-500 focus:ring-1 focus:ring-green-500 focus:outline-none">
                            </div>
                        </div>
                        <button type="button"
                                @click="templates.splice(index, 1)"
                                class="flex-shrink-0 p-2 text-red-400 hover:text-red-300 hover:bg-red-500/20 rounded-lg transition"
                                title="Remove template">
                            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M6 18L18 6M6 6l12 12"/>
                            </svg>
                        </button>
                    </div>
                </template>

                <div x-show="templates.length === 0" class="text-center py-6 text-gray-500 text-sm">
                    No templates added. Click "Add Template" to create one.
                </div>
            </div>

            <div class="flex items-center justify-between">
                <button type="button"
                        @click="templates.push({ label: '', message: '' })"
                        class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 4v16m8-8H4"/>
                    </svg>
                    Add Template
                </button>

                <button type="submit"
                        class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg text-sm font-medium transition flex items-center gap-2">
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M5 13l4 4L19 7"/>
                    </svg>
                    Save Templates
                </button>
            </div>
        </form>
    </div>
</div>
@endsection
