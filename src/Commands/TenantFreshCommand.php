<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use AngelitoSystems\FilamentTenancy\Support\DatabaseManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantFreshCommand extends Command
{
    protected $signature = 'tenant:fresh 
                            {tenant? : Tenant ID or slug}
                            {--seed : Run database seeders after migration}
                            {--force : Force operation in production}
                            {--drop-views : Drop all views}
                            {--drop-types : Drop all custom types}';

    protected $description = 'Drop all tables and re-run migrations for a specific tenant';

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
            $tenantIdentifier = $this->ask('Enter tenant ID or slug to fresh');
        }

        $tenant = $this->findTenant($tenantIdentifier);

        if (!$tenant) {
            $this->error("❌ Tenant '{$tenantIdentifier}' not found.");
            return self::FAILURE;
        }

        // Warning message
        $this->warn("⚠️  WARNING: This will drop all tables in tenant '{$tenant->name}' database!");
        $this->warn("   This action is IRREVERSIBLE and will delete all tenant data.");
        $this->newLine();

        if (!$this->option('force')) {
            $confirmed = $this->confirm('Do you really want to drop all tables and re-run migrations?', false);
            if (!$confirmed) {
                $this->info('❌ Operation cancelled.');
                return self::FAILURE;
            }
        }

        $this->info("🧹 Fresh start for tenant: {$tenant->name}");
        $this->line("📍 Domain: " . ($tenant->domain ?? $tenant->subdomain ?? 'N/A'));
        $this->newLine();

        try {
            // Get database manager
            $databaseManager = app(DatabaseManager::class);

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

            // Switch to tenant context and drop all tables
            Tenancy::runForTenant($tenant, function () use ($tenant) {
                $this->info('🗑️  Dropping all tenant tables...');
                
                // Get all table names
                $tables = $this->getAllTables();
                
                if (empty($tables)) {
                    $this->info('ℹ️  No tables found to drop.');
                } else {
                    // Disable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=0;');
                    
                    foreach ($tables as $table) {
                        try {
                            Schema::dropIfExists($table);
                            $this->line("   ✓ Dropped table: {$table}");
                        } catch (\Exception $e) {
                            $this->warn("   ⚠️  Could not drop table {$table}: {$e->getMessage()}");
                        }
                    }
                    
                    // Re-enable foreign key checks
                    DB::statement('SET FOREIGN_KEY_CHECKS=1;');
                    
                    $this->info('✅ All tables dropped successfully!');
                }

                // Drop views if requested
                if ($this->option('drop-views')) {
                    $this->info('🗑️  Dropping views...');
                    $this->dropAllViews();
                }

                // Drop types if requested (PostgreSQL)
                if ($this->option('drop-types')) {
                    $this->info('🗑️  Dropping custom types...');
                    $this->dropAllTypes();
                }
            });

            // Re-run migrations
            $this->newLine();
            $this->info('🚀 Re-running tenant migrations...');
            
            $success = $databaseManager->runTenantMigrations($tenant);

            if (!$success) {
                $this->error('❌ Tenant migrations failed during fresh operation.');
                return self::FAILURE;
            }

            $this->info('✅ Tenant migrations completed successfully!');

            // Run seeders if requested
            if ($this->option('seed')) {
                $this->newLine();
                $this->info('🌱 Running tenant seeders...');
                
                Tenancy::runForTenant($tenant, function () {
                    $this->call('db:seed', [
                        '--force' => $this->option('force'),
                    ]);
                });
                
                $this->info('✅ Tenant seeders completed!');
            }

        } catch (\Exception $e) {
            $this->error("❌ Fresh operation error: {$e->getMessage()}");
            DebugHelper::error("Tenant fresh operation failed", [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        }

        $this->newLine();
        $this->info("🎉 Tenant '{$tenant->name}' has been reset successfully!");
        $this->line("🔗 URL: " . $tenant->getUrl());

        return self::SUCCESS;
    }

    protected function getAllTables(): array
    {
        $driver = config('database.default');
        
        switch ($driver) {
            case 'mysql':
                return DB::select('SHOW TABLES');
            case 'pgsql':
                return DB::select("SELECT tablename FROM pg_catalog.pg_tables WHERE schemaname = 'public'");
            default:
                return [];
        }
    }

    protected function dropAllViews(): void
    {
        $driver = config('database.default');
        
        switch ($driver) {
            case 'mysql':
                $views = DB::select("SHOW FULL TABLES WHERE Table_type = 'VIEW'");
                foreach ($views as $view) {
                    $viewName = array_values((array)$view)[0];
                    DB::statement("DROP VIEW IF EXISTS {$viewName}");
                    $this->line("   ✓ Dropped view: {$viewName}");
                }
                break;
            case 'pgsql':
                $views = DB::select("SELECT table_name FROM information_schema.views WHERE table_schema = 'public'");
                foreach ($views as $view) {
                    DB::statement("DROP VIEW IF EXISTS \"{$view->table_name}\" CASCADE");
                    $this->line("   ✓ Dropped view: {$view->table_name}");
                }
                break;
        }
    }

    protected function dropAllTypes(): void
    {
        $driver = config('database.default');
        
        if ($driver === 'pgsql') {
            $types = DB::select("SELECT typname FROM pg_type WHERE typtype = 'e' AND typnamespace = (SELECT oid FROM pg_namespace WHERE nspname = 'public')");
            foreach ($types as $type) {
                DB::statement("DROP TYPE IF EXISTS \"{$type->typname}\" CASCADE");
                $this->line("   ✓ Dropped type: {$type->typname}");
            }
        }
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
