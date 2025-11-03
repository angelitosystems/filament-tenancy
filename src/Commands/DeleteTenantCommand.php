<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use Illuminate\Console\Command;

class DeleteTenantCommand extends Command
{
    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenancy:delete 
                            {tenant : The tenant ID or slug to delete}
                            {--force : Force deletion without confirmation}
                            {--keep-database : Keep the tenant database (do not delete)}
                            {--delete-database : Delete the tenant database (default behavior)}';

    /**
     * The console command description.
     */
    protected $description = 'Delete a tenant';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $tenantIdentifier = $this->argument('tenant');
        $force = $this->option('force');
        $keepDatabase = $this->option('keep-database');
        $deleteDatabase = $this->option('delete-database');

        try {
            // Find tenant by ID or slug
            $tenant = null;
            if (is_numeric($tenantIdentifier)) {
                $tenant = Tenancy::findTenant((int) $tenantIdentifier);
            } else {
                $tenant = Tenancy::findTenantBySlug($tenantIdentifier);
            }

            if (!$tenant) {
                $this->error("Tenant '{$tenantIdentifier}' not found.");
                return self::FAILURE;
            }

            // Show tenant information
            $this->info("Tenant to delete:");
            $this->table(
                ['Property', 'Value'],
                [
                    ['ID', $tenant->id],
                    ['Name', $tenant->name],
                    ['Slug', $tenant->slug],
                    ['Domain', $tenant->domain ?: 'N/A'],
                    ['Subdomain', $tenant->subdomain ?: 'N/A'],
                    ['Database', $tenant->database_name],
                    ['Active', $tenant->is_active ? 'Yes' : 'No'],
                    ['Created', $tenant->created_at->format('Y-m-d H:i:s')],
                ]
            );

            // Default behavior: delete database unless explicitly told to keep it
            $willDeleteDatabase = !$keepDatabase;

            // Confirmation
            if (!$force) {
                $databaseAction = $willDeleteDatabase ? 'DELETED' : 'KEPT';
                $confirmed = $this->confirm(
                    "⚠️  Are you sure you want to delete tenant '{$tenant->name}' and ALL its data? " .
                    "The tenant database '{$tenant->database_name}' will be {$databaseAction}. " .
                    "This action is IRREVERSIBLE!"
                );

                if (!$confirmed) {
                    $this->info('❌ Deletion cancelled.');
                    return self::SUCCESS;
                }
            }

            // Force database deletion for this operation
            $originalSetting = config('filament-tenancy.database.auto_delete_tenant_database');
            config(['filament-tenancy.database.auto_delete_tenant_database' => $willDeleteDatabase]);

            // Delete tenant
            $this->info('🗑️  Deleting tenant and all related data...');
            $deleted = Tenancy::deleteTenant($tenant);

            // Restore original setting
            config(['filament-tenancy.database.auto_delete_tenant_database' => $originalSetting]);

            if ($deleted) {
                $this->info("✅ Tenant '{$tenant->name}' deleted successfully!");
                
                if ($willDeleteDatabase) {
                    $this->info("🗑️  Database '{$tenant->database_name}' was also deleted.");
                } else {
                    $this->warn("⚠️  Database '{$tenant->database_name}' was kept as requested.");
                }
                
                $this->line("🎉 All tenant data including users, roles, permissions, and subscriptions have been removed.");
            } else {
                $this->error('❌ Failed to delete tenant.');
                return self::FAILURE;
            }

            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('❌ Failed to delete tenant: ' . $e->getMessage());
            return self::FAILURE;
        }
    }
}