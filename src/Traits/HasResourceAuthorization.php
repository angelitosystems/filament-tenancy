<?php

namespace AngelitoSystems\FilamentTenancy\Traits;

use AngelitoSystems\FilamentTenancy\Support\AuthorizationHelper;

trait HasResourceAuthorization
{
    /**
     * Get permissions required to access this resource
     * 
     * @return array
     */
    protected static function getAccessPermissions(): array
    {
        return [];
    }

    /**
     * Get roles required to access this resource
     * 
     * @return array
     */
    protected static function getAccessRoles(): array
    {
        return [];
    }

    /**
     * Get permissions required to view any records
     * 
     * @return array
     */
    protected static function getViewAnyPermissions(): array
    {
        return static::getAccessPermissions();
    }

    /**
     * Get roles required to view any records
     * 
     * @return array
     */
    protected static function getViewAnyRoles(): array
    {
        return static::getAccessRoles();
    }

    /**
     * Get permissions required to view a specific record
     * 
     * @return array
     */
    protected static function getViewPermissions(): array
    {
        return static::getAccessPermissions();
    }

    /**
     * Get roles required to view a specific record
     * 
     * @return array
     */
    protected static function getViewRoles(): array
    {
        return static::getAccessRoles();
    }

    /**
     * Get permissions required to create records
     * 
     * @return array
     */
    protected static function getCreatePermissions(): array
    {
        return [];
    }

    /**
     * Get roles required to create records
     * 
     * @return array
     */
    protected static function getCreateRoles(): array
    {
        return [];
    }

    /**
     * Get permissions required to edit a specific record
     * 
     * @return array
     */
    protected static function getEditPermissions(): array
    {
        return [];
    }

    /**
     * Get roles required to edit a specific record
     * 
     * @return array
     */
    protected static function getEditRoles(): array
    {
        return [];
    }

    /**
     * Get permissions required to delete a specific record
     * 
     * @return array
     */
    protected static function getDeletePermissions(): array
    {
        return [];
    }

    /**
     * Get roles required to delete a specific record
     * 
     * @return array
     */
    protected static function getDeleteRoles(): array
    {
        return [];
    }

