@extends('layouts.app')

@section('title', 'Two-Factor Challenge')

@section('content')
<div class="max-w-md mx-auto mt-8">
    <div class="bg-gray-800/50 backdrop-blur-sm border border-gray-700 rounded-xl p-8" x-data="{ recovery: false }">
        <div class="text-center mb-6">
            <div class="mx-auto w-12 h-12 bg-green-500/20 rounded-full flex items-center justify-center mb-4">
                <svg class="w-6 h-6 text-green-400" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                    <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M12 15v2m-6 4h12a2 2 0 002-2v-6a2 2 0 00-2-2H6a2 2 0 00-2 2v6a2 2 0 002 2zm10-10V7a4 4 0 00-8 0v4h8z"/>
                </svg>
            </div>
            <h1 class="text-2xl font-bold text-white">Two-Factor Authentication</h1>
            <p class="text-gray-400 text-sm mt-2" x-show="!recovery">
                Enter the 6-digit code from your authenticator app.
            </p>
            <p class="text-gray-400 text-sm mt-2" x-show="recovery" x-cloak>
                Enter one of your recovery codes.
            </p>
        </div>

        <form method="POST" action="{{ route('two-factor.verify') }}" class="space-y-5">
            @csrf

            <div x-show="!recovery">
                <label for="code" class="block text-sm font-medium text-gray-300 mb-1">Authentication Code</label>
                <input type="text" name="code" id="code" inputmode="numeric" autocomplete="one-time-code" autofocus
                       maxlength="6" pattern="[0-9]{6}"
                       placeholder="000000"
                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-center text-2xl font-mono tracking-widest placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
            </div>

            <div x-show="recovery" x-cloak>
                <label for="recovery_code" class="block text-sm font-medium text-gray-300 mb-1">Recovery Code</label>
                <input type="text" id="recovery_code" autocomplete="off"
                       placeholder="xxxxxxxxxx"
                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-center font-mono tracking-wider placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                <input type="hidden" name="recovery" x-bind:value="recovery ? '1' : '0'">
            </div>

            @error('code')
                <p class="text-sm text-red-400">{{ $message }}</p>
            @enderror

            <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                <span x-show="!recovery">Verify</span>
                <span x-show="recovery" x-cloak>Use Recovery Code</span>
            </button>
        </form>

        <div class="mt-4 text-center">
            <button @click="recovery = !recovery; $nextTick(() => { if(recovery) { document.getElementById('recovery_code').focus() } else { document.getElementById('code').focus() } })"
                    class="text-sm text-gray-400 hover:text-green-400 transition">
                <span x-show="!recovery">Use a recovery code instead</span>
                <span x-show="recovery" x-cloak>Use authenticator code instead</span>
            </button>
        </div>
    </div>
</div>

<script>
    // Sync the recovery code input to the hidden 'code' field
    document.addEventListener('DOMContentLoaded', function() {
        const recoveryInput = document.getElementById('recovery_code');
        const codeInput = document.getElementById('code');
        const form = recoveryInput.closest('form');

        form.addEventListener('submit', function() {
            const recoveryHidden = form.querySelector('input[name="recovery"]');
            if (recoveryHidden && recoveryHidden.value === '1') {
                codeInput.value = recoveryInput.value;
            }
        });
    });
</script>
@endsection
