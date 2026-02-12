@extends('layouts.app')

@section('title', 'Register')

@section('content')
<div class="max-w-md mx-auto mt-8">
    <div class="glass-card rounded-xl p-8">
        <h1 class="text-2xl font-bold text-white text-center mb-6">Create an Account</h1>

        <form method="POST" action="{{ route('register') }}" class="space-y-5">
            @csrf

            <div>
                <label for="name" class="block text-sm font-medium text-gray-300 mb-1">Name</label>
                <input type="text" name="name" id="name" value="{{ old('name') }}" required autofocus
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @error('name')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="email" class="block text-sm font-medium text-gray-300 mb-1">Email</label>
                <input type="email" name="email" id="email" value="{{ old('email') }}" required
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @error('email')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password" class="block text-sm font-medium text-gray-300 mb-1">Password</label>
                <input type="password" name="password" id="password" required
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @error('password')
                    <p class="mt-1 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>

            <div>
                <label for="password_confirmation" class="block text-sm font-medium text-gray-300 mb-1">Confirm Password</label>
                <input type="password" name="password_confirmation" id="password_confirmation" required
                       class="w-full px-4 py-2 bg-white/5 border border-white/10 rounded-xl text-white placeholder-gray-400 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-500 text-white font-semibold rounded-xl transition">
                Register
            </button>
        </form>

        <div class="relative my-6">
            <div class="absolute inset-0 flex items-center">
                <div class="w-full border-t border-white/5"></div>
            </div>
            <div class="relative flex justify-center text-sm">
                <span class="px-3 bg-[#0d1117] text-gray-400">or</span>
            </div>
        </div>

        <div class="space-y-3">
            <a href="{{ route('auth.steam') }}" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-white/5 hover:bg-white/10 rounded-xl transition">
                <svg class="w-5 h-5" fill="currentColor" viewBox="0 0 24 24">
                    <path d="M12 0C5.373 0 0 5.373 0 12c0 5.084 3.163 9.426 7.627 11.174l2.896-4.143c-.468-.116-.91-.293-1.317-.525L4.5 21.75c-.913-.288-1.772-.684-2.563-1.176l4.707-3.308c-.155-.369-.277-.758-.359-1.162L0 19.293V12C0 5.373 5.373 0 12 0zm0 4.5c-4.136 0-7.5 3.364-7.5 7.5 0 .768.115 1.509.328 2.206l3.908-2.745c.493-2.293 2.535-4.011 4.997-4.011 2.795 0 5.067 2.272 5.067 5.067 0 2.462-1.758 4.514-4.089 4.977l-2.725 3.896C9.788 22.285 10.869 22.5 12 22.5c6.627 0 12-5.373 12-12S18.627 0 12 0z"/>
                </svg>
                <span>Login with Steam</span>
            </a>

            <a href="{{ route('auth.google') }}" class="flex items-center justify-center space-x-2 w-full py-2.5 bg-white/5 hover:bg-white/10 rounded-xl transition">
                <svg class="w-5 h-5" viewBox="0 0 24 24" fill="currentColor">
                    <path d="M22.56 12.25c0-.78-.07-1.53-.2-2.25H12v4.26h5.92c-.26 1.37-1.04 2.53-2.21 3.31v2.77h3.57c2.08-1.92 3.28-4.74 3.28-8.09z" fill="#4285F4"/>
                    <path d="M12 23c2.97 0 5.46-.98 7.28-2.66l-3.57-2.77c-.98.66-2.23 1.06-3.71 1.06-2.86 0-5.29-1.93-6.16-4.53H2.18v2.84C3.99 20.53 7.7 23 12 23z" fill="#34A853"/>
                    <path d="M5.84 14.09c-.22-.66-.35-1.36-.35-2.09s.13-1.43.35-2.09V7.07H2.18C1.43 8.55 1 10.22 1 12s.43 3.45 1.18 4.93l2.85-2.22.81-.62z" fill="#FBBC05"/>
                    <path d="M12 5.38c1.62 0 3.06.56 4.21 1.64l3.15-3.15C17.45 2.09 14.97 1 12 1 7.7 1 3.99 3.47 2.18 7.07l3.66 2.84c.87-2.6 3.3-4.53 6.16-4.53z" fill="#EA4335"/>
                </svg>
                <span>Login with Google</span>
            </a>
        </div>

        <p class="mt-6 text-center text-sm text-gray-400">
            Already have an account? <a href="{{ route('login') }}" class="text-green-400 hover:text-green-300">Login</a>
        </p>
    </div>
</div>
@endsection
