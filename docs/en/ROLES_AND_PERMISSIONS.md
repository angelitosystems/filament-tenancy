# Roles and Permissions System

## Overview

Filament Tenancy now includes a comprehensive roles and permissions system similar to Spatie Laravel Permission, designed specifically for multi-tenant environments.

## Features

- **Role-based Access Control**: Assign roles to users with specific permissions
- **Permission Management**: Granular permissions for different actions
- **Tenant Isolation**: Roles and permissions are isolated per tenant
- **Middleware Protection**: Built-in middleware for route protection
- **Dynamic Assignment**: Assign and revoke permissions at runtime

## Installation

The roles and permissions system is automatically installed when you run:

```bash
php artisan filament-tenancy:install
```

This will:
- Create the necessary database tables
- Seed basic roles (Super Admin, Admin, User)
- Create default permissions
- Register middleware

## Basic Usage

### Using the HasRoles Trait

Add the `HasRoles` trait to your User model:

```php
<?php

namespace App\Models;

use AngelitoSystems\FilamentTenancy\Concerns\HasRoles;
use Illuminate\Foundation\Auth\User as Authenticatable;

class User extends Authenticatable
{
    use HasRoles;
    
    // Your model code...
}
```

### Assigning Roles

```php
// Assign a role
$user->assignRole('admin');

// Assign multiple roles
$user->syncRoles(['admin', 'editor']);

// Remove a role
$user->removeRole('admin');
```

### Checking Roles

```php
// Check if user has a specific role
$user->hasRole('admin');

// Check if user has any of the given roles
$user->hasAnyRole(['admin', 'editor']);

// Check if user has all the given roles
$user->hasAllRoles(['admin', 'editor']);
```

### Managing Permissions

```php
// Give permission to user
$user->givePermissionTo('manage users');

// Sync permissions
$user->syncPermissions(['manage users', 'view dashboard']);

// Revoke permission
$user->revokePermissionTo('manage users');

// Check permissions
$user->hasPermissionTo('manage users');
$user->hasAnyPermission(['manage users', 'edit posts']);
$user->hasAllPermissions(['manage users', 'view dashboard']);
```

### Role Permissions

```php
// Create a role with permissions
$role = Role::create([
    'name' => 'Editor',
    'slug' => 'editor',
    'description' => 'Content editor role'
]);

// Assign permissions to role
$role->givePermissionTo('create posts');
$role->syncPermissions(['create posts', 'edit posts', 'publish posts']);

// Check role permissions
$role->hasPermissionTo('create posts');
```

## Default Roles and Permissions

### Default Roles

1. **Super Admin**: Full access to all features
2. **Admin**: Administrative access with limited permissions
3. **User**: Basic user permissions

### Default Permissions

- `manage users` - Manage user accounts
- `manage roles` - Manage roles and permissions
- `manage permissions` - Manage system permissions
- `manage tenants` - Manage tenant accounts
- `view dashboard` - Access the dashboard
- `manage settings` - Manage system settings
- `create posts` - Create content posts
- `edit posts` - Edit content posts
- `delete posts` - Delete content posts
- `publish posts` - Publish content posts

## Middleware Protection

### Permission Middleware

Protect routes based on permissions:

```php
Route::get('/admin/users', function () {
    // Admin users page
})->middleware('permission:manage users');

Route::post('/admin/settings', function () {
    // Update settings
})->middleware('permission:manage settings');
```

### Role Middleware

Protect routes based on roles:

```php
Route::get('/admin', function () {
    // Admin panel
})->middleware('role:admin');

Route::get('/super-admin', function () {
    // Super admin panel
})->middleware('role:super-admin');
```

## Database Tables

The system creates the following tables in each tenant database:

- `roles` - Stores role definitions
- `permissions` - Stores permission definitions
- `role_has_permissions` - Many-to-many relationship between roles and permissions
- `model_has_roles` - Many-to-many relationship between models and roles
- `model_has_permissions` - Many-to-many relationship between models and permissions

## API Reference

### Role Model

```php
// Find roles
Role::findByName('admin');
Role::findBySlug('admin');

// Create role
Role::create([
    'name' => 'Custom Role',
    'slug' => 'custom-role',
    'description' => 'A custom role'
]);

// Role permissions
$role->permissions;
$role->givePermissionTo('permission_name');
$role->revokePermissionTo('permission_name');
$role->syncPermissions(['perm1', 'perm2']);
$role->hasPermissionTo('permission_name');
```

### Permission Model

```php
// Find permissions
Permission::findByName('manage users');
Permission::findBySlug('manage-users');

// Create permission
Permission::create([
    'name' => 'Custom Permission',
    'slug' => 'custom-permission',
    'description' => 'A custom permission'
]);

// Permission roles
$permission->roles;
```

### HasRoles Trait Methods

```php
// Role management
$user->assignRole($role);
$user->removeRole($role);
$user->syncRoles($roles);
$user->hasRole($role);
$user->hasAnyRole($roles);
$user->hasAllRoles($roles);

// Permission management
$user->givePermissionTo($permission);
$user->revokePermissionTo($permission);
$user->syncPermissions($permissions);
$user->hasPermissionTo($permission);
$user->hasAnyPermission($permissions);
$user->hasAllPermissions($permissions);

// Get all permissions
$user->getAllPermissions();
$user->getPermissionsViaRoles();
```

## Configuration

Add to your `config/filament-tenancy.php`:

```php
'permissions' => [
    'register_middleware' => env('TENANCY_PERMISSIONS_MIDDLEWARE', true),
    'auto_seed' => env('TENANCY_PERMISSIONS_AUTO_SEED', true),
],
```

## Best Practices

1. **Use Slugs**: Always use slugs for role and permission identification
2. **Granular Permissions**: Create specific permissions rather than broad ones
3. **Role Hierarchies**: Use roles to group related permissions
4. **Cache Permissions**: Consider caching user permissions for performance
5. **Tenant Isolation**: Remember that roles and permissions are tenant-specific

## Troubleshooting

### Common Issues

1. **Method Not Found**: Ensure the User model has the `HasRoles` trait
2. **Permission Denied**: Check that the user has the required role or permission
3. **Database Issues**: Run migrations to ensure all tables exist

### Debug Mode

Enable permission debugging in your environment:

```env
TENANCY_PERMISSIONS_DEBUG=true
```

This will log all permission checks to your Laravel logs.
