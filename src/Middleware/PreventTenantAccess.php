<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que previene el acceso al panel admin/landlord cuando hay un tenant activo.
 * El panel admin solo debe estar accesible desde dominios centrales sin tenant.
 */
class PreventTenantAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenant = Tenancy::current();

        // Si hay un tenant activo, bloquear el acceso al panel admin
        if ($currentTenant !== null) {
            abort(403, 'Access denied: Admin panel cannot be accessed from tenant context.');
        }

        return $next($request);
    }
}

