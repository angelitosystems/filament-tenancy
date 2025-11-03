<?php

namespace Database\Seeders;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        // Disable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');

        // Clear existing data
        DB::table('model_has_permissions')->truncate();
        DB::table('role_has_permissions')->truncate();
        DB::table('model_has_roles')->truncate();
        Permission::truncate();
        Role::truncate();

        // Create permissions
        $permissions = [
            ['name' => 'manage users', 'slug' => 'manage-users', 'description' => 'Gestionar usuarios'],
            ['name' => 'manage roles', 'slug' => 'manage-roles', 'description' => 'Gestionar roles'],
            ['name' => 'manage permissions', 'slug' => 'manage-permissions', 'description' => 'Gestionar permisos'],
            ['name' => 'manage tenants', 'slug' => 'manage-tenants', 'description' => 'Gestionar tenants'],
            ['name' => 'view dashboard', 'slug' => 'view-dashboard', 'description' => 'Ver dashboard'],
            ['name' => 'manage settings', 'slug' => 'manage-settings', 'description' => 'Gestionar configuración'],
            ['name' => 'create posts', 'slug' => 'create-posts', 'description' => 'Crear posts'],
            ['name' => 'edit posts', 'slug' => 'edit-posts', 'description' => 'Editar posts'],
            ['name' => 'delete posts', 'slug' => 'delete-posts', 'description' => 'Eliminar posts'],
            ['name' => 'publish posts', 'slug' => 'publish-posts', 'description' => 'Publicar posts'],
        ];

        foreach ($permissions as $permission) {
            Permission::create([
                'name' => $permission['name'],
                'slug' => $permission['slug'],
                'description' => $permission['description'],
                'guard_name' => config('auth.defaults.guard', 'web'),
                'is_active' => true,
            ]);
        }

        // Create roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Super administrador con acceso total',
                'permissions' => ['manage users', 'manage roles', 'manage permissions', 'manage tenants', 'view dashboard', 'manage settings', 'create posts', 'edit posts', 'delete posts', 'publish posts']
            ],
            [
                'name' => 'Admin',
                'slug' => 'admin',
                'description' => 'Administrador con permisos limitados',
                'permissions' => ['manage users', 'view dashboard', 'manage settings', 'create posts', 'edit posts', 'publish posts']
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Editor de contenido',
                'permissions' => ['view dashboard', 'create posts', 'edit posts', 'publish posts']
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Usuario básico',
                'permissions' => ['view dashboard']
            ],
        ];

        foreach ($roles as $roleData) {
            $role = Role::create([
                'name' => $roleData['name'],
                'slug' => $roleData['slug'],
                'description' => $roleData['description'],
                'guard_name' => config('auth.defaults.guard', 'web'),
                'is_active' => true,
            ]);

            // Assign permissions to role
            foreach ($roleData['permissions'] as $permissionName) {
                $permission = Permission::where('slug', Str::slug($permissionName))->first();
                if ($permission) {
                    $role->givePermissionTo($permission);
                }
            }
        }

        // Re-enable foreign key checks
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');

        $this->command->info('✓ Roles y permisos creados correctamente');
        $this->command->info('  • Super Admin: Todos los permisos');
        $this->command->info('  • Admin: Permisos de gestión básicos');
        $this->command->info('  • Editor: Permisos de contenido');
        $this->command->info('  • User: Permisos básicos');
    }
}
