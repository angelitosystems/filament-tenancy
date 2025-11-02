# Guía de Uso de Plugins - Filament Tenancy

Esta guía explica cómo usar los plugins de Filament Tenancy para configurar paneles de administración central (landlord) y paneles de tenant.

## Introducción

Filament Tenancy proporciona dos plugins principales para integrar multi-tenancy con Filament:

- **TenancyLandlordPlugin**: Para el panel de administración central (admin/landlord)
- **TenancyTenantPlugin**: Para los paneles de tenant

Estos plugins configuran automáticamente los middlewares necesarios y las restricciones de acceso para garantizar la seguridad y el aislamiento entre tenants.

## Requisitos Previos

Antes de usar los plugins, asegúrate de:

1. ✅ Tener Filament instalado (`composer require filament/filament:"^4.0"`)
2. ✅ Haber ejecutado `php artisan filament-tenancy:install`
3. ✅ Tener al menos un panel de Filament creado

## TenancyLandlordPlugin

El plugin de landlord se usa para el panel de administración central donde se gestionan todos los tenants.

### Características

- ✅ Acceso solo desde dominios centrales sin tenant activo
- ✅ Middleware `PreventTenantAccess` para bloquear acceso desde contexto tenant
- ✅ Conexión automática a la base de datos central (landlord)
- ✅ Registro automático del recurso `TenantResource` para gestionar tenants

### Configuración Básica

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
            ->plugin(TenancyLandlordPlugin::make()); // 👈 Plugin de landlord
    }
}
```

### Middlewares Incluidos

El plugin agrega automáticamente estos middlewares:

1. **InitializeTenancy**: Resuelve el tenant desde el dominio/subdominio
2. **PreventTenantAccess**: Bloquea el acceso si hay un tenant activo (garantiza acceso solo desde dominios centrales)

### Restricciones de Acceso

El panel admin/landlord tiene las siguientes restricciones:

- ❌ **NO puede accederse** desde un dominio de tenant (ej: `tenant1.example.com`)
- ✅ **SÍ puede accederse** desde dominios centrales (ej: `app.example.com`, `admin.example.com`)
- ❌ **Bloquea automáticamente** el acceso si hay un tenant resuelto (retorna 403)

### Personalización Avanzada

```php
TenancyLandlordPlugin::make()
    ->autoRegister(false) // Desactivar registro automático de recursos
    ->middleware([
        // Agregar middlewares adicionales
        YourCustomMiddleware::class,
    ])
    ->resources([
        // Agregar recursos adicionales
        YourResource::class,
    ])
    ->pages([
        // Agregar páginas adicionales
        YourPage::class,
    ]);
```

## TenancyTenantPlugin

El plugin de tenant se usa para los paneles de cada tenant individual.

### Características

- ✅ Acceso solo cuando hay un tenant activo y resuelto
- ✅ Middleware `PreventLandlordAccess` para bloquear acceso sin tenant
- ✅ Conexión automática a la base de datos del tenant
- ✅ Branding dinámico basado en el nombre del tenant

### Configuración Básica

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
            ->plugin(TenancyTenantPlugin::make()); // 👈 Plugin de tenant
    }
}
```

### Middlewares Incluidos

El plugin agrega automáticamente estos middlewares:

1. **InitializeTenancy**: Resuelve el tenant desde el dominio/subdominio
2. **EnsureTenantAccess**: Verifica que el tenant esté activo y no expirado
3. **PreventLandlordAccess**: Bloquea el acceso si no hay tenant activo

### Restricciones de Acceso

El panel tenant tiene las siguientes restricciones:

- ❌ **NO puede accederse** desde dominios centrales sin tenant (ej: `app.example.com`)
- ✅ **SÍ puede accederse** desde dominios de tenant (ej: `tenant1.example.com`)
- ❌ **Bloquea automáticamente** el acceso si no hay tenant resuelto (retorna 403)
- ❌ **Bloquea automáticamente** el acceso si el tenant está inactivo o expirado (retorna 403)

### Personalización Avanzada

```php
TenancyTenantPlugin::make()
    ->autoRegister(false) // Desactivar registro automático de recursos
    ->middleware([
        // Agregar middlewares adicionales
        YourCustomMiddleware::class,
    ])
    ->excludeResources([
        // Excluir recursos específicos del contexto tenant
        SomeResource::class,
    ])
    ->excludePages([
        // Excluir páginas específicas del contexto tenant
        SomePage::class,
    ]);
```

## Configuración de IDs de Panel

Los plugins se registran automáticamente según los IDs de panel configurados. Por defecto:

- **Landlord Panel ID**: `admin`
- **Tenant Panel ID**: `tenant`

Puedes cambiar estos valores en `config/filament-tenancy.php`:

```php
'filament' => [
    'auto_register_plugins' => true,
    'landlord_panel_id' => 'admin',    // Cambiar si tu panel admin tiene otro ID
    'tenant_panel_id' => 'tenant',      // Cambiar si tu panel tenant tiene otro ID
    'tenant_panel_path' => '/admin',
],
```

Si tienes IDs diferentes, actualiza la configuración antes de usar los plugins.

