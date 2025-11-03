<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\App;
use AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher;

class ClearLanguageSessionCommand extends Command
{
    protected $signature = 'filament-tenancy:clear-language-session';
    
    protected $description = 'Clear language session and reset to default locale';

    public function handle()
    {
        $this->info('🧹 Clearing Language Session');
        $this->newLine();

        // Show current state
        $this->info('📋 Current State:');
        $this->line('   Session Locale: ' . (Session::get('locale') ?: 'null'));
        $this->line('   App Locale: ' . App::getLocale());
        $this->line('   Config Locale: ' . config('app.locale'));
        $this->line('   Auto Detect Browser: ' . (config('filament-tenancy.localization.auto_detect', false) ? 'ENABLED' : 'DISABLED'));
        $this->line('   LanguageSwitcher Current: ' . LanguageSwitcher::getCurrentLocale());
        $this->newLine();

        // Clear session
        Session::forget('locale');
        $this->info('✅ Session locale cleared');

        // Reset to config default
        $defaultLocale = config('app.locale', 'en');
        App::setLocale($defaultLocale);
        $this->info("✅ App locale reset to: {$defaultLocale}");
        
        // Show new state
        $this->newLine();
        $this->info('📋 New State:');
        $this->line('   Session Locale: ' . (Session::get('locale') ?: 'null'));
        $this->line('   App Locale: ' . App::getLocale());
        $this->line('   Config Locale: ' . config('app.locale'));
        $this->line('   LanguageSwitcher Current: ' . LanguageSwitcher::getCurrentLocale());
        $this->newLine();

        // Test setting locales
        $this->info('🧪 Testing Language Switching:');
        
        // Test English
        $this->line('   Testing English...');
        LanguageSwitcher::setLocale('en');
        $this->line('     Session: ' . Session::get('locale'));
        $this->line('     App: ' . App::getLocale());
        $this->line('     Current: ' . LanguageSwitcher::getCurrentLocale());
        
        // Test Spanish
        $this->line('   Testing Spanish...');
        LanguageSwitcher::setLocale('es');
        $this->line('     Session: ' . Session::get('locale'));
        $this->line('     App: ' . App::getLocale());
        $this->line('     Current: ' . LanguageSwitcher::getCurrentLocale());
        
        // Reset to default
        LanguageSwitcher::setLocale($defaultLocale);
        $this->line("   Reset to default ({$defaultLocale}):");
        $this->line('     Session: ' . Session::get('locale'));
        $this->line('     App: ' . App::getLocale());
        $this->line('     Current: ' . LanguageSwitcher::getCurrentLocale());
        
        $this->newLine();
        $this->info('🎯 Language session cleared and tested!');
        
        return 0;
    }
}
