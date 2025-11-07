<?php

namespace AngelitoSystems\FilamentTenancy\Traits;

use Filament\Notifications\Notification;

trait BlocksSubscriptionRestrictedAccess
{
    /**
     * Resources that are always allowed even with subscription restrictions.
     */
    protected function getAllowedResourcesWithRestrictions(): array
    {
        return [
            \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource::class,
            \AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource::class,
        ];
    }

    /**
     * Check if subscription is restricted.
     */
    protected function isSubscriptionRestricted(): bool
    {
        return session()->get('subscription_restricted', false);
    }

    /**
     * Check if this resource is allowed with subscription restrictions.
     */
    protected function isAllowedWithRestrictions(): bool
    {
        $resourceClass = static::$resource;
        $allowedResources = $this->getAllowedResourcesWithRestrictions();
        
        return in_array($resourceClass, $allowedResources);
    }

    /**
     * Mount the page and check for subscription restrictions.
     */
    public function mount(): void
    {
        // Check if subscription is restricted and this resource is not allowed
        if ($this->isSubscriptionRestricted() && !$this->isAllowedWithRestrictions()) {
            $message = session()->get('subscription_restriction_message', __('filament-tenancy::tenancy.errors.subscription_payment_required'));
            
            Notification::make()
                ->title(__('filament-tenancy::tenancy.errors.subscription_payment_required'))
                ->body($message)
                ->warning()
                ->persistent()
                ->send();

            // Redirect to plans page to pay
            $planResource = \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource::class;
            $this->redirect($planResource::getUrl('index'));
            
            return;
        }

        // Call parent mount if it exists
        if (method_exists(parent::class, 'mount')) {
            parent::mount();
        }
    }
}

