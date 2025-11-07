<?php

namespace AngelitoSystems\FilamentTenancy\Listeners;

use AngelitoSystems\FilamentTenancy\Events\SubscriptionStatusChanged;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use Illuminate\Support\Facades\Log;

class UpdateTenantStatusOnSubscriptionChange
{
    /**
     * Handle the event.
     */
    public function handle(SubscriptionStatusChanged $event): void
    {
        $subscription = $event->subscription;
        $tenant = $subscription->tenant;

        if (!$tenant) {
            return;
        }

        // Activate tenant when subscription becomes active
        if ($event->newStatus === Subscription::STATUS_ACTIVE && $event->oldStatus !== Subscription::STATUS_ACTIVE) {
            if (!$tenant->is_active) {
                $tenant->update(['is_active' => true]);
                
                Log::info('Tenant activated due to subscription activation', [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                ]);
            }

            // Limpiar restricciones de sesión para habilitar todos los recursos
            session()->forget('subscription_restricted');
            session()->forget('subscription_restriction_type');
            session()->forget('subscription_restriction_message');
        }

        // Deactivate tenant when subscription is canceled (immediately)
        if ($event->newStatus === Subscription::STATUS_CANCELED && $event->oldStatus !== Subscription::STATUS_CANCELED) {
            if ($tenant->is_active) {
                $tenant->update(['is_active' => false]);
                
                Log::info('Tenant deactivated due to subscription cancellation', [
                    'tenant_id' => $tenant->id,
                    'subscription_id' => $subscription->id,
                ]);
            }
        }

        // For expired subscriptions, tenant will be deactivated by command after grace period
        // This listener only handles immediate actions (active/canceled)
    }
}

