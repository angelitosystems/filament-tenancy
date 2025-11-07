<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckPendingSubscription
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenancy::current();

        // Only check for tenant context
        if (!$tenant) {
            return $next($request);
        }

        // Skip if accessing admin/landlord routes
        if ($this->isLandlordRoute($request)) {
            return $next($request);
        }

        // Check if tenant has pending subscription
        $pendingSubscription = $tenant->subscriptions()
            ->where('status', Subscription::STATUS_PENDING)
            ->latest()
            ->first();

        if ($pendingSubscription) {
            // Generate payment link if it doesn't exist or has expired
            $paypalService = app(PayPalService::class);
            
            if (empty($pendingSubscription->payment_link) || 
                ($pendingSubscription->payment_link_expires_at && $pendingSubscription->payment_link_expires_at->isPast())) {
                
                // Generate payment link
                $paymentLink = $paypalService->generatePaymentLinkForPending($pendingSubscription);
                
                if ($paymentLink) {
                    // Store in session for Filament notification
                    session()->put('pending_subscription_payment_link', $paymentLink);
                    session()->put('pending_subscription_id', $pendingSubscription->id);
                }
            } else {
                // Payment link exists and is valid
                session()->put('pending_subscription_payment_link', $pendingSubscription->payment_link);
                session()->put('pending_subscription_id', $pendingSubscription->id);
            }
        }

        return $next($request);
    }

    /**
     * Check if the current route is a landlord/admin route.
     */
    protected function isLandlordRoute(Request $request): bool
    {
        $path = $request->path();
        $landlordPaths = config('filament-tenancy.middleware.landlord_paths', [
            '/admin',
            '/landlord',
        ]);

        foreach ($landlordPaths as $landlordPath) {
            if (str_starts_with($path, $landlordPath)) {
                return true;
            }
        }

        return false;
    }
}

