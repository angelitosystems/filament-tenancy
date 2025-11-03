<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;

/**
 * Permission Manager for handling role and permission operations
 */
class PermissionManager
{
    /**
     * Create a new permission
     */
    public function createPermission(array $data): Permission
    {
        return Permission::create($data);
    }

    /**
     * Create a new role
     */
    public function createRole(array $data): Role
    {
        return Role::create($data);
    }

    /**
     * Get all permissions
     */
    public function getAllPermissions()
    {
        return Permission::all();
    }

    /**
     * Get all roles
     */
    public function getAllRoles()
    {
        return Role::all();
    }

    /**
     * Find permission by name
     */
    public function findPermissionByName(string $name, ?string $guardName = null): ?Permission
    {
        return Permission::findByName($name, $guardName);
    }

    /**
     * Find role by name
     */
    public function findRoleByName(string $name, ?string $guardName = null): ?Role
    {
        return Role::findByName($name, $guardName);
    }

    /**
     * Seed basic permissions and roles
     */
    public function seedBasicPermissions(): void
    {
        $permissions = [
            ['name' => 'manage users', 'slug' => 'manage-users', 'description' => 'Gestionar usuarios'],
            ['name' => 'manage roles', 'slug' => 'manage-roles', 'description' => 'Gestionar roles'],
            ['name' => 'manage permissions', 'slug' => 'manage-permissions', 'description' => 'Gestionar permisos'],
            ['name' => 'manage tenants', 'slug' => 'manage-tenants', 'description' => 'Gestionar tenants'],
            ['name' => 'view dashboard', 'slug' => 'view-dashboard', 'description' => 'Ver dashboard'],
            ['name' => 'manage settings', 'slug' => 'manage-settings', 'description' => 'Gestionar configuración'],
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(
                ['slug' => $permission['slug']],
                $permission
            );
        }

        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Super administrador con acceso total',
                'permissions' => ['manage users', 'manage roles', 'manage permissions', 'manage tenants', 'view dashboard', 'manage settings']
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrador con permisos limitados',
                'permissions' => ['manage users', 'view dashboard', 'manage settings']
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Usuario básico',
                'permissions' => ['view dashboard']
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::firstOrCreate(
                ['slug' => $roleData['slug']],
                [
                    'name' => $roleData['name'],
                    'description' => $roleData['description'],
                    'guard_name' => config('auth.defaults.guard', 'web'),
                ]
            );

            // Assign permissions to role
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::where('slug', str_replace(' ', '-', $permissionName))->first();
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }
    }
}
