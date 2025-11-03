<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\File;

class PublishAssetsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'filament-tenancy:publish 
                            {--lang : Publicar archivos de idioma}
                            {--docs : Publicar documentación}
                            {--all : Publicar todos los recursos (idioma y documentación)}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Publicar recursos del paquete Filament Tenancy (idiomas y documentación)';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayBranding();

        $publishLang = $this->option('lang') || $this->option('all');
        $publishDocs = $this->option('docs') || $this->option('all');

        if (!$publishLang && !$publishDocs) {
            $this->info('Opciones disponibles:');
            $this->line('  --lang   : Publicar archivos de idioma');
            $this->line('  --docs   : Publicar documentación');
            $this->line('  --all    : Publicar todos los recursos');
            $this->newLine();
            $this->info('Ejemplos:');
            $this->line('  php artisan filament-tenancy:publish --lang');
            $this->line('  php artisan filament-tenancy:publish --docs');
            $this->line('  php artisan filament-tenancy:publish --all');
            return self::SUCCESS;
        }

        $this->info('Publicando recursos de Filament Tenancy...');
        $this->newLine();

        if ($publishLang) {
            $this->publishLanguageFiles();
        }

        if ($publishDocs) {
            $this->publishDocumentation();
        }

        $this->displaySuccessMessage();
        return self::SUCCESS;
    }

    /**
     * Publicar archivos de idioma.
     */
    protected function publishLanguageFiles(): void
    {
        $this->info('📝 Publicando archivos de idioma...');

        try {
            // Publicar idiomas usando vendor:publish
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-lang',
            ]);

            // Publicar traducciones simples para __('tenancy.key')
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-simple-lang',
            ]);

            // Publicar traducciones de Filament
            $this->call('vendor:publish', [
                '--provider' => 'AngelitoSystems\FilamentTenancy\TenancyServiceProvider',
                '--tag' => 'filament-tenancy-filament-lang',
            ]);

            $this->line('  ✓ Archivos de idioma publicados en <fg=green>resources/lang/vendor/filament-tenancy/</fg=green>');
            $this->line('  ✓ Traducciones simples publicadas en <fg=green>resources/lang/{locale}/tenancy.php</fg=green>');
            $this->line('  ✓ Traducciones de Filament publicadas en <fg=green>resources/lang/es/</fg=green>');
            
            // Verificar que los archivos se publicaron correctamente
            $langPath = resource_path('lang/vendor/filament-tenancy');
            if (File::exists($langPath)) {
                $locales = File::directories($langPath);
                foreach ($locales as $locale) {
                    $localeName = basename($locale);
                    $this->line("    • Idioma publicado: <fg=cyan>{$localeName}</fg=cyan>");
                }
            }

            $this->newLine();
            $this->info('📚 Uso de los archivos de idioma:');
            $this->line('  Los archivos de idioma están disponibles para:');
            $this->line('  • Traducciones del paquete: filament-tenancy::tenancy.key');
            $this->line('  • Traducciones simples: __("tenancy.key")');
            $this->line('  • Traducciones de Filament: __("filament.actions.create")');
            $this->line('  • Personalizar traducciones existentes');
            $this->line('  • Agregar nuevos idiomas');
            $this->newLine();
        } catch (\Exception $e) {
            $this->error('  ✗ Error al publicar archivos de idioma: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Publicar documentación.
     */
    protected function publishDocumentation(): void
    {
        $this->info('📚 Publicando documentación...');

        $docsPath = base_path('docs/filament-tenancy');
        $packageDocsPath = __DIR__ . '/../../docs';

        try {
            // Crear directorio docs si no existe
            if (!File::exists(base_path('docs'))) {
                File::makeDirectory(base_path('docs'));
            }

            // Copiar documentación del paquete
            if (File::exists($packageDocsPath)) {
                if (File::exists($docsPath)) {
                    if ($this->confirm('  El directorio docs/filament-tenancy ya existe. ¿Deseas sobrescribirlo?', false)) {
                        File::deleteDirectory($docsPath);
                        $this->line('  ✓ Directorio existente eliminado');
                    } else {
                        $this->warn('  ⚠ Documentación no actualizada');
                        $this->newLine();
                        return;
                    }
                }

                File::copyDirectory($packageDocsPath, $docsPath);
                $this->line('  ✓ Documentación publicada en <fg=green>docs/filament-tenancy/</fg=green>');

                // Listar archivos de documentación publicados
                $this->newLine();
                $this->info('📄 Archivos de documentación publicados:');
                $this->listDocumentationFiles($docsPath);
            } else {
                $this->warn('  ⚠ No se encontró la documentación del paquete');
            }

            $this->newLine();
            $this->info('📖 Contenido de la documentación:');
            $this->line('  • Guías de instalación y configuración');
            $this->line('  • Documentación de comandos');
            $this->line('  • Ejemplos de uso');
            $this->line('  • Guías de multilingüismo');
            $this->line('  • Solución de problemas');
            $this->newLine();
        } catch (\Exception $e) {
            $this->error('  ✗ Error al publicar documentación: ' . $e->getMessage());
        }

        $this->newLine();
    }

    /**
     * Listar archivos de documentación publicados.
     */
    protected function listDocumentationFiles(string $docsPath): void
    {
        $files = File::allFiles($docsPath, true);
        
        foreach ($files as $file) {
            if ($file->getExtension() === 'md') {
                $relativePath = $file->getRelativePathname();
                $this->line("    • <fg=cyan>{$relativePath}</fg=cyan>");
            }
        }

        // También listar directorios
        $directories = File::directories($docsPath);
        foreach ($directories as $dir) {
            $relativePath = basename($dir);
            $this->line("    📁 <fg=yellow>{$relativePath}/</fg=yellow>");
        }
    }

    /**
     * Muestra el branding inicial del paquete.
     */
    protected function displayBranding(): void
    {
        $this->newLine();
        $this->line('╔═══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                               ║');
        $this->line('║           <fg=cyan>Filament Tenancy</fg=cyan> - Publish Assets          ║');
        $this->line('║                  <fg=yellow>Angelito Systems</fg=yellow>                      ║');
        $this->line('║                                                               ║');
        $this->line('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Muestra el mensaje final de éxito.
     */
    protected function displaySuccessMessage(): void
    {
        $this->info('✅ ¡Recursos publicados exitosamente!');
        $this->newLine();
        
        $this->info('📚 Próximos pasos:');
        $this->line('  1. Revisa los archivos publicados en sus respectivos directorios');
        $this->line('  2. Personaliza las traducciones según necesites');
        $this->line('  3. Consulta la documentación para más detalles');
        $this->newLine();
        
        $this->info('📖 Para más ayuda:');
        $this->line('  • Documentación: <fg=green>docs/filament-tenancy/</fg=green>');
        $this->line('  • Idiomas: <fg=green>resources/lang/vendor/filament-tenancy/</fg=green>');
        $this->line('  • Comandos disponibles: <fg=yellow>php artisan list | findstr filament-tenancy</fg=yellow>');
        $this->newLine();
    }
}
