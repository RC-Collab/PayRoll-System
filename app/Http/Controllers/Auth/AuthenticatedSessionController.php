<?php

namespace App\Http\Controllers\Auth;

use App\Http\Controllers\Controller;
use App\Http\Requests\Auth\LoginRequest;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Auth;

class AuthenticatedSessionController extends Controller
{
    /**
     * Handle an incoming authentication request.
     */
    public function store(LoginRequest $request): RedirectResponse
{
    $request->authenticate();
    $request->session()->regenerate();

    $user = auth()->user();

    // ROLE BASED REDIRECT
    if ($user->hasRole('admin')) {
        return redirect()->intended('/admin/dashboard');
    }

    if ($user->hasRole('hr')) {
        return redirect()->intended('/hr/dashboard');
    }

    if ($user->hasRole('accountant')) {
        return redirect()->intended('/accountant/dashboard');
    }

    // fallback
    return redirect('/');
}

    /**
     * Destroy an authenticated session.
     */
    public function destroy(Request $request): Response
    {
        Auth::guard('web')->logout();

        $request->session()->invalidate();

        $request->session()->regenerateToken();

        return response()->noContent();
    }
}
