<?php

namespace AngelitoSystems\FilamentTenancy\Concerns;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Collection;

/**
 * Trait for models that can have roles and permissions.
 */
trait HasRoles
{
    /**
     * A model may have multiple roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'model_has_roles',
            'model_id',
            'role_id'
        )
        ->wherePivot('model_type', static::class)
        ->withPivot('model_type');
    }

    /**
     * A model may have multiple direct permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'model_has_permissions',
            'model_id',
            'permission_id'
        )
        ->wherePivot('model_type', static::class)
        ->withPivot('model_type');
    }

    /**
     * Assign the given role to the model.
     */
    public function assignRole($role): self
    {
        $role = $this->getStoredRole($role);

        if (!$this->hasRole($role->name)) {
            $this->roles()->attach($role->id, ['model_type' => static::class]);
        }

        return $this;
    }

    /**
     * Remove all current roles and assign the given ones.
     */
    public function syncRoles($roles): self
    {
        $roleIds = collect($roles)->map(function ($role) {
            // Si es un ID numérico, usarlo directamente
            if (is_numeric($role)) {
                return (int) $role;
            }
            // Si es un string o Role, buscar/obtener el ID
            return $this->getStoredRole($role)->id;
        })->filter()->values()->toArray();

        $syncData = [];
        foreach ($roleIds as $roleId) {
            $syncData[$roleId] = ['model_type' => static::class];
        }

        $this->roles()->sync($syncData);

        return $this;
    }

    /**
     * Remove the given role from the model.
     */
    public function removeRole($role): self
    {
        $role = $this->getStoredRole($role);

        $this->roles()->detach($role->id);

        return $this;
    }

    /**
     * Determine if the model has (one of) the given role(s).
     */
    public function hasRole($roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->id);
        }

        if (is_array($roles)) {
            foreach ($roles as $role) {
                if ($this->hasRole($role)) {
                    return true;
                }
            }
            return false;
        }

        return $roles->intersect($this->roles)->isNotEmpty();
    }

    /**
     * Determine if the model has any of the given role(s).
     */
    public function hasAnyRole($roles): bool
    {
        return $this->hasRole($roles);
    }

    /**
     * Determine if the model has all of the given role(s).
     */
    public function hasAllRoles($roles): bool
    {
        if (is_string($roles)) {
            return $this->roles->contains('name', $roles);
        }

        if ($roles instanceof Role) {
            return $this->roles->contains($roles->id);
        }

        $roles = collect($roles)->map(function ($role) {
            return $role instanceof Role ? $role->name : $role;
        });

        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Determine if the model has the given permission.
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission, $this->getDefaultGuardName());
        }

        if ($permission instanceof Permission) {
            return $this->hasDirectPermission($permission) || $this->hasPermissionViaRole($permission);
        }

        return false;
    }

    /**
     * Determine if the model has any of the given permissions.
     */
    public function hasAnyPermission($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if ($this->hasPermissionTo($permission)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Determine if the model has all of the given permissions.
     */
    public function hasAllPermissions($permissions): bool
    {
        if (is_string($permissions)) {
            $permissions = [$permissions];
        }

        foreach ($permissions as $permission) {
            if (!$this->hasPermissionTo($permission)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Give the given permission to the model.
     */
    public function givePermissionTo($permission): self
    {
        $permission = $this->getStoredPermission($permission);

        if (!$this->hasDirectPermission($permission)) {
            $this->permissions()->attach($permission->id, ['model_type' => static::class]);
        }

        return $this;
    }

    /**
     * Remove all current permissions and assign the given ones.
     */
    public function syncPermissions($permissions): self
    {
        $permissionIds = collect($permissions)->map(function ($permission) {
            // Si es un ID numérico, usarlo directamente
            if (is_numeric($permission)) {
                return (int) $permission;
            }
            // Si es un string o Permission, buscar/obtener el ID
            return $this->getStoredPermission($permission)->id;
        })->filter()->values()->toArray();

        $syncData = [];
        foreach ($permissionIds as $permissionId) {
            $syncData[$permissionId] = ['model_type' => static::class];
        }

        $this->permissions()->sync($syncData);

        return $this;
    }

    /**
     * Revoke the given permission from the model.
     */
    public function revokePermissionTo($permission): self
    {
        $permission = $this->getStoredPermission($permission);

        $this->permissions()->detach($permission->id);

        return $this;
    }

    /**
     * Get all permissions of the model.
     */
    public function getAllPermissions(): Collection
    {
        return $this->permissions
            ->merge($this->getPermissionsViaRoles())
            ->sort()
            ->values();
    }

    /**
     * Get the permissions via roles.
     */
    public function getPermissionsViaRoles(): Collection
    {
        return $this->loadMissing('roles', 'roles.permissions')
            ->roles
            ->flatMap(function ($role) {
                return $role->permissions;
            })
            ->sort()
            ->values();
    }

    /**
     * Check if the model has a direct permission.
     */
    protected function hasDirectPermission($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission, $this->getDefaultGuardName());
        }

        if ($permission instanceof Permission) {
            return $this->permissions->contains($permission->id);
        }

        return false;
    }

    /**
     * Check if the model has permission via role.
     */
    protected function hasPermissionViaRole($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission, $this->getDefaultGuardName());
        }

        if ($permission instanceof Permission) {
            return $this->roles->contains(function ($role) use ($permission) {
                return $role->hasPermissionTo($permission);
            });
        }

        return false;
    }

    /**
     * Get stored role from string, ID, or Role model.
     */
    protected function getStoredRole($role): Role
    {
        // Si es una instancia de Role, retornarla directamente
        if ($role instanceof Role) {
            return $role;
        }

        // Si es un ID numérico, buscar por ID
        if (is_numeric($role)) {
            $foundRole = Role::find((int) $role);
            if (!$foundRole) {
                throw new \InvalidArgumentException("Role with ID {$role} not found");
            }
            return $foundRole;
        }

        // Si es un string, buscar por nombre
        if (is_string($role)) {
            return Role::findByName($role, $this->getDefaultGuardName());
        }

        throw new \InvalidArgumentException('Role must be a string, numeric ID, or Role instance');
    }

    /**
     * Get stored permission from string, ID, or Permission model.
     */
    protected function getStoredPermission($permission): Permission
    {
        // Si es una instancia de Permission, retornarla directamente
        if ($permission instanceof Permission) {
            return $permission;
        }

        // Si es un ID numérico, buscar por ID
        if (is_numeric($permission)) {
            $foundPermission = Permission::find((int) $permission);
            if (!$foundPermission) {
                throw new \InvalidArgumentException("Permission with ID {$permission} not found");
            }
            return $foundPermission;
        }

        // Si es un string, buscar por nombre
        if (is_string($permission)) {
            return Permission::findByName($permission, $this->getDefaultGuardName());
        }

        throw new \InvalidArgumentException('Permission must be a string, numeric ID, or Permission instance');
    }

    /**
     * Get default guard name.
     */
    protected function getDefaultGuardName(): string
    {
        return config('auth.defaults.guard');
    }
}
