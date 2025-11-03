<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Schema;

/**
 * Sistema propio de migraciones para tenants.
 * Maneja las migraciones de forma independiente del sistema de Laravel,
 * asegurando que siempre se ejecuten en la conexión correcta del tenant.
 */
class TenantMigrationRunner
{
    protected string $connection;
    protected string $originalConnection;
    protected Tenant $tenant;

    public function __construct(Tenant $tenant, string $connection)
    {
        $this->tenant = $tenant;
        $this->connection = $connection;
        $this->originalConnection = Config::get('database.default');
    }

    /**
     * Ejecutar todas las migraciones pendientes del tenant.
     */
    public function run(string $migrationPath): bool
    {
        try {
            // Cambiar a conexión del tenant
            $this->switchToTenantConnection();

            // Crear tabla de migraciones si no existe
            $this->ensureMigrationsTableExists();

            // Obtener archivos de migración
            $migrationFiles = $this->getMigrationFiles($migrationPath);

            if (empty($migrationFiles)) {
                DebugHelper::info('No tenant migration files found', [
                    'tenant_id' => $this->tenant->id,
                    'path' => $migrationPath,
                ]);
                return true;
            }

            // Ordenar migraciones por nombre (timestamp)
            sort($migrationFiles);

            DebugHelper::info('Starting tenant migrations', [
                'tenant_id' => $this->tenant->id,
                'connection' => $this->connection,
                'migration_count' => count($migrationFiles),
            ]);

            $batch = $this->getNextBatch();
            $executed = 0;

            foreach ($migrationFiles as $migrationFile) {
                if ($this->runMigration($migrationFile, $batch)) {
                    $executed++;
                }
            }

            DebugHelper::info('Tenant migrations completed', [
                'tenant_id' => $this->tenant->id,
                'executed' => $executed,
                'total' => count($migrationFiles),
            ]);

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to run tenant migrations: {$e->getMessage()}", [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        } finally {
            $this->restoreOriginalConnection();
        }
    }

    /**
     * Ejecutar una migración específica.
     */
    protected function runMigration(string $migrationFile, int $batch): bool
    {
        $migrationName = basename($migrationFile, '.php');

        // Verificar si ya se ejecutó
        if ($this->isMigrationRun($migrationName)) {
            DebugHelper::debug("Migration already run: {$migrationName}");
            return false;
        }

        DebugHelper::info("Running migration: {$migrationName}", [
            'tenant_id' => $this->tenant->id,
            'connection' => $this->connection,
        ]);

        // Cargar el archivo de migración y obtener la instancia
        // Las migraciones modernas de Laravel retornan instancias de clases anónimas
        $migration = require $migrationFile;

        // Si el archivo retorna null o no es un objeto, intentar método alternativo
        if (!is_object($migration)) {
            // Intentar con require_once y buscar clase con nombre
            require_once $migrationFile;
            $migrationClass = $this->getMigrationClassFromFile($migrationFile);
            
            if ($migrationClass && class_exists($migrationClass)) {
                $migration = new $migrationClass();
            } else {
                throw new \Exception("Could not load migration from file: {$migrationFile}. Make sure it returns a Migration instance or defines a Migration class.");
            }
        }

        // Ejecutar la migración con conexión forzada
        $this->executeMigrationWithConnection($migration);

        // Registrar migración ejecutada
        $this->recordMigration($migrationName, $batch);

        DebugHelper::info("Migration completed: {$migrationName}", [
            'tenant_id' => $this->tenant->id,
            'batch' => $batch,
        ]);

        return true;
    }

    /**
     * Ejecutar migración forzando el uso de la conexión del tenant.
     */
    protected function executeMigrationWithConnection($migration): void
    {
        // Guardar estado original
        $originalDefault = Config::get('database.default');
        $originalSchemaConnection = null;

        try {
            // Forzar conexión por defecto
            Config::set('database.default', $this->connection);
            DB::setDefaultConnection($this->connection);

            // Limpiar conexiones cacheadas
            DB::purge($this->connection);

            // Verificar conexión activa
            $activeConnection = DB::getDefaultConnection();
            if ($activeConnection !== $this->connection) {
                throw new \Exception(
                    "Failed to set default connection. Expected: {$this->connection}, Got: {$activeConnection}"
                );
            }

            // Ejecutar up() - ahora Schema:: y DB:: usarán la conexión del tenant
            $migration->up();

        } finally {
            // Restaurar conexión original
            Config::set('database.default', $originalDefault);
            DB::setDefaultConnection($originalDefault);
        }
    }

    /**
     * Obtener archivos de migración.
     */
    protected function getMigrationFiles(string $path): array
    {
        if (!is_dir($path)) {
            return [];
        }

        $files = glob($path . '/*.php');
        return $files ?: [];
    }

