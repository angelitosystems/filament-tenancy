<?php

namespace AngelitoSystems\FilamentTenancy\Listeners;

use AngelitoSystems\FilamentTenancy\Events\TenantCreated;
use AngelitoSystems\FilamentTenancy\Support\AssetManager;

class ShareAssetsOnTenantCreated
{
    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        // Share assets when a new tenant is created
        AssetManager::copyAssetsForTenant();
    }
}
