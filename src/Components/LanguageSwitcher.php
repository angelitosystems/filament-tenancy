<?php

namespace AngelitoSystems\FilamentTenancy\Components;

use Filament\Actions\Action;
use Filament\Forms\Components\Select;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Request;

/**
 * Componente para gestionar el locale de la aplicación mediante rutas.
 * 
 * Proporciona:
 * - Locales disponibles con getAvailableLocales()
 * - Locale actual con getCurrentLocale()
 * - Cambio de locale con setLocale($locale) cuando es válido
 * - Integración con Filament para acciones y select components
 * 
 * Implementado en tenant.php para ruta /language/{locale}
 */
class LanguageSwitcher
{
    /**
     * Get the available languages.
     * 
     * @return array Array de locales disponibles con sus nombres
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
     * 
     * @return string Locale actual de la aplicación
     */
    public static function getCurrentLocale(): string
    {
        // Priority: 
        // 1. Session (user manual selection) - HIGHEST PRIORITY
        // 2. App current locale (what Laravel is using now)
        // 3. Package default locale (independent from APP_LOCALE)
        // 4. App config locale (from .env)
        // 5. Fallback to 'en'
        
        $locale = Session::get('locale');
        
        if (!$locale) {
            $locale = App::getLocale();
        }
        
        if (!$locale || !self::isValidLocale($locale)) {
            // Try package default first
            $locale = config('filament-tenancy.localization.default_locale', 'en');
        }
        
        if (!$locale || !self::isValidLocale($locale)) {
            // Fall back to app locale
            $locale = config('app.locale', 'en');
        }
        
        // Final fallback
        if (!self::isValidLocale($locale)) {
            $locale = 'en';
        }
        
        return $locale;
    }

    /**
     * Set the application locale.
     * 
     * @param string $locale Locale a establecer
     * @return bool True si se estableció correctamente, false si no es válido
     */
    public static function setLocale(string $locale): bool
    {
        if (self::isValidLocale($locale)) {
            // Debug: Log the session save attempt
            \Log::info('LanguageSwitcher::setLocale called', [
                'requested_locale' => $locale,
                'session_before' => Session::get('locale'),
                'session_id' => Session::getId(),
                'session_driver' => config('session.driver')
            ]);
            
            Session::put('locale', $locale);
            Session::save(); // Force session save
            
            // Also save in cookie as backup (in case session is lost due to tenant switching)
            cookie()->queue('locale', $locale, 525600); // 1 year
            
            App::setLocale($locale);
            
            // Debug: Verify session was saved
            \Log::info('LanguageSwitcher::setLocale completed', [
                'session_after' => Session::get('locale'),
                'app_locale_after' => App::getLocale()
            ]);
            
            return true;
        }
        
        \Log::warning('LanguageSwitcher::setLocale failed - invalid locale', ['locale' => $locale]);
        return false;
    }

    /**
     * Check if a locale is valid and available.
     * 
     * @param string $locale Locale a validar
     * @return bool True si el locale es válido
     */
    public static function isValidLocale(string $locale): bool
    {
        return in_array($locale, array_keys(self::getAvailableLocales()));
    }

    /**
     * Get locale name from locale code.
     * 
     * @param string $locale Código del locale
     * @return string Nombre del locale
     */
    public static function getLocaleName(string $locale): string
    {
        return self::getAvailableLocales()[$locale] ?? $locale;
    }

    /**
     * Get all locales except the current one.
     * 
     * @return array Locales disponibles excepto el actual
     */
    public static function getOtherLocales(): array
    {
        $current = self::getCurrentLocale();
        $locales = self::getAvailableLocales();
        unset($locales[$current]);
        return $locales;
    }

    /**
     * Create a language switcher action for Filament.
     */
    public static function makeAction(): Action
    {
        return Action::make('switch_language')
            ->label(__('tenancy.switch_language'))
            ->icon('heroicon-o-language')
            ->schema([
                Select::make('locale')
                    ->label(__('tenancy.language'))
                    ->options(self::getAvailableLocales())
                    ->default(self::getCurrentLocale())
                    ->required(),
            ])
            ->action(function (array $data) {
                self::setLocale($data['locale']);
            });
    }

    /**
     * Create a language switcher select component.
     */
    public static function makeSelect(): Select
    {
        return Select::make('locale')
            ->label(__('tenancy.language'))
            ->options(self::getAvailableLocales())
            ->default(self::getCurrentLocale())
            ->live()
            ->afterStateUpdated(function (string $state) {
                self::setLocale($state);
            });
    }

    /**
     * Get language switcher URL for a specific locale.
     * 
     * @param string $locale Locale target
     * @return string URL para cambiar al locale especificado
     */
    public static function getSwitchUrl(string $locale): string
    {
        return route('language.switch', $locale);
    }

    /**
     * Detect locale from browser Accept-Language header.
     * 
     * @param \Illuminate\Http\Request|null $request Request object (optional)
     * @return string|null Locale detectado o null
     */
    public static function detectBrowserLocale($request = null): ?string
    {
        $request = $request ?? request();
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            if (preg_match('/^([a-z]{1,2}(?:-[A-Z]{2})?)(?:;q=([0-9.]+))?$/', $part, $matches)) {
                $lang = $matches[1];
                $quality = isset($matches[2]) ? (float) $matches[2] : 1.0;
                $languages[$lang] = $quality;
            }
        }

        // Sort by quality
        arsort($languages);

        // Find first matching available locale
        foreach (array_keys($languages) as $lang) {
            // Check exact match
            if (self::isValidLocale($lang)) {
                return $lang;
            }
            
            // Check language part only (e.g., 'en' from 'en-US')
            $langPart = explode('-', $lang)[0];
            if (self::isValidLocale($langPart)) {
                return $langPart;
            }
        }

        return null;
    }
}
