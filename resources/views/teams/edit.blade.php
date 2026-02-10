@extends('layouts.app')
@section('title', 'Edit ' . $team->name)
@section('content')
    <div class="mb-6">
        <a href="{{ route('teams.my') }}" class="text-gray-400 hover:text-white transition text-sm">
            &larr; Back to my platoon
        </a>
    </div>
    <div class="glass-card p-6 mb-6">
        <h1 class="text-2xl font-bold text-white mb-6">Edit platoon</h1>
        <form action="{{ route('teams.update', $team) }}" method="POST" enctype="multipart/form-data" class="space-y-6">
            @csrf
            @method('PUT')
            <div>
                <label for="name" class="block text-sm font-medium text-gray-400 mb-2">Platoon name *</label>
                <input type="text" name="name" id="name" value="{{ old('name', $team->name) }}" required
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="tag" class="block text-sm font-medium text-gray-400 mb-2">Tag (short name) *</label>
                <input type="text" name="tag" id="tag" value="{{ old('tag', $team->tag) }}" required maxlength="10"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 uppercase">
                @error('tag')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="description" class="block text-sm font-medium text-gray-400 mb-2">Description</label>
                <textarea name="description" id="description" rows="4"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">{{ old('description', $team->description) }}</textarea>
                @error('description')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div>
                <label for="logo_url" class="block text-sm font-medium text-gray-400 mb-2">Logo URL</label>
                <input type="url" name="logo_url" id="logo_url" value="{{ old('logo_url', $team->logo_url) }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500">
                @error('logo_url')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                @if($team->logo_url)
                    <div class="mt-3">
                        <p class="text-sm text-gray-400 mb-2">Current logo:</p>
                        <img src="{{ $team->logo_url }}" alt="{{ $team->name }}" class="w-16 h-16 rounded-lg object-cover">
                    </div>
                @endif
            </div>
            <!-- Avatar Upload -->
            <div>
                <label for="avatar" class="block text-sm font-medium text-gray-400 mb-2">Avatar image</label>
                <input type="file" name="avatar" id="avatar" accept="image/jpeg,image/png,image/webp"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-white/10 file:text-white hover:file:bg-white/15">
                <p class="mt-1 text-xs text-gray-500">Recommended: 200 x 200 px. JPG, PNG or WebP. Max 1 MB. Overrides logo URL if set.</p>
                @error('avatar')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                @if($team->avatar_path)
                    <div class="mt-3 flex items-center gap-4">
                        <div>
                            <p class="text-sm text-gray-400 mb-2">Current avatar:</p>
                            <img src="{{ Storage::url($team->avatar_path) }}" alt="{{ $team->name }}" class="w-16 h-16 rounded-lg object-cover">
                        </div>
                        <label class="flex items-center gap-2 text-sm text-red-400 cursor-pointer">
                            <input type="checkbox" name="remove_avatar" value="1" class="rounded bg-white/5 border-white/10 text-red-500 focus:ring-red-500">
                            Remove avatar
                        </label>
                    </div>
                @endif
            </div>
            <!-- Header Image Upload -->
            <div>
                <label for="header" class="block text-sm font-medium text-gray-400 mb-2">Header banner</label>
                <input type="file" name="header" id="header" accept="image/jpeg,image/png,image/webp"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500 file:mr-4 file:py-1 file:px-3 file:rounded file:border-0 file:text-sm file:bg-white/10 file:text-white hover:file:bg-white/15">
                <p class="mt-1 text-xs text-gray-500">JPG, PNG or WebP. Max 2MB. Recommended: 1200x400px.</p>
                @error('header')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
                @if($team->header_image)
                    <div class="mt-3">
                        <p class="text-sm text-gray-400 mb-2">Current header:</p>
                        <img src="{{ Storage::url($team->header_image) }}" alt="Header" class="w-full h-24 rounded-lg object-cover">
                        <label class="flex items-center gap-2 text-sm text-red-400 cursor-pointer mt-2">
                            <input type="checkbox" name="remove_header" value="1" class="rounded bg-white/5 border-white/10 text-red-500 focus:ring-red-500">
                            Remove header
                        </label>
                    </div>
                @endif
            </div>
            <!-- Website -->
            <div>
                <label for="website" class="block text-sm font-medium text-gray-400 mb-2">Website</label>
                <input type="url" name="website" id="website" value="{{ old('website', $team->website) }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://example.com">
                @error('website')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <div class="flex gap-4">
                <button type="submit" class="flex-1 px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
                    Save changes
                </button>
                <a href="{{ route('teams.my') }}" class="px-4 py-2 bg-white/5 hover:bg-white/10 text-white rounded-xl transition">
                    Cancel
                </a>
            </div>
        </form>
    </div>
    <!-- Social Links -->
    <div class="glass-card p-6">
        <h2 class="text-sm font-semibold text-white uppercase tracking-wider mb-3">Social media links</h2>
        <form action="{{ route('teams.social-links', $team) }}" method="POST" class="space-y-4">
            @csrf
            @php $socialLinks = old() ? old() : ($team->social_links ?? []); @endphp
            <div>
                <label for="twitch" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-purple-400" fill="currentColor" viewBox="0 0 24 24"><path d="M11.571 4.714h1.715v5.143H11.57zm4.715 0H18v5.143h-1.714zM6 0L1.714 4.286v15.428h5.143V24l4.286-4.286h3.428L22.286 12V0zm14.571 11.143l-3.428 3.428h-3.429l-3 3v-3H6.857V1.714h13.714z"/></svg>
                    Twitch
                </label>
                <input type="url" name="twitch" id="twitch" value="{{ $socialLinks['twitch'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://twitch.tv/...">
                @error('twitch') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="youtube" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-red-400" fill="currentColor" viewBox="0 0 24 24"><path d="M23.498 6.186a3.016 3.016 0 0 0-2.122-2.136C19.505 3.545 12 3.545 12 3.545s-7.505 0-9.377.505A3.017 3.017 0 0 0 .502 6.186C0 8.07 0 12 0 12s0 3.93.502 5.814a3.016 3.016 0 0 0 2.122 2.136c1.871.505 9.376.505 9.376.505s7.505 0 9.377-.505a3.015 3.015 0 0 0 2.122-2.136C24 15.93 24 12 24 12s0-3.93-.502-5.814zM9.545 15.568V8.432L15.818 12l-6.273 3.568z"/></svg>
                    YouTube
                </label>
                <input type="url" name="youtube" id="youtube" value="{{ $socialLinks['youtube'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://youtube.com/...">
                @error('youtube') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="tiktok" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M12.525.02c1.31-.02 2.61-.01 3.91-.02.08 1.53.63 3.09 1.75 4.17 1.12 1.11 2.7 1.62 4.24 1.79v4.03c-1.44-.05-2.89-.35-4.2-.97-.57-.26-1.1-.59-1.62-.93-.01 2.92.01 5.84-.02 8.75-.08 1.4-.54 2.79-1.35 3.94-1.31 1.92-3.58 3.17-5.91 3.21-1.43.08-2.86-.31-4.08-1.03-2.02-1.19-3.44-3.37-3.65-5.71-.02-.5-.03-1-.01-1.49.18-1.9 1.12-3.72 2.58-4.96 1.66-1.44 3.98-2.13 6.15-1.72.02 1.48-.04 2.96-.04 4.44-.99-.32-2.15-.23-3.02.37-.63.41-1.11 1.04-1.36 1.75-.21.51-.15 1.07-.14 1.61.24 1.64 1.82 3.02 3.5 2.87 1.12-.01 2.19-.66 2.77-1.61.19-.33.4-.67.41-1.06.1-1.79.06-3.57.07-5.36.01-4.03-.01-8.05.02-12.07z"/></svg>
                    TikTok
                </label>
                <input type="url" name="tiktok" id="tiktok" value="{{ $socialLinks['tiktok'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://tiktok.com/@...">
                @error('tiktok') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="kick" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-green-400" fill="currentColor" viewBox="0 0 24 24"><path d="M1.333 0C.597 0 0 .597 0 1.333v21.334C0 23.403.597 24 1.333 24h21.334c.736 0 1.333-.597 1.333-1.333V1.333C24 .597 23.403 0 22.667 0H1.333zm7.334 4h2.666v5.333L14 6h3.333l-4 4.667L17.667 18h-3.334l-2.666-5.333L10 14v4H7.333V4h1.334z"/></svg>
                    Kick
                </label>
                <input type="url" name="kick" id="kick" value="{{ $socialLinks['kick'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://kick.com/...">
                @error('kick') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="twitter" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-gray-300" fill="currentColor" viewBox="0 0 24 24"><path d="M18.244 2.25h3.308l-7.227 8.26 8.502 11.24H16.17l-5.214-6.817L4.99 21.75H1.68l7.73-8.835L1.254 2.25H8.08l4.713 6.231zm-1.161 17.52h1.833L7.084 4.126H5.117z"/></svg>
                    X (Twitter)
                </label>
                <input type="url" name="twitter" id="twitter" value="{{ $socialLinks['twitter'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://x.com/...">
                @error('twitter') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="facebook" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-blue-400" fill="currentColor" viewBox="0 0 24 24"><path d="M24 12.073c0-6.627-5.373-12-12-12s-12 5.373-12 12c0 5.99 4.388 10.954 10.125 11.854v-8.385H7.078v-3.47h3.047V9.43c0-3.007 1.792-4.669 4.533-4.669 1.312 0 2.686.235 2.686.235v2.953H15.83c-1.491 0-1.956.925-1.956 1.874v2.25h3.328l-.532 3.47h-2.796v8.385C19.612 23.027 24 18.062 24 12.073z"/></svg>
                    Facebook
                </label>
                <input type="url" name="facebook" id="facebook" value="{{ $socialLinks['facebook'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://facebook.com/...">
                @error('facebook') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <div>
                <label for="instagram" class="flex items-center gap-2 text-sm font-medium text-gray-400 mb-2">
                    <svg class="w-4 h-4 text-pink-400" fill="currentColor" viewBox="0 0 24 24"><path d="M12 0C8.74 0 8.333.015 7.053.072 5.775.132 4.905.333 4.14.63c-.789.306-1.459.717-2.126 1.384S.935 3.35.63 4.14C.333 4.905.131 5.775.072 7.053.012 8.333 0 8.74 0 12s.015 3.667.072 4.947c.06 1.277.261 2.148.558 2.913.306.788.717 1.459 1.384 2.126.667.666 1.336 1.079 2.126 1.384.766.296 1.636.499 2.913.558C8.333 23.988 8.74 24 12 24s3.667-.015 4.947-.072c1.277-.06 2.148-.262 2.913-.558.788-.306 1.459-.718 2.126-1.384.666-.667 1.079-1.335 1.384-2.126.296-.765.499-1.636.558-2.913.06-1.28.072-1.687.072-4.947s-.015-3.667-.072-4.947c-.06-1.277-.262-2.149-.558-2.913-.306-.789-.718-1.459-1.384-2.126C21.319 1.347 20.651.935 19.86.63c-.765-.297-1.636-.499-2.913-.558C15.667.012 15.26 0 12 0zm0 2.16c3.203 0 3.585.016 4.85.071 1.17.055 1.805.249 2.227.415.562.217.96.477 1.382.896.419.42.679.819.896 1.381.164.422.36 1.057.413 2.227.057 1.266.07 1.646.07 4.85s-.015 3.585-.074 4.85c-.061 1.17-.256 1.805-.421 2.227-.224.562-.479.96-.899 1.382-.419.419-.824.679-1.38.896-.42.164-1.065.36-2.235.413-1.274.057-1.649.07-4.859.07-3.211 0-3.586-.015-4.859-.074-1.171-.061-1.816-.256-2.236-.421-.569-.224-.96-.479-1.379-.899-.421-.419-.69-.824-.9-1.38-.165-.42-.359-1.065-.42-2.235-.045-1.26-.061-1.649-.061-4.844 0-3.196.016-3.586.061-4.861.061-1.17.255-1.814.42-2.234.21-.57.479-.96.9-1.381.419-.419.81-.689 1.379-.898.42-.166 1.051-.361 2.221-.421 1.275-.045 1.65-.06 4.859-.06l.045.03zm0 3.678a6.162 6.162 0 100 12.324 6.162 6.162 0 100-12.324zM12 16c-2.21 0-4-1.79-4-4s1.79-4 4-4 4 1.79 4 4-1.79 4-4 4zm7.846-10.405a1.441 1.441 0 11-2.88 0 1.441 1.441 0 012.88 0z"/></svg>
                    Instagram
                </label>
                <input type="url" name="instagram" id="instagram" value="{{ $socialLinks['instagram'] ?? '' }}"
                    class="w-full bg-white/5 border-white/10 text-white rounded-lg px-4 py-2 focus:ring-green-500 focus:border-green-500"
                    placeholder="https://instagram.com/...">
                @error('instagram') <p class="mt-1 text-sm text-red-400">{{ $message }}</p> @enderror
            </div>
            <button type="submit" class="w-full px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-xl transition font-medium">
                Save social links
            </button>
        </form>
    </div>

@endsection
