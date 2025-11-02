# Plugins Usage Guide - Filament Tenancy

This guide explains how to use Filament Tenancy plugins to configure central administration panels (landlord) and tenant panels.

## Introduction

Filament Tenancy provides two main plugins to integrate multi-tenancy with Filament:

- **TenancyLandlordPlugin**: For the central administration panel (admin/landlord)
- **TenancyTenantPlugin**: For tenant panels

These plugins automatically configure the necessary middlewares and access restrictions to ensure security and isolation between tenants.

## Prerequisites

Before using the plugins, make sure you have:

1. ✅ Filament installed (`composer require filament/filament:"^4.0"`)
2. ✅ Run `php artisan filament-tenancy:install`
3. ✅ Have at least one Filament panel created

## TenancyLandlordPlugin

The landlord plugin is used for the central administration panel where all tenants are managed.

### Features

- ✅ Access only from central domains without active tenant
- ✅ `PreventTenantAccess` middleware to block access from tenant context
- ✅ Automatic connection to central database (landlord)
- ✅ Automatic registration of `TenantResource` to manage tenants

### Basic Configuration

```php
// app/Providers/Filament/AdminPanelProvider.php
<?php

namespace App\Providers\Filament;

use AngelitoSystems\FilamentTenancy\FilamentPlugins\TenancyLandlordPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class AdminPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('admin')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Blue,
            ])
            ->discoverResources(in: app_path('Filament/Resources'), for: 'App\\Filament\\Resources')
            ->discoverPages(in: app_path('Filament/Pages'), for: 'App\\Filament\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Widgets'), for: 'App\\Filament\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(TenancyLandlordPlugin::make()); // 👈 Landlord plugin
    }
}
```

### Included Middlewares

The plugin automatically adds these middlewares:

1. **InitializeTenancy**: Resolves tenant from domain/subdomain
2. **PreventTenantAccess**: Blocks access if there's an active tenant (ensures access only from central domains)

**⚠️ Important**: These middlewares only apply to Filament panel routes. If you have routes outside Filament (API, normal web routes, etc.) that need tenant resolution, you must register `InitializeTenancy` in `bootstrap/app.php`. However, if you only use Filament panels, the middleware in `bootstrap/app.php` is optional.

### Access Restrictions

The admin/landlord panel has the following restrictions:

- ❌ **CANNOT be accessed** from a tenant domain (e.g., `tenant1.example.com`)
- ✅ **CAN be accessed** from central domains (e.g., `app.example.com`, `admin.example.com`)
- ❌ **Automatically blocks** access if a tenant is resolved (returns 403)

### Advanced Customization

```php
TenancyLandlordPlugin::make()
    ->autoRegister(false) // Disable automatic resource registration
    ->middleware([
        // Add additional middlewares
        YourCustomMiddleware::class,
    ])
    ->resources([
        // Add additional resources
        YourResource::class,
    ])
    ->pages([
        // Add additional pages
        YourPage::class,
    ]);
```

## TenancyTenantPlugin

The tenant plugin is used for individual tenant panels.

### Features

- ✅ Access only when there's an active and resolved tenant
- ✅ `PreventLandlordAccess` middleware to block access without tenant
- ✅ Automatic connection to tenant database
- ✅ Dynamic branding based on tenant name

### Basic Configuration

```php
// app/Providers/Filament/TenantPanelProvider.php
<?php

namespace App\Providers\Filament;

use AngelitoSystems\FilamentTenancy\FilamentPlugins\TenancyTenantPlugin;
use Filament\Http\Middleware\Authenticate;
use Filament\Http\Middleware\DisableBladeIconComponents;
use Filament\Http\Middleware\DispatchServingFilamentEvent;
use Filament\Pages;
use Filament\Panel;
use Filament\PanelProvider;
use Filament\Widgets;
use Illuminate\Cookie\Middleware\AddQueuedCookiesToResponse;
use Illuminate\Cookie\Middleware\EncryptCookies;
use Illuminate\Foundation\Http\Middleware\VerifyCsrfToken;
use Illuminate\Routing\Middleware\SubstituteBindings;
use Illuminate\Session\Middleware\AuthenticateSession;
use Illuminate\Session\Middleware\StartSession;
use Illuminate\View\Middleware\ShareErrorsFromSession;

class TenantPanelProvider extends PanelProvider
{
    public function panel(Panel $panel): Panel
    {
        return $panel
            ->id('tenant')
            ->path('admin')
            ->login()
            ->colors([
                'primary' => \Filament\Support\Colors\Color::Green,
            ])
            ->discoverResources(in: app_path('Filament/Tenant/Resources'), for: 'App\\Filament\\Tenant\\Resources')
            ->discoverPages(in: app_path('Filament/Tenant/Pages'), for: 'App\\Filament\\Tenant\\Pages')
            ->pages([
                Pages\Dashboard::class,
            ])
            ->discoverWidgets(in: app_path('Filament/Tenant/Widgets'), for: 'App\\Filament\\Tenant\\Widgets')
            ->widgets([
                Widgets\AccountWidget::class,
            ])
            ->middleware([
                EncryptCookies::class,
                AddQueuedCookiesToResponse::class,
                StartSession::class,
                AuthenticateSession::class,
                ShareErrorsFromSession::class,
                VerifyCsrfToken::class,
                SubstituteBindings::class,
                DisableBladeIconComponents::class,
                DispatchServingFilamentEvent::class,
            ])
            ->authMiddleware([
                Authenticate::class,
            ])
            ->plugin(TenancyTenantPlugin::make()); // 👈 Tenant plugin
    }
}
```

