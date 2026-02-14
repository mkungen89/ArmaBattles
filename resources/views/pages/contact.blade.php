@extends('layouts.app')

@section('title', 'Contact Us')

@section('content')
<div class="max-w-4xl mx-auto">
    {{-- Header --}}
    <div class="text-center mb-12">
        <h1 class="text-4xl font-bold text-white mb-4">Contact Us</h1>
        <p class="text-gray-400">Get in touch with the Arma Battles team</p>
    </div>

    <div class="grid md:grid-cols-2 gap-6">
        {{-- Contact Information --}}
        <div class="space-y-6">
            {{-- Email Support --}}
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M3 8l7.89 5.26a2 2 0 002.22 0L21 8M5 19h14a2 2 0 002-2V7a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Email Support</h3>
                        <p class="text-gray-400 text-sm mb-3">For general inquiries, bug reports, and support</p>
                        <a href="mailto:support@armabattles.com" class="text-blue-400 hover:text-blue-300 transition">
                            support@armabattles.com
                        </a>
                    </div>
                </div>
            </div>

            {{-- Discord --}}
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-indigo-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-indigo-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M20.317 4.37a19.791 19.791 0 00-4.885-1.515.074.074 0 00-.079.037c-.21.375-.444.864-.608 1.25a18.27 18.27 0 00-5.487 0 12.64 12.64 0 00-.617-1.25.077.077 0 00-.079-.037A19.736 19.736 0 003.677 4.37a.07.07 0 00-.032.027C.533 9.046-.32 13.58.099 18.057a.082.082 0 00.031.057 19.9 19.9 0 005.993 3.03.078.078 0 00.084-.028c.462-.63.874-1.295 1.226-1.994a.076.076 0 00-.041-.106 13.107 13.107 0 01-1.872-.892.077.077 0 01-.008-.128 10.2 10.2 0 00.372-.292.074.074 0 01.077-.01c3.928 1.793 8.18 1.793 12.062 0a.074.074 0 01.078.01c.12.098.246.198.373.292a.077.077 0 01-.006.127 12.299 12.299 0 01-1.873.892.077.077 0 00-.041.107c.36.698.772 1.362 1.225 1.993a.076.076 0 00.084.028 19.839 19.839 0 006.002-3.03.077.077 0 00.032-.054c.5-5.177-.838-9.674-3.549-13.66a.061.061 0 00-.031-.03zM8.02 15.33c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.956-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.956 2.418-2.157 2.418zm7.975 0c-1.183 0-2.157-1.085-2.157-2.419 0-1.333.955-2.419 2.157-2.419 1.21 0 2.176 1.096 2.157 2.42 0 1.333-.946 2.418-2.157 2.418z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Discord Community</h3>
                        <p class="text-gray-400 text-sm mb-3">Join our Discord server for quick help and community chat</p>
                        <a href="https://discord.gg/armabattles" target="_blank" class="inline-flex items-center gap-2 text-indigo-400 hover:text-indigo-300 transition">
                            <span>Join Discord</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>

            {{-- GitHub Issues --}}
            <div class="glass-card rounded-xl p-6">
                <div class="flex items-start gap-4">
                    <div class="w-12 h-12 rounded-full bg-gray-500/20 flex items-center justify-center flex-shrink-0">
                        <svg class="w-6 h-6 text-gray-400" fill="currentColor" viewBox="0 0 24 24">
                            <path d="M12 0c-6.626 0-12 5.373-12 12 0 5.302 3.438 9.8 8.207 11.387.599.111.793-.261.793-.577v-2.234c-3.338.726-4.033-1.416-4.033-1.416-.546-1.387-1.333-1.756-1.333-1.756-1.089-.745.083-.729.083-.729 1.205.084 1.839 1.237 1.839 1.237 1.07 1.834 2.807 1.304 3.492.997.107-.775.418-1.305.762-1.604-2.665-.305-5.467-1.334-5.467-5.931 0-1.311.469-2.381 1.236-3.221-.124-.303-.535-1.524.117-3.176 0 0 1.008-.322 3.301 1.23.957-.266 1.983-.399 3.003-.404 1.02.005 2.047.138 3.006.404 2.291-1.552 3.297-1.23 3.297-1.23.653 1.653.242 2.874.118 3.176.77.84 1.235 1.911 1.235 3.221 0 4.609-2.807 5.624-5.479 5.921.43.372.823 1.102.823 2.222v3.293c0 .319.192.694.801.576 4.765-1.589 8.199-6.086 8.199-11.386 0-6.627-5.373-12-12-12z"/>
                        </svg>
                    </div>
                    <div>
                        <h3 class="text-lg font-semibold text-white mb-2">Bug Reports & Features</h3>
                        <p class="text-gray-400 text-sm mb-3">Report bugs or request features on GitHub</p>
                        <a href="https://github.com/mkungen89/ArmaBattles/issues" target="_blank" class="inline-flex items-center gap-2 text-gray-400 hover:text-gray-300 transition">
                            <span>GitHub Issues</span>
                            <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10 6H6a2 2 0 00-2 2v10a2 2 0 002 2h10a2 2 0 002-2v-4M14 4h6m0 0v6m0-6L10 14"></path>
                            </svg>
                        </a>
                    </div>
                </div>
            </div>
        </div>

        {{-- Quick Links & FAQ --}}
        <div class="space-y-6">
            {{-- Common Questions --}}
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8.228 9c.549-1.165 2.03-2 3.772-2 2.21 0 4 1.343 4 3 0 1.4-1.278 2.575-3.006 2.907-.542.104-.994.54-.994 1.093m0 3h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Common Questions
                </h3>
                <div class="space-y-4">
                    <div>
                        <h4 class="text-white font-medium mb-1">How do I link my game account?</h4>
                        <p class="text-sm text-gray-400">Visit your <a href="{{ route('profile.settings') }}" class="text-blue-400 hover:text-blue-300">profile settings</a> and enter your in-game Player UUID.</p>
                    </div>
                    <div>
                        <h4 class="text-white font-medium mb-1">My stats aren't updating?</h4>
                        <p class="text-sm text-gray-400">Stats update when you play on our tracked servers. Check that you're linked correctly.</p>
                    </div>
                    <div>
                        <h4 class="text-white font-medium mb-1">How do I report a player?</h4>
                        <p class="text-sm text-gray-400">Use the report button on their profile or contact an admin on Discord.</p>
                    </div>
                </div>
                <a href="{{ route('faq') }}" class="inline-flex items-center gap-2 mt-4 text-sm text-green-400 hover:text-green-300 transition">
                    <span>View full FAQ</span>
                    <svg class="w-4 h-4" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 5l7 7-7 7"></path>
                    </svg>
                </a>
            </div>

            {{-- Response Time --}}
            <div class="glass-card rounded-xl p-6">
                <h3 class="text-lg font-semibold text-white mb-4 flex items-center gap-2">
                    <svg class="w-5 h-5 text-yellow-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 8v4l3 3m6-3a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                    </svg>
                    Response Time
                </h3>
                <p class="text-gray-400 text-sm mb-3">
                    We're a community-run project. Response times vary:
                </p>
                <ul class="space-y-2 text-sm text-gray-400">
                    <li class="flex items-start gap-2">
                        <span class="text-green-400 mt-0.5">•</span>
                        <span><strong class="text-white">Discord:</strong> Usually within a few hours</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-yellow-400 mt-0.5">•</span>
                        <span><strong class="text-white">Email:</strong> 1-3 business days</span>
                    </li>
                    <li class="flex items-start gap-2">
                        <span class="text-orange-400 mt-0.5">•</span>
                        <span><strong class="text-white">GitHub:</strong> As time allows</span>
                    </li>
                </ul>
            </div>

            {{-- Business Inquiries --}}
            <div class="glass-card rounded-xl p-6 border border-purple-500/20 bg-purple-500/5">
                <h3 class="text-lg font-semibold text-white mb-2 flex items-center gap-2">
                    <svg class="w-5 h-5 text-purple-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M21 13.255A23.931 23.931 0 0112 15c-3.183 0-6.22-.62-9-1.745M16 6V4a2 2 0 00-2-2h-4a2 2 0 00-2 2v2m4 6h.01M5 20h14a2 2 0 002-2V8a2 2 0 00-2-2H5a2 2 0 00-2 2v10a2 2 0 002 2z"></path>
                    </svg>
                    Business Inquiries
                </h3>
                <p class="text-purple-300 text-sm mb-3">
                    For partnerships, sponsorships, or media inquiries:
                </p>
                <a href="mailto:business@armabattles.com" class="text-purple-400 hover:text-purple-300 transition">
                    business@armabattles.com
                </a>
            </div>
        </div>
    </div>

    {{-- Additional Resources --}}
    <div class="mt-12 grid md:grid-cols-3 gap-4">
        <a href="{{ route('privacy') }}" class="glass-card rounded-xl p-4 hover:bg-white/5 transition group">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-blue-400 group-hover:text-blue-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12l2 2 4-4m5.618-4.016A11.955 11.955 0 0112 2.944a11.955 11.955 0 01-8.618 3.04A12.02 12.02 0 003 9c0 5.591 3.824 10.29 9 11.622 5.176-1.332 9-6.03 9-11.622 0-1.042-.133-2.052-.382-3.016z"></path>
                </svg>
                <span class="text-white group-hover:text-blue-300 transition">Privacy Policy</span>
            </div>
        </a>
        <a href="{{ route('terms') }}" class="glass-card rounded-xl p-4 hover:bg-white/5 transition group">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-green-400 group-hover:text-green-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M9 12h6m-6 4h6m2 5H7a2 2 0 01-2-2V5a2 2 0 012-2h5.586a1 1 0 01.707.293l5.414 5.414a1 1 0 01.293.707V19a2 2 0 01-2 2z"></path>
                </svg>
                <span class="text-white group-hover:text-green-300 transition">Terms of Service</span>
            </div>
        </a>
        <a href="{{ route('rules') }}" class="glass-card rounded-xl p-4 hover:bg-white/5 transition group">
            <div class="flex items-center gap-3">
                <svg class="w-5 h-5 text-yellow-400 group-hover:text-yellow-300 transition" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 6.253v13m0-13C10.832 5.477 9.246 5 7.5 5S4.168 5.477 3 6.253v13C4.168 18.477 5.754 18 7.5 18s3.332.477 4.5 1.253m0-13C13.168 5.477 14.754 5 16.5 5c1.747 0 3.332.477 4.5 1.253v13C19.832 18.477 18.247 18 16.5 18c-1.746 0-3.332.477-4.5 1.253"></path>
                </svg>
                <span class="text-white group-hover:text-yellow-300 transition">Server Rules</span>
            </div>
        </a>
    </div>
</div>
@endsection
