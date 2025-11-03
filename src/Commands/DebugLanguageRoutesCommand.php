<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use Illuminate\Support\Facades\File;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

class DebugLanguageRoutesCommand extends Command
{
    protected $signature = 'filament-tenancy:debug-language-routes';
    
    protected $description = 'Debug language switching routes and functionality';

    public function handle()
    {
        $this->info('🔍 Debugging Language Switching Routes');
        $this->newLine();

        // 1. Check if routes are loaded
        $this->info('📋 1. Route Loading Status:');
        $routes = Route::getRoutes();
        $languageRoute = null;
        
        foreach ($routes as $route) {
            if ($route->getName() === 'language.switch') {
                $languageRoute = $route;
                break;
            }
        }
        
        if ($languageRoute) {
            $this->line('   ✅ language.switch route found');
            $this->line('   URI: ' . $languageRoute->uri());
            $this->line('   Methods: ' . implode(', ', $languageRoute->methods()));
            $this->line('   Action: ' . $languageRoute->getActionName());
        } else {
            $this->line('   ❌ language.switch route NOT found');
            $this->line('   Available routes with "language":');
            
            foreach ($routes as $route) {
                if (strpos($route->uri(), 'language') !== false) {
                    $this->line('     - ' . $route->uri() . ' [' . $route->getName() . ']');
                }
            }
        }
        $this->newLine();

        // 2. Check route file exists
        $this->info('📁 2. Route Files Check:');
        $packageRouteFile = __DIR__ . '/../../routes/tenant.php';
        $appRouteFile = base_path('routes/tenant.php');
        
        $this->line('   Package route file: ' . (file_exists($packageRouteFile) ? '✅ EXISTS' : '❌ MISSING'));
        $this->line('   App route file: ' . (file_exists($appRouteFile) ? '✅ EXISTS' : '❌ MISSING'));
        
        if (file_exists($packageRouteFile)) {
            $this->line('   Package route file content:');
            $content = file_get_contents($packageRouteFile);
            $this->line('   ' . str_replace("\n", "\n   ", $content));
        }
        $this->newLine();

        // 3. Test URL generation
        $this->info('🔗 3. URL Generation Test:');
        try {
            if (Route::has('language.switch')) {
                $enUrl = route('language.switch', 'en');
                $esUrl = route('language.switch', 'es');
                $this->line('   English URL: ' . $enUrl);
                $this->line('   Spanish URL: ' . $esUrl);
            } else {
                $this->line('   ❌ Cannot generate URLs - route not found');
            }
        } catch (\Exception $e) {
            $this->line('   ❌ URL generation failed: ' . $e->getMessage());
        }
        $this->newLine();

        // 4. Test LanguageSwitcher functionality
        $this->info('🧪 4. LanguageSwitcher Test:');
        $originalLocale = LanguageSwitcher::getCurrentLocale();
        $this->line('   Current locale: ' . $originalLocale);
        
        // Test switching to English
        $result = LanguageSwitcher::setLocale('en');
        $this->line('   Switch to EN: ' . ($result ? '✅ SUCCESS' : '❌ FAILED'));
        $this->line('   New locale: ' . app()->getLocale());
        $this->line('   Session locale: ' . session('locale'));
        
        // Test switching to Spanish
        $result = LanguageSwitcher::setLocale('es');
        $this->line('   Switch to ES: ' . ($result ? '✅ SUCCESS' : '❌ FAILED'));
        $this->line('   New locale: ' . app()->getLocale());
        $this->line('   Session locale: ' . session('locale'));
        
        // Reset
        LanguageSwitcher::setLocale($originalLocale);
        $this->newLine();

        // 5. Check middleware
        $this->info('🔧 5. Middleware Check:');
        $middlewareGroups = config('middleware.web', []);
        $this->line('   Web middleware groups:');
        foreach ($middlewareGroups as $middleware) {
            if (strpos($middleware, 'locale') !== false || strpos($middleware, 'SetLocale') !== false) {
                $this->line('     ✅ ' . $middleware);
            }
        }
        $this->newLine();

        // 6. Laravel version check
        $this->info('📦 6. Laravel Version:');
        $this->line('   Laravel version: ' . app()->version());
        $this->line('   PHP version: ' . PHP_VERSION);
        $this->newLine();

        // 7. Recommendations
        $this->info('💡 7. Recommendations:');
        
        if (!Route::has('language.switch')) {
            $this->line('   ⚠️  Routes may not be loading correctly');
            $this->line('   Try adding this to your routes/web.php:');
            $this->line('   ```php');
            $this->line('   require vendor_path(\'angelito-systems/filament-tenancy/routes/tenant.php\');');
            $this->line('   ```');
        }
        
        if (!session('locale') && LanguageSwitcher::getCurrentLocale() === config('app.locale')) {
            $this->line('   ℹ️  Session locale is empty - this is normal on first visit');
        }
        
        $this->newLine();
        $this->info('🎯 Debug complete!');
        
        return 0;
    }
}
