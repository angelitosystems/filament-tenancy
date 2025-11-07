<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\App;
use Illuminate\Support\Facades\Session;
use Symfony\Component\HttpFoundation\Response;

class SetLocale
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        // IMPORTANTE: Acceder a la sesión aquí asegura que se inicialize correctamente
        // Esto previene errores 419 (CSRF token mismatch) cuando el language switcher no está visible
        // El acceso a session() siempre inicializa la sesión si no está iniciada
        
        // Intentar obtener el locale de la sesión primero (si el usuario lo cambió manualmente)
        // Usar session() helper que maneja la inicialización automáticamente
        $sessionLocale = session('locale');
        
        // Si hay un locale en la sesión y es válido, usarlo
        if ($sessionLocale && $this->isValidLocale($sessionLocale)) {
            $locale = $sessionLocale;
        } else {
            // Si no hay locale en sesión, usar el config por defecto
            $locale = config('app.locale', 'en');
            
            // Verificar que el locale sea válido
            if (!$this->isValidLocale($locale)) {
                $locale = 'en'; // Fallback a inglés si no es válido
            }
        }
        
        // Establecer el locale en la aplicación
        App::setLocale($locale);

        return $next($request);
    }

    /**
     * Verificar si un locale es válido.
     */
    protected function isValidLocale(string $locale): bool
    {
        $availableLocales = array_keys(\AngelitoSystems\FilamentTenancy\Components\LanguageSwitcher::getAvailableLocales());
        return in_array($locale, $availableLocales);
    }

}
