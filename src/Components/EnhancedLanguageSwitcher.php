<?php

namespace AngelitoSystems\FilamentTenancy\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;
use Illuminate\Support\Facades\URL;

/**
 * Enhanced Language Switcher for Laravel 12 compatibility.
 * 
 * Provides multiple methods for language switching:
 * - Modal-based action
 * - Direct URL switching
 * - JavaScript-based switching
 * - Form-based switching
 */
class EnhancedLanguageSwitcher
{
    /**
     * Get the available languages.
     */
    public static function getAvailableLocales(): array
    {
        return [
            'en' => 'English',
            'es' => 'Español',
        ];
    }

    /**
     * Get the current locale.
     */
    public static function getCurrentLocale(): string
    {
        return Session::get('locale', config('app.locale', 'en'));
    }

    /**
     * Set the application locale with enhanced persistence.
     */
    public static function setLocale(string $locale): bool
    {
        if (self::isValidLocale($locale)) {
            Session::put('locale', $locale);
            App::setLocale($locale);
            
            // Also set cookie for better persistence
            cookie()->queue('locale', $locale, 525600); // 1 year
            
            return true;
        }
        return false;
    }

    /**
     * Check if a locale is valid.
     */
    public static function isValidLocale(string $locale): bool
    {
        return in_array($locale, array_keys(self::getAvailableLocales()));
    }

    /**
     * Create a direct URL for language switching.
     */
    public static function getSwitchUrl(string $locale): string
    {
        $currentUrl = URL::full();
        $separator = strpos($currentUrl, '?') !== false ? '&' : '?';
        
        return $currentUrl . $separator . 'lang=' . $locale;
    }

    /**
     * Create a JavaScript-based language switcher action.
     */
    public static function makeJsAction(): Action
    {
        return Action::make('switch_language')
            ->label(__('tenancy.switch_language'))
            ->icon('heroicon-o-language')
            ->color('gray')
            ->action(function () {
                // This will be handled by JavaScript
            })
            ->extraAttributes([
                'x-data' => '{ language: "' . self::getCurrentLocale() . '" }',
                'x-on:click' => <<<'JS'
                    const newLocale = language === 'en' ? 'es' : 'en';
                    const currentUrl = window.location.href.split('?')[0];
                    const params = new URLSearchParams(window.location.search);
                    params.set('lang', newLocale);
                    
                    // Update URL and reload
                    window.location.href = currentUrl + '?' + params.toString();
                JS,
            ]);
    }

    /**
     * Create a form-based language switcher.
     */
    public static function makeFormAction(): Action
    {
        return Action::make('switch_language_form')
            ->label(__('tenancy.switch_language'))
            ->icon('heroicon-o-language')
            ->form([
                Select::make('locale')
                    ->label(__('tenancy.language'))
                    ->options(self::getAvailableLocales())
                    ->default(self::getCurrentLocale())
                    ->required(),
            ])
            ->action(function (array $data) {
                self::setLocale($data['locale']);
                
                // Force page reload to apply language
                return redirect()->back();
            });
    }

    /**
     * Create dropdown menu items for user menu.
     */
    public static function makeUserMenuItems(): array
    {
        $currentLocale = self::getCurrentLocale();
        $items = [];

        foreach (self::getAvailableLocales() as $locale => $name) {
            if ($locale !== $currentLocale) {
                $items["language_{$locale}"] = \Filament\Navigation\MenuItem::make($name)
                    ->label($name)
                    ->icon('heroicon-o-language')
                    ->url(self::getSwitchUrl($locale));
            }
        }

        return $items;
    }

    /**
     * Handle language switching from URL parameter.
     */
    public static function handleUrlParameter(): void
    {
        $locale = request()->get('lang');
        
        if ($locale && self::isValidLocale($locale)) {
            self::setLocale($locale);
            
            // Redirect to clean URL without lang parameter
            $cleanUrl = request()->url();
            redirect($cleanUrl)->send();
            exit;
        }
    }

    /**
     * Get language switching JavaScript code.
     */
    public static function getJavaScript(): string
    {
        $currentLocale = self::getCurrentLocale();
        $switchUrl = route('language.switch', '__LOCALE__');
        
        return <<<JS
        function switchLanguage(locale) {
            // Show loading state
            document.body.style.opacity = '0.7';
            
            // Create form for POST request (more reliable)
            const form = document.createElement('form');
            form.method = 'POST';
            form.action = '{$switchUrl}'.replace('__LOCALE__', locale);
            
            // Add CSRF token
            const csrfToken = document.querySelector('meta[name="csrf-token"]');
            if (csrfToken) {
                const csrfInput = document.createElement('input');
                csrfInput.type = 'hidden';
                csrfInput.name = '_token';
                csrfInput.value = csrfToken.getAttribute('content');
                form.appendChild(csrfInput);
            }
            
            // Add locale input
            const localeInput = document.createElement('input');
            localeInput.type = 'hidden';
            localeInput.name = 'locale';
            localeInput.value = locale;
            form.appendChild(localeInput);
            
            // Submit form
            document.body.appendChild(form);
            form.submit();
        }
        
        // Add language switcher to page if not exists
        if (!document.getElementById('language-switcher')) {
            const div = document.createElement('div');
            div.id = 'language-switcher';
            div.style.cssText = 'position: fixed; top: 10px; right: 10px; z-index: 9999; background: white; padding: 10px; border-radius: 8px; box-shadow: 0 2px 10px rgba(0,0,0,0.1);';
            div.innerHTML = `
                <div style="display: flex; gap: 5px; align-items: center;">
                    <span style="font-size: 12px; color: #666;">Language:</span>
                    <button onclick="switchLanguage('en')" style="padding: 4px 8px; font-size: 12px; border: 1px solid #ddd; background: \${currentLocale === 'en' ? '#007bff' : 'white'}; color: \${currentLocale === 'en' ? 'white' : '#333'}; border-radius: 4px; cursor: pointer;">EN</button>
                    <button onclick="switchLanguage('es')" style="padding: 4px 8px; font-size: 12px; border: 1px solid #ddd; background: \${currentLocale === 'es' ? '#007bff' : 'white'}; color: \${currentLocale === 'es' ? 'white' : '#333'}; border-radius: 4px; cursor: pointer;">ES</button>
                </div>
            `;
            document.body.appendChild(div);
        }
        JS;
    }
}
