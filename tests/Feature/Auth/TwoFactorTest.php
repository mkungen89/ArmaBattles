<?php

namespace Tests\Feature\Auth;

use App\Models\User;
use Illuminate\Foundation\Testing\RefreshDatabase;
use PragmaRX\Google2FA\Google2FA;
use Tests\TestCase;

class TwoFactorTest extends TestCase
{
    use RefreshDatabase;

    protected Google2FA $google2fa;

    protected function setUp(): void
    {
        parent::setUp();
        $this->google2fa = new Google2FA;
    }

    public function test_enable_stores_secret(): void
    {
        $user = User::factory()->create();

        $response = $this->actingAs($user)->post('/profile/two-factor');

        $response->assertRedirect(route('two-factor.setup'));

        $user->refresh();
        $this->assertNotNull($user->two_factor_secret);
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_confirm_with_valid_totp(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ]);

        $validCode = $this->google2fa->getCurrentOtp($secret);

        $response = $this->actingAs($user)->post('/profile/two-factor/confirm', [
            'code' => $validCode,
        ]);

        $response->assertOk();

        $user->refresh();
        $this->assertNotNull($user->two_factor_confirmed_at);
        $this->assertNotNull($user->two_factor_recovery_codes);
    }

    public function test_confirm_with_invalid_totp_rejected(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => null,
        ]);

        $response = $this->actingAs($user)->post('/profile/two-factor/confirm', [
            'code' => '000000',
        ]);

        $response->assertSessionHasErrors('code');

        $user->refresh();
        $this->assertNull($user->two_factor_confirmed_at);
    }

    public function test_challenge_with_valid_totp_logs_in(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = ['recovery-abc-123', 'recovery-def-456'];

        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        $validCode = $this->google2fa->getCurrentOtp($secret);

        // Simulate the login flow: session must have two_factor_user_id
        $response = $this->withSession(['two_factor_user_id' => $user->id])
            ->post('/two-factor-challenge', [
                'code' => $validCode,
            ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);
    }

    public function test_challenge_with_recovery_code_logs_in_and_consumes_code(): void
    {
        $secret = $this->google2fa->generateSecretKey();
        $recoveryCodes = ['recovery-code-1', 'recovery-code-2', 'recovery-code-3'];

        $user = User::factory()->create([
            'two_factor_secret' => $secret,
            'two_factor_confirmed_at' => now(),
            'two_factor_recovery_codes' => json_encode($recoveryCodes),
        ]);

        $response = $this->withSession(['two_factor_user_id' => $user->id])
            ->post('/two-factor-challenge', [
                'code' => 'recovery-code-1',
                'recovery' => true,
            ]);

        $response->assertRedirect();
        $this->assertAuthenticatedAs($user);

        // Recovery code should be consumed
        $user->refresh();
        $remainingCodes = json_decode($user->two_factor_recovery_codes, true);
        $this->assertCount(2, $remainingCodes);
        $this->assertNotContains('recovery-code-1', $remainingCodes);
    }
}
