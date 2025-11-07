<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

/**
 * Middleware to restrict features when subscription is pending or expired.
 * This middleware should be applied to routes that need active subscription.
 */
class RestrictSubscriptionFeatures
{
    /**
     * Routes that are always allowed (payment-related routes).
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
        // Check if subscription is restricted
        if (session()->get('subscription_restricted')) {
            // Check if this is a payment route - allow it
            if ($this->isPaymentRoute($request)) {
                return $next($request);
            }

            // Block access to other features
            $restrictionType = session()->get('subscription_restriction_type', 'pending');
            $message = session()->get('subscription_restriction_message', 'Payment required to access this feature.');

            // For API requests, return JSON response
            if ($request->expectsJson() || $request->is('api/*')) {
                return response()->json([
                    'message' => $message,
                    'restricted' => true,
                    'restriction_type' => $restrictionType,
                ], 403);
            }

            // For web requests, redirect or show error
            abort(403, $message);
        }

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

