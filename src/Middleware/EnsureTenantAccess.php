<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class EnsureTenantAccess
{
    /**
     * Routes that are always allowed even without active subscription (payment routes).
     */
    protected array $allowedRoutes = [
        'paypal',
        'payment',
        'subscription',
        'billing',
    ];

    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenancy::current();

        // Ensure we have a current tenant
        if (!$tenant) {
            abort(404, __('filament-tenancy::tenancy.errors.tenant_not_found'));
        }

        // Ensure tenant is active
        if (!$tenant->is_active) {
            abort(403, __('filament-tenancy::tenancy.errors.tenant_not_active'));
        }

        // PRIMERO: Verificar siempre la BD para suscripción activa (sin depender de la sesión)
        // Esto asegura que si el admin activa la suscripción, se detecte inmediatamente
        $activeSubscription = $tenant->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where(function ($query) {
                $query->whereNull('ends_at')
                    ->orWhere('ends_at', '>', now());
            })
            ->first();

        if ($activeSubscription) {
            // Tenant has active subscription, allow full access
            // Limpiar todas las restricciones de sesión inmediatamente
            session()->forget('subscription_restricted');
            session()->forget('subscription_restriction_type');
            session()->forget('subscription_restriction_message');
            return $next($request);
        }

        // Check if this is a payment-related route - always allow
        if ($this->isPaymentRoute($request)) {
            return $next($request);
        }

        // Check for expired subscription within grace period
        $expiredSubscription = $tenant->subscriptions()
            ->where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '<=', now())
            ->where('ends_at', '>=', now()->subDays(config('filament-tenancy.paypal.grace_period_days', 7)))
            ->first();

        if ($expiredSubscription) {
            // Subscription expired but within grace period
            // Allow limited access - mark as restricted
            session()->put('subscription_restricted', true);
            session()->put('subscription_restriction_type', 'expired');
            session()->put('subscription_restriction_message', __('filament-tenancy::tenancy.errors.subscription_expired_payment_required'));
            
            // Allow access but functions will be blocked by other middleware/checks
            return $next($request);
        }

        // Check for pending subscription
        $pendingSubscription = $tenant->subscriptions()
            ->where('status', Subscription::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingSubscription) {
            // Tenant has pending subscription - allow limited access
            session()->put('subscription_restricted', true);
            session()->put('subscription_restriction_type', 'pending');
            session()->put('subscription_restriction_message', __('filament-tenancy::tenancy.errors.subscription_pending_payment_required'));
            
            // Allow access but functions will be blocked by other middleware/checks
            return $next($request);
        }

        // Check for any subscription (even canceled or expired beyond grace period)
        // This allows access but marks as restricted so they can see payment options
        $anySubscription = $tenant->subscriptions()->latest()->first();

        if ($anySubscription) {
            // Tenant has a subscription but it's not active/pending/expired within grace
            // Allow access but mark as restricted
            session()->put('subscription_restricted', true);
            session()->put('subscription_restriction_type', 'expired');
            session()->put('subscription_restriction_message', __('filament-tenancy::tenancy.errors.subscription_expired_payment_required'));
            
            // Allow access so they can pay
            return $next($request);
        }

        // No subscription found at all - still allow access but mark as restricted
        // This allows tenant to access the system to create/pay for subscription
        session()->put('subscription_restricted', true);
        session()->put('subscription_restriction_type', 'none');
        session()->put('subscription_restriction_message', __('filament-tenancy::tenancy.errors.subscription_pending_payment_required'));
        
        // Allow access so they can create and pay for subscription
        return $next($request);
    }

    /**
     * Check if the current route is a payment-related route.
     */
    protected function isPaymentRoute(Request $request): bool
    {
        $path = $request->path();
        $routeName = $request->route()?->getName() ?? '';

        // Check if path contains payment-related keywords
        foreach ($this->allowedRoutes as $allowedRoute) {
            if (str_contains($path, $allowedRoute) || str_contains($routeName, $allowedRoute)) {
                return true;
            }
        }

        // Check specific PayPal routes
        if (str_contains($path, 'paypal/success') || 
            str_contains($path, 'paypal/cancel') || 
            str_contains($path, 'paypal/webhook')) {
            return true;
        }

        return false;
    }
}