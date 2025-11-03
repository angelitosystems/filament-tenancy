<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\ConnectionManager;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Database\DatabaseManager as IlluminateDatabaseManager;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

class DatabaseManager
{
    protected IlluminateDatabaseManager $databaseManager;
    protected ConnectionManager $connectionManager;
    protected string $originalConnection;

    public function __construct(IlluminateDatabaseManager $databaseManager, ConnectionManager $connectionManager)
    {
        $this->databaseManager = $databaseManager;
        $this->connectionManager = $connectionManager;
        $this->originalConnection = Config::get('database.default');
    }

    /**
     * Switch to tenant database.
     */
    public function switchToTenant(Tenant $tenant): void
    {
        $this->connectionManager->switchToTenant($tenant);
        
        DebugHelper::info('Switched to tenant database', [
            'tenant_id' => $tenant->id,
            'connection' => $this->connectionManager->getTenantConnectionName($tenant),
        ]);
    }

    /**
     * Switch back to central database.
     */
    public function switchToCentral(): void
    {
        $this->connectionManager->switchToCentral();
        
        DebugHelper::info('Switched to central database', [
            'connection' => $this->originalConnection,
        ]);
    }

    /**
     * Get tenant connection name.
     */
    public function getTenantConnectionName(Tenant $tenant): string
    {
        return $this->connectionManager->getTenantConnectionName($tenant);
    }

