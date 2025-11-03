<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;

class DebugConfigCommand extends Command
{
    protected $signature = 'filament-tenancy:debug-config';
    
    protected $description = 'Debug configuration values for language switching';

    public function handle()
    {
        $this->info('🔍 Debug Configuration Values');
        $this->newLine();

        // Check localization config
        $this->info('📋 Localization Configuration:');
        $this->line('   enabled: ' . (config('filament-tenancy.localization.enabled', 'NOT SET') ? 'true' : 'false'));
        $this->line('   auto_detect: ' . (config('filament-tenancy.localization.auto_detect', 'NOT SET') ? 'true' : 'false'));
        $this->line('   default_locale: ' . config('filament-tenancy.localization.default_locale', 'NOT SET'));
        $this->line('   show_language_switcher: ' . (config('filament-tenancy.localization.show_language_switcher', 'NOT SET') ? 'true' : 'false'));
        $this->newLine();

        // Check app config
        $this->info('📋 App Configuration:');
        $this->line('   app.locale: ' . config('app.locale', 'NOT SET'));
        $this->line('   app.fallback_locale: ' . config('app.fallback_locale', 'NOT SET'));
        $this->newLine();

        // Check if config is cached
        $this->info('📋 Cache Status:');
        $configCached = file_exists(base_path('bootstrap/cache/config.php'));
        $this->line('   Config cached: ' . ($configCached ? 'YES - Run php artisan config:clear' : 'NO'));
        $this->newLine();

        // Raw config check
        $this->info('📋 Raw Config Values:');
        $rawAutoDetect = config('filament-tenancy.localization.auto_detect');
        $this->line('   Raw auto_detect value: ' . var_export($rawAutoDetect, true));
        $this->line('   Type: ' . gettype($rawAutoDetect));
        $this->newLine();

        // Environment check
        $this->info('📋 Environment Variables:');
        $this->line('   TENANCY_AUTO_DETECT_LOCALE: ' . (env('TENANCY_AUTO_DETECT_LOCALE') ?? 'NOT SET'));
        $this->line('   APP_LOCALE: ' . (env('APP_LOCALE') ?? 'NOT SET'));
        $this->newLine();

        // Recommendations
        $this->info('💡 Recommendations:');
        if ($configCached) {
            $this->line('   ⚠️  Run: php artisan config:clear');
        }
        if ($rawAutoDetect === true) {
            $this->line('   ⚠️  auto_detect is TRUE - Browser detection is ENABLED');
        } elseif ($rawAutoDetect === false) {
            $this->line('   ✅ auto_detect is FALSE - Browser detection is DISABLED');
        } else {
            $this->line('   ⚠️  auto_detect value is unexpected: ' . var_export($rawAutoDetect, true));
        }
        
        return 0;
    }
}