## Flujo de Acceso

### Panel Admin/Landlord

```
Usuario accede a: app.example.com/admin
     ↓
InitializeTenancy resuelve el dominio
     ↓
¿Es dominio central? → SÍ
     ↓
¿Hay tenant activo? → NO (es dominio central)
     ↓
PreventTenantAccess permite acceso
     ↓
✅ Acceso al panel admin
```

### Panel Tenant

```
Usuario accede a: tenant1.example.com/admin
     ↓
InitializeTenancy resuelve el dominio
     ↓
¿Es dominio central? → NO
     ↓
Resuelve tenant desde dominio/subdominio
     ↓
¿Tenant encontrado? → SÍ
     ↓
¿Tenant activo? → SÍ (EnsureTenantAccess)
     ↓
¿Hay tenant activo? → SÍ (PreventLandlordAccess)
     ↓
✅ Acceso al panel tenant
```

## Ejemplos de Casos de Uso

### Caso 1: Panel Admin desde Dominio Central

```php
// URL: https://app.example.com/admin
// Resultado: ✅ Acceso permitido al panel admin
// Contexto: Base de datos central (landlord)
```

### Caso 2: Panel Tenant desde Dominio de Tenant

```php
// URL: https://acme.example.com/admin
// Resultado: ✅ Acceso permitido al panel tenant
// Contexto: Base de datos del tenant "acme"
```

### Caso 3: Intento de Acceso Admin desde Dominio Tenant

```php
// URL: https://acme.example.com/admin (intentando acceder al panel admin)
// Resultado: ❌ Error 403 - Access denied
// Razón: PreventTenantAccess detecta tenant activo y bloquea
```

### Caso 4: Intento de Acceso Tenant desde Dominio Central

```php
// URL: https://app.example.com/admin (intentando acceder al panel tenant)
// Resultado: ❌ Error 403 - Access denied
// Razón: PreventLandlordAccess detecta que no hay tenant activo
```

## Verificación Durante la Instalación

El comando `php artisan filament-tenancy:install` verifica automáticamente:

- ✅ Existencia de paneles de Filament
- ✅ Configuración correcta de los plugins
- ✅ IDs de panel coincidentes con la configuración
- ✅ Restricciones de seguridad aplicadas

Si detecta problemas, te mostrará mensajes de advertencia con instrucciones para corregirlos.

## Troubleshooting

### Error: "Access denied: Admin panel cannot be accessed from tenant context"

**Causa**: Estás intentando acceder al panel admin desde un dominio de tenant.

**Solución**: 
- Accede al panel admin desde un dominio central (ej: `app.example.com/admin`)
- O configura el panel tenant si quieres acceder desde el dominio de tenant

### Error: "Access denied: Tenant panel requires an active tenant context"

**Causa**: Estás intentando acceder al panel tenant desde un dominio central sin tenant.

**Solución**:
- Accede al panel tenant desde un dominio de tenant (ej: `tenant1.example.com/admin`)
- Asegúrate de que el tenant existe y está activo en la base de datos

### Los plugins no se registran automáticamente

**Causa**: Los IDs de panel no coinciden con la configuración.

**Solución**:
1. Verifica los IDs de tus paneles en los PanelProviders
2. Actualiza `config/filament-tenancy.php` con los IDs correctos:
   ```php
   'landlord_panel_id' => 'tu-id-admin',
   'tenant_panel_id' => 'tu-id-tenant',
   ```
3. O registra los plugins manualmente en cada PanelProvider

### El panel muestra datos del tenant incorrecto

**Causa**: El middleware `InitializeTenancy` está resolviendo un tenant diferente al esperado.

**Solución**:
1. Verifica la configuración de `central_domains` en `config/filament-tenancy.php`
2. Asegúrate de que `APP_DOMAIN` está configurado correctamente en `.env`
3. Verifica que los tenants tienen los dominios/subdominios correctos en la base de datos

## Mejores Prácticas

1. **Separación de Recursos**: Mantén recursos de landlord y tenant en directorios separados:
   - `app/Filament/Resources/` → Recursos del panel admin
   - `app/Filament/Tenant/Resources/` → Recursos del panel tenant

2. **Modelos con Traits**: Usa los traits correctos en tus modelos:
   - `BelongsToTenant` para modelos de tenant
   - `UsesLandlordConnection` para modelos centrales

3. **Configuración de Dominios**: Configura correctamente los dominios centrales:
   ```php
   'central_domains' => [
       'app.example.com',
       'admin.example.com',
       env('APP_DOMAIN', 'localhost'),
   ],
   ```

4. **Testing**: Prueba siempre ambos paneles desde sus dominios correspondientes para verificar las restricciones.

## Recursos Adicionales

- [README.md](../../README.md) - Documentación general del paquete
- [TECHNICAL.md](TECHNICAL.md) - Documentación técnica y arquitectura
- [Configuración](../../config/filament-tenancy.php) - Archivo de configuración con opciones disponibles

## Soporte

Si encuentras problemas o tienes preguntas:

- Abre un issue en GitHub: https://github.com/angelitosystems/filament-tenancy/issues
- Contacta: angelitosystems@gmail.com

