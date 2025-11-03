<?php

namespace AngelitoSystems\FilamentTenancy\Listeners;

use AngelitoSystems\FilamentTenancy\Events\TenantCreated;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use AngelitoSystems\FilamentTenancy\Support\DatabaseManager;

class RunTenantMigrationsOnTenantCreated
{
    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        try {
            // Get database manager
            $databaseManager = app(DatabaseManager::class);
            
            // Run tenant migrations
            $success = $databaseManager->runTenantMigrations($tenant);

            if ($success) {
                DebugHelper::info('Tenant migrations completed successfully', [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                ]);
            } else {
                DebugHelper::warning('Tenant migrations completed with warnings', [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                ]);
            }
        } catch (\Exception $e) {
            DebugHelper::error("Failed to run tenant migrations for tenant {$tenant->id}: {$e->getMessage()}", [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
                'error' => $e->getMessage(),
            ]);
        }
    }
}
