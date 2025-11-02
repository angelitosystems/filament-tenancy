<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware que previene el acceso al panel tenant cuando no hay un tenant activo.
 * El panel tenant solo debe estar accesible cuando hay un tenant resuelto.
 */
class PreventLandlordAccess
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     */
    public function handle(Request $request, Closure $next): Response
    {
        $currentTenant = Tenancy::current();

        // Si no hay un tenant activo, bloquear el acceso al panel tenant
        if ($currentTenant === null) {
            abort(403, 'Access denied: Tenant panel requires an active tenant context.');
        }

        return $next($request);
    }
}

