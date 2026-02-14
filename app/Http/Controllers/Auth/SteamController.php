<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use App\Notifications\LinkArmaIdNotification;
use Illuminate\Support\Facades\Auth;
use Laravel\Socialite\Facades\Socialite;

class SteamController extends Controller
{
    public function redirect()
    {
        if (! site_setting('allow_steam_login', true)) {
            return redirect()->route('home')->with('error', 'Steam login is currently disabled.');
        }

        return Socialite::driver('steam')->redirect();
    }

    public function callback()
    {
        // If user is linking account, redirect to linkCallback
        if (session('linking_steam') && Auth::check()) {
            return $this->linkCallback();
        }

        $steamUser = Socialite::driver('steam')->user();

        $user = User::updateOrCreate(
            ['steam_id' => $steamUser->getId()],
            [
                'name' => $steamUser->getNickname(),
                'avatar' => $steamUser->getAvatar(),
                'avatar_full' => $steamUser->user['avatarfull'] ?? null,
                'profile_url' => $steamUser->user['profileurl'] ?? null,
            ]
        );

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

    public function logout()
    {
        Auth::logout();
        request()->session()->invalidate();
        request()->session()->regenerateToken();

        return redirect()->route('home');
    }

    public function linkRedirect()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to link accounts.');
        }

        if (! site_setting('allow_steam_login', true)) {
            return redirect()->route('profile.settings')->with('error', 'Steam login is currently disabled.');
        }

        // Store intent in session
        session(['linking_steam' => true]);

        return Socialite::driver('steam')->redirect();
    }

    public function linkCallback()
    {
        if (! Auth::check()) {
            return redirect()->route('login')->with('error', 'You must be logged in to link accounts.');
        }

        if (! session('linking_steam')) {
            return redirect()->route('profile.settings')->with('error', 'Invalid linking request.');
        }

        session()->forget('linking_steam');

        try {
            $steamUser = Socialite::driver('steam')->user();
        } catch (\Exception $e) {
            return redirect()->route('profile.settings')->with('error', 'Failed to authenticate with Steam. Please try again.');
        }

        $steamId = $steamUser->getId();

        // Check if Steam ID is already linked to another account
        $existingUser = User::where('steam_id', $steamId)->where('id', '!=', Auth::id())->first();
        if ($existingUser) {
            return redirect()->route('profile.settings')->with('error', 'This Steam account is already linked to another user.');
        }

        // Link Steam account to current user
        Auth::user()->update([
            'steam_id' => $steamId,
            'avatar' => Auth::user()->custom_avatar ?? $steamUser->getAvatar(),
            'avatar_full' => $steamUser->user['avatarfull'] ?? null,
            'profile_url' => $steamUser->user['profileurl'] ?? null,
        ]);

        return redirect()->route('profile.settings')->with('success', 'Steam account linked successfully!');
    }

    public function unlink()
    {
        $user = Auth::user();

        // Check if user has another login method
        if (! $user->google_id && ! $user->twitch_id && ! $user->password) {
            return redirect()->route('profile.settings')->with('error', 'You cannot unlink your only login method. Please link another account first.');
        }

        $user->update([
            'steam_id' => null,
            'avatar_full' => null,
            'profile_url' => null,
        ]);

        return redirect()->route('profile.settings')->with('success', 'Steam account unlinked successfully.');
    }
}
