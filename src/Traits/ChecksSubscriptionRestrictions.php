<?php

namespace AngelitoSystems\FilamentTenancy\Traits;

trait ChecksSubscriptionRestrictions
{
    /**
     * Resources that are always allowed even with subscription restrictions.
     */
    protected static function getAllowedResourcesWithRestrictions(): array
    {
        return [
            \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource::class,
            \AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource::class,
        ];
    }

    /**
     * Check if subscription is restricted.
     */
    protected static function isSubscriptionRestricted(): bool
    {
        return session()->get('subscription_restricted', false);
    }

    /**
     * Check if this resource is allowed with subscription restrictions.
     */
    protected static function isAllowedWithRestrictions(): bool
    {
        $resourceClass = static::class;
        $allowedResources = static::getAllowedResourcesWithRestrictions();
        
        return in_array($resourceClass, $allowedResources);
    }
}

