<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Console\Command;

class TenantMigrateCommand extends Command
{
    protected $signature = 'tenant:migrate 
                            {tenant? : Tenant ID or slug}
                            {--path= : Specific migration path}
                            {--force : Force migration in production}
                            {--seed : Run database seeders after migration}
                            {--step : Force migration to run one step at a time}';

    protected $description = 'Run migrations for a specific tenant';

    public function handle(): int
    {
        $tenantIdentifier = $this->argument('tenant');

        if (!$tenantIdentifier) {
            $this->info('📋 Available tenants:');
            $this->newLine();

            $tenants = Tenant::all(['id', 'name', 'slug', 'domain', 'subdomain', 'is_active']);

            if ($tenants->isEmpty()) {
                $this->warn('⚠️  No tenants found. Create a tenant first:');
                $this->line('   php artisan tenancy:create');
                return self::FAILURE;
            }

            $this->table(
                ['ID', 'Name', 'Slug', 'Domain/Subdomain', 'Active'],
                $tenants->map(fn($tenant) => [
                    $tenant->id,
                    $tenant->name,
                    $tenant->slug,
                    $tenant->domain ?? $tenant->subdomain ?? 'N/A',
                    $tenant->is_active ? '✅ Yes' : '❌ No',
                ])
            );

            $this->newLine();
            $tenantIdentifier = $this->ask('Enter tenant ID or slug to migrate');
        }

        $tenant = $this->findTenant($tenantIdentifier);

        if (!$tenant) {
            $this->error("❌ Tenant '{$tenantIdentifier}' not found.");
            return self::FAILURE;
        }

        if (!$tenant->is_active) {
            $this->warn("⚠️  Tenant '{$tenant->name}' is not active.");
            if (!$this->confirm('Do you want to continue anyway?', false)) {
                return self::FAILURE;
            }
        }

        $this->info("🚀 Running migrations for tenant: {$tenant->name}");
        $this->line("📍 Domain: " . ($tenant->domain ?? $tenant->subdomain ?? 'N/A'));
        $this->newLine();

        try {
            // Get database manager
            $databaseManager = app(\AngelitoSystems\FilamentTenancy\Support\DatabaseManager::class);

            // Check if tenant database exists
            if (!$databaseManager->tenantDatabaseExists($tenant)) {
                $this->warn("⚠️  Tenant database doesn't exist.");
                
                if ($this->confirm('Do you want to create the tenant database?', true)) {
                    $this->info('🔧 Creating tenant database...');
                    
                    if (!$databaseManager->createTenantDatabase($tenant)) {
                        $this->error('❌ Failed to create tenant database.');
                        return self::FAILURE;
                    }
                    
                    $this->info('✅ Tenant database created successfully.');
                } else {
                    return self::FAILURE;
                }
            }

            // Run tenant migrations
            $success = $databaseManager->runTenantMigrations($tenant);

            if ($success) {
                $this->info('✅ Tenant migrations completed successfully!');
                
                // Run seeders if requested
                if ($this->option('seed')) {
                    $this->newLine();
                    $this->info('🌱 Running tenant seeders...');
                    
                    $seederSuccess = $databaseManager->runTenantSeeders($tenant);
                    
                    if ($seederSuccess) {
                        $this->info('✅ Tenant seeders completed!');
                    } else {
                        $this->warn('⚠️  Some seeders may have failed. Check logs for details.');
                    }
                }
            } else {
                $this->error('❌ Tenant migrations failed. Check logs for details.');
                return self::FAILURE;
            }

        } catch (\Exception $e) {
            $this->error("❌ Migration error: {$e->getMessage()}");
            DebugHelper::error("Tenant migration failed", [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("🎉 Tenant '{$tenant->name}' is ready to use!");
        $this->line("🔗 URL: " . $tenant->getUrl());

        return self::SUCCESS;
    }

    protected function findTenant(string $identifier): ?Tenant
    {
        // Try by ID first
        if (is_numeric($identifier)) {
            $tenant = Tenant::find($identifier);
            if ($tenant) {
                return $tenant;
            }
        }

        // Try by slug
        return Tenant::where('slug', $identifier)->first();
    }
}
