<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use Illuminate\Contracts\Auth\Authenticatable;
use Illuminate\Database\Eloquent\Model;

class AuthorizationHelper
{
    /**
     * Check if user is super admin (case-insensitive check by name or slug)
     */
    public static function isSuperAdmin(Authenticatable|Model|null $user): bool
    {
        if (!$user) {
            return false;
        }

        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        // Check by various possible names/slugs (case-insensitive)
        $superAdminVariants = [
            'super admin',
            'Super Admin',
            'super-admin',
            'super_admin',
            'SuperAdmin',
            'superadmin',
        ];

        foreach ($superAdminVariants as $variant) {
            if ($user->hasRole($variant)) {
                return true;
            }
        }

        // Also check directly by slug/name if roles are loaded
        if ($user->relationLoaded('roles')) {
            foreach ($user->roles as $role) {
                $slug = strtolower(trim($role->slug ?? ''));
                $name = strtolower(trim($role->name ?? ''));
                
                $superAdminSlugs = ['super-admin', 'super_admin', 'superadmin'];
                $superAdminNames = ['super admin', 'superadmin'];
                
                if (in_array($slug, $superAdminSlugs) || in_array($name, $superAdminNames)) {
                    return true;
                }
            }
        }

        // Fallback: try to load roles and check again
        try {
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
                return static::isSuperAdmin($user);
            }
        } catch (\Exception $e) {
            // If we can't load roles, return false
            return false;
        }

        return false;
    }

    /**
     * Check if user is tenant admin (only valid in tenant context)
     */
    public static function isTenantAdmin(Authenticatable|Model|null $user): bool
    {
        // Solo válido si hay un tenant activo
        if (!Tenancy::current()) {
            return false;
        }

        if (!$user) {
            return false;
        }

        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        // Check by various possible tenant admin names/slugs (case-insensitive)
        $tenantAdminVariants = [
            'tenant admin',
            'Tenant Admin',
            'tenant-admin',
            'tenant_admin',
            'TenantAdmin',
            'tenantadmin',
            'admin', // También considerar 'admin' como tenant admin en contexto tenant
        ];

        foreach ($tenantAdminVariants as $variant) {
            if ($user->hasRole($variant)) {
                return true;
            }
        }

        // Also check directly by slug/name if roles are loaded
        if ($user->relationLoaded('roles')) {
            foreach ($user->roles as $role) {
                $slug = strtolower(trim($role->slug ?? ''));
                $name = strtolower(trim($role->name ?? ''));
                
                $tenantAdminSlugs = ['tenant-admin', 'tenant_admin', 'tenantadmin', 'admin'];
                $tenantAdminNames = ['tenant admin', 'tenantadmin', 'admin'];
                
                if (in_array($slug, $tenantAdminSlugs) || in_array($name, $tenantAdminNames)) {
                    return true;
                }
            }
        }

        // Fallback: try to load roles and check again
        try {
            if (!$user->relationLoaded('roles')) {
                $user->load('roles');
                return static::isTenantAdmin($user);
            }
        } catch (\Exception $e) {
            // If we can't load roles, return false
            return false;
        }

        return false;
    }

    /**
     * Check if user has any of the given permissions or is super admin/tenant admin
     */
    public static function hasPermissionOrSuperAdmin(Authenticatable|Model|null $user, string|array $permissions): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin has access to everything
        if (static::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has access to everything in tenant context
        if (static::isTenantAdmin($user)) {
            return true;
        }

        if (!method_exists($user, 'hasPermissionTo')) {
            return false;
        }

        $permissions = is_array($permissions) ? $permissions : [$permissions];

        foreach ($permissions as $permission) {
            if ($user->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if user has any of the given roles or is super admin/tenant admin
     */
    public static function hasRoleOrSuperAdmin(Authenticatable|Model|null $user, string|array $roles): bool
    {
        if (!$user) {
            return false;
        }

        // Super admin has access to everything
        if (static::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has access to everything in tenant context
        if (static::isTenantAdmin($user)) {
            return true;
        }

        if (!method_exists($user, 'hasRole')) {
            return false;
        }

        $roles = is_array($roles) ? $roles : [$roles];

        foreach ($roles as $role) {
            if ($user->hasRole($role)) {
                return true;
            }
        }

        return false;
    }
}

