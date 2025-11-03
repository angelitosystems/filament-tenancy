<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Support\AssetManager;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\File;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Str;

class InstallCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-tenancy:install';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Instala y configura el paquete Filament Tenancy';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayBranding();

        $this->info('Instalando Filament Tenancy...');
        $this->newLine();

        // Verificar e instalar Filament si es necesario
        $this->checkAndInstallFilament();

        // Verificar y configurar base de datos
        $this->checkAndConfigureDatabase();

        // Configurar variables de entorno para sesiones
        $this->configureSessionEnvironment();

        // Publicar configuración
        $this->publishConfiguration();

        // Ejecutar migraciones
        $this->runMigrations();

        // Registrar ServiceProvider
        $this->registerServiceProvider();

        // Verificar y configurar paneles de Filament
        $this->checkAndConfigureFilamentPanels();

        // Registrar middlewares en bootstrap/app.php (Laravel 11)
        $this->registerMiddlewares();

        // Publicar componentes y vistas de 404
        $this->publish404Components();

        // Preguntar si desea crear usuario admin
        $this->askToCreateAdminUser();

        // Mensaje final
        $this->displaySuccessMessage();

        return self::SUCCESS;
    }

    /**
     * Muestra el branding inicial del paquete.
     */
    protected function displayBranding(): void
    {
        $this->newLine();
        $this->line('╔═══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                               ║');
        $this->line('║           <fg=cyan>Filament Tenancy</fg=cyan> - Multi-Tenancy Package        ║');
        $this->line('║                  <fg=yellow>Angelito Systems</fg=yellow>                      ║');
        $this->line('║                                                               ║');
        $this->line('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Verifica e instala Filament si es necesario.
     */
    protected function checkAndInstallFilament(): void
    {
        $this->info('🔍 Verificando instalación de Filament...');

        // Verificar si Filament está instalado
        if (class_exists(\Filament\Facades\Filament::class)) {
            $this->line('  ✓ Filament ya está instalado');
            $this->newLine();
            return;
        }

        // Verificar si el paquete está en composer.json
        $composerPath = base_path('composer.json');
        if (File::exists($composerPath)) {
            $composerContent = json_decode(File::get($composerPath), true);
            $require = $composerContent['require'] ?? [];
            $requireDev = $composerContent['require-dev'] ?? [];
            $allRequire = array_merge($require, $requireDev);

            if (isset($allRequire['filament/filament'])) {
                $this->line('  ℹ Filament está en composer.json pero no está cargado correctamente');
                $this->warn('  ⚠ Ejecuta <fg=yellow>composer install</fg=yellow> y <fg=yellow>php artisan filament:install --panels</fg=yellow>');
                $this->newLine();
                return;
            }
        }

        $this->warn('  ⚠ Filament no está instalado.');
        $this->newLine();
        $this->line('  Filament Tenancy requiere Filament para funcionar.');
        $this->newLine();

        if (!$this->confirm('¿Deseas instalar Filament ahora?', true)) {
            $this->warn('  ⚠ Deberás instalar Filament manualmente más tarde.');
            $this->line('  Ejecuta: <fg=yellow>composer require filament/filament:"^4.0"</fg=yellow>');
            $this->line('  Luego: <fg=yellow>php artisan filament:install --panels</fg=yellow>');
            $this->newLine();
            return;
        }

        $this->installFilament();
    }

    /**
     * Instala Filament.
     */
    protected function installFilament(): void
    {
        $this->newLine();
        $this->info('📦 Instalando Filament...');

        // Instalar paquete via composer
        $this->line('  Ejecutando composer require filament/filament:"^4.0"...');
        
        $composerCommand = 'composer require filament/filament:"^4.0"';
        $output = [];
        $returnVar = 0;
        
        exec($composerCommand . ' 2>&1', $output, $returnVar);

        if ($returnVar !== 0) {
            $this->error('  ✗ Error al instalar Filament via Composer');
            $this->warn('  ⚠ Ejecuta manualmente: <fg=yellow>composer require filament/filament:"^4.0"</fg=yellow>');
            $this->newLine();
            if (!empty($output)) {
                $this->line('  Salida del comando:');
                foreach ($output as $line) {
                    $this->line('    ' . $line);
                }
            }
            $this->newLine();
            return;
        }

        $this->line('  ✓ Filament instalado via Composer');

        // Instalar Filament panels
        $this->newLine();
        $this->line('  Ejecutando filament:install --panels...');
        
        try {
            $this->call('filament:install', [
                '--panels' => true,
            ]);
            $this->line('  ✓ Filament configurado');
            
            // Configurar automáticamente el panel admin creado
            $this->configureAdminPanel();
            
            // Crear y configurar el panel tenant
            $this->createTenantPanel();
        } catch (\Exception $e) {
            $this->warn('  ⚠ No se pudo ejecutar filament:install automáticamente');
            $this->line('  Ejecuta manualmente: <fg=yellow>php artisan filament:install --panels</fg=yellow>');
        }

        $this->newLine();
    }

    /**
     * Verifica y configura la base de datos.
     */
    protected function checkAndConfigureDatabase(): void
    {
        $this->info('🔍 Verificando configuración de base de datos...');

        $currentConnection = env('DB_CONNECTION', 'mysql');
        $compatibleConnections = ['mysql', 'pgsql'];

        if (!in_array($currentConnection, $compatibleConnections)) {
            $this->warn("  ⚠ La conexión actual (<fg=yellow>{$currentConnection}</fg=yellow>) no es compatible con multi-tenancy multi-database.");
            $this->newLine();
            $this->line('  Para multi-tenancy con múltiples bases de datos, necesitas MySQL o PostgreSQL.');
            $this->line('  SQLite solo permite una base de datos por archivo.');
            $this->newLine();

            if (!$this->confirm('¿Deseas configurar una conexión compatible ahora?', true)) {
                $this->warn('  ⚠ Deberás configurar manualmente una conexión compatible más tarde.');
                $this->newLine();
                return;
            }

            $this->configureDatabaseConnection();
        } else {
            $this->line("  ✓ Conexión compatible detectada: <fg=green>{$currentConnection}</fg=green>");
            $this->newLine();
        }
    }

    /**
     * Configura la conexión de base de datos.
     */
    protected function configureDatabaseConnection(): void
    {
        $this->newLine();
        $this->info('📊 Configurando conexión de base de datos...');

        $driver = $this->choice(
            '¿Qué tipo de base de datos quieres usar?',
            ['mysql' => 'MySQL', 'pgsql' => 'PostgreSQL'],
            'mysql'
        );

        $this->newLine();
        $this->line('Ingresa las credenciales de tu base de datos:');
        $this->newLine();

        $host = $this->ask('Host', '127.0.0.1');
        $port = $this->ask('Puerto', $driver === 'pgsql' ? '5432' : '3306');
        $database = $this->ask('Nombre de la base de datos', 'laravel');
        $username = $this->ask('Usuario', 'root');
        $password = $this->secret('Contraseña') ?? '';

        $this->newLine();
        $this->info('🔄 Actualizando archivo .env...');

        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            $this->error('  ✗ No se encontró el archivo .env');
            return;
        }

        $envContent = File::get($envPath);

        // Actualizar valores
        $envContent = preg_replace('/^DB_CONNECTION=.*/m', "DB_CONNECTION={$driver}", $envContent);
        $envContent = preg_replace('/^DB_HOST=.*/m', "DB_HOST={$host}", $envContent);
        $envContent = preg_replace('/^DB_PORT=.*/m', "DB_PORT={$port}", $envContent);
        $envContent = preg_replace('/^DB_DATABASE=.*/m', "DB_DATABASE={$database}", $envContent);
        $envContent = preg_replace('/^DB_USERNAME=.*/m', "DB_USERNAME={$username}", $envContent);
        $envContent = preg_replace('/^DB_PASSWORD=.*/m', "DB_PASSWORD={$password}", $envContent);

        // Si no existen, agregarlos
        if (!preg_match('/^DB_CONNECTION=/m', $envContent)) {
            $envContent .= "\nDB_CONNECTION={$driver}\n";
        }
        if (!preg_match('/^DB_HOST=/m', $envContent)) {
            $envContent .= "DB_HOST={$host}\n";
        }
        if (!preg_match('/^DB_PORT=/m', $envContent)) {
            $envContent .= "DB_PORT={$port}\n";
        }
        if (!preg_match('/^DB_DATABASE=/m', $envContent)) {
            $envContent .= "DB_DATABASE={$database}\n";
        }
        if (!preg_match('/^DB_USERNAME=/m', $envContent)) {
            $envContent .= "DB_USERNAME={$username}\n";
        }
        if (!preg_match('/^DB_PASSWORD=/m', $envContent)) {
            $envContent .= "DB_PASSWORD={$password}\n";
        }

        File::put($envPath, $envContent);

        // Limpiar caché de configuración
        $this->call('config:clear');

        $this->line('  ✓ Archivo .env actualizado');
        $this->line("  ✓ Conexión configurada: <fg=green>{$driver}</fg=green>");
        $this->newLine();

        // Verificar conexión
        if ($this->confirm('¿Deseas probar la conexión ahora?', true)) {
            $this->testDatabaseConnection($driver, $host, $port, $database, $username, $password);
        }
    }

    /**
     * Prueba la conexión a la base de datos.
     */
    protected function testDatabaseConnection(string $driver, string $host, string $port, string $database, string $username, ?string $password = null): void
    {
        $this->info('🔌 Probando conexión...');

        try {
            $config = [
                'driver' => $driver,
                'host' => $host,
                'port' => $port,
                'database' => $database,
                'username' => $username,
                'password' => $password ?? '',
            ];

            if ($driver === 'mysql') {
                $config['charset'] = 'utf8mb4';
                $config['collation'] = 'utf8mb4_unicode_ci';
            } elseif ($driver === 'pgsql') {
                $config['charset'] = 'utf8';
            }

            Config::set("database.connections.test_connection", $config);

            DB::connection('test_connection')->getPdo();

            $this->line('  ✓ Conexión exitosa');
        } catch (\Exception $e) {
            $this->error('  ✗ Error de conexión: ' . $e->getMessage());
            $this->warn('  ⚠ Verifica las credenciales y asegúrate de que la base de datos exista.');
        }

        $this->newLine();
    }

    /**
     * Configura las variables de entorno para sesiones de tenants.
     */
    protected function configureSessionEnvironment(): void
    {
        $this->info('🔧 Configurando variables de entorno para sesiones...');

        $envPath = base_path('.env');
        if (!File::exists($envPath)) {
            $this->error('  ✗ No se encontró el archivo .env');
            return;
        }

        $envContent = File::get($envPath);
        $sessionVariables = [
            'TENANCY_SESSION_ISOLATION' => 'true',
            'TENANCY_AUTO_CREATE_SESSION_TABLE' => 'true',
            'TENANCY_SESSION_COOKIE_SAME_SITE' => 'lax',
        ];

        $updatedVariables = [];
        foreach ($sessionVariables as $key => $defaultValue) {
            // Verificar si la variable ya existe
            if (preg_match("/^{$key}=/m", $envContent)) {
                $currentValue = $this->getCurrentEnvValue($envContent, $key);
                $this->line("  ℹ Variable <fg=yellow>{$key}</fg=yellow> ya existe: <fg=cyan>{$currentValue}</fg=cyan>");
                
                if ($this->confirm("¿Deseas actualizar {$key} al valor recomendado ({$defaultValue})?", false)) {
                    $envContent = preg_replace("/^{$key}=.*/m", "{$key}={$defaultValue}", $envContent);
                    $updatedVariables[] = $key;
                    $this->line("    ✓ Actualizado a: <fg=green>{$defaultValue}</fg=green>");
                }
            } else {
                // Preguntar si agregar la variable
                if ($this->confirm("¿Deseas agregar la variable <fg=yellow>{$key}</fg=yellow> con valor <fg=green>{$defaultValue}</fg=green>? Esto previene errores 419 en tenants.", true)) {
                    $envContent .= "\n{$key}={$defaultValue}\n";
                    $updatedVariables[] = $key;
                    $this->line("  ✓ Agregado: <fg=green>{$key}={$defaultValue}</fg=green>");
                }
            }
        }

        // Guardar cambios si hay actualizaciones
        if (!empty($updatedVariables)) {
            File::put($envPath, $envContent);
            $this->call('config:clear');
            $this->line('  ✓ Variables de entorno actualizadas');
            $this->newLine();
            
            // Explicación de las variables
            $this->info('📚 Explicación de las variables configuradas:');
            $this->line('  • <fg=yellow>TENANCY_SESSION_ISOLATION=true</fg=yellow>: Aísla sesiones entre tenants para prevenir conflictos');
            $this->line('  • <fg=yellow>TENANCY_AUTO_CREATE_SESSION_TABLE=true</fg=yellow>: Crea automáticamente la tabla de sesiones en bases de datos de tenants');
            $this->line('  • <fg=yellow>TENANCY_SESSION_COOKIE_SAME_SITE=lax</fg=yellow>: Configura cookies SameSite para prevenir errores CSRF');
            $this->newLine();
        } else {
            $this->line('  ℹ No se realizaron cambios en las variables de sesión');
            $this->newLine();
        }
    }

    /**
     * Obtiene el valor actual de una variable de entorno.
     */
    protected function getCurrentEnvValue(string $envContent, string $key): string
    {
        if (preg_match("/^{$key}=(.*)$/m", $envContent, $matches)) {
            return trim($matches[1]);
        }
        return '';
    }

    /**
     * Publica el archivo de configuración.
     */
    protected function publishConfiguration(): void
    {
        $this->info('📝 Publicando archivo de configuración...');

        try {
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-config',
            ]);

            $publishedConfig = config_path('filament-tenancy.php');
            if (File::exists($publishedConfig)) {
                $this->line('  ✓ Archivo de configuración publicado como <fg=green>config/filament-tenancy.php</fg=green>');
            }
        } catch (\Exception $e) {
            $this->error('  ✗ Error al publicar la configuración: ' . $e->getMessage());
        }

        $this->newLine();

        // Publicar rutas del paquete
        $this->publishRoutes();

        // Publicar migraciones del tenant
        $this->publishTenantMigrations();
    }

    /**
     * Publica las migraciones del tenant.
     */
    protected function publishTenantMigrations(): void
    {
        $this->info('📦 Publicando migraciones del tenant...');

        try {
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-tenant-migrations',
            ]);

            $this->line('  ✓ Migraciones del tenant publicadas en <fg=green>database/migrations/tenant/</fg=green>');
            $this->line('  ℹ Estas migraciones se ejecutarán automáticamente cuando crees un tenant');
        } catch (\Exception $e) {
            $this->error('  ✗ Error al publicar las migraciones del tenant: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Publica las rutas del paquete.
     */
    protected function publishRoutes(): void
    {
        $this->info('🛣️ Publicando rutas del paquete...');

        try {
            // Publicar rutas usando vendor:publish
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-routes',
            ]);

            $this->line('  ✓ Rutas publicadas en <fg=green>routes/tenant.php</fg=green>');
            $this->line('  ℹ Estas rutas incluyen el cambio de idioma y otras funcionalidades del paquete');
        } catch (\Exception $e) {
            $this->error('  ✗ Error al publicar las rutas: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Ejecuta las migraciones del paquete si el usuario confirma.
     */
    protected function runMigrations(): void
    {
        if (!$this->confirm('¿Deseas ejecutar las migraciones del paquete ahora?', true)) {
            $this->warn('  ⚠ Migraciones omitidas. Ejecuta <fg=yellow>php artisan migrate</fg=yellow> más tarde.');
            $this->newLine();
            return;
        }

        $this->info('🔄 Ejecutando migraciones...');

        $maxAttempts = 3;
        $attempt = 0;
        $success = false;

        while ($attempt < $maxAttempts && !$success) {
            $attempt++;
            
            try {
                $this->call('migrate');
                $this->line('  ✓ Migraciones ejecutadas correctamente');
                
                // Ejecutar seeder de planes después de las migraciones
                $this->runPlanSeeder();
                
                $success = true;
            } catch (\Exception $e) {
                $errorMessage = $e->getMessage();
                $isConnectionError = str_contains($errorMessage, 'No se puede establecer una conexión') 
                    || str_contains($errorMessage, 'Connection refused')
                    || str_contains($errorMessage, 'denegó expresamente')
                    || str_contains($errorMessage, 'HY000')
                    || str_contains($errorMessage, 'SQLSTATE[HY000]');

                if ($isConnectionError && $attempt < $maxAttempts) {
                    $this->error("  ✗ Error de conexión (intento {$attempt}/{$maxAttempts}): " . $errorMessage);
                    $this->newLine();
                    
                    if ($this->confirm("  ¿Deseas reintentar la conexión?", true)) {
                        $this->line('  🔄 Reintentando...');
                        $this->newLine();
                        continue;
                    } else {
                        break;
                    }
                } else {
                    $this->error('  ✗ Error al ejecutar las migraciones: ' . $errorMessage);
                    
                    if ($isConnectionError && $attempt >= $maxAttempts) {
                        $this->newLine();
                        $this->warn('  ⚠ Se agotaron los intentos de conexión.');
                        
                        if ($this->confirm('  ¿Deseas limpiar la instalación y configurar la base de datos nuevamente?', false)) {
                            $this->cleanupInstallation();
                            return;
                        }
                    }
                    
                    $this->warn('  ⚠ Puedes ejecutar las migraciones manualmente con: <fg=yellow>php artisan migrate</fg=yellow>');
                    break;
                }
            }
        }

        $this->newLine();
    }

    /**
     * Ejecuta el seeder de planes.
     */
    protected function runPlanSeeder(): void
    {
        try {
            // Publicar los seeders primero si no existen
            $this->publishSeeders();
            
            // Intentar ejecutar desde Database\Seeders (si fue publicado)
            $seederClass = 'Database\\Seeders\\PlanSeeder';
            
            if (class_exists($seederClass)) {
                $this->call('db:seed', [
                    '--class' => $seederClass,
                    '--force' => true,
                ]);
                $this->line('  ✓ Planes creados exitosamente');
            } else {
                // Si no existe, usar el seeder del paquete directamente
                $packageSeeder = \AngelitoSystems\FilamentTenancy\Database\Seeders\PlanSeeder::class;
                if (class_exists($packageSeeder)) {
                    $seeder = new $packageSeeder();
                    $seeder->setCommand($this);
                    $seeder->run();
                    $this->line('  ✓ Planes creados exitosamente');
                } else {
                    $this->warn('  ⚠ No se encontró el seeder de planes');
                }
            }
        } catch (\Exception $e) {
            $this->warn('  ⚠ No se pudieron crear los planes: ' . $e->getMessage());
            $this->line('  Puedes ejecutar el seeder manualmente con: <fg=yellow>php artisan db:seed --class=Database\\Seeders\\PlanSeeder</fg=yellow>');
        }
    }

    /**
     * Publica todos los seeders con el namespace correcto.
     */
    protected function publishSeeders(): void
    {
        $this->info('🌱 Publicando seeders...');
        
        try {
            // Publicar seeders centrales usando vendor:publish
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-seeders',
            ]);
            
            // Publicar seeders de tenants usando vendor:publish
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-tenant-seeders',
            ]);
            
            $this->line('  ✓ Seeders centrales publicados en <fg=green>database/seeders/</fg=green>');
            $this->line('  ✓ Seeders de tenants publicados en <fg=green>database/seeders/tenant/</fg=green>');
            $this->line('  ℹ Los seeders de tenants se ejecutarán automáticamente cuando crees un tenant');
        } catch (\Exception $e) {
            $this->error('  ✗ Error al publicar los seeders: ' . $e->getMessage());
        }
    }

    /**
     * Limpia la instalación en caso de error.
     */
    protected function cleanupInstallation(): void
    {
        $this->newLine();
        $this->info('🧹 Limpiando instalación...');

        try {
            // Eliminar archivos de configuración publicados
            $configFiles = [
                config_path('filament-tenancy.php'),
            ];

            foreach ($configFiles as $configFile) {
                if (File::exists($configFile)) {
                    File::delete($configFile);
                    $this->line("  ✓ Eliminado: {$configFile}");
                }
            }

            // Eliminar rutas publicadas
            $routesFile = base_path('routes/tenant.php');
            if (File::exists($routesFile)) {
                File::delete($routesFile);
                $this->line("  ✓ Eliminado: {$routesFile}");
            }

            // Remover ServiceProvider si fue agregado automáticamente
            $this->removeServiceProvider();

            $this->line('  ✓ Instalación limpiada');
            $this->newLine();
            $this->warn('  ⚠ La instalación fue limpiada debido a errores de conexión.');
            $this->line('  Ejecuta <fg=yellow>php artisan filament-tenancy:install</fg=yellow> nuevamente después de configurar la base de datos correctamente.');
            $this->newLine();
        } catch (\Exception $e) {
            $this->error('  ✗ Error al limpiar la instalación: ' . $e->getMessage());
            $this->newLine();
        }
    }

    /**
     * Remueve el ServiceProvider del registro.
     */
    protected function removeServiceProvider(): void
    {
        $providerClass = 'AngelitoSystems\\FilamentTenancy\\TenancyServiceProvider';
        $providersPath = base_path('bootstrap/providers.php');
        $appConfigPath = base_path('config/app.php');

        // Laravel 11: bootstrap/providers.php
        if (File::exists($providersPath)) {
            $content = File::get($providersPath);
            $originalContent = $content;

            // Remover el provider
            $content = preg_replace(
                '/\s*' . preg_quote($providerClass, '/') . '::class,?\s*/',
                '',
                $content
            );

            if ($content !== $originalContent) {
                File::put($providersPath, $content);
                $this->line('  ✓ ServiceProvider removido de bootstrap/providers.php');
            }
        }

        // Laravel 10: config/app.php
        if (File::exists($appConfigPath)) {
            $content = File::get($appConfigPath);
            $originalContent = $content;

            // Remover del array de providers
            $content = preg_replace(
                '/\s*' . preg_quote($providerClass . '::class', '/') . ',?\s*/',
                '',
                $content
            );

            if ($content !== $originalContent) {
                File::put($appConfigPath, $content);
                $this->line('  ✓ ServiceProvider removido de config/app.php');
            }
        }
    }

    /**
     * Registra el TenancyServiceProvider en config/app.php si no está agregado.
     */
    protected function registerServiceProvider(): void
    {
        $this->info('🔧 Verificando registro del ServiceProvider...');

        $providerClass = 'AngelitoSystems\\FilamentTenancy\\TenancyServiceProvider';
        $providerString = $providerClass . '::class';

        // Detectar versión de Laravel
        $laravelVersion = (int) app()->version();
        $providersPath = base_path('bootstrap/providers.php');
        $appConfigPath = base_path('config/app.php');

        // Laravel 11: Priorizar bootstrap/providers.php
        if ($laravelVersion >= 11 || File::exists($providersPath)) {
            if (File::exists($providersPath)) {
                $this->registerInProvidersFile($providersPath, $providerClass);
            } else {
                // Crear bootstrap/providers.php si no existe en Laravel 11
                $this->createProvidersFile($providersPath, $providerClass);
            }
            return;
        }

        // Laravel 10 y anteriores: usar config/app.php
        if (File::exists($appConfigPath)) {
            $this->registerInAppConfig($appConfigPath, $providerString);
            return;
        }

        // Si no se encuentra ningún archivo, verificar si está auto-descubierto
        $this->line('  ℹ El ServiceProvider se registrará automáticamente mediante auto-discovery de Composer.');
        $this->newLine();
    }

    /**
     * Registra el provider en config/app.php.
     */
    protected function registerInAppConfig(string $configPath, string $providerString): void
    {
        $content = File::get($configPath);

        // Verificar si ya está registrado (buscar tanto con ::class como sin él)
        $providerClass = 'AngelitoSystems\\FilamentTenancy\\TenancyServiceProvider';
        if (Str::contains($content, $providerString) || Str::contains($content, $providerClass)) {
            $this->line('  ✓ TenancyServiceProvider ya está registrado en <fg=green>config/app.php</fg=green>');
            $this->newLine();
            return;
        }

        // Buscar el array de providers usando múltiples patrones
        $patterns = [
            "/'providers'\s*=>\s*\[(.*?)\]/s",  // Patrón estándar
            "/\"providers\"\s*=>\s*\[(.*?)\]/s", // Con comillas dobles
            "/'providers'\s*=>\s*ServiceProvider::defaultProviders\(\)->merge\(\[(.*?)\]\)/s", // Laravel 11 con merge
        ];

        $providersArray = null;
        $fullMatch = null;

        foreach ($patterns as $pattern) {
            if (preg_match($pattern, $content, $matches)) {
                $providersArray = $matches[1] ?? null;
                $fullMatch = $matches[0] ?? null;
                break;
            }
        }

        if ($providersArray !== null) {
            // Buscar el último provider en el array para insertar después
            $lines = explode("\n", $providersArray);
            $lastProviderLine = null;
            $lastProviderLineIndex = 0;
            
            // Encontrar la última línea con un provider
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                if (preg_match("/([A-Za-z0-9\\\]+::class),?\s*$/", $line)) {
                    $lastProviderLine = $line;
                    $lastProviderLineIndex = $i;
                    break;
                }
            }
            
            if ($lastProviderLine !== null) {
                // Insertar después del último provider
                $indentation = str_repeat(' ', 8); // 8 espacios de indentación estándar
                $newProviderLine = $indentation . $providerString . ',';
                
                // Encontrar la línea completa con indentación
                $fullLine = $lines[$lastProviderLineIndex];
                $replacement = $fullLine . "\n" . $newProviderLine;
                
                $content = str_replace($fullLine, $replacement, $content);
                File::put($configPath, $content);
                $this->line('  ✓ TenancyServiceProvider registrado en <fg=green>config/app.php</fg=green>');
            } else {
                // Si no encuentra providers, agregar después de la apertura del array
                $content = preg_replace(
                    "/('providers'\s*=>\s*\[)/",
                    "$1\n        " . $providerString . ',',
                    $content
                );
                File::put($configPath, $content);
                $this->line('  ✓ TenancyServiceProvider registrado en <fg=green>config/app.php</fg=green>');
            }
        } else {
            // Intentar agregar después de App\Providers\RouteServiceProvider::class
            if (preg_match('/(App\\\\Providers\\\\RouteServiceProvider::class,?)/', $content, $matches)) {
                $replacement = $matches[1] . "\n        " . $providerString . ',';
                $content = str_replace($matches[1], $replacement, $content);
                File::put($configPath, $content);
                $this->line('  ✓ TenancyServiceProvider registrado en <fg=green>config/app.php</fg=green>');
            } else {
                $this->warn('  ⚠ No se pudo encontrar el array de providers en config/app.php');
                $this->warn('  ⚠ El ServiceProvider se registrará automáticamente mediante auto-discovery de Composer.');
            }
        }

        $this->newLine();
    }

    /**
     * Crea el archivo bootstrap/providers.php si no existe (Laravel 11).
     */
    protected function createProvidersFile(string $providersPath, string $providerClass): void
    {
        $directory = dirname($providersPath);
        if (!File::exists($directory)) {
            File::makeDirectory($directory, 0755, true);
        }

        $content = "<?php\n\nreturn [\n    " . $providerClass . "::class,\n];\n";
        File::put($providersPath, $content);
        $this->line('  ✓ Archivo <fg=green>bootstrap/providers.php</fg=green> creado');
        $this->line('  ✓ TenancyServiceProvider registrado en <fg=green>bootstrap/providers.php</fg=green>');
        $this->newLine();
    }

    /**
     * Registra el provider en bootstrap/providers.php (Laravel 11).
     */
    protected function registerInProvidersFile(string $providersPath, string $providerClass): void
    {
        $content = File::get($providersPath);

        // Verificar si ya está registrado (buscar con ::class o sin él)
        $providerString = $providerClass . '::class';
        if (Str::contains($content, $providerClass) || Str::contains($content, $providerString)) {
            $this->line('  ✓ TenancyServiceProvider ya está registrado en <fg=green>bootstrap/providers.php</fg=green>');
            $this->newLine();
            return;
        }

        // Si el archivo ya tiene un return array, agregar el provider al array
        if (preg_match('/return\s+\[(.*?)\];/s', $content, $matches)) {
            $providersArray = $matches[1];
            
            // Buscar la última línea del array (ignorar comentarios y líneas vacías)
            $lines = explode("\n", $providersArray);
            $lastLine = null;
            $lastLineIndex = -1;
            
            for ($i = count($lines) - 1; $i >= 0; $i--) {
                $line = trim($lines[$i]);
                // Buscar líneas con providers (::class o sin él)
                if (!empty($line) && 
                    !preg_match('/^\s*\/\//', $line) && 
                    (preg_match('/::class/', $line) || preg_match('/^[A-Za-z0-9\\\]+$/', $line))) {
                    $lastLine = $lines[$i];
                    $lastLineIndex = $i;
                    break;
                }
            }
            
            if ($lastLine !== null && $lastLineIndex >= 0) {
                // Agregar después de la última línea no vacía
                $indentation = str_repeat(' ', 4);
                $newProviderLine = $indentation . $providerString . ',';
                
                // Si la última línea no termina con coma, agregarla
                $lineToReplace = rtrim($lastLine);
                if (!Str::endsWith($lineToReplace, ',')) {
                    $lineToReplace .= ',';
                }
                
                $replacement = $lineToReplace . "\n" . $newProviderLine;
                $content = str_replace($lastLine, $replacement, $content);
            } else {
                // Si el array está vacío o solo tiene comentarios, agregar directamente
                $content = preg_replace(
                    '/return\s+\[(.*?)\];/s',
                    "return [\n    " . $providerString . ",\n];",
                    $content
                );
            }
        } else {
            // Si no hay return, agregar uno nuevo al final del archivo
            $content = trim($content);
            if (!empty($content) && !Str::endsWith($content, "\n")) {
                $content .= "\n";
            }
            $content .= "\nreturn [\n    " . $providerString . ",\n];\n";
        }

        File::put($providersPath, $content);
        $this->line('  ✓ TenancyServiceProvider registrado en <fg=green>bootstrap/providers.php</fg=green>');
        $this->newLine();
    }

    /**
     * Registra los middlewares necesarios en bootstrap/app.php (Laravel 11).
     */
    protected function registerMiddlewares(): void
    {
        // Solo para Laravel 11
        $laravelVersion = (int) app()->version();
        if ($laravelVersion < 11) {
            return; // Laravel 10 registra middlewares automáticamente a través del ServiceProvider
        }

        $bootstrapAppPath = base_path('bootstrap/app.php');
        
        if (!File::exists($bootstrapAppPath)) {
            return;
        }

        $this->info('🔧 Registrando middlewares en bootstrap/app.php...');

        $content = File::get($bootstrapAppPath);
        $originalContent = $content;

        // Verificar si ya están registrados los middlewares
        $middlewareClass = 'AngelitoSystems\\FilamentTenancy\\Middleware\\InitializeTenancy';
        if (str_contains($content, $middlewareClass)) {
            $this->line('  ✓ Middlewares ya están registrados en bootstrap/app.php');
            return;
        }

        // Buscar el bloque withMiddleware
        $pattern = '/->withMiddleware\s*\(\s*function\s*\(\s*Middleware\s+\$middleware\s*\):\s*void\s*\{([^}]*)\}\s*\)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            // Middleware ya existe, agregar el registro
            $middlewareBlock = $matches[0];
            $middlewareContent = $matches[1];
            
            // Verificar si está vacío o solo tiene comentarios
            $trimmedContent = trim($middlewareContent);
            if (empty($trimmedContent) || $trimmedContent === '//') {
                // Reemplazar el comentario o bloque vacío con el registro del middleware
                $newMiddlewareBlock = str_replace(
                    $middlewareContent,
                    "\n        \$middleware->web(append: [\n            \\{$middlewareClass}::class,\n        ]);",
                    $middlewareBlock
                );
            } else {
                // Agregar al final del bloque existente
                $newMiddlewareBlock = str_replace(
                    '}',
                    "        \$middleware->web(append: [\n            \\{$middlewareClass}::class,\n        ]);\n    }",
                    $middlewareBlock
                );
            }
            
            $content = str_replace($middlewareBlock, $newMiddlewareBlock, $content);
        } else {
            // No existe el bloque, agregarlo después de withRouting
            $withRoutingPattern = '/->withRouting\s*\([^)]+\)/s';
            if (preg_match($withRoutingPattern, $content, $routingMatch)) {
                $middlewareRegistration = "\n    ->withMiddleware(function (Middleware \$middleware): void {\n        \$middleware->web(append: [\n            \\{$middlewareClass}::class,\n        ]);\n    })";
                $content = str_replace($routingMatch[0], $routingMatch[0] . $middlewareRegistration, $content);
            }
        }

        if ($content !== $originalContent) {
            File::put($bootstrapAppPath, $content);
            $this->line('  ✓ Middlewares registrados en <fg=green>bootstrap/app.php</fg=green>');
        } else {
            $this->warn('  ⚠ No se pudo registrar los middlewares automáticamente.');
            $this->line('  Por favor, agrega manualmente en bootstrap/app.php:');
            $this->line("  \$middleware->web(append: [\\{$middlewareClass}::class]);");
        }
    }

    /**
     * Publica los componentes y vistas de 404 personalizadas.
     */
    protected function publish404Components(): void
    {
        // Solo para Laravel 11
        $laravelVersion = (int) app()->version();
        if ($laravelVersion < 11) {
            return; // Laravel 10 maneja 404 de forma diferente
        }

        $this->info('🎨 Configurando página 404 personalizada...');

        // Preguntar si quiere publicar los componentes
        if (!$this->confirm('¿Deseas publicar los componentes y vistas de la página 404 personalizada?', true)) {
            $this->line('  ℹ Los componentes y vistas se usarán desde el paquete.');
            $this->register404View();
            return;
        }

        // Publicar vistas
        try {
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-views',
            ]);
            $this->line('  ✓ Vistas publicadas en <fg=green>resources/views/vendor/filament-tenancy</fg=green>');
        } catch (\Exception $e) {
            $this->warn('  ⚠ No se pudieron publicar las vistas: ' . $e->getMessage());
        }

        // Publicar componente Livewire (opcional, solo si Livewire está disponible)
        if (class_exists(\Livewire\Component::class)) {
            try {
                $this->call('vendor:publish', [
                    '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                    '--tag' => 'filament-tenancy-components',
                ]);
                
                // Actualizar el namespace del componente publicado
                $componentPath = app_path('Livewire/TenantNotFound.php');
                if (File::exists($componentPath)) {
                    $content = File::get($componentPath);
                    $content = str_replace(
                        'namespace AngelitoSystems\\FilamentTenancy\\Components;',
                        'namespace App\\Livewire;',
                        $content
                    );
                    File::put($componentPath, $content);
                    $this->line('  ✓ Componente Livewire publicado en <fg=green>app/Livewire/TenantNotFound.php</fg=green>');
                    $this->line('  ℹ Puedes personalizar el componente según tus necesidades.');
                }
            } catch (\Exception $e) {
                $this->warn('  ⚠ No se pudo publicar el componente: ' . $e->getMessage());
            }
        } else {
            $this->line('  ℹ Livewire no está disponible. La vista funcionará sin componente Livewire.');
        }

        // Registrar la vista 404 en bootstrap/app.php
        $this->register404View();
    }

    /**
     * Registra la vista 404 en bootstrap/app.php (Laravel 11).
     */
    protected function register404View(): void
    {
        $laravelVersion = (int) app()->version();
        if ($laravelVersion < 11) {
            return;
        }

        $bootstrapAppPath = base_path('bootstrap/app.php');
        
        if (!File::exists($bootstrapAppPath)) {
            return;
        }

        $content = File::get($bootstrapAppPath);
        $originalContent = $content;

        // Verificar si ya está registrada la vista 404
        if (str_contains($content, 'tenant-not-found') || str_contains($content, 'TenantNotFound')) {
            $this->line('  ✓ Vista 404 ya está registrada en bootstrap/app.php');
            return;
        }

        // Buscar el bloque withExceptions
        $pattern = '/->withExceptions\s*\(\s*function\s*\(\s*Exceptions\s+\$exceptions\s*\):\s*void\s*\{([^}]*)\}\s*\)/s';
        
        if (preg_match($pattern, $content, $matches)) {
            $exceptionsBlock = $matches[0];
            $exceptionsContent = $matches[1];
            
            // Verificar si está vacío o solo tiene comentarios
            $trimmedContent = trim($exceptionsContent);
            if (empty($trimmedContent) || $trimmedContent === '//') {
                // Reemplazar el comentario o bloque vacío
                $exceptionRegistration = "\n        \$exceptions->render(function (\\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException \$e, \\Illuminate\\Http\\Request \$request) {\n            if (str_contains(\$e->getMessage(), 'Tenant not found')) {\n                return response()->view('filament-tenancy::errors.tenant-not-found', [\n                    'host' => \$request->getHost(),\n                    'resolver' => config('filament-tenancy.resolver', 'domain'),\n                    'appDomain' => env('APP_DOMAIN'),\n                ], 404);\n            }\n        });";
                
                $newExceptionsBlock = str_replace(
                    $exceptionsContent,
                    $exceptionRegistration,
                    $exceptionsBlock
                );
            } else {
                // Agregar al final del bloque existente
                $exceptionRegistration = "\n        \$exceptions->render(function (\\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException \$e, \\Illuminate\\Http\\Request \$request) {\n            if (str_contains(\$e->getMessage(), 'Tenant not found')) {\n                return response()->view('filament-tenancy::errors.tenant-not-found', [\n                    'host' => \$request->getHost(),\n                    'resolver' => config('filament-tenancy.resolver', 'domain'),\n                    'appDomain' => env('APP_DOMAIN'),\n                ], 404);\n            }\n        });";
                
                $newExceptionsBlock = str_replace(
                    '}',
                    $exceptionRegistration . "\n    }",
                    $exceptionsBlock
                );
            }
            
            $content = str_replace($exceptionsBlock, $newExceptionsBlock, $content);
        } else {
            // No existe el bloque, agregarlo después de withMiddleware
            $withMiddlewarePattern = '/->withMiddleware\s*\([^)]+\)/s';
            if (preg_match($withMiddlewarePattern, $content, $middlewareMatch)) {
                $exceptionRegistration = "\n    ->withExceptions(function (Exceptions \$exceptions): void {\n        \$exceptions->render(function (\\Symfony\\Component\\HttpKernel\\Exception\\NotFoundHttpException \$e, \\Illuminate\\Http\\Request \$request) {\n            if (str_contains(\$e->getMessage(), 'Tenant not found')) {\n                return response()->view('filament-tenancy::errors.tenant-not-found', [\n                    'host' => \$request->getHost(),\n                    'resolver' => config('filament-tenancy.resolver', 'domain'),\n                    'appDomain' => env('APP_DOMAIN'),\n                ], 404);\n            }\n        });\n    })";
                $content = str_replace($middlewareMatch[0], $middlewareMatch[0] . $exceptionRegistration, $content);
            }
        }

        if ($content !== $originalContent) {
            File::put($bootstrapAppPath, $content);
            $this->line('  ✓ Vista 404 registrada en <fg=green>bootstrap/app.php</fg=green>');
        } else {
            $this->warn('  ⚠ No se pudo registrar la vista 404 automáticamente.');
            $this->line('  Puedes agregarla manualmente en bootstrap/app.php en el bloque withExceptions.');
        }
    }

    /**
     * Configura automáticamente el panel admin con el plugin de landlord.
     */
    protected function configureAdminPanel(): void
    {
        $panelProvidersPath = app_path('Providers/Filament');
        $landlordPanelId = config('filament-tenancy.filament.landlord_panel_id', 'admin');
        
        if (!File::exists($panelProvidersPath)) {
            return;
        }
        
        // Buscar AdminPanelProvider (creado por filament:install --panels)
        $adminProviderFile = $panelProvidersPath . '/' . Str::studly($landlordPanelId) . 'PanelProvider.php';
        
        // También buscar AdminPanelProvider.php por nombre común
        if (!File::exists($adminProviderFile)) {
            $providers = glob($panelProvidersPath . '/*PanelProvider.php');
            foreach ($providers as $providerFile) {
                $content = File::get($providerFile);
                if (preg_match("/->id\(['\"]([^'\"]+)['\"]\)/", $content, $matches)) {
                    $panelId = $matches[1];
                    if ($panelId === $landlordPanelId || str_contains(strtolower($panelId), 'admin')) {
                        $adminProviderFile = $providerFile;
                        break;
                    }
                }
            }
        }
        
        if (!File::exists($adminProviderFile)) {
            $this->line('  ℹ No se encontró AdminPanelProvider para configurar automáticamente');
            return;
        }
        
        $content = File::get($adminProviderFile);
        
        // Verificar si ya tiene el plugin configurado
        if (str_contains($content, 'TenancyLandlordPlugin') || str_contains($content, 'LandlordPlugin')) {
            $this->line('  ✓ AdminPanelProvider ya tiene TenancyLandlordPlugin configurado');
            return;
        }
        
        // Agregar el import del plugin
        if (!str_contains($content, 'use AngelitoSystems\\FilamentTenancy\\FilamentPlugins\\TenancyLandlordPlugin;')) {
            // Encontrar la última línea de imports
            $lines = explode("\n", $content);
            $lastUseIndex = 0;
            foreach ($lines as $index => $line) {
                if (preg_match('/^use\s+/', $line)) {
                    $lastUseIndex = $index;
                }
            }
            
            // Insertar el import después del último use
            array_splice($lines, $lastUseIndex + 1, 0, 'use AngelitoSystems\\FilamentTenancy\\FilamentPlugins\\TenancyLandlordPlugin;');
            $content = implode("\n", $lines);
        }
        
        // Agregar el plugin al final del método panel, antes del punto y coma final
        // Buscar el patrón ->authMiddleware([...]) seguido de punto y coma o retorno
        if (preg_match('/(->authMiddleware\(\[[\s\S]*?\]\));/', $content, $matches)) {
            // Reemplazar con el authMiddleware + plugin
            $replacement = $matches[1] . "\n            ->plugin(TenancyLandlordPlugin::make());";
            $content = str_replace($matches[0], $replacement, $content);
        } elseif (preg_match('/(->authMiddleware\(\[[\s\S]*?\]\))\s*;/', $content, $matches)) {
            // Buscar con espacios antes del punto y coma
            $replacement = $matches[1] . "\n            ->plugin(TenancyLandlordPlugin::make());";
            $content = str_replace($matches[0], $replacement, $content);
        } else {
            // Fallback: buscar el final del return statement
            if (preg_match('/(return\s+\$panel[\s\S]*?->authMiddleware\(\[[\s\S]*?\]\));/', $content, $matches)) {
                $replacement = $matches[1] . "\n            ->plugin(TenancyLandlordPlugin::make());";
                $content = str_replace($matches[1] . ';', $replacement, $content);
            }
        }
        
        File::put($adminProviderFile, $content);
        $this->line("  ✓ AdminPanelProvider configurado con TenancyLandlordPlugin");
    }
    
    /**
     * Crea automáticamente el TenantPanelProvider con el plugin configurado.
     */
    protected function createTenantPanel(): void
    {
        $panelProvidersPath = app_path('Providers/Filament');
        $tenantPanelId = config('filament-tenancy.filament.tenant_panel_id', 'tenant');
        $tenantProviderFile = $panelProvidersPath . '/' . Str::studly($tenantPanelId) . 'PanelProvider.php';
        
        // Verificar si ya existe
        if (File::exists($tenantProviderFile)) {
            $content = File::get($tenantProviderFile);
            if (str_contains($content, 'TenancyTenantPlugin') || str_contains($content, 'TenantPlugin')) {
                $this->line('  ✓ TenantPanelProvider ya existe y tiene TenancyTenantPlugin configurado');
                return;
            }
            // Si existe pero no tiene el plugin, lo configuraremos
        }
        
        // Crear el directorio si no existe
        if (!File::exists($panelProvidersPath)) {
            File::makeDirectory($panelProvidersPath, 0755, true);
        }
        
        $namespace = app()->getNamespace();
        $className = Str::studly($tenantPanelId) . 'PanelProvider';
        $fqn = $namespace . 'Providers\\Filament\\' . $className;
        
        // Generar el contenido del TenantPanelProvider
        $content = <<<PHP
<?php

namespace {$namespace}Providers\Filament;

use AngelitoSystems\FilamentTenancy\FilamentPlugins\TenancyTenantPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class {$className} extends PanelProvider
{
    public function panel(Panel \$panel): Panel
    {
        return \$panel
            ->id('{$tenantPanelId}')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: '{$namespace}Filament\\Tenant\\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: '{$namespace}Filament\\Tenant\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: '{$namespace}Filament\\Tenant\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(TenancyTenantPlugin::make());
    }
}
PHP;
        
        File::put($tenantProviderFile, $content);
        
        // Registrar el provider en bootstrap/providers.php o config/app.php
        $this->registerPanelProvider($fqn);
        
        $this->line("  ✓ TenantPanelProvider creado y configurado con TenancyTenantPlugin");
    }
    
    /**
     * Registra un PanelProvider en bootstrap/providers.php o config/app.php.
     */
    protected function registerPanelProvider(string $fqn): void
    {
        // Laravel 11+ usa bootstrap/providers.php
        $bootstrapProvidersPath = base_path('bootstrap/providers.php');
        
        if (File::exists($bootstrapProvidersPath)) {
            // Laravel 11+
            $content = File::get($bootstrapProvidersPath);
            if (!str_contains($content, $fqn)) {
                // Agregar al final del array, antes del cierre
                if (preg_match('/(return\s+\[[\s\S]*?)(\s*\];)/', $content, $matches)) {
                    $newLine = "    " . $fqn . "::class,";
                    // Verificar si hay otros providers para agregar después del último
                    $replacement = $matches[1];
                    if (!str_ends_with(trim($matches[1]), ',')) {
                        $replacement = rtrim($matches[1]) . ",";
                    }
                    $replacement .= "\n" . $newLine . "\n" . $matches[2];
                    $content = str_replace($matches[0], $replacement, $content);
                    File::put($bootstrapProvidersPath, $content);
                } else {
                    // Fallback: agregar al final del archivo
                    $content = rtrim($content) . "\n" . $fqn . "::class," . "\n";
                    File::put($bootstrapProvidersPath, $content);
                }
            }
        } else {
            // Laravel 10 usa config/app.php
            $appConfigPath = config_path('app.php');
            if (File::exists($appConfigPath)) {
                $content = File::get($appConfigPath);
                if (!str_contains($content, $fqn)) {
                    $namespace = app()->getNamespace();
                    $search = $namespace . 'Providers\\RouteServiceProvider::class,';
                    $replacement = $fqn . "::class," . "\n        " . $search;
                    if (str_contains($content, $search)) {
                        $content = str_replace($search, $replacement, $content);
                    } else {
                        // Buscar el array de providers
                        if (preg_match("/(\'providers'\s*=>\s*\[[\s\S]*?)(\s*\],)/", $content, $matches)) {
                            $newLine = "        " . $fqn . "::class,";
                            $replacement = $matches[1];
                            if (!str_ends_with(trim($matches[1]), ',')) {
                                $replacement = rtrim($matches[1]) . ",";
                            }
                            $replacement .= "\n" . $newLine . "\n" . $matches[2];
                            $content = str_replace($matches[0], $replacement, $content);
                        }
                    }
                    File::put($appConfigPath, $content);
                }
            }
        }
    }

    /**
     * Verifica y configura los paneles de Filament.
     */
    protected function checkAndConfigureFilamentPanels(): void
    {
        $this->info('🔍 Verificando paneles de Filament...');

        // Verificar si Filament está disponible
        if (!class_exists(\Filament\Facades\Filament::class)) {
            $this->line('  ℹ Filament no está disponible. Los paneles se verificarán después de instalar Filament.');
            $this->newLine();
            return;
        }

        $panelProvidersPath = app_path('Providers/Filament');
        $landlordPanelId = config('filament-tenancy.filament.landlord_panel_id', 'admin');
        $tenantPanelId = config('filament-tenancy.filament.tenant_panel_id', 'tenant');

        $foundPanels = [];
        $landlordPanelFound = false;
        $tenantPanelFound = false;

        // Buscar PanelProviders
        if (File::exists($panelProvidersPath)) {
            $providers = glob($panelProvidersPath . '/*PanelProvider.php');
            
            foreach ($providers as $providerFile) {
                $content = File::get($providerFile);
                
                // Extraer el ID del panel buscando ->id('...')
                if (preg_match("/->id\(['\"]([^'\"]+)['\"]\)/", $content, $matches)) {
                    $panelId = $matches[1];
                    $foundPanels[] = [
                        'id' => $panelId,
                        'file' => basename($providerFile),
                    ];

                    // Verificar si es el panel landlord
                    if ($panelId === $landlordPanelId || str_contains(strtolower($panelId), 'admin')) {
                        $landlordPanelFound = true;
                        $this->line("  ✓ Panel landlord encontrado: <fg=green>{$panelId}</fg=green> ({$panelId}PanelProvider.php)");
                        
                        // Verificar si tiene el plugin configurado
                        if (!str_contains($content, 'TenancyLandlordPlugin') && !str_contains($content, 'LandlordPlugin')) {
                            $this->warn("  ⚠ El panel <fg=yellow>{$panelId}</fg=yellow> no tiene TenancyLandlordPlugin configurado.");
                            $this->line("  Agrega: ->plugin(\\AngelitoSystems\\FilamentTenancy\\FilamentPlugins\\TenancyLandlordPlugin::make())");
                        } else {
                            $this->line("  ✓ TenancyLandlordPlugin está configurado en el panel <fg=green>{$panelId}</fg=green>");
                        }
                    }
                    
                    // Verificar si es el panel tenant
                    if ($panelId === $tenantPanelId || str_contains(strtolower($panelId), 'tenant')) {
                        $tenantPanelFound = true;
                        $this->line("  ✓ Panel tenant encontrado: <fg=green>{$panelId}</fg=green> ({$panelId}PanelProvider.php)");
                        
                        // Verificar si tiene el plugin configurado
                        if (!str_contains($content, 'TenancyTenantPlugin') && !str_contains($content, 'TenantPlugin')) {
                            $this->warn("  ⚠ El panel <fg=yellow>{$panelId}</fg=yellow> no tiene TenancyTenantPlugin configurado.");
                            $this->line("  Agrega: ->plugin(\\AngelitoSystems\\FilamentTenancy\\FilamentPlugins\\TenancyTenantPlugin::make())");
                        } else {
                            $this->line("  ✓ TenancyTenantPlugin está configurado en el panel <fg=green>{$panelId}</fg=green>");
                        }
                    }
                }
            }
        }

        // Si no se encontraron paneles específicos, buscar otros paneles
        if (empty($foundPanels)) {
            $this->line('  ℹ No se encontraron paneles de Filament en <fg=yellow>app/Providers/Filament/</fg=yellow>');
            $this->line('  Los paneles se configurarán automáticamente cuando uses los plugins.');
        } else {
            // Mostrar paneles encontrados que no son landlord ni tenant
            $otherPanels = array_filter($foundPanels, function ($panel) use ($landlordPanelId, $tenantPanelId) {
                return $panel['id'] !== $landlordPanelId && 
                       $panel['id'] !== $tenantPanelId &&
                       !str_contains(strtolower($panel['id']), 'admin') &&
                       !str_contains(strtolower($panel['id']), 'tenant');
            });

            foreach ($otherPanels as $panel) {
                $this->line("  ℹ Panel encontrado: <fg=cyan>{$panel['id']}</fg=cyan> ({$panel['file']})");
            }
        }

        // Resumen de seguridad
        $this->newLine();
        $this->info('🔒 Configuración de seguridad de paneles:');
        
        if ($landlordPanelFound) {
            $this->line('  ✓ Panel admin/landlord: Bloqueado para acceso desde contexto tenant');
            $this->line('    → El middleware PreventTenantAccess previene acceso cuando hay tenant activo');
        } else {
            $this->warn('  ⚠ Panel admin/landlord no encontrado');
            $this->line('    → Crea un panel con id "admin" o similar y agrega TenancyLandlordPlugin');
        }

        if ($tenantPanelFound) {
            $this->line('  ✓ Panel tenant: Bloqueado para acceso sin tenant activo');
            $this->line('    → El middleware PreventLandlordAccess previene acceso sin tenant');
        } else {
            $this->warn('  ⚠ Panel tenant no encontrado');
            $this->line('    → Crea un panel con id "tenant" o similar y agrega TenancyTenantPlugin');
        }

        $this->newLine();
        $this->line('  📝 Recordatorio:');
        $this->line('    • El panel admin solo es accesible desde dominios centrales sin tenant');
        $this->line('    • El panel tenant solo es accesible cuando hay un tenant resuelto');
        $this->line('    • Los middlewares se aplican automáticamente cuando usas los plugins');
        $this->newLine();
    }

    /**
     * Muestra el mensaje de éxito final.
     */
    protected function displaySuccessMessage(): void
    {
        $this->newLine();
        $this->line('╔═══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                               ║');
        $this->line('║        <fg=green>✓ Instalación completada exitosamente</fg=green>               ║');
        $this->line('║                                                               ║');
        $this->line('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();
        
        $this->info('🎉 ¡Filament Tenancy ha sido instalado correctamente!');
        $this->newLine();
        
        $this->line('📋 <fg=cyan>Próximos pasos:</fg=cyan>');
        $this->line('  1. Crea tu primer tenant: <fg=yellow>php artisan tenancy:create</fg=yellow>');
        $this->line('  2. Accede al panel de administración central para gestionar tenants');
        $this->line('  3. Los assets de Livewire y otros recursos se compartirán automáticamente');
        $this->line('  4. El sistema de roles y permisos está listo para usar');
        $this->newLine();
        
        $this->line('📚 <fg=cyan>Comandos útiles:</fg=cyan>');
        $this->line('  • <fg=yellow>php artisan tenancy:list</fg=yellow> - Listar todos los tenants');
        $this->line('  • <fg=yellow>php artisan tenant:migrate</fg=yellow> - Migrar tenants');
        $this->line('  • <fg=yellow>php artisan tenancy:delete</fg=yellow> - Eliminar un tenant');
        $this->newLine();
        
        $this->line('🔐 <fg=cyan>Sistema de permisos:</fg=cyan>');
        $this->line('  • Usa el trait <fg=yellow>HasRoles</fg=yellow> en tus modelos');
        $this->line('  • Los roles y permisos se crearán automáticamente cuando crees tu primer tenant');
        $this->line('  • Sistema de permisos básicos configurado para tenants');
        $this->newLine();
    }

    /**
     * Crea roles y permisos básicos para el sistema.
     */
    protected function createBasicRolesAndPermissions(): void
    {
        $this->info('🔐 Creando roles y permisos básicos...');

        try {
            // Crear permisos básicos
            $permissions = [
                ['name' => 'manage users', 'slug' => 'manage-users', 'description' => 'Gestionar usuarios'],
                ['name' => 'manage roles', 'slug' => 'manage-roles', 'description' => 'Gestionar roles'],
                ['name' => 'manage permissions', 'slug' => 'manage-permissions', 'description' => 'Gestionar permisos'],
                ['name' => 'manage tenants', 'slug' => 'manage-tenants', 'description' => 'Gestionar tenants'],
                ['name' => 'view dashboard', 'slug' => 'view-dashboard', 'description' => 'Ver dashboard'],
                ['name' => 'manage settings', 'slug' => 'manage-settings', 'description' => 'Gestionar configuración'],
            ];

            foreach ($permissions as $permission) {
                Permission::firstOrCreate(
                    ['slug' => $permission['slug']],
                    $permission
                );
            }

            // Crear roles básicos
            $roles = [
                [
                    'name' => 'Super Admin',
                    'slug' => 'super-admin',
                    'description' => 'Super administrador con acceso total',
                    'permissions' => ['manage users', 'manage roles', 'manage permissions', 'manage tenants', 'view dashboard', 'manage settings']
                ],
                [
                    'name' => 'Admin',
                    'slug' => 'admin',
                    'description' => 'Administrador con permisos limitados',
                    'permissions' => ['manage users', 'view dashboard', 'manage settings']
                ],
                [
                    'name' => 'User',
                    'slug' => 'user',
                    'description' => 'Usuario básico',
                    'permissions' => ['view dashboard']
                ],
            ];

            foreach ($roles as $roleData) {
                $role = Role::firstOrCreate(
                    ['slug' => $roleData['slug']],
                    [
                        'name' => $roleData['name'],
                        'description' => $roleData['description'],
                        'guard_name' => config('auth.defaults.guard', 'web'),
                    ]
                );

                // Asignar permisos al rol
                foreach ($roleData['permissions'] as $permissionName) {
                    $permission = Permission::where('slug', Str::slug($permissionName))->first();
                    if ($permission) {
                        $role->givePermissionTo($permission);
                    }
                }
            }

            $this->line('  ✓ Roles y permisos creados correctamente');
            $this->line('    • Super Admin: Todos los permisos');
            $this->line('    • Admin: Permisos de gestión básicos');
            $this->line('    • User: Permisos básicos');

        } catch (\Exception $e) {
            $this->warn('  ⚠ No se pudieron crear los roles y permisos: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Pregunta al usuario si desea crear un usuario administrador.
     */
    protected function askToCreateAdminUser(): void
    {
        $this->info('👤 Creación de usuario administrador');
        $this->newLine();

        if (!$this->confirm('¿Deseas crear un usuario administrador ahora?', true)) {
            $this->line('  ℹ Puedes crear usuarios administradores más tarde usando el panel de Filament.');
            $this->newLine();
            return;
        }

        $this->createAdminUser();
    }

    /**
     * Crea un usuario administrador.
     */
    protected function createAdminUser(): void
    {
        $this->newLine();
        $this->info('📝 Configuración del usuario administrador:');

        // Obtener datos del usuario
        $name = $this->ask('Nombre del administrador', 'Super Admin');
        $email = $this->ask('Email del administrador');
        $password = $this->secret('Contraseña (dejar en blanco para generar automática)');

        // Validar email
        if (empty($email) || !filter_var($email, FILTER_VALIDATE_EMAIL)) {
            $this->error('  ✗ Email inválido. El usuario no será creado.');
            return;
        }

        // Generar contraseña si no se proporciona
        if (empty($password)) {
            $password = Str::random(12);
            $this->line("  ℹ Contraseña generada automáticamente: <fg=yellow>{$password}</fg=yellow>");
        }

        try {
            // Crear modelo User dinámicamente
            $userModel = config('auth.providers.users.model', 'App\\Models\\User');
            
            if (!class_exists($userModel)) {
                // Crear modelo User básico si no existe
                $this->createBasicUserModel();
                $userModel = 'App\\Models\\User';
            }

            // Crear usuario
            $user = $userModel::create([
                'name' => $name,
                'email' => $email,
                'password' => Hash::make($password),
                'email_verified_at' => now(),
            ]);

            // Asignar rol de Super Admin
            if (method_exists($user, 'assignRole')) {
                $superAdminRole = Role::where('slug', 'super-admin')->first();
                if ($superAdminRole) {
                    $user->assignRole($superAdminRole);
                    $this->line('  ✓ Rol Super Admin asignado correctamente');
                }
            }

            $this->newLine();
            $this->line('╔═══════════════════════════════════════════════════════════════╗');
            $this->line('║                    <fg=green>Usuario Creado</fg=green>                      ║');
            $this->line('╚═══════════════════════════════════════════════════════════════╝');
            $this->line("  Nombre: <fg=cyan>{$name}</fg=cyan>");
            $this->line("  Email: <fg=cyan>{$email}</fg=cyan>");
            $this->line("  Contraseña: <fg=yellow>{$password}</fg=yellow>");
            $this->line("  Rol: <fg=cyan>Super Admin</fg=cyan>");
            $this->newLine();
            $this->warn('  ⚠ Guarda estas credenciales en un lugar seguro.');
            $this->newLine();

        } catch (\Exception $e) {
            $this->error('  ✗ Error al crear el usuario: ' . $e->getMessage());
            $this->line('  ℹ Puedes crear el usuario manualmente más tarde.');
        }
    }

    /**
     * Crea un modelo User básico si no existe.
     */
    protected function createBasicUserModel(): void
    {
        $userModelPath = app_path('Models/User.php');
        
        if (!File::exists($userModelPath)) {
            $userModelContent = '<?php

            namespace App\Models;

            use AngelitoSystems\FilamentTenancy\Concerns\HasRoles;
            use Illuminate\Database\Eloquent\Factories\HasFactory;
            use Illuminate\Foundation\Auth\User as Authenticatable;
            use Illuminate\Notifications\Notifiable;

            class User extends Authenticatable
            {
                use HasFactory, Notifiable, HasRoles;

                protected $fillable = [
                    \'name\',
                    \'email\',
                    \'password\',
                ];

                protected $hidden = [
                    \'password\',
                    \'remember_token\',
                ];

                protected $casts = [
                    \'email_verified_at\' => \'datetime\',
                ];
            }';

            File::put($userModelPath, $userModelContent);
            $this->line('  ✓ Modelo User creado en app/Models/User.php');
        }
    }
}