### Included Middlewares

The plugin automatically adds these middlewares:

1. **InitializeTenancy**: Resolves tenant from domain/subdomain
2. **EnsureTenantAccess**: Verifies that the tenant is active and not expired
3. **PreventLandlordAccess**: Blocks access if there's no active tenant

**⚠️ Important**: These middlewares only apply to Filament panel routes. If you have routes outside Filament (API, normal web routes, etc.) that need tenant resolution, you must register `InitializeTenancy` in `bootstrap/app.php`. However, if you only use Filament panels, the middleware in `bootstrap/app.php` is optional.

### Access Restrictions

The tenant panel has the following restrictions:

- ❌ **CANNOT be accessed** from central domains without tenant (e.g., `app.example.com`)
- ✅ **CAN be accessed** from tenant domains (e.g., `tenant1.example.com`)
- ❌ **Automatically blocks** access if no tenant is resolved (returns 403)
- ❌ **Automatically blocks** access if tenant is inactive or expired (returns 403)

### Advanced Customization

```php
TenancyTenantPlugin::make()
    ->autoRegister(false) // Disable automatic resource registration
    ->middleware([
        // Add additional middlewares
        YourCustomMiddleware::class,
    ])
    ->excludeResources([
        // Exclude specific resources from tenant context
        SomeResource::class,
    ])
    ->excludePages([
        // Exclude specific pages from tenant context
        SomePage::class,
    ]);
```

## Panel ID Configuration

Plugins are automatically registered according to configured panel IDs. By default:

- **Landlord Panel ID**: `admin`
- **Tenant Panel ID**: `tenant`

You can change these values in `config/filament-tenancy.php`:

```php
'filament' => [
    'auto_register_plugins' => true,
    'landlord_panel_id' => 'admin',    // Change if your admin panel has a different ID
    'tenant_panel_id' => 'tenant',      // Change if your tenant panel has a different ID
    'tenant_panel_path' => '/admin',
],
```

If you have different IDs, update the configuration before using the plugins.

## Middleware in bootstrap/app.php

### Is It Necessary?

**Short answer**: It depends on whether you have routes outside Filament.

### When it's NOT necessary

If you **only use Filament panels** (no API routes, normal web routes, etc.), you **DO NOT need** to register `InitializeTenancy` in `bootstrap/app.php` because the plugins already include it automatically for their routes.

### When it IS necessary

If you have routes outside Filament that need tenant resolution, you **DO need** to register the middleware in `bootstrap/app.php`:

**Examples of routes that need global middleware:**
- API routes (`routes/api.php`)
- Normal web routes (`routes/web.php`) that are not Filament
- Custom authentication routes
- Webhooks or external callbacks

**Example registration in `bootstrap/app.php` (Laravel 11):**

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->web(append: [
        \AngelitoSystems\FilamentTenancy\Middleware\InitializeTenancy::class,
    ]);
    
    $middleware->api(append: [
        \AngelitoSystems\FilamentTenancy\Middleware\InitializeTenancy::class,
    ]);
})
```

### Summary

| Scenario | Middleware in bootstrap/app.php? |
|----------|----------------------------------|
| Only Filament panels | ❌ Not necessary (plugins include it) |
| Filament panels + API | ✅ Necessary for API routes |
| Filament panels + web routes | ✅ Necessary for web routes |
| Only routes outside Filament | ✅ Necessary |

## Access Flow

### Admin/Landlord Panel

```
User accesses: app.example.com/admin
     ↓
