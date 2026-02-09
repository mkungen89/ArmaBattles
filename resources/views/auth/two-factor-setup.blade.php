@extends('layouts.app')

@section('title', 'Two-Factor Authentication Setup')

@section('content')
<div class="max-w-lg mx-auto mt-8 space-y-6">
    <div class="flex items-center gap-4">
        <a href="{{ route('profile.settings') }}" class="text-gray-400 hover:text-white transition">
            <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M15 19l-7-7 7-7"/>
            </svg>
        </a>
        <h1 class="text-2xl font-bold">Two-Factor Authentication</h1>
    </div>

    @if(session('success'))
    <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg">
        {{ session('success') }}
    </div>
    @endif

    {{-- QR Code Setup Step --}}
    @if(isset($qrCodeSvg) && isset($secret))
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        <h3 class="text-lg font-semibold text-white mb-2">Step 1: Scan QR Code</h3>
        <p class="text-gray-400 text-sm mb-6">
            Scan the QR code below with your authenticator app (Google Authenticator, Authy, etc.).
        </p>

        <div class="flex justify-center mb-6">
            <div class="bg-white p-4 rounded-lg">
                {!! $qrCodeSvg !!}
            </div>
        </div>

        <div class="mb-6">
            <p class="text-sm text-gray-400 mb-2">Or enter this key manually:</p>
            <div class="bg-gray-700/50 rounded-lg p-3 flex items-center justify-between">
                <code class="text-green-400 font-mono text-sm break-all">{{ $secret }}</code>
                <button onclick="navigator.clipboard.writeText('{{ $secret }}')" class="ml-3 flex-shrink-0 text-gray-400 hover:text-white transition" title="Copy">
                    <svg class="w-5 h-5" fill="none" stroke="currentColor" viewBox="0 0 24 24">
                        <path stroke-linecap="round" stroke-linejoin="round" stroke-width="2" d="M8 16H6a2 2 0 01-2-2V6a2 2 0 012-2h8a2 2 0 012 2v2m-6 12h8a2 2 0 002-2v-8a2 2 0 00-2-2h-8a2 2 0 00-2 2v8a2 2 0 002 2z"/>
                    </svg>
                </button>
            </div>
        </div>

        <h3 class="text-lg font-semibold text-white mb-2">Step 2: Verify Code</h3>
        <p class="text-gray-400 text-sm mb-4">
            Enter the 6-digit code from your authenticator app to confirm setup.
        </p>

        <form method="POST" action="{{ route('two-factor.confirm') }}" class="space-y-4">
            @csrf
            <div>
                <input type="text" name="code" inputmode="numeric" autocomplete="one-time-code" autofocus
                       maxlength="6" pattern="[0-9]{6}"
                       placeholder="000000"
                       class="w-full px-4 py-3 bg-gray-700 border border-gray-600 rounded-lg text-white text-center text-2xl font-mono tracking-widest placeholder-gray-500 focus:outline-none focus:ring-2 focus:ring-green-500 focus:border-transparent">
                @error('code')
                    <p class="mt-2 text-sm text-red-400">{{ $message }}</p>
                @enderror
            </div>
            <button type="submit" class="w-full py-2.5 bg-green-600 hover:bg-green-700 text-white font-semibold rounded-lg transition">
                Confirm & Enable 2FA
            </button>
        </form>
    </div>
    @endif

    {{-- Recovery Codes Display --}}
    @if(isset($confirmed) && $confirmed && isset($recoveryCodes))
    <div class="bg-gray-800/50 border border-gray-700 rounded-xl p-6">
        @if(isset($regenerated) && $regenerated)
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-4">
                Recovery codes have been regenerated. Old codes are no longer valid.
            </div>
        @elseif(! isset($viewingCodes))
            <div class="bg-green-500/20 border border-green-500/50 text-green-400 px-4 py-3 rounded-lg mb-4">
                Two-factor authentication has been enabled successfully!
            </div>
        @endif

        <h3 class="text-lg font-semibold text-white mb-2">Recovery Codes</h3>
        <p class="text-gray-400 text-sm mb-4">
            Store these recovery codes in a secure location. Each code can only be used once to sign in if you lose access to your authenticator device.
        </p>

        <div class="bg-gray-900/50 border border-gray-600 rounded-lg p-4 mb-4">
            <div class="grid grid-cols-2 gap-2">
                @foreach($recoveryCodes as $code)
                    <code class="text-green-400 font-mono text-sm">{{ $code }}</code>
                @endforeach
            </div>
        </div>

        <div class="flex gap-3">
            <button onclick="copyRecoveryCodes()" class="px-4 py-2 bg-gray-700 hover:bg-gray-600 text-white rounded-lg transition text-sm">
                Copy Codes
            </button>
            <a href="{{ route('profile.settings') }}" class="px-4 py-2 bg-green-600 hover:bg-green-500 text-white rounded-lg transition text-sm">
                Back to Settings
            </a>
        </div>
    </div>

    <div class="bg-yellow-500/10 border border-yellow-500/30 rounded-lg p-4">
        <p class="text-yellow-400 text-sm">
            <strong>Warning:</strong> If you lose your authenticator device and these recovery codes, you will be locked out of your account.
        </p>
    </div>

    <script>
        function copyRecoveryCodes() {
            const codes = @json($recoveryCodes);
            navigator.clipboard.writeText(codes.join('\n'));
        }
    </script>
    @endif
</div>
@endsection
