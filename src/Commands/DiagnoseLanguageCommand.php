<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Route;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;
use AngelitoSystems\FilamentTenancy\Middleware\SetLocale;

class DiagnoseLanguageCommand extends Command
{
    protected $signature = 'filament-tenancy:diagnose-language';
    
    protected $description = 'Diagnose language switching issues';

    public function handle()
    {
        $this->info('🔍 Diagnosing Filament Tenancy Language System');
        $this->newLine();

        // 1. Check .env configuration
        $this->info('📋 1. Environment Configuration:');
        $this->line('   APP_LOCALE: ' . config('app.locale'));
        $this->line('   APP_FALLBACK_LOCALE: ' . config('app.fallback_locale'));
        $this->line('   TENANCY_SHOW_LANGUAGE_SWITCHER: ' . config('filament-tenancy.localization.show_language_switcher', 'default true'));
        $this->newLine();

        // 2. Check current locale
        $this->info('🌐 2. Current Locale Status:');
        $this->line('   App Locale: ' . app()->getLocale());
        $this->line('   Session Locale: ' . session('locale', 'null'));
        $this->line('   LanguageSwitcher Current: ' . LanguageSwitcher::getCurrentLocale());
        $this->newLine();

        // 3. Check translation files
        $this->info('📁 3. Translation Files:');
        $esPath = resource_path('lang/es/tenancy.php');
        $enPath = resource_path('lang/en/tenancy.php');
        
        $this->line('   Spanish translations: ' . (file_exists($esPath) ? '✅ EXISTS' : '❌ MISSING'));
        $this->line('   English translations: ' . (file_exists($enPath) ? '✅ EXISTS' : '❌ MISSING'));
        $this->newLine();

        // 4. Test translations
        $this->info('🧪 4. Translation Tests:');
        $tests = [
            'tenancy.plans' => __('tenancy.plans'),
            'tenancy.name' => __('tenancy.name'),
            'tenancy.switch_language' => __('tenancy.switch_language'),
            'tenancy.language' => __('tenancy.language'),
        ];

        foreach ($tests as $key => $result) {
            $status = ($result !== $key) ? '✅' : '❌';
            $this->line("   {$status} {$key}: \"{$result}\"");
        }
        $this->newLine();

        // 5. Test language switching
        $this->info('🔄 5. Language Switching Test:');
        
        // Test English
        LanguageSwitcher::setLocale('en');
        $enResult = __('tenancy.plans');
        $this->line("   English test: \"{$enResult}\" " . ($enResult === 'Plans' ? '✅' : '❌'));
        
        // Test Spanish
        LanguageSwitcher::setLocale('es');
        $esResult = __('tenancy.plans');
        $this->line("   Spanish test: \"{$esResult}\" " . ($esResult === 'Planes' ? '✅' : '❌'));
        
        // Reset to original
        $originalLocale = LanguageSwitcher::getCurrentLocale();
        LanguageSwitcher::setLocale($originalLocale);
        $this->newLine();

        // 6. Check routes
        $this->info('🛣️  6. Routes Check:');
        if (Route::has('language.switch')) {
            $this->line('   ✅ language.switch route exists');
            $this->line('   English URL: ' . route('language.switch', 'en'));
            $this->line('   Spanish URL: ' . route('language.switch', 'es'));
        } else {
            $this->line('   ❌ language.switch route NOT found');
        }
        $this->newLine();

        // 7. Available locales
        $this->info('🌍 7. Available Locales:');
        $locales = LanguageSwitcher::getAvailableLocales();
        foreach ($locales as $code => $name) {
            $current = LanguageSwitcher::getCurrentLocale();
            $marker = ($code === $current) ? ' ← CURRENT' : '';
            $this->line("   {$code}: {$name}{$marker}");
        }
        $this->newLine();

        // 8. Recommendations
        $this->info('💡 8. Recommendations:');
        
        if (!file_exists($esPath) || !file_exists($enPath)) {
            $this->line('   ⚠️  Run: php artisan filament-tenancy:publish --lang');
        }
        
        if (session('locale') && session('locale') !== config('app.locale')) {
            $this->line('   ℹ️  Session locale differs from .env - this is normal after language switch');
        }
        
        if (config('app.locale') !== 'en' && config('app.locale') !== 'es') {
            $this->line('   ⚠️  APP_LOCALE should be "en" or "es"');
        }
        
        $this->newLine();
        $this->info('🎯 Diagnosis complete!');
        
        return 0;
    }
}
