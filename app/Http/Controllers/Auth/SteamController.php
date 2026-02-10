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
}
