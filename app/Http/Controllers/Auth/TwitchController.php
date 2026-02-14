<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LinkArmaIdNotification;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class TwitchController extends Controller
{
    public function redirect()
    {
        if (! site_setting('allow_twitch_login', true)) {
            return redirect()->route('home')->with('error', 'Twitch login is currently disabled.');
        }

        return Socialite::driver('twitch')->redirect();
    }

    public function callback()
    {
        // If user is linking account, redirect to linkCallback
        if (session('linking_twitch') && Auth::check()) {
            return $this->linkCallback();
        }

        try {
            $twitchUser = Socialite::driver('twitch')->user();
        } catch (\Exception $e) {
            return redirect()->route('home')->with('error', 'Failed to authenticate with Twitch. Please try again.');
        }

        // Check if user exists with this Twitch ID
        $user = User::where('twitch_id', $twitchUser->getId())->first();

        if ($user) {
            // Update existing user's info
            $user->update([
                'twitch_username' => $twitchUser->getNickname(),
                'twitch_email' => $twitchUser->getEmail(),
                'avatar' => $user->custom_avatar ?? $twitchUser->getAvatar(),
            ]);
        } else {
            // Check if user exists with this email (from Steam, Google or other login)
            $user = User::where('email', $twitchUser->getEmail())->first();

            if ($user) {
                // Link Twitch account to existing user
                $user->update([
                    'twitch_id' => $twitchUser->getId(),
                    'twitch_username' => $twitchUser->getNickname(),
                    'twitch_email' => $twitchUser->getEmail(),
                    'avatar' => $user->custom_avatar ?? ($user->avatar ?? $twitchUser->getAvatar()),
                ]);
            } else {
                // Create new user
                $user = User::create([
                    'name' => $twitchUser->getNickname() ?? $twitchUser->getName(),
                    'email' => $twitchUser->getEmail(),
                    'twitch_id' => $twitchUser->getId(),
                    'twitch_username' => $twitchUser->getNickname(),
                    'twitch_email' => $twitchUser->getEmail(),
                    'avatar' => $twitchUser->getAvatar(),
                    'email_verified_at' => now(), // Twitch emails are verified
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

    public function linkRedirect()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to link accounts.');
        }

        if (! site_setting('allow_twitch_login', true)) {
            return redirect()->route('profile.settings')->with('error', 'Twitch login is currently disabled.');
        }

        // Store intent in session
        session(['linking_twitch' => true]);

        return Socialite::driver('twitch')->redirect();
    }

    public function linkCallback()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to link accounts.');
        }

        if (! session('linking_twitch')) {
            return redirect()->route('profile.settings')->with('error', 'Invalid linking request.');
        }

        session()->forget('linking_twitch');

        try {
            $twitchUser = Socialite::driver('twitch')->user();
        } catch (\Exception $e) {
            return redirect()->route('profile.settings')->with('error', 'Failed to authenticate with Twitch. Please try again.');
        }

        $twitchId = $twitchUser->getId();

        // Check if Twitch ID is already linked to another account
        $existingUser = User::where('twitch_id', $twitchId)->where('id', '!=', Auth::id())->first();
        if ($existingUser) {
            return redirect()->route('profile.settings')->with('error', 'This Twitch account is already linked to another user.');
        }

        // Link Twitch account to current user
        Auth::user()->update([
            'twitch_id' => $twitchId,
            'twitch_username' => $twitchUser->getNickname(),
            'twitch_email' => $twitchUser->getEmail(),
            'email' => Auth::user()->email ?? $twitchUser->getEmail(),
            'email_verified_at' => Auth::user()->email_verified_at ?? now(),
            'avatar' => Auth::user()->custom_avatar ?? Auth::user()->avatar ?? $twitchUser->getAvatar(),
        ]);

        return redirect()->route('profile.settings')->with('success', 'Twitch account linked successfully!');
    }

    public function unlink()
    {
        $user = Auth::user();

        // Check if user has another login method
        if (! $user->steam_id && ! $user->google_id && ! $user->password) {
            return redirect()->route('profile.settings')->with('error', 'You cannot unlink your only login method. Please link another account first.');
        }

        $user->update([
            'twitch_id' => null,
            'twitch_username' => null,
            'twitch_email' => null,
        ]);

        return redirect()->route('profile.settings')->with('success', 'Twitch account unlinked successfully.');
    }
}
