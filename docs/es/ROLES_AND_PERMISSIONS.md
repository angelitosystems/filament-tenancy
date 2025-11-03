# Sistema de Roles y Permisos

## Resumen

Filament Tenancy ahora incluye un sistema completo de roles y permisos similar a Spatie Laravel Permission, diseñado específicamente para entornos multi-tenant.

## Características

- **Control de Acceso Basado en Roles**: Asigna roles a usuarios con permisos específicos
- **Gestión de Permisos**: Permisos granulares para diferentes acciones
- **Aislamiento de Tenant**: Roles y permisos están aislados por tenant
- **Protección Middleware**: Middleware incorporado para protección de rutas
- **Asignación Dinámica**: Asigna y revoca permisos en tiempo de ejecución

## Instalación

El sistema de roles y permisos se instala automáticamente cuando ejecutas:

```bash
php artisan filament-tenancy:install
```

Esto:
- Creará las tablas de base de datos necesarias
- Sembrará roles básicos (Super Admin, Admin, User)
- Creará permisos por defecto
- Registrará middleware

## Uso Básico

### Usando el Trait HasRoles

Añade el trait `HasRoles` a tu modelo User:

```php
<?php

namespace App\Models;

use AngelitoSystems\FilamentTenancy\Concerns\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;
    
    // Tu código de modelo...
}
```

### Asignando Roles

```php
// Asignar un rol
$user->assignRole('admin');

// Asignar múltiples roles
$user->syncRoles(['admin', 'editor']);

// Eliminar un rol
$user->removeRole('admin');
```

### Verificando Roles

```php
// Verificar si el usuario tiene un rol específico
$user->hasRole('admin');

// Verificar si el usuario tiene alguno de los roles dados
$user->hasAnyRole(['admin', 'editor']);

// Verificar si el usuario tiene todos los roles dados
$user->hasAllRoles(['admin', 'editor']);
```

### Gestionando Permisos

```php
// Dar permiso al usuario
$user->givePermissionTo('manage users');

// Sincronizar permisos
$user->syncPermissions(['manage users', 'view dashboard']);

// Revocar permiso
$user->revokePermissionTo('manage users');

// Verificar permisos
$user->hasPermissionTo('manage users');
$user->hasAnyPermission(['manage users', 'edit posts']);
$user->hasAllPermissions(['manage users', 'view dashboard']);
```

### Permisos de Rol

```php
// Crear un rol con permisos
$role = Role::create([
    'name' => 'Editor',
    'slug' => 'editor',
    'description' => 'Rol de editor de contenido'
]);

// Asignar permisos al rol
$role->givePermissionTo('create posts');
$role->syncPermissions(['create posts', 'edit posts', 'publish posts']);

// Verificar permisos del rol
$role->hasPermissionTo('create posts');
```

## Roles y Permisos por Defecto

### Roles por Defecto

1. **Super Admin**: Acceso completo a todas las características
2. **Admin**: Acceso administrativo con permisos limitados
3. **User**: Permisos básicos de usuario

### Permisos por Defecto

- `manage users` - Gestionar cuentas de usuario
- `manage roles` - Gestionar roles y permisos
- `manage permissions` - Gestionar permisos del sistema
- `manage tenants` - Gestionar cuentas de tenant
- `view dashboard` - Acceder al dashboard
- `manage settings` - Gestionar configuración del sistema
- `create posts` - Crear posts de contenido
- `edit posts` - Editar posts de contenido
- `delete posts` - Eliminar posts de contenido
- `publish posts` - Publicar posts de contenido

## Protección Middleware

### Middleware de Permisos

Protege rutas basadas en permisos:

```php
Route::get('/admin/users', function () {
    // Página de usuarios de admin
})->middleware('permission:manage users');

Route::post('/admin/settings', function () {
    // Actualizar configuración
})->middleware('permission:manage settings');
```

### Middleware de Roles

Protege rutas basadas en roles:

```php
Route::get('/admin', function () {
    // Panel de admin
})->middleware('role:admin');

Route::get('/super-admin', function () {
    // Panel de super admin
})->middleware('role:super-admin');
```

## Tablas de Base de Datos

El sistema crea las siguientes tablas en cada base de datos de tenant:

- `roles` - Almacena definiciones de roles
- `permissions` - Almacena definiciones de permisos
- `role_has_permissions` - Relación muchos-a-muchos entre roles y permisos
- `model_has_roles` - Relación muchos-a-muchos entre modelos y roles
- `model_has_permissions` - Relación muchos-a-muchos entre modelos y permisos

## Referencia de API

### Modelo Role

```php
// Encontrar roles
Role::findByName('admin');
Role::findBySlug('admin');

// Crear rol
Role::create([
    'name' => 'Rol Personalizado',
    'slug' => 'custom-role',
    'description' => 'Un rol personalizado'
]);

// Permisos del rol
$role->permissions;
$role->givePermissionTo('permission_name');
$role->revokePermissionTo('permission_name');
$role->syncPermissions(['perm1', 'perm2']);
$role->hasPermissionTo('permission_name');
```

### Modelo Permission

```php
// Encontrar permisos
Permission::findByName('manage users');
Permission::findBySlug('manage-users');

// Crear permiso
Permission::create([
    'name' => 'Permiso Personalizado',
    'slug' => 'custom-permission',
    'description' => 'Un permiso personalizado'
]);

// Roles del permiso
$permission->roles;
```

### Métodos del Trait HasRoles

```php
// Gestión de roles
$user->assignRole($role);
$user->removeRole($role);
$user->syncRoles($roles);
$user->hasRole($role);
$user->hasAnyRole($roles);
$user->hasAllRoles($roles);

// Gestión de permisos
$user->givePermissionTo($permission);
$user->revokePermissionTo($permission);
$user->syncPermissions($permissions);
$user->hasPermissionTo($permission);
$user->hasAnyPermission($permissions);
$user->hasAllPermissions($permissions);

// Obtener todos los permisos
$user->getAllPermissions();
$user->getPermissionsViaRoles();
```

## Configuración

Añade a tu `config/filament-tenancy.php`:

```php
'permissions' => [
    'register_middleware' => env('TENANCY_PERMISSIONS_MIDDLEWARE', true),
    'auto_seed' => env('TENANCY_PERMISSIONS_AUTO_SEED', true),
],
```

## Mejores Prácticas

1. **Usa Slugs**: Siempre usa slugs para identificación de roles y permisos
2. **Permisos Granulares**: Crea permisos específicos en lugar de permisos amplios
3. **Jerarquías de Roles**: Usa roles para agrupar permisos relacionados
4. **Caché de Permisos**: Considera cachear permisos de usuario para rendimiento
5. **Aislamiento de Tenant**: Recuerda que los roles y permisos son específicos del tenant

## Solución de Problemas

### Problemas Comunes

1. **Método No Encontrado**: Asegúrate que el modelo User tenga el trait `HasRoles`
2. **Permiso Denegado**: Verifica que el usuario tenga el rol o permiso requerido
3. **Problemas de Base de Datos**: Ejecuta migraciones para asegurar que todas las tablas existan

### Modo Depuración

Habilita la depuración de permisos en tu entorno:

```env
TENANCY_PERMISSIONS_DEBUG=true
```

Esto registrará todas las verificaciones de permisos en tus logs de Laravel.
