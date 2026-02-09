<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\AdminAuditLog;
use App\Models\User;
use BaconQrCode\Renderer\Image\SvgImageBackEnd;
use BaconQrCode\Renderer\ImageRenderer;
use BaconQrCode\Renderer\RendererStyle\RendererStyle;
use BaconQrCode\Writer;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Str;
use PragmaRX\Google2FA\Google2FA;

class TwoFactorController extends Controller
{
    protected Google2FA $google2fa;

    public function __construct()
    {
        $this->google2fa = new Google2FA();
    }

    /**
     * Generate a new 2FA secret and show setup page with QR code.
     */
    public function enable(Request $request)
    {
        $user = $request->user();

        if ($user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.settings')->with('error', 'Two-factor authentication is already enabled.');
        }

        $secret = $this->google2fa->generateSecretKey();

        $user->forceFill([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
            'two_factor_recovery_codes' => null,
        ])->save();

        $this->logSecurityEvent('2fa.initiated', $user->id);

        return redirect()->route('two-factor.setup');
    }

    /**
     * Show the 2FA setup page with QR code.
     */
    public function setup(Request $request)
    {
        $user = $request->user();

        if (! $user->two_factor_secret || $user->two_factor_confirmed_at) {
            return redirect()->route('profile.settings');
        }

        $secret = $user->two_factor_secret;
        $qrCodeUrl = $this->google2fa->getQRCodeUrl(
            config('app.name'),
            $user->email ?? $user->name,
            $secret
        );

        $renderer = new ImageRenderer(
            new RendererStyle(200),
            new SvgImageBackEnd()
        );
        $writer = new Writer($renderer);
        $qrCodeSvg = $writer->writeString($qrCodeUrl);

        return view('auth.two-factor-setup', [
            'qrCodeSvg' => $qrCodeSvg,
            'secret' => $secret,
        ]);
    }

    /**
     * Confirm the 2FA setup with a valid TOTP code.
     */
    public function confirm(Request $request)
    {
        $request->validate([
            'code' => 'required|string|size:6',
        ]);

        $user = $request->user();

        if (! $user->two_factor_secret) {
            return redirect()->route('profile.settings')->with('error', 'Two-factor authentication has not been initiated.');
        }

        $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

        if (! $valid) {
            return back()->withErrors(['code' => 'The provided two-factor code was invalid.']);
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ])->save();

        $this->logSecurityEvent('2fa.enabled', $user->id);

        return view('auth.two-factor-setup', [
            'recoveryCodes' => $recoveryCodes,
            'confirmed' => true,
        ]);
    }

    /**
     * Disable 2FA (requires password for email/password users).
     */
    public function disable(Request $request)
    {
        $user = $request->user();

        // Steam-only users don't have a password
        if ($user->password) {
            $request->validate([
                'password' => 'required|string|current_password',
            ]);
        }

        $user->forceFill([
            'two_factor_secret' => null,
            'two_factor_recovery_codes' => null,
            'two_factor_confirmed_at' => null,
        ])->save();

        $this->logSecurityEvent('2fa.disabled', $user->id);

        return redirect()->route('profile.settings')->with('success', 'Two-factor authentication has been disabled.');
    }

    /**
     * Show recovery codes.
     */
    public function showRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.settings');
        }

        $recoveryCodes = json_decode($user->two_factor_recovery_codes, true);

        return view('auth.two-factor-setup', [
            'recoveryCodes' => $recoveryCodes,
            'confirmed' => true,
            'viewingCodes' => true,
        ]);
    }

    /**
     * Regenerate recovery codes.
     */
    public function regenerateRecoveryCodes(Request $request)
    {
        $user = $request->user();

        if (! $user->hasTwoFactorEnabled()) {
            return redirect()->route('profile.settings');
        }

        $recoveryCodes = $this->generateRecoveryCodes();

        $user->forceFill([
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ])->save();

        $this->logSecurityEvent('2fa.recovery-codes-regenerated', $user->id);

        return view('auth.two-factor-setup', [
            'recoveryCodes' => $recoveryCodes,
            'confirmed' => true,
            'regenerated' => true,
        ]);
    }

    /**
     * Show the 2FA challenge form during login.
     */
    public function showChallenge(Request $request)
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        return view('auth.two-factor-challenge');
    }

    /**
     * Verify the 2FA challenge code and complete login.
     */
    public function verifyChallenge(Request $request)
    {
        if (! $request->session()->has('two_factor_user_id')) {
            return redirect()->route('login');
        }

        $request->validate([
            'code' => 'required|string',
            'recovery' => 'nullable|boolean',
        ]);

        $userId = $request->session()->get('two_factor_user_id');
        $user = User::findOrFail($userId);

        if ($request->boolean('recovery')) {
            // Recovery code flow
            $recoveryCodes = json_decode($user->two_factor_recovery_codes, true) ?? [];
            $code = $request->code;

            if (! in_array($code, $recoveryCodes)) {
                return back()->withErrors(['code' => 'The provided recovery code was invalid.']);
            }

            // Remove used recovery code
            $recoveryCodes = array_values(array_filter($recoveryCodes, fn ($c) => $c !== $code));
            $user->forceFill([
                'two_factor_recovery_codes' => json_encode($recoveryCodes),
            ])->save();
        } else {
            // TOTP code flow
            $valid = $this->google2fa->verifyKey($user->two_factor_secret, $request->code);

            if (! $valid) {
                return back()->withErrors(['code' => 'The provided two-factor code was invalid.']);
            }
        }

        $this->logSecurityEvent('2fa.challenge-passed', $userId, [
            'method' => $request->boolean('recovery') ? 'recovery_code' : 'totp',
        ]);

        // Clear 2FA session data and log in
        $request->session()->forget('two_factor_user_id');
        $request->session()->regenerate();

        Auth::loginUsingId($userId, $request->session()->get('two_factor_remember', false));
        $request->session()->forget('two_factor_remember');

        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('profile'))->with('success', 'Welcome back, ' . $user->name . '!');
    }

    /**
     * Generate 8 random recovery codes.
     */
    protected function generateRecoveryCodes(): array
    {
        $codes = [];
        for ($i = 0; $i < 8; $i++) {
            $codes[] = Str::random(10);
        }

        return $codes;
    }

    protected function logSecurityEvent(string $action, int $userId, ?array $metadata = null): void
    {
        AdminAuditLog::create([
            'user_id' => $userId,
            'action' => $action,
            'target_type' => 'User',
            'target_id' => $userId,
            'metadata' => $metadata,
            'ip_address' => request()->ip(),
        ]);
    }
}
