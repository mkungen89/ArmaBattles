<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LinkArmaIdNotification;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class GoogleController extends Controller
{
    public function redirect()
    {
        if (! site_setting('allow_google_login', true)) {
            return redirect()->route('home')->with('error', 'Google login is currently disabled.');
        }

        return Socialite::driver('google')->redirect();
    }

    public function callback()
    {
        try {
            $googleUser = Socialite::driver('google')->user();
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Failed to authenticate with Google. Please try again.');
        }

        // Check if user exists with this Google ID
        $user = User::where('google_id', $googleUser->getId())->first();

        if ($user) {
            // Update existing user's info
            $user->update([
                'google_email' => $googleUser->getEmail(),
                'avatar' => $user->custom_avatar ?? $googleUser->getAvatar(),
            ]);
        } else {
            // Check if user exists with this email (from Steam or other login)
            $user = User::where('email', $googleUser->getEmail())->first();

            if ($user) {
                // Link Google account to existing user
                $user->update([
                    'google_id' => $googleUser->getId(),
                    'google_email' => $googleUser->getEmail(),
                    'avatar' => $user->custom_avatar ?? ($user->avatar ?? $googleUser->getAvatar()),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $googleUser->getName(),
                    'email' => $googleUser->getEmail(),
                    'google_id' => $googleUser->getId(),
                    'google_email' => $googleUser->getEmail(),
                    'avatar' => $googleUser->getAvatar(),
                    'email_verified_at' => now(), // Google emails are verified
                ]);
            }
        }

        if ($user->is_banned) {
            return redirect()->route('home')->with('error', 'You are banned from this community.');
        }

        // Check for 2FA
        if ($user->hasTwoFactorEnabled()) {
            request()->session()->put('two_factor_user_id', $user->id);
            request()->session()->put('two_factor_remember', true);

            return redirect()->route('two-factor.challenge');
        }

        Auth::login($user, true);

        $user->update(['last_login_at' => now()]);

        // Nudge users who haven't linked their Arma ID yet (max once per 7 days)
        if (! $user->player_uuid) {
            $recentNotification = $user->notifications()
                ->where('type', LinkArmaIdNotification::class)
                ->where('created_at', '>=', now()->subDays(7))
                ->exists();

            if (! $recentNotification) {
                $user->notify(new LinkArmaIdNotification);
            }
        }

        return redirect()->route('profile')->with('success', 'Welcome, '.$user->name.'!');
    }
}
