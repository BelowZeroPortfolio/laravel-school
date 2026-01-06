<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureSchoolContext
{
    /**
     * Handle an incoming request.
     * Ensures non-super-admin users have a school assigned.
     */
    public function handle(Request $request, Closure $next): Response
    {
        if (auth()->check()) {
            $user = auth()->user();
            
            // Super admins can access without school context
            if ($user->isSuperAdmin()) {
                return $next($request);
            }
            
            // Other users must have a school assigned
            if (!$user->school_id) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'No school assigned to your account. Please contact administrator.'
                    ], 403);
                }
                
                abort(403, 'No school assigned to your account. Please contact administrator.');
            }
            
            // Check if school is active
            if (!$user->school || !$user->school->is_active) {
                if ($request->expectsJson()) {
                    return response()->json([
                        'message' => 'Your school is currently inactive. Please contact administrator.'
                    ], 403);
                }
                
                abort(403, 'Your school is currently inactive. Please contact administrator.');
            }
        }

        return $next($request);
    }
}
