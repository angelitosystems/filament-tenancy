<?php

namespace AngelitoSystems\FilamentTenancy\Models;

use AngelitoSystems\FilamentTenancy\Models\Core\PermissionCore;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Relations\BelongsToMany;
use Illuminate\Support\Str;

/**
 * Permission Model
 * 
 * This model extends PermissionCore which contains all the business logic.
 * This class only adds Eloquent-specific features like factories.
 */
class Permission extends PermissionCore
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
     * A permission can be applied to roles.
     */
    public function roles(): BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            'role_has_permissions',
            'permission_id',
            'role_id'
        );
    }

    /**
     * A permission may be assigned to various users.
     */
    public function users(): BelongsToMany
    {
        $userModel = config('auth.providers.users.model', 'App\\Models\\User');
        
        return $this->belongsToMany(
            $userModel,
            'model_has_permissions',
            'permission_id',
            'model_id'
        )->where('model_type', $userModel);
    }

    /**
     * Find a permission by its name.
     */
    public static function findByName(string $name, $guardName = null): ?self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        return static::where('name', $name)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Find a permission by its slug.
     */
    public static function findBySlug(string $slug, $guardName = null): ?self
    {
        $guardName = $guardName ?? config('auth.defaults.guard');

        return static::where('slug', $slug)
            ->where('guard_name', $guardName)
            ->first();
    }

    /**
     * Create a new permission.
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
