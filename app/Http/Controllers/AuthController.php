<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Hash;
use App\Models\User;

class AuthController extends Controller
{
    public function login(Request $request)
    {
        $request->validate([
            'email' => 'required|email',
            'password' => 'required',
        ]);

        $user = User::where('email', $request->email)->first();

        if (!$user) {
            return back()->withErrors(['email' => 'User not found.']);
        }

        if (!Hash::check($request->password, $user->password)) {
            return back()->withErrors(['password' => 'Invalid password.']);
        }

        // must have a verified, active user record
        if (is_null($user->email_verified_at) || !$user->is_active) {
            return back()->withErrors(['email' => 'Your account is not active. Please activate first.']);
        }

        // Allow only admin, hr, accountant
        if (!in_array($user->role, ['admin', 'hr', 'accountant'])) {
    return back()->withErrors([
        'email' => 'You are not allowed to access the dashboard.'
    ]);
}
        Auth::login($user);

        return redirect()->route('dashboard');
    }

    public function logout(Request $request)
    {
        Auth::logout();

        $request->session()->invalidate();
        $request->session()->regenerateToken();

        return redirect()->route('login');
    }
}