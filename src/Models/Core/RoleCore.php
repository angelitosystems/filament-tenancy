<?php

namespace AngelitoSystems\FilamentTenancy\Models\Core;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Support\Str;

/**
 * Role Core Model
 * 
 * Contains the business logic for roles.
 */
class RoleCore extends Model
{
    protected $table = 'roles';

    protected $guarded = [];

    /**
     * Check if the role has a specific permission.
     */
    public function hasPermissionTo($permission): bool
    {
        if (is_string($permission)) {
            $permission = Permission::findByName($permission, $this->guard_name);
        }

        if ($permission instanceof Permission) {
            return $this->permissions->contains($permission->id);
        }

        return false;
    }

    /**
     * Give permission to a role.
     */
    public function givePermissionTo($permission): self
    {
        $permission = $this->getStoredPermission($permission);

        if (!$this->permissions->contains($permission->id)) {
            $this->permissions()->save($permission);
        }

        return $this;
    }

    /**
     * Revoke permission from a role.
     */
    public function revokePermissionTo($permission): self
    {
        $permission = $this->getStoredPermission($permission);

        $this->permissions()->detach($permission->id);

        return $this;
    }

    /**
     * Sync permissions for the role.
     */
    public function syncPermissions($permissions): self
    {
        $permissions = collect($permissions)->map(function ($permission) {
            return $this->getStoredPermission($permission)->id;
        });

        $this->permissions()->sync($permissions);

        return $this;
    }

    /**
     * Get stored permission from string or Permission model.
     */
    protected function getStoredPermission($permission): Permission
    {
        if (is_string($permission)) {
            return Permission::findByName($permission, $this->guard_name);
        }

        if ($permission instanceof Permission) {
            return $permission;
        }

        throw new \InvalidArgumentException('Permission must be a string or Permission instance');
    }
}
