<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;

class RoleMiddleware
{
    public function handle(Request $request, Closure $next, ...$roles)
    {
        $user = auth()->user();

        if (!$user) {
            abort(403, 'Not logged in');
        }

        // if the user's role matches one of the allowed roles we're good
        if (in_array($user->role, $roles)) {
            return $next($request);
        }

        // employees from administrative departments should have full access
        if ($user->employee) {
            $hasAdminDept = $user->employee->departments()
                                ->where('category', 'administrative')
                                ->exists();
            if ($hasAdminDept) {
                return $next($request);
            }
        }

        abort(403, 'Unauthorized: your role does not have access');

        return $next($request);
    }
}