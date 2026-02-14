<!DOCTYPE html>
<html lang="en" class="h-full">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Under Maintenance - {{ site_setting('site_name', config('app.name')) }}</title>
    @vite(['resources/css/app.css'])
    <script defer src="https://cdn.jsdelivr.net/npm/alpinejs@3.x.x/dist/cdn.min.js"></script>
    <style>
        @keyframes float {
            0%, 100% { transform: translateY(0px); }
            50% { transform: translateY(-20px); }
        }
        @keyframes pulse-glow {
            0%, 100% { box-shadow: 0 0 20px rgba(34, 197, 94, 0.3); }
            50% { box-shadow: 0 0 40px rgba(34, 197, 94, 0.6); }
        }
        .float-animation {
            animation: float 3s ease-in-out infinite;
        }
        .glow-animation {
            animation: pulse-glow 2s ease-in-out infinite;
        }
    </style>
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
<body class="h-full bg-gray-900 text-white overflow-hidden">
    <!-- Full Page Background (same as homepage) -->
    <div class="fixed inset-0 z-0">
        <img src="https://images.wallpapersden.com/image/download/arma-reforger-4k-gaming_bWhrbm6UmZqaraWkpJRobWllrWdma2U.jpg" alt="" class="w-full h-full object-cover opacity-30">
        <div class="absolute inset-0 bg-gradient-to-b from-gray-900/40 via-gray-900/70 to-gray-900/90"></div>
    </div>

    <!-- Content -->
    <div class="relative h-full flex items-center justify-center px-6" x-data="{
        targetDate: new Date('2026-02-27T00:00:00').getTime(),
        now: Date.now(),
        days: 0,
        hours: 0,
        minutes: 0,
        seconds: 0,
        init() {
            this.updateCountdown();
            setInterval(() => {
                this.updateCountdown();
            }, 1000);
        },
        updateCountdown() {
            this.now = Date.now();
            const distance = this.targetDate - this.now;

            if (distance > 0) {
                this.days = Math.floor(distance / (1000 * 60 * 60 * 24));
                this.hours = Math.floor((distance % (1000 * 60 * 60 * 24)) / (1000 * 60 * 60));
                this.minutes = Math.floor((distance % (1000 * 60 * 60)) / (1000 * 60));
                this.seconds = Math.floor((distance % (1000 * 60)) / 1000);
            } else {
                this.days = 0;
                this.hours = 0;
                this.minutes = 0;
                this.seconds = 0;
            }
        }
    }">
        <div class="text-center max-w-4xl">
            <!-- Logo/Icon -->
            <div class="mb-8 float-animation">
                <svg class="w-24 h-24 text-green-500 mx-auto drop-shadow-[0_0_15px_rgba(34,197,94,0.5)]" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M10.325 4.317c.426-1.756 2.924-1.756 3.35 0a1.724 1.724 0 002.573 1.066c1.543-.94 3.31.826 2.37 2.37a1.724 1.724 0 001.065 2.572c1.756.426 1.756 2.924 0 3.35a1.724 1.724 0 00-1.066 2.573c.94 1.543-.826 3.31-2.37 2.37a1.724 1.724 0 00-2.572 1.065c-.426 1.756-2.924 1.756-3.35 0a1.724 1.724 0 00-2.573-1.066c-1.543.94-3.31-.826-2.37-2.37a1.724 1.724 0 00-1.065-2.572c-1.756-.426-1.756-2.924 0-3.35a1.724 1.724 0 001.066-2.573c-.94-1.543.826-3.31 2.37-2.37.996.608 2.296.07 2.572-1.065z"/>
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 12a3 3 0 11-6 0 3 3 0 016 0z"/>
                </svg>
            </div>

            <!-- Title -->
            <h1 class="text-5xl md:text-6xl font-bold mb-4 bg-gradient-to-r from-green-400 to-blue-500 bg-clip-text text-transparent">
                Under Maintenance
            </h1>

            <!-- Message -->
            <p class="text-gray-300 text-lg md:text-xl mb-12 max-w-2xl mx-auto leading-relaxed">
                {{ $message }}
            </p>

            <!-- Countdown Timer -->
            <div class="mb-12">
                <p class="text-sm text-gray-400 uppercase tracking-wider mb-6">Expected Return</p>
                <div class="grid grid-cols-4 gap-4 md:gap-6 max-w-2xl mx-auto">
                    <!-- Days -->
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-xl p-4 md:p-6 shadow-xl">
                        <div class="text-3xl md:text-5xl font-bold text-green-400 mb-2" x-text="days.toString().padStart(2, '0')">00</div>
                        <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wider">Days</div>
                    </div>

                    <!-- Hours -->
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-xl p-4 md:p-6 shadow-xl">
                        <div class="text-3xl md:text-5xl font-bold text-green-400 mb-2" x-text="hours.toString().padStart(2, '0')">00</div>
                        <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wider">Hours</div>
                    </div>

                    <!-- Minutes -->
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-xl p-4 md:p-6 shadow-xl">
                        <div class="text-3xl md:text-5xl font-bold text-green-400 mb-2" x-text="minutes.toString().padStart(2, '0')">00</div>
                        <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wider">Minutes</div>
                    </div>

                    <!-- Seconds -->
                    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700/50 rounded-xl p-4 md:p-6 shadow-xl">
                        <div class="text-3xl md:text-5xl font-bold text-green-400 mb-2" x-text="seconds.toString().padStart(2, '0')">00</div>
                        <div class="text-xs md:text-sm text-gray-400 uppercase tracking-wider">Seconds</div>
                    </div>
                </div>
            </div>

            <!-- Additional Info -->
            <div class="space-y-4">
                <p class="text-gray-500 text-sm">
                    We're working hard to bring you an improved experience.
                </p>
                <a href="{{ route('login') }}" class="inline-block text-sm text-gray-600 hover:text-green-400 transition-colors duration-200">
                    Admin Login →
                </a>
            </div>
        </div>
    </div>
</body>
</html>
