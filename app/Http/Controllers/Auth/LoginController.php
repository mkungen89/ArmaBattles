<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class LoginController extends Controller
{
    public function showForm()
    {
        return view('auth.login');
    }

    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|string|email',
            'password' => 'required|string',
        ]);

        if (! Auth::attempt($request->only('email', 'password'), remember: true)) {
            return back()->withErrors([
                'email' => 'These credentials do not match our records.',
            ])->onlyInput('email');
        }

        $request->session()->regenerate();

        $user = Auth::user();

        if ($user->is_banned) {
            Auth::logout();
            $request->session()->invalidate();
            $request->session()->regenerateToken();

            return redirect()->route('home')->with('error', 'You are banned from this community.');
        }

        // Check for 2FA
        if ($user->hasTwoFactorEnabled()) {
            Auth::logout();

            $request->session()->put('two_factor_user_id', $user->id);
            $request->session()->put('two_factor_remember', true);

            return redirect()->route('two-factor.challenge');
        }

        $user->update(['last_login_at' => now()]);

        return redirect()->intended(route('profile'))->with('success', 'Welcome back, '.$user->name.'!');
    }
}