    /**
     * Check if user can access this resource
     * 
     * @return bool
     */
    public static function canAccess(): bool
    {
        $user = auth()->user();

        if (AuthorizationHelper::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has full access in tenant context
        if (AuthorizationHelper::isTenantAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $permissions = static::getAccessPermissions();
        $roles = static::getAccessRoles();

        $hasPermission = !empty($permissions) && AuthorizationHelper::hasPermissionOrSuperAdmin($user, $permissions);
        $hasRole = !empty($roles) && AuthorizationHelper::hasRoleOrSuperAdmin($user, $roles);

        return $hasPermission || $hasRole;
    }

    /**
     * Check if user can view any records
     * 
     * @return bool
     */
    public static function canViewAny(): bool
    {
        // Check subscription restrictions if ChecksSubscriptionRestrictions trait is present
        if (in_array(\AngelitoSystems\FilamentTenancy\Traits\ChecksSubscriptionRestrictions::class, class_uses_recursive(static::class))) {
            // Check if subscription is restricted
            $isRestricted = session()->get('subscription_restricted', false);
            
            if ($isRestricted) {
                // Check if this resource is allowed with restrictions
                $allowedResources = [
                    \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource::class,
                    \AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource::class,
                ];
                
                $resourceClass = static::class;
                $isAllowed = in_array($resourceClass, $allowedResources);
                
                // If restricted and not allowed, block access
                if (!$isAllowed) {
                    return false;
                }
            }
        }

        $user = auth()->user();

        if (AuthorizationHelper::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has full access in tenant context
        if (AuthorizationHelper::isTenantAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $permissions = static::getViewAnyPermissions();
        $roles = static::getViewAnyRoles();

        $hasPermission = !empty($permissions) && AuthorizationHelper::hasPermissionOrSuperAdmin($user, $permissions);
        $hasRole = !empty($roles) && AuthorizationHelper::hasRoleOrSuperAdmin($user, $roles);

        return $hasPermission || $hasRole;
    }

    /**
     * Check if user can view a specific record
     * 
     * @param mixed $record
     * @return bool
     */
    public static function canView($record): bool
    {
        $user = auth()->user();

        if (AuthorizationHelper::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has full access in tenant context
        if (AuthorizationHelper::isTenantAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $permissions = static::getViewPermissions();
        $roles = static::getViewRoles();

        $hasPermission = !empty($permissions) && AuthorizationHelper::hasPermissionOrSuperAdmin($user, $permissions);
        $hasRole = !empty($roles) && AuthorizationHelper::hasRoleOrSuperAdmin($user, $roles);

        return $hasPermission || $hasRole;
    }

    /**
     * Check if user can create records
     * 
     * @return bool
     */
    public static function canCreate(): bool
    {
        $user = auth()->user();

        if (AuthorizationHelper::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has full access in tenant context
        if (AuthorizationHelper::isTenantAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $permissions = static::getCreatePermissions();
        $roles = static::getCreateRoles();

        $hasPermission = !empty($permissions) && AuthorizationHelper::hasPermissionOrSuperAdmin($user, $permissions);
        $hasRole = !empty($roles) && AuthorizationHelper::hasRoleOrSuperAdmin($user, $roles);

        return $hasPermission || $hasRole;
    }

    /**
     * Check if user can edit a specific record
     * 
     * @param mixed $record
     * @return bool
     */
    public static function canEdit($record): bool
    {
        $user = auth()->user();

        if (AuthorizationHelper::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has full access in tenant context
        if (AuthorizationHelper::isTenantAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $permissions = static::getEditPermissions();
        $roles = static::getEditRoles();

        $hasPermission = !empty($permissions) && AuthorizationHelper::hasPermissionOrSuperAdmin($user, $permissions);
        $hasRole = !empty($roles) && AuthorizationHelper::hasRoleOrSuperAdmin($user, $roles);

        return $hasPermission || $hasRole;
    }

    /**
     * Check if user can delete a specific record
     * 
     * @param mixed $record
     * @return bool
     */
    public static function canDelete($record): bool
    {
        $user = auth()->user();

        if (AuthorizationHelper::isSuperAdmin($user)) {
            return true;
        }

        // Tenant admin has full access in tenant context
        if (AuthorizationHelper::isTenantAdmin($user)) {
            return true;
        }

        if (!$user) {
            return false;
        }

        $permissions = static::getDeletePermissions();
        $roles = static::getDeleteRoles();

        $hasPermission = !empty($permissions) && AuthorizationHelper::hasPermissionOrSuperAdmin($user, $permissions);
        $hasRole = !empty($roles) && AuthorizationHelper::hasRoleOrSuperAdmin($user, $roles);

        return $hasPermission || $hasRole;
    }

    /**
     * Check if navigation item should be visible
     * 
     * @return bool
     */
    public static function shouldRegisterNavigation(): bool
    {
        // Check subscription restrictions if ChecksSubscriptionRestrictions trait is present
        if (in_array(\AngelitoSystems\FilamentTenancy\Traits\ChecksSubscriptionRestrictions::class, class_uses_recursive(static::class))) {
            // Check if subscription is restricted
            $isRestricted = session()->get('subscription_restricted', false);
            
            if ($isRestricted) {
                // Check if this resource is allowed with restrictions
                $allowedResources = [
                    \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource::class,
                    \AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource::class,
                ];
                
                $resourceClass = static::class;
                $isAllowed = in_array($resourceClass, $allowedResources);
                
                // If restricted and not allowed, hide from navigation
                if (!$isAllowed) {
                    return false;
                }
            }
        }
        
        return static::canAccess();
    }
}

