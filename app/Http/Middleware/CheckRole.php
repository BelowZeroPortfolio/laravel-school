<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * Check if the authenticated user has the required role(s).
     * - Admin bypasses all role checks (Requirement 2.1)
     * - Principal inherits teacher access (Requirement 2.2)
     * - Teacher denied admin routes (Requirement 2.3)
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  The required roles for this route
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // Unauthenticated users should be redirected to login (Requirement 2.4)
        if (!$user) {
            return redirect()->route('login');
        }

        // Admin bypasses all role checks (Requirement 2.1)
        if ($user->isAdmin()) {
            return $next($request);
        }

        // Check if user has any of the required roles
        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return $next($request);
            }

            // Principal inherits teacher access (Requirement 2.2)
            if ($role === 'teacher' && $user->isPrincipal()) {
                return $next($request);
            }
        }

        // User doesn't have required role - return 403 (Requirement 2.3)
        abort(403, 'Unauthorized. You do not have the required role to access this resource.');
    }
}
