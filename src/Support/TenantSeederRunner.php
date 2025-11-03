<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Console\Command;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;

/**
 * Sistema propio de seeders para tenants.
 * Maneja los seeders de forma independiente del sistema de Laravel,
 * asegurando que siempre se ejecuten en la conexión correcta del tenant.
 */
class TenantSeederRunner
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
     * Ejecutar seeders configurados para el tenant.
     */
    public function run(array $seederClasses = []): bool
    {
        try {
            // Si no se proporcionan seeders, obtener de configuración
            if (empty($seederClasses)) {
                $seederClasses = config('filament-tenancy.seeders.classes', []);
            }

            if (empty($seederClasses)) {
                DebugHelper::info('No tenant seeder classes configured', [
                    'tenant_id' => $this->tenant->id,
                ]);
                return true;
            }

            DebugHelper::info('Starting tenant seeders', [
                'tenant_id' => $this->tenant->id,
                'connection' => $this->connection,
                'seeder_count' => count($seederClasses),
            ]);

            // No cambiar conexión aquí porque DatabaseManager ya lo hizo
            // Solo asegurarnos de que estamos usando la conexión correcta
            foreach ($seederClasses as $seederClass) {
                $this->runSeeder($seederClass);
            }

            DebugHelper::info('Tenant seeders completed', [
                'tenant_id' => $this->tenant->id,
                'executed' => count($seederClasses),
            ]);

            return true;
        } catch (\Exception $e) {
            DebugHelper::error("Failed to run tenant seeders: {$e->getMessage()}", [
                'tenant_id' => $this->tenant->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            throw $e;
        }
        // No restaurar conexión aquí - DatabaseManager se encarga de eso
    }

    /**
     * Ejecutar un seeder específico.
     */
    protected function runSeeder(string $seederClass): void
    {
        if (!class_exists($seederClass)) {
            DebugHelper::warning("Seeder class not found: {$seederClass}", [
                'tenant_id' => $this->tenant->id,
            ]);
            return;
        }

        DebugHelper::info("Running seeder: {$seederClass}", [
            'tenant_id' => $this->tenant->id,
            'connection' => $this->connection,
        ]);

        // Ejecutar seeder con conexión forzada
        $this->executeSeederWithConnection($seederClass);

        DebugHelper::info("Seeder completed: {$seederClass}", [
            'tenant_id' => $this->tenant->id,
        ]);
    }

    /**
     * Ejecutar seeder forzando el uso de la conexión del tenant.
     */
    protected function executeSeederWithConnection(string $seederClass): void
    {
        $originalDefault = Config::get('database.default');

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

            // Crear instancia del seeder
            $seeder = new $seederClass();
            
            // Si el seeder extiende de Seeder, inyectar un comando mock para evitar errores
            if ($seeder instanceof Seeder) {
                $mockCommand = $this->createMockCommand();
                $seeder->setCommand($mockCommand);
            }
            
            // Ejecutar el seeder
            $seeder->run();

        } finally {
            // Restaurar conexión original solo después de que el seeder termine completamente
            Config::set('database.default', $originalDefault);
            DB::setDefaultConnection($originalDefault);
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
     * Crear un comando mock para los seeders que necesitan $this->command.
     */
    protected function createMockCommand(): Command
    {
        return new class extends Command
        {
            protected $signature = 'tenant-seeder-mock';
            protected $description = 'Mock command for tenant seeders';

            public function handle(): int
            {
                return 0;
            }

            // Métodos comunes que los seeders pueden usar
            public function info($string, $verbosity = null)
            {
                // Usar DebugHelper en lugar de output
                DebugHelper::info("Seeder: {$string}");
            }

            public function warn($string, $verbosity = null)
            {
                DebugHelper::warning("Seeder: {$string}");
            }

            public function error($string, $verbosity = null)
            {
                DebugHelper::error("Seeder: {$string}");
            }

            public function line($string, $style = null, $verbosity = null)
            {
                DebugHelper::info("Seeder: {$string}");
            }
        };
    }
}