InitializeTenancy resolves domain
     ↓
Is central domain? → YES
     ↓
Is there active tenant? → NO (it's a central domain)
     ↓
PreventTenantAccess allows access
     ↓
✅ Access to admin panel
```

### Tenant Panel

```
User accesses: tenant1.example.com/admin
     ↓
InitializeTenancy resolves domain
     ↓
Is central domain? → NO
     ↓
Resolves tenant from domain/subdomain
     ↓
Tenant found? → YES
     ↓
Is tenant active? → YES (EnsureTenantAccess)
     ↓
Is there active tenant? → YES (PreventLandlordAccess)
     ↓
✅ Access to tenant panel
```

## Use Case Examples

### Case 1: Admin Panel from Central Domain

```php
// URL: https://app.example.com/admin
// Result: ✅ Access allowed to admin panel
// Context: Central database (landlord)
```

### Case 2: Tenant Panel from Tenant Domain

```php
// URL: https://acme.example.com/admin
// Result: ✅ Access allowed to tenant panel
// Context: "acme" tenant database
```

### Case 3: Attempt to Access Admin from Tenant Domain

```php
// URL: https://acme.example.com/admin (attempting to access admin panel)
// Result: ❌ Error 403 - Access denied
// Reason: PreventTenantAccess detects active tenant and blocks
```

### Case 4: Attempt to Access Tenant from Central Domain

```php
// URL: https://app.example.com/admin (attempting to access tenant panel)
// Result: ❌ Error 403 - Access denied
// Reason: PreventLandlordAccess detects no active tenant
```

## Installation Verification

The `php artisan filament-tenancy:install` command automatically verifies:

- ✅ Existence of Filament panels
- ✅ Correct plugin configuration
- ✅ Panel IDs matching configuration
- ✅ Security restrictions applied

If it detects problems, it will show warning messages with instructions to fix them.

## Troubleshooting

### Error: "Access denied: Admin panel cannot be accessed from tenant context"

**Cause**: You're trying to access the admin panel from a tenant domain.

**Solution**: 
- Access the admin panel from a central domain (e.g., `app.example.com/admin`)
- Or configure the tenant panel if you want to access from the tenant domain

### Error: "Access denied: Tenant panel requires an active tenant context"

**Cause**: You're trying to access the tenant panel from a central domain without tenant.

**Solution**:
- Access the tenant panel from a tenant domain (e.g., `tenant1.example.com/admin`)
- Make sure the tenant exists and is active in the database

### Plugins don't register automatically

**Cause**: Panel IDs don't match the configuration.

**Solution**:
1. Check your panel IDs in PanelProviders
2. Update `config/filament-tenancy.php` with the correct IDs:
   ```php
   'landlord_panel_id' => 'your-admin-id',
   'tenant_panel_id' => 'your-tenant-id',
   ```
3. Or register plugins manually in each PanelProvider

### Panel shows data from wrong tenant

**Cause**: The `InitializeTenancy` middleware is resolving a different tenant than expected.

**Solution**:
1. Check `central_domains` configuration in `config/filament-tenancy.php`
2. Make sure `APP_DOMAIN` is correctly configured in `.env`
3. Verify that tenants have correct domains/subdomains in the database

## Best Practices

1. **Resource Separation**: Keep landlord and tenant resources in separate directories:
   - `app/Filament/Resources/` → Admin panel resources
   - `app/Filament/Tenant/Resources/` → Tenant panel resources

2. **Models with Traits**: Use the correct traits in your models:
   - `BelongsToTenant` for tenant models
   - `UsesLandlordConnection` for central models

3. **Domain Configuration**: Configure central domains correctly:
   ```php
   'central_domains' => [
       'app.example.com',
       'admin.example.com',
       env('APP_DOMAIN', 'localhost'),
   ],
   ```

4. **Testing**: Always test both panels from their corresponding domains to verify restrictions.

## Additional Resources

- [README.md](../../README.md) - General package documentation
- [TECHNICAL.md](TECHNICAL.md) - Technical documentation and architecture
- [Configuration](../../config/filament-tenancy.php) - Configuration file with available options

## Support

If you encounter problems or have questions:

- Open an issue on GitHub: https://github.com/angelitosystems/filament-tenancy/issues
- Contact: angelitosystems@gmail.com

