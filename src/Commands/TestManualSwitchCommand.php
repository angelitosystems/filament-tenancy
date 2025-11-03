<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

class TestManualSwitchCommand extends Command
{
    protected $signature = 'filament-tenancy:test-manual-switch {locale?}';
    
    protected $description = 'Test manual language switching';

    public function handle()
    {
        $locale = $this->argument('locale');
        
        $this->info('🧪 Testing Manual Language Switch');
        $this->newLine();

        // Show current state
        $this->info('📋 Current State:');
        $this->line('   Session Locale: ' . (Session::get('locale') ?: 'null'));
        $this->line('   App Locale: ' . App::getLocale());
        $this->line('   Config APP_LOCALE: ' . config('app.locale'));
        $this->line('   Package Default: ' . config('filament-tenancy.localization.default_locale'));
        $this->line('   LanguageSwitcher Current: ' . LanguageSwitcher::getCurrentLocale());
        $this->newLine();

        if (!$locale) {
            $locale = $this->choice('Select language to switch to:', ['en', 'es']);
        }

        // Test manual switch
        $this->info("🔄 Switching to: {$locale}");
        
        $success = LanguageSwitcher::setLocale($locale);
        
        if ($success) {
            $this->line("   ✅ LanguageSwitcher::setLocale('{$locale}') returned TRUE");
        } else {
            $this->line("   ❌ LanguageSwitcher::setLocale('{$locale}') returned FALSE");
        }

        // Show new state
        $this->newLine();
        $this->info('📋 New State After Switch:');
        $this->line('   Session Locale: ' . (Session::get('locale') ?: 'null'));
        $this->line('   App Locale: ' . App::getLocale());
        $this->line('   LanguageSwitcher Current: ' . LanguageSwitcher::getCurrentLocale());
        $this->newLine();

        // Test persistence
        $this->info('🔄 Testing Persistence (simulating new request):');
        
        // Clear app locale to simulate fresh request
        App::setLocale(config('app.locale', 'en'));
        $this->line('   Reset App locale to: ' . App::getLocale());
        
        // Get current locale again (should read from session)
        $persistedLocale = LanguageSwitcher::getCurrentLocale();
        $this->line('   LanguageSwitcher::getCurrentLocale(): ' . $persistedLocale);
        
        if ($persistedLocale === $locale) {
            $this->line('   ✅ User choice PERSISTED correctly');
        } else {
            $this->line('   ❌ User choice NOT persisted - reverted to: ' . $persistedLocale);
        }

        $this->newLine();
        $this->info('💡 Instructions for Web Testing:');
        $this->line('1. Go to your Filament panel');
        $this->line('2. Click on your user menu (top right)');
        $this->line('3. Click on the language switcher');
        $this->line('4. Check storage/logs/laravel.log for debug info');
        
        return 0;
    }
}
