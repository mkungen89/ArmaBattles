<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class RegisterController extends Controller
{
    public function showForm()
    {
        if (! site_setting('allow_registration', true)) {
            return redirect()->route('home')->with('error', 'Registration is currently disabled.');
        }

        return view('auth.register');
    }

    public function register(Request $request)
    {
        if (! site_setting('allow_registration', true)) {
            return redirect()->route('home')->with('error', 'Registration is currently disabled.');
        }

        $validated = $request->validate([
            'name' => 'required|string|max:255',
            'email' => 'required|string|email|max:255|unique:users,email',
            'password' => 'required|string|min:8|confirmed',
        ]);

        $user = User::create([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'password' => $validated['password'],
            'role' => 'user',
            'last_login_at' => now(),
        ]);

        Auth::login($user, true);

        return redirect()->route('profile')->with('success', 'Welcome, '.$user->name.'! Your account has been created.');
    }
}
