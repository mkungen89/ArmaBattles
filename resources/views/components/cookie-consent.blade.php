{{-- Cookie Consent Banner (GDPR Compliant) --}}
<div x-data="{
    show: false,
    init() {
        // Check if user has already made a choice
        const consent = localStorage.getItem('cookie_consent');
        if (!consent) {
            // Show banner after 1 second delay
            setTimeout(() => { this.show = true; }, 1000);
        }
    },
    accept() {
        localStorage.setItem('cookie_consent', 'accepted');
        localStorage.setItem('cookie_consent_date', new Date().toISOString());
        this.show = false;

        // Enable analytics if configured
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {
                'analytics_storage': 'granted'
            });
        }
    },
    decline() {
        localStorage.setItem('cookie_consent', 'declined');
        localStorage.setItem('cookie_consent_date', new Date().toISOString());
        this.show = false;

        // Disable analytics
        if (typeof gtag !== 'undefined') {
            gtag('consent', 'update', {
                'analytics_storage': 'denied'
            });
        }
    }
}"
x-show="show"
x-transition:enter="transition ease-out duration-300"
x-transition:enter-start="opacity-0 transform translate-y-4"
x-transition:enter-end="opacity-100 transform translate-y-0"
x-transition:leave="transition ease-in duration-200"
x-transition:leave-start="opacity-100 transform translate-y-0"
x-transition:leave-end="opacity-0 transform translate-y-4"
class="fixed bottom-0 left-0 right-0 z-50 p-4 md:p-6"
style="display: none;"
x-cloak>
    <div class="max-w-7xl mx-auto">
        <div class="glass-card rounded-xl p-4 md:p-6 shadow-2xl border border-white/10">
            <div class="flex flex-col md:flex-row items-start md:items-center gap-4">
                {{-- Icon --}}
                <div class="flex-shrink-0">
                    <div class="w-12 h-12 rounded-full bg-blue-500/20 flex items-center justify-center">
                        <svg class="w-6 h-6 text-blue-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                            <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M13 16h-1v-4h-1m1-4h.01M21 12a9 9 0 11-18 0 9 9 0 0118 0z"></path>
                        </svg>
                    </div>
                </div>

                {{-- Content --}}
                <div class="flex-1 min-w-0">
                    <h3 class="text-lg font-semibold text-white mb-1">We use cookies</h3>
                    <p class="text-sm text-gray-300 leading-relaxed">
                        We use essential cookies to make our site work. With your consent, we may also use non-essential cookies to improve user experience and analyze website traffic.
                        By clicking "Accept," you agree to our website's cookie use as described in our
                        <a href="{{ route('privacy') }}" class="text-blue-400 hover:text-blue-300 underline">Privacy Policy</a>.
                    </p>
                </div>

                {{-- Actions --}}
                <div class="flex flex-col sm:flex-row gap-3 w-full md:w-auto flex-shrink-0">
                    <button @click="decline"
                            class="px-6 py-2.5 bg-white/5 hover:bg-white/10 text-gray-300 hover:text-white font-medium rounded-xl transition border border-white/10">
                        Decline
                    </button>
                    <button @click="accept"
                            class="px-6 py-2.5 bg-blue-600 hover:bg-blue-500 text-white font-semibold rounded-xl transition shadow-lg shadow-blue-500/25">
                        Accept All
                    </button>
                </div>
            </div>

            {{-- Preferences Link --}}
            <div class="mt-3 pt-3 border-t border-white/5">
                <a href="{{ route('privacy') }}#cookies" class="text-xs text-gray-400 hover:text-gray-300 transition flex items-center gap-1.5">
                    <svg class="w-3.5 h-3.5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"></path>
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"></path>
                    </svg>
                    <span>Manage cookie preferences</span>
                </a>
            </div>
        </div>
    </div>
</div>