    /**
     * Create tenant database.
     */
    public function createTenantDatabase(Tenant $tenant): bool
    {
        if (! config('filament-tenancy.database.auto_create_tenant_database', true)) {
            return false;
        }

        try {
            $config = $this->connectionManager->getTenantDatabaseConfig($tenant);
            $driver = $config['driver'] ?? env('DB_CONNECTION', 'mysql');
            $databaseName = $config['database'];

            // SQLite doesn't support CREATE DATABASE
            if ($driver === 'sqlite') {
                DebugHelper::warning('SQLite does not support multi-database tenancy. Skipping database creation.', [
                    'tenant_id' => $tenant->id,
                ]);
                return false;
            }
            
            // Create temporary connection to create database
            $tempConnectionName = 'temp_tenant_creation';
            $tempConfig = array_merge($config, [
                'database' => null, // Connect without specifying database
            ]);
            
            Config::set("database.connections.{$tempConnectionName}", $tempConfig);
            
            // Create database based on driver
            if ($driver === 'pgsql') {
                // PostgreSQL: Use CREATE DATABASE
                DB::connection($tempConnectionName)
                    ->statement("CREATE DATABASE \"{$databaseName}\"");
            } else {
                // MySQL: Use CREATE DATABASE with charset and collation
            DB::connection($tempConnectionName)
                ->statement("CREATE DATABASE IF NOT EXISTS `{$databaseName}` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci");
            }

            DebugHelper::info('Created tenant database', [
                'tenant_id' => $tenant->id,
                'database' => $databaseName,
                'driver' => $driver,
            ]);

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to create tenant database: {$e->getMessage()}", [
                'tenant_id' => $tenant->id,
            ]);
            return false;
        }
    }

    /**
     * Delete tenant database.
     */
    public function deleteTenantDatabase(Tenant $tenant): bool
    {
        if (! config('filament-tenancy.database.auto_delete_tenant_database', false)) {
            return false;
        }

        try {
            $config = $this->connectionManager->getTenantDatabaseConfig($tenant);
            $databaseName = $config['database'];
            
            // Close tenant connection first
            $this->connectionManager->closeTenantConnection($tenant);
            
            // Create temporary connection to delete database
            $tempConnectionName = 'temp_tenant_deletion';
            $tempConfig = array_merge($config, [
                'database' => null, // Connect without specifying database
            ]);
            
            Config::set("database.connections.{$tempConnectionName}", $tempConfig);
            
            // Delete database
            DB::connection($tempConnectionName)
                ->statement("DROP DATABASE IF EXISTS `{$databaseName}`");

            DebugHelper::info('Deleted tenant database', [
                'tenant_id' => $tenant->id,
                'database' => $databaseName,
            ]);

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to delete tenant database: {$e->getMessage()}", [
                'tenant_id' => $tenant->id,
            ]);
            return false;
        }
    }

    /**
     * Get the tenant connection template configuration.
     * Uses Laravel's default database configuration if template is null.
     */
    protected function getTenantConnectionTemplate(): array
    {
        $template = config('filament-tenancy.database.tenants_connection_template');

        // If null, use Laravel's default connection configuration
        if ($template === null) {
            return $this->buildTemplateFromDefaultConnection();
        }

        // Otherwise return configured template
        return is_array($template) ? $template : [];
    }

    /**
     * Build connection template from Laravel's default database configuration.
     */
    protected function buildTemplateFromDefaultConnection(): array
    {
        $driver = env('DB_CONNECTION', 'mysql');
        $defaultConnection = config("database.connections.{$driver}", []);
        
        // If default connection exists, use it as base
        if (!empty($defaultConnection)) {
            $template = $defaultConnection;
            // Remove database name as it will be set per tenant
            unset($template['database']);
            return $template;
        }

        // Otherwise build from env variables
        $template = [
            'driver' => $driver,
            'prefix' => '',
            'prefix_indexes' => true,
        ];

        // Configure based on database driver
        switch ($driver) {
            case 'sqlite':
                $template['database'] = env('DB_DATABASE', database_path('database.sqlite'));
                $template['foreign_key_constraints'] = env('DB_FOREIGN_KEYS', true);
                break;

            case 'pgsql':
                $template['host'] = env('DB_HOST', '127.0.0.1');
                $template['port'] = env('DB_PORT', '5432');
                $template['username'] = env('DB_USERNAME', 'forge');
                $template['password'] = env('DB_PASSWORD', '');
                $template['charset'] = env('DB_CHARSET', 'utf8');
                $template['search_path'] = 'public';
                $template['sslmode'] = 'prefer';
                break;

            case 'mysql':
            default:
                $template['host'] = env('DB_HOST', '127.0.0.1');
                $template['port'] = env('DB_PORT', '3306');
                $template['username'] = env('DB_USERNAME', 'forge');
                $template['password'] = env('DB_PASSWORD', '');
                $template['charset'] = env('DB_CHARSET', 'utf8mb4');
                $template['collation'] = env('DB_COLLATION', 'utf8mb4_unicode_ci');
                $template['strict'] = true;
                $template['engine'] = null;
                $template['options'] = extension_loaded('pdo_mysql') ? array_filter([
                    \PDO::MYSQL_ATTR_SSL_CA => env('MYSQL_ATTR_SSL_CA'),
                ]) : [];
                break;
        }

        return $template;
    }

    public function tenantDatabaseExists(Tenant $tenant): bool
    {
        try {
            $databaseName = $tenant->database_name;
            $template = $this->getTenantConnectionTemplate();
            $driver = $template['driver'] ?? env('DB_CONNECTION', 'mysql');
            
            // SQLite doesn't use schemas, check file existence instead
            if ($driver === 'sqlite') {
                $databasePath = $template['database'] ?? database_path('database.sqlite');
                return file_exists($databasePath);
            }
            
            $tempConnectionName = 'temp_tenant_check';
            $tempConfig = array_merge($template, [
                'database' => null,
            ]);
            
            Config::set("database.connections.{$tempConnectionName}", $tempConfig);
            
            // Use appropriate query based on driver
            if ($driver === 'pgsql') {
                $result = DB::connection($tempConnectionName)
                    ->select("SELECT datname FROM pg_database WHERE datname = ?", [$databaseName]);
            } else {
            $result = DB::connection($tempConnectionName)
                ->select("SELECT SCHEMA_NAME FROM INFORMATION_SCHEMA.SCHEMATA WHERE SCHEMA_NAME = ?", [$databaseName]);
            }

            return count($result) > 0;
        } catch (\Exception $e) {
            return false;
        }
    }

    /**
     * Ensure the sessions table exists in the tenant database.
     */
    protected function ensureSessionsTableExists(Tenant $tenant): void
    {
        try {
            $connectionName = $this->getTenantConnectionName($tenant);
            
            if (!Schema::connection($connectionName)->hasTable('sessions')) {
                Schema::connection($connectionName)->create('sessions', function (Blueprint $table) {
                    $table->string('id')->primary();
                    $table->foreignId('user_id')->nullable()->index();
                    $table->string('ip_address', 45)->nullable();
                    $table->text('user_agent')->nullable();
                    $table->text('payload');
                    $table->integer('last_activity')->index();
                });

                DebugHelper::info('Created sessions table for tenant', [
                    'tenant_id' => $tenant->id,
                    'connection' => $connectionName,
                ]);
            }
        } catch (\Exception $e) {
            DebugHelper::error("Failed to create sessions table for tenant {$tenant->id}: {$e->getMessage()}");
            // Don't throw exception, just log it as sessions table is not critical
        }
    }

    /**
     * Get current tenant connection name.
     */
    public function getCurrentTenantConnection(): ?string
    {
        $currentConnection = $this->databaseManager->getDefaultConnection();
        
        if (str_starts_with($currentConnection, 'tenant_')) {
            return $currentConnection;
        }

        return null;
    }

    /**
     * Check if currently using tenant connection.
     */
    public function isUsingTenantConnection(): bool
    {
        return $this->getCurrentTenantConnection() !== null;
    }

    /**
     * Get tenant from current connection.
     */
    public function getTenantFromCurrentConnection(): ?Tenant
    {
        $connectionName = $this->getCurrentTenantConnection();
        
        if (! $connectionName) {
            return null;
        }

        // Extract tenant ID from connection name (tenant_{id})
        $tenantId = str_replace('tenant_', '', $connectionName);
        
        if (! is_numeric($tenantId)) {
            return null;
        }

        // Switch to central connection to query tenant
        $originalConnection = $this->databaseManager->getDefaultConnection();
        $this->switchToCentral();
        
        $tenant = Tenant::find($tenantId);
        
        // Switch back to original connection
        $this->databaseManager->setDefaultConnection($originalConnection);
        
        return $tenant;
    }

    /**
     * Run tenant migrations from project's database/migrations/tenant/*.
     */
    public function runTenantMigrations(Tenant $tenant): bool
    {
        try {
            // Switch to tenant context
            $this->switchToTenant($tenant);
            
            // Ensure we're using the tenant connection
            $tenantConnection = $this->getTenantConnectionName($tenant);
            
            DebugHelper::info('Starting tenant migrations', [
                'tenant_id' => $tenant->id,
                'tenant_connection' => $tenantConnection,
                'current_default_connection' => config('database.default'),
            ]);

            // Get tenant migration path from project (not package)
            $tenantMigrationPath = database_path('migrations/tenant');
            
            if (!is_dir($tenantMigrationPath)) {
                DebugHelper::info('No tenant migrations directory found in project', [
                    'tenant_id' => $tenant->id,
                    'path' => $tenantMigrationPath,
                ]);
                return true; // Not an error, just no migrations to run
            }

            // Get migration files
            $migrationFiles = glob($tenantMigrationPath . '/*.php');
            
            if (empty($migrationFiles)) {
                DebugHelper::info('No tenant migration files found in project', [
                    'tenant_id' => $tenant->id,
                    'path' => $tenantMigrationPath,
                ]);
                return true;
            }

            DebugHelper::info('Running tenant migrations from project', [
                'tenant_id' => $tenant->id,
                'migration_count' => count($migrationFiles),
                'path' => $tenantMigrationPath,
            ]);

            // Create migrations table if it doesn't exist
            $this->ensureMigrationsTableExists();

            // Run each migration
            foreach ($migrationFiles as $migrationFile) {
                $this->runMigrationFile($migrationFile);
            }

            DebugHelper::info('Tenant migrations completed successfully', [
                'tenant_id' => $tenant->id,
                'migrations_run' => count($migrationFiles),
            ]);

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to run tenant migrations for tenant {$tenant->id}: {$e->getMessage()}", [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            // Always switch back to central connection
            $this->switchToCentral();
        }
    }

    /**
     * Ensure migrations table exists in tenant database.
     */
    protected function ensureMigrationsTableExists(): void
    {
        $connection = config('database.default');
        
        DebugHelper::info("Ensuring migrations table exists", [
            'connection' => $connection,
        ]);
        
        if (!Schema::connection($connection)->hasTable('migrations')) {
            Schema::connection($connection)->create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
            });
            
            DebugHelper::info("Created migrations table on tenant connection", [
                'connection' => $connection,
            ]);
        }
    }

    /**
     * Run a single migration file.
     */
    protected function runMigrationFile(string $migrationFile): void
    {
        $migrationName = basename($migrationFile, '.php');
        
        // Get current tenant connection (should be set by switchToTenant)
        $connection = config('database.default');
        
        DebugHelper::info("Running migration on connection", [
            'migration' => $migrationName,
            'connection' => $connection,
        ]);
        
        // Check if migration already ran (use specific connection)
        $ran = DB::connection($connection)
            ->table('migrations')
            ->where('migration', $migrationName)
            ->exists();

        if ($ran) {
            DebugHelper::debug("Migration already run: {$migrationName}");
            return;
        }

        // Include and run migration
        require_once $migrationFile;
        
        $migrationClass = $this->getMigrationClassFromFile($migrationFile);
        
        if (class_exists($migrationClass)) {
            // Store the current database connection
            $originalConnection = config('database.default');
            
            try {
                // Set the connection for the migration
                Config::set('database.default', $connection);
                
                // Create a new instance of the migration
                $migration = new $migrationClass();
                
                DebugHelper::info("Executing migration up() method", [
                    'migration_class' => $migrationClass,
                    'connection' => $connection,
                ]);
                
                // Run the migration
                $migration->up();
                
                // Record the migration in the migrations table
                $batch = DB::connection($connection)
                    ->table('migrations')
                    ->max('batch') + 1;
                
                DB::connection($connection)->table('migrations')->insert([
                    'migration' => $migrationName,
                    'batch' => $batch,
                ]);
                
                DebugHelper::info("Migration completed successfully", [
                    'migration' => $migrationName,
                    'batch' => $batch,
                ]);
                
            } catch (\Exception $e) {
                DebugHelper::error("Migration failed: " . $e->getMessage(), [
                    'migration' => $migrationName,
                    'error' => $e->getMessage(),
                    'trace' => $e->getTraceAsString(),
                ]);
                throw $e;
            } finally {
                // Always restore the original connection
                Config::set('database.default', $originalConnection);
            }

            // Record migration (use specific connection)
            DB::connection($connection)->table('migrations')->insert([
                'migration' => $migrationName,
                'batch' => DB::connection($connection)->table('migrations')->max('batch') + 1,
            ]);

            DebugHelper::debug("Migration completed: {$migrationName}");
        }
    }

    /**
     * Get migration class name from file.
     */
    protected function getMigrationClassFromFile(string $migrationFile): string
    {
        $content = file_get_contents($migrationFile);
        
        // Extract class name using regex
        if (preg_match('/class\s+(\w+)/', $content, $matches)) {
            return $matches[1];
        }

        throw new \Exception("Could not determine migration class from file: {$migrationFile}");
    }

    /**
     * Run tenant seeders from project's database/seeders/tenant/*.
     */
    public function runTenantSeeders(Tenant $tenant): bool
    {
        try {
            // Switch to tenant context
            $this->switchToTenant($tenant);

            // Get configured seeder classes from config
            $seederClasses = config('filament-tenancy.seeders.classes', []);
            
            if (empty($seederClasses)) {
                DebugHelper::info('No tenant seeder classes configured', [
                    'tenant_id' => $tenant->id,
                ]);
                return true; // Not an error, just no seeders to run
            }

            DebugHelper::info('Running tenant seeders', [
                'tenant_id' => $tenant->id,
                'seeder_count' => count($seederClasses),
                'seeders' => $seederClasses,
            ]);

            // Run each seeder class
            foreach ($seederClasses as $seederClass) {
                $this->runSeederClass($seederClass, $tenant);
            }

            DebugHelper::info('Tenant seeders completed successfully', [
                'tenant_id' => $tenant->id,
                'seeders_run' => count($seederClasses),
            ]);

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to run tenant seeders for tenant {$tenant->id}: {$e->getMessage()}", [
                'tenant_id' => $tenant->id,
                'error' => $e->getMessage(),
            ]);
            return false;
        } finally {
            // Always switch back to central connection
            $this->switchToCentral();
        }
    }

    /**
     * Run a specific seeder class for tenant.
     */
    protected function runSeederClass(string $seederClass, Tenant $tenant): void
    {
        try {
            if (!class_exists($seederClass)) {
                DebugHelper::warning("Seeder class not found: {$seederClass}", [
                    'tenant_id' => $tenant->id,
                    'seeder_class' => $seederClass,
                ]);
                return;
            }

            // Get current tenant connection
            $connection = config('database.default');
            
            // Temporarily set the default connection to tenant connection for seeder
            $originalConnection = config('database.default');
            Config::set('database.default', $connection);

            try {
                DebugHelper::info("Running seeder: {$seederClass}", [
                    'tenant_id' => $tenant->id,
                    'seeder_class' => $seederClass,
                    'connection' => $connection,
                ]);

                $seeder = new $seederClass();
                $seeder->run();

                DebugHelper::info("Seeder completed: {$seederClass}", [
                    'tenant_id' => $tenant->id,
                    'seeder_class' => $seederClass,
                ]);
            } finally {
                // Always restore original connection
                Config::set('database.default', $originalConnection);
            }
        } catch (\Exception $e) {
            DebugHelper::error("Failed to run seeder {$seederClass}: {$e->getMessage()}", [
                'tenant_id' => $tenant->id,
                'seeder_class' => $seederClass,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        }
    }
}