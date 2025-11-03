<?php

namespace AngelitoSystems\FilamentTenancy\Middleware;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Closure;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken as Middleware;
use Illuminate\Http\Request;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\Support\Facades\Config;
use Illuminate\Support\Facades\Cookie;
use Symfony\Component\HttpFoundation\Response;

class TenantSessionMiddleware
{
    /**
     * Handle an incoming request.
     */
    public function handle(Request $request, Closure $next): Response
    {
        $tenant = Tenancy::current();
        
        if ($tenant) {
            // Configure session for tenant context
            $this->configureTenantSession($tenant);
        }

        return $next($request);
    }

    /**
     * Configure session settings for tenant context.
     */
    protected function configureTenantSession($tenant): void
    {
        $sessionDriver = config('session.driver');
        $sessionLifetime = config('session.lifetime', 120);
        
        // Modify session configuration for tenant
        switch ($sessionDriver) {
            case 'database':
                // Ensure session table exists for tenant
                $this->ensureSessionTableExists($tenant);
                
                // Set tenant-specific session table if needed
                Config::set('session.table', 'sessions');
                break;
                
            case 'file':
                // Use tenant-specific session path
                $tenantSessionPath = storage_path("framework/sessions/tenant_{$tenant->id}");
                Config::set('session.files', $tenantSessionPath);
                break;
                
            case 'redis':
                // Use tenant-specific Redis prefix
                Config::set('session.prefix', "tenant_{$tenant->id}_");
                break;
        }

        // Set tenant-specific cookie domain/path if needed
        $cookieDomain = $tenant->domain ?? null;
        if ($cookieDomain) {
            Config::set('session.domain', $cookieDomain);
        }

        // Configure session cookie settings to prevent 419 errors
        $cookieConfig = config('filament-tenancy.session.cookie', []);
        if (!empty($cookieConfig['domain'])) {
            Config::set('session.domain', $cookieConfig['domain']);
        }
        if (!empty($cookieConfig['secure'])) {
            Config::set('session.secure', $cookieConfig['secure']);
        }
        if (!empty($cookieConfig['same_site'])) {
            Config::set('session.same_site', $cookieConfig['same_site']);
        }

        // Ensure session cookie is accessible across subdomains if needed
        if (config('filament-tenancy.session.cross_subdomain', false)) {
            $host = request()->getHost();
            if (!str_contains($host, '.')) {
                // For subdomains like tenant1.localhost, set domain to .localhost
                Config::set('session.domain', '.' . $host);
            } else {
                // For domains like tenant1.example.com, set domain to .example.com
                $parts = explode('.', $host);
                if (count($parts) > 2) {
                    array_shift($parts);
                    Config::set('session.domain', '.' . implode('.', $parts));
                }
            }
        }
    }

    /**
     * Ensure the sessions table exists in the tenant database.
     */
    protected function ensureSessionTableExists($tenant): void
    {
        // Check if sessions table exists in current tenant connection
        try {
            $connection = Tenancy::database()->getCurrentTenantConnection();
            if (!$connection) {
                return;
            }

            $tableExists = \Illuminate\Support\Facades\Schema::connection($connection)
                ->hasTable('sessions');

            if (!$tableExists) {
                // Create sessions table for tenant
                $this->createSessionsTable($connection);
            }
        } catch (\Exception $e) {
            // Log error but don't break the request
            DebugHelper::warning(
                "Failed to ensure sessions table for tenant {$tenant->id}: {$e->getMessage()}"
            );
        }
    }

    /**
     * Create the sessions table in the tenant database.
     */
    protected function createSessionsTable(string $connection): void
    {
        \Illuminate\Support\Facades\Schema::connection($connection)->create('sessions', function ($table) {
            $table->string('id')->primary();
            $table->foreignId('user_id')->nullable()->index();
            $table->string('ip_address', 45)->nullable();
            $table->text('user_agent')->nullable();
            $table->text('payload');
            $table->integer('last_activity')->index();
        });
    }
}
