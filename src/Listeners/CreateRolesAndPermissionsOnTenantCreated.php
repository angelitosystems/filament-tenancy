<?php

namespace AngelitoSystems\FilamentTenancy\Listeners;

use AngelitoSystems\FilamentTenancy\Events\TenantCreated;
use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Support\Facades\Artisan;

class CreateRolesAndPermissionsOnTenantCreated
{
    /**
     * Handle the event.
     */
    public function handle(TenantCreated $event): void
    {
        $tenant = $event->tenant;

        try {
            // Switch to tenant context to create roles and permissions in tenant database
            if (class_exists('\AngelitoSystems\FilamentTenancy\Facades\Tenancy')) {
                \AngelitoSystems\FilamentTenancy\Facades\Tenancy::runForTenant($tenant, function () use ($tenant) {
                    $this->seedTenantRolesAndPermissions();
                });
            } else {
                // Fallback method if facade is not available
                DebugHelper::error('Tenancy facade not available');
            }

            DebugHelper::info('Roles and permissions created for tenant', [
                'tenant_id' => $tenant->id,
                'tenant_name' => $tenant->name,
            ]);
        } catch (\Exception $e) {
            DebugHelper::error("Failed to create roles and permissions for tenant {$tenant->id}: {$e->getMessage()}");
        }
    }

    /**
     * Seed tenant roles and permissions using the tenant seeder.
     */
    protected function seedTenantRolesAndPermissions(): void
    {
        try {
            // Run the tenant role and permission seeder
            Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\Tenant\\RolePermissionSeeder',
                '--force' => true,
            ]);

            DebugHelper::info('Tenant roles and permissions seeded successfully');
        } catch (\Exception $e) {
            DebugHelper::error('Failed to seed tenant roles and permissions', [
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}