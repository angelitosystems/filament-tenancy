<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckRole
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $role)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have the required role to access this resource.');
        }

        // Check role using call_user_func to avoid IDE static analysis issues
        $hasRole = call_user_func(function($u, $r) {
            if (method_exists($u, 'hasRole')) {
                return $u->hasRole($r);
            }
            return false;
        }, $user, $role);

        if (!$hasRole) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have the required role to access this resource.');
        }

        return $next($request);
    }
}
