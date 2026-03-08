<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class Authenticate
{

protected function redirectTo($request)
{
    if (! $request->expectsJson()) {
        return route('login');
    }
}

    public function handle(Request $request, Closure $next, ...$guards)
    {
        if (!Auth::check()) {
            return response()->json(['message' => 'Unauthenticated.'], 401);
        }
        return $next($request);
    }

    
}