<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class CheckPermission
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next, string $permission)
    {
        $user = Auth::user();

        if (!$user) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        // Check permission using call_user_func to avoid IDE static analysis issues
        $hasPermission = call_user_func(function($u, $perm) {
            if (method_exists($u, 'hasPermissionTo')) {
                return $u->hasPermissionTo($perm);
            }
            return false;
        }, $user, $permission);

        if (!$hasPermission) {
            if ($request->expectsJson()) {
                return response()->json(['message' => 'Unauthorized'], 403);
            }
            abort(403, 'You do not have permission to access this resource.');
        }

        return $next($request);
    }
}
