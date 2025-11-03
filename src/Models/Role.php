<?php

namespace AngelitoSystems\FilamentTenancy\Models;

use AngelitoSystems\FilamentTenancy\Models\Core\RoleCore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Role Model
 * 
 * This model extends RoleCore which contains all the business logic.
 * This class only adds Eloquent-specific features like factories.
 */
class Role extends RoleCore
{
    use HasFactory;

    protected $fillable = [
        'name',
        'slug',
        'description',
        'guard_name',
        'is_active',
    ];

    protected $casts = [
        'is_active' => 'boolean',
    ];

    /**
     * A role may be given various permissions.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            'role_has_permissions',
            'role_id',
            'permission_id'
        );
    }

    /**
     * A role may be assigned to various users.
     */
    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        
        return $this->belongsToMany(
            $userModel,
            'model_has_roles',
            'role_id',
            'model_id'
        )->where('model_type', $userModel);
    }

    /**
     * Find a role by its name.
     */
    public static function findByName(string $name, $guardName = null): ?self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        return static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Find a role by its slug.
     */
    public static function findBySlug(string $slug, $guardName = null): ?self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        return static::where('slug', $slug)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Create a new role.
     */
    public static function create(array $attributes = [])
    {
        if (empty($attributes['slug'])) {
            $attributes['slug'] = Str::slug($attributes['name']);
        }

        if (empty($attributes['guard_name'])) {
            $attributes['guard_name'] = config('auth.defaults.guard');
        }

        return static::query()->create($attributes);
    }
}
