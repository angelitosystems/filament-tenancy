# Solución de Errores 419 de Página Expirada en Tenants

Este documento explica cómo solucionar los errores 419 "Página Expirada" que ocurren al trabajar con tenants en Filament Tenancy.

## Problema

Al cambiar a contextos de tenant, los usuarios pueden encontrar errores 419 "Página Expirada" debido a fallas en la validación de tokens CSRF. Esto sucede porque:

1. La configuración de sesión no está correctamente aislada entre tenants
2. La tabla de sesiones no existe en las bases de datos de los tenants
3. Los dominios de cookies no están configurados correctamente para subdominios de tenant

## Solución

El paquete ahora incluye varias soluciones para prevenir estos problemas:

### 1. Middleware de Sesión de Tenant

Un nuevo `TenantSessionMiddleware` configura automáticamente las configuraciones de sesión para contextos de tenant:

- Aísla las sesiones entre tenants
- Configura dominios de cookies apropiados para subdominios
- Asegura que la tabla de sesiones exista en las bases de datos de los tenants
- Maneja diferentes drivers de sesión (base de datos, archivo, redis)

### 2. Configuración de Sesión

Nuevas opciones de configuración se han agregado a `config/filament-tenancy.php`:

```php
'session' => [
    'isolation' => env('TENANCY_SESSION_ISOLATION', true),
    'cross_subdomain' => env('TENANCY_CROSS_SUBDOMAIN_SESSIONS', false),
    'prefix' => env('TENANCY_SESSION_PREFIX', 'tenant'),
    'auto_create_session_table' => env('TENANCY_AUTO_CREATE_SESSION_TABLE', true),
    'cookie' => [
        'domain' => env('TENANCY_SESSION_COOKIE_DOMAIN', null),
        'secure' => env('TENANCY_SESSION_COOKIE_SECURE', null),
        'same_site' => env('TENANCY_SESSION_COOKIE_SAME_SITE', 'lax'),
    ],
],
```

### 3. Creación Automática de Tabla de Sesiones

El paquete ahora crea automáticamente la tabla `sessions` en las bases de datos de los tenants cuando:

- Se usa el driver de sesión de base de datos
- `auto_create_session_table` está habilitado (por defecto: true)
- Se ejecutan migraciones de tenant

## Variables de Entorno

Agrega estas a tu archivo `.env` para configurar el comportamiento de la sesión:

```env
# Habilitar aislamiento de sesión entre tenants
TENANCY_SESSION_ISOLATION=true

# Permitir sesiones funcionen a través de subdominios
TENANCY_CROSS_SUBDOMAIN_SESSIONS=false

# Auto-crear tabla de sesiones en bases de datos de tenants
TENANCY_AUTO_CREATE_SESSION_TABLE=true

# Configuración de cookies de sesión
TENANCY_SESSION_COOKIE_DOMAIN=null
TENANCY_SESSION_COOKIE_SECURE=null
TENANCY_SESSION_COOKIE_SAME_SITE=lax
```

## Ruta de Migración

La migración de la tabla de sesiones está incluida en el paquete en:
`database/migrations/tenant/2024_01_01_000004_create_sessions_table.php`

Esta se ejecutará automáticamente al crear nuevos tenants o ejecutar migraciones de tenant.

## Creación Manual de Tabla de Sesiones

Si necesitas crear manualmente la tabla de sesiones en bases de datos de tenants existentes:

```bash
php artisan tenant:migrate --tenant=1
```

O usa el comando migrate para todos los tenants:

```bash
php artisan tenant:migrate --all
```

## Solución de Problemas

### ¿Sigue Obteniendo Errores 419?

1. **Limpia cachés**: `php artisan cache:clear` y `php artisan config:clear`
2. **Verifica driver de sesión**: Asegúrate que tu driver de sesión esté configurado correctamente
3. **Verifica conexión de base de datos**: Asegúrate que las bases de datos de los tenants sean accesibles
4. **Revisa dominio de cookies**: Verifica que el dominio de cookies coincida con tus dominios de tenant

### ¿Tabla de Sesiones No Creada?

1. Verifica que `TENANCY_AUTO_CREATE_SESSION_TABLE=true` en tu `.env`
2. Asegúrate que el driver de sesión esté configurado como `database`
3. Ejecuta migraciones de tenant manualmente: `php artisan tenant:migrate --all`

### ¿Problemas de Cross-Subdominio?

1. Establece `TENANCY_CROSS_SUBDOMAIN_SESSIONS=true` en tu `.env`
2. Configura dominio de cookies apropiado: `TENANCY_SESSION_COOKIE_DOMAIN=.example.com`

## Mejores Prácticas

1. Siempre usa driver de sesión de base de datos para multi-tenancy
2. Habilita aislamiento de sesión para prevenir conflictos
3. Configura dominios de cookies apropiados para tu configuración
4. Prueba el comportamiento de sesión a través de diferentes tenants
5. Monitorea logs para errores relacionados con sesiones

## Consideraciones de Seguridad

- El aislamiento de sesión previene secuestro de sesión entre tenants
- La configuración apropiada de dominio de cookies previene filtración de tokens CSRF
- Las configuraciones de cookies SameSite proporcionan protección CSRF adicional
- Considera usar HTTPS para entornos de producción
