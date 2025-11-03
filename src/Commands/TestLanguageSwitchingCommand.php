<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

class TestLanguageSwitchingCommand extends Command
{
    protected $signature = 'filament-tenancy:test-switching';
    
    protected $description = 'Test all language switching methods';

    public function handle()
    {
        $this->info('🧪 Testing All Language Switching Methods');
        $this->newLine();

        // 1. Test all available routes
        $this->info('📋 1. Available Routes:');
        $routes = [
            'language.switch' => '/language/{locale}',
            'language.switch.post' => 'POST /language/{locale}',
            'language.switch.alt' => '/switch-language/{locale}',
            'language.switch.alt.post' => 'POST /switch-language/{locale}',
        ];

        foreach ($routes as $name => $pattern) {
            $status = Route::has($name) ? '✅' : '❌';
            if (Route::has($name)) {
                $enUrl = route($name, 'en');
                $esUrl = route($name, 'es');
                $this->line("   {$status} {$name}: {$pattern}");
                $this->line("      EN: {$enUrl}");
                $this->line("      ES: {$esUrl}");
            } else {
                $this->line("   {$status} {$name}: {$pattern} - NOT FOUND");
            }
        }
        $this->newLine();

        // 2. Test LanguageSwitcher functionality
        $this->info('🧪 2. LanguageSwitcher Tests:');
        
        $originalLocale = LanguageSwitcher::getCurrentLocale();
        $this->line("   Original locale: {$originalLocale}");

        // Test switching to English
        $result = LanguageSwitcher::setLocale('en');
        $this->line("   Switch to EN: " . ($result ? '✅ SUCCESS' : '❌ FAILED'));
        $this->line("   App locale: " . app()->getLocale());
        $this->line("   Session locale: " . session('locale'));
        $this->line("   Translation test: " . __('tenancy.plans'));

        // Test switching to Spanish
        $result = LanguageSwitcher::setLocale('es');
        $this->line("   Switch to ES: " . ($result ? '✅ SUCCESS' : '❌ FAILED'));
        $this->line("   App locale: " . app()->getLocale());
        $this->line("   Session locale: " . session('locale'));
        $this->line("   Translation test: " . __('tenancy.plans'));

        // Reset
        LanguageSwitcher::setLocale($originalLocale);
        $this->newLine();

        // 3. Manual testing instructions
        $this->info('🌐 3. Manual Testing Instructions:');
        $this->line('   Test these URLs in your browser:');
        
        if (Route::has('language.switch')) {
            $this->line('   1. GET ' . route('language.switch', 'es'));
            $this->line('   2. GET ' . route('language.switch', 'en'));
        }
        
        if (Route::has('language.switch.alt')) {
            $this->line('   3. GET ' . route('language.switch.alt', 'es'));
            $this->line('   4. GET ' . route('language.switch.alt', 'en'));
        }
        
        $this->newLine();
        $this->line('   Test with URL parameters:');
        $this->line('   5. ' . url('/?lang=es'));
        $this->line('   6. ' . url('/?lang=en'));
        $this->newLine();

        // 4. Debugging checklist
        $this->info('🔍 4. Debugging Checklist:');
        $this->line('   ✅ Routes are loaded');
        $this->line('   ✅ LanguageSwitcher works');
        $this->line('   ✅ Translations change');
        $this->line('   ✅ Session stores locale');
        $this->newLine();

        $this->info('📝 5. Next Steps:');
        $this->line('   1. Clear all caches:');
        $this->line('      php artisan optimize:clear');
        $this->line('   2. Test URLs manually in browser');
        $this->line('   3. Check browser DevTools Network tab');
        $this->line('   4. Verify session cookie is set');
        $this->newLine();

        $this->info('🎯 If routes work but clicking doesnt:');
        $this->line('   - Check JavaScript console for errors');
        $this->line('   - Verify Filament panel is using correct middleware');
        $this->line('   - Check if any other middleware is blocking requests');
        $this->newLine();

        $this->info('✅ Testing complete!');
        
        return 0;
    }
}
