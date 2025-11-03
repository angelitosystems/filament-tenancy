<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // Priority order for locale detection:
        // 1. Session locale (USER MANUAL SELECTION - HIGHEST PRIORITY)
        // 2. User preference (if authenticated)
        // 3. Browser accept-language header (if enabled)
        // 4. Package default locale
        // 5. App default locale

        $locale = Session::get('locale');

        // Also check cookies as backup (in case session is affected by tenant switching)
        if (!$locale) {
            $locale = request()->cookie('locale');
            if ($locale) {
                Log::info('SetLocale: Using cookie locale (session lost)', ['cookie_locale' => $locale]);
                // Restore to session
                Session::put('locale', $locale);
            }
        }

        // If we have a session locale, use it immediately (respect user choice)
        if ($locale && in_array($locale, array_keys(\AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher::getAvailableLocales()))) {
            Log::info('SetLocale: Using session locale', ['session_locale' => $locale]);
            App::setLocale($locale);
            return $next($request);
        }

        if (!$locale && Auth::check()) {
            // Get user preferred locale if stored
            $locale = Auth::user()->locale ?? null;
        }

        // Only detect from browser if auto_detect is enabled
        $autoDetect = config('filament-tenancy.localization.auto_detect', false);
        
        if (!$locale && $autoDetect) {
            $locale = $this->detectBrowserLocale($request);
        }

        // If no locale found, use package default, then app default as last resort
        if (!$locale) {
            // Use package default locale first (independent from APP_LOCALE)
            $locale = config('filament-tenancy.localization.default_locale', 'en');
            
            // If package default is not available, fall back to app locale
            if (!$locale || !in_array($locale, array_keys(\AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher::getAvailableLocales()))) {
                $locale = config('app.locale', 'en');
            }
        }

        // Ensure the locale is valid, otherwise use 'en' as fallback
        $availableLocales = array_keys(\AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher::getAvailableLocales());
        if (!in_array($locale, $availableLocales)) {
            $locale = 'en'; // Fallback to English if locale is not available
        }

        // Always set the locale
        Log::info('SetLocale: Final locale decision', [
            'final_locale' => $locale,
            'session_before' => Session::get('locale'),
            'app_locale_config' => config('app.locale'),
            'package_default' => config('filament-tenancy.localization.default_locale')
        ]);
        
        App::setLocale($locale);
        Session::put('locale', $locale);

        return $next($request);
    }

    /**
     * Detect locale from browser Accept-Language header.
     */
    protected function detectBrowserLocale(Request $request): ?string
    {
        $acceptLanguage = $request->header('Accept-Language');
        
        if (!$acceptLanguage) {
            return null;
        }

        // Parse Accept-Language header
        $languages = [];
        $parts = explode(',', $acceptLanguage);
        
        foreach ($parts as $part) {
            $part = trim($part);
            $partParts = explode(';q=', $part);
            $lang = trim($partParts[0]);
            $priority = isset($partParts[1]) ? (float) $partParts[1] : 1.0;
            $languages[$lang] = $priority;
        }

        // Sort by priority
        arsort($languages);

        $availableLocales = array_keys(\AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher::getAvailableLocales());

        // Find first matching locale
        foreach (array_keys($languages) as $lang) {
            // Check exact match
            if (in_array($lang, $availableLocales)) {
                return $lang;
            }
            
            // Check language part (e.g., 'en-US' -> 'en')
            $langPart = explode('-', $lang)[0];
            if (in_array($langPart, $availableLocales)) {
                return $langPart;
            }
        }

        return null;
    }
}
