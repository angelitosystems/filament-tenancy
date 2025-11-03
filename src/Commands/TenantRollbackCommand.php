<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\DatabaseManager;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use AngelitoSystems\FilamentTenancy\Support\TenantMigrationRunner;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class TenantRollbackCommand extends Command
{
    protected $signature = 'tenant:rollback 
                            {tenant? : Tenant ID or slug}
                            {--step=1 : Number of migrations to rollback}
                            {--batch : Rollback to specific batch}
                            {--force : Force rollback in production}';

    protected $description = 'Rollback migrations for a specific tenant';

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
            $tenantIdentifier = $this->ask('Enter tenant ID or slug to rollback');
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

        $this->info("🔄 Rolling back migrations for tenant: {$tenant->name}");
        $this->line("📍 Domain: " . ($tenant->domain ?? $tenant->subdomain ?? 'N/A'));
        $this->newLine();

        try {
            // Get database manager
            $databaseManager = app(DatabaseManager::class);
            
            // Switch to tenant context
            $databaseManager->switchToTenant($tenant);
            $tenantConnection = $databaseManager->getTenantConnectionName($tenant);

            // Check if migrations table exists
            if (!Schema::connection($tenantConnection)->hasTable('migrations')) {
                $this->warn('⚠️  No migrations table found. Nothing to rollback.');
                return self::SUCCESS;
            }

            // Get migrations to rollback
            $step = (int) $this->option('step');
            $batch = $this->option('batch');

            if ($batch) {
                $migrations = DB::connection($tenantConnection)
                    ->table('migrations')
                    ->where('batch', $batch)
                    ->orderBy('id', 'desc')
                    ->get();
            } else {
                $lastBatch = DB::connection($tenantConnection)
                    ->table('migrations')
                    ->max('batch');
                $migrations = DB::connection($tenantConnection)
                    ->table('migrations')
                    ->where('batch', $lastBatch)
                    ->orderBy('id', 'desc')
                    ->limit($step)
                    ->get();
            }

            if ($migrations->isEmpty()) {
                $this->info('ℹ️  No migrations to rollback.');
                return self::SUCCESS;
            }

            $this->info("📦 Found {$migrations->count()} migration(s) to rollback:");
            foreach ($migrations as $migration) {
                $this->line("   • {$migration->migration}");
            }
            $this->newLine();

            if (!$this->confirm('Do you want to continue with the rollback?', false)) {
                $this->info('❌ Rollback cancelled.');
                return self::SUCCESS;
            }

            // Usar el sistema propio de rollback
            $runner = new TenantMigrationRunner($tenant, $tenantConnection);
            $runner->rollback($migrations->count());

            $this->info('✅ Rollback completed successfully!');

        } catch (\Exception $e) {
            $this->error("❌ Rollback error: {$e->getMessage()}");
            DebugHelper::error("Tenant rollback failed", [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return self::FAILURE;
        } finally {
            // Restaurar conexión central
            if (isset($databaseManager)) {
                $databaseManager->switchToCentral();
            }
        }

        $this->newLine();
        $this->info("🎉 Tenant '{$tenant->name}' rollback completed!");

        return self::SUCCESS;
    }

    protected function rollbackMigration(string $migrationName): void
    {
        try {
            $migrationPath = database_path("migrations/tenant/{$migrationName}.php");
            
            if (!file_exists($migrationPath)) {
                $this->warn("⚠️  Migration file not found: {$migrationName}.php");
                return;
            }

            require_once $migrationPath;
            
            $migrationClass = $this->getMigrationClassFromFile($migrationPath);
            
            if (class_exists($migrationClass)) {
                $migration = new $migrationClass();
                $migration->down();

                // Remove from migrations table
                DB::table('migrations')->where('migration', $migrationName)->delete();

                $this->line("   ✓ Rolled back: {$migrationName}");
            }
        } catch (\Exception $e) {
            $this->error("   ✗ Failed to rollback {$migrationName}: {$e->getMessage()}");
            throw $e;
        }
    }

    protected function getMigrationClassFromFile(string $migrationFile): string
    {
        $content = file_get_contents($migrationFile);
        
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        throw new \Exception("Could not determine migration class from file: {$migrationFile}");
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