    /**
     * Obtener nombre de clase desde archivo.
     * Maneja tanto clases con nombre como clases anónimas.
     */
    protected function getMigrationClassFromFile(string $migrationFile): ?string
    {
        $content = file_get_contents($migrationFile);

        // Buscar clase con nombre primero: class NombreClase extends
        if (preg_match('/class\s+(\w+)\s+extends/', $content, $matches)) {
            return $matches[1];
        }

        // Si no encuentra clase con nombre, es una clase anónima
        // Las clases anónimas se manejan ejecutando el archivo directamente
        return null;
    }

    /**
     * Verificar si una migración ya se ejecutó.
     */
    protected function isMigrationRun(string $migrationName): bool
    {
        return DB::connection($this->connection)
            ->table('migrations')
            ->where('migration', $migrationName)
            ->exists();
    }

    /**
     * Registrar migración ejecutada.
     */
    protected function recordMigration(string $migrationName, int $batch): void
    {
        DB::connection($this->connection)->table('migrations')->insert([
            'migration' => $migrationName,
            'batch' => $batch,
        ]);
    }

    /**
     * Obtener siguiente número de batch.
     */
    protected function getNextBatch(): int
    {
        $maxBatch = DB::connection($this->connection)
            ->table('migrations')
            ->max('batch') ?? 0;

        return $maxBatch + 1;
    }

    /**
     * Asegurar que existe la tabla de migraciones.
     */
    protected function ensureMigrationsTableExists(): void
    {
        if (!Schema::connection($this->connection)->hasTable('migrations')) {
            Schema::connection($this->connection)->create('migrations', function (Blueprint $table) {
                $table->id();
                $table->string('migration');
                $table->integer('batch');
                $table->timestamps();
            });

            DebugHelper::info("Created migrations table", [
                'tenant_id' => $this->tenant->id,
                'connection' => $this->connection,
            ]);
        }
    }

    /**
     * Cambiar a conexión del tenant.
     */
    protected function switchToTenantConnection(): void
    {
        Config::set('database.default', $this->connection);
        DB::setDefaultConnection($this->connection);
    }

    /**
     * Restaurar conexión original.
     */
    protected function restoreOriginalConnection(): void
    {
        Config::set('database.default', $this->originalConnection);
        DB::setDefaultConnection($this->originalConnection);
    }

    /**
     * Rollback última migración o batch específico.
     */
    public function rollback(int $steps = 1): bool
    {
        try {
            $this->switchToTenantConnection();

            $migrations = DB::connection($this->connection)
                ->table('migrations')
                ->orderBy('batch', 'desc')
                ->orderBy('id', 'desc')
                ->limit($steps)
                ->get();

            if ($migrations->isEmpty()) {
                DebugHelper::info('No migrations to rollback', [
                    'tenant_id' => $this->tenant->id,
                ]);
                return true;
            }

            foreach ($migrations as $migrationRecord) {
                $this->rollbackMigration($migrationRecord->migration);
            }

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to rollback migrations: {$e->getMessage()}", [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
            ]);
            throw $e;
        } finally {
            $this->restoreOriginalConnection();
        }
    }

    /**
     * Rollback una migración específica.
     */
    protected function rollbackMigration(string $migrationName): void
    {
        $migrationPath = database_path("migrations/tenant/{$migrationName}.php");

        if (!file_exists($migrationPath)) {
            DebugHelper::warning("Migration file not found for rollback: {$migrationName}");
            return;
        }

        // Cargar el archivo de migración y obtener la instancia
        $migration = require $migrationPath;

        // Si el archivo retorna null o no es un objeto, intentar método alternativo
        if (!is_object($migration)) {
            require_once $migrationPath;
            $migrationClass = $this->getMigrationClassFromFile($migrationPath);
            
            if ($migrationClass && class_exists($migrationClass)) {
                $migration = new $migrationClass();
            } else {
                throw new \Exception("Could not load migration from file: {$migrationPath} for rollback.");
            }
        }

        // Ejecutar down() con conexión forzada
        $this->executeRollbackWithConnection($migration);

        // Eliminar registro de migración
        DB::connection($this->connection)
            ->table('migrations')
            ->where('migration', $migrationName)
            ->delete();

        DebugHelper::info("Migration rolled back: {$migrationName}", [
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Ejecutar rollback forzando conexión.
     */
    protected function executeRollbackWithConnection($migration): void
    {
        $originalDefault = Config::get('database.default');

        try {
            Config::set('database.default', $this->connection);
            DB::setDefaultConnection($this->connection);
            DB::purge($this->connection);

            $migration->down();
        } finally {
            Config::set('database.default', $originalDefault);
            DB::setDefaultConnection($originalDefault);
        }
    }
}

