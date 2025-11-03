<?php

namespace Database\Seeders\Tenant;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class RolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds for tenant database.
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

        // Create tenant-specific permissions
        $permissions = [
            ['name' => 'manage users', 'slug' => 'manage-users', 'description' => 'Gestionar usuarios del tenant'],
            ['name' => 'manage roles', 'slug' => 'manage-roles', 'description' => 'Gestionar roles del tenant'],
            ['name' => 'manage permissions', 'slug' => 'manage-permissions', 'description' => 'Gestionar permisos del tenant'],
            ['name' => 'view dashboard', 'slug' => 'view-dashboard', 'description' => 'Ver dashboard del tenant'],
            ['name' => 'manage settings', 'slug' => 'manage-settings', 'description' => 'Gestionar configuración del tenant'],
            ['name' => 'create posts', 'slug' => 'create-posts', 'description' => 'Crear posts en el tenant'],
            ['name' => 'edit posts', 'slug' => 'edit-posts', 'description' => 'Editar posts en el tenant'],
            ['name' => 'delete posts', 'slug' => 'delete-posts', 'description' => 'Eliminar posts en el tenant'],
            ['name' => 'publish posts', 'slug' => 'publish-posts', 'description' => 'Publicar posts en el tenant'],
            ['name' => 'manage tenant users', 'slug' => 'manage-tenant-users', 'description' => 'Gestionar usuarios específicos del tenant'],
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

        // Create tenant-specific roles
        $roles = [
            [
                'name' => 'Tenant Admin',
                'slug' => 'tenant-admin',
                'description' => 'Administrador del tenant con acceso completo',
                'permissions' => ['manage users', 'manage roles', 'manage permissions', 'view dashboard', 'manage settings', 'create posts', 'edit posts', 'delete posts', 'publish posts', 'manage tenant users']
            ],
            [
                'name' => 'Manager',
                'slug' => 'manager',
                'description' => 'Gerente con permisos de gestión',
                'permissions' => ['manage users', 'view dashboard', 'manage settings', 'create posts', 'edit posts', 'publish posts']
            ],
            [
                'name' => 'Editor',
                'slug' => 'editor',
                'description' => 'Editor de contenido del tenant',
                'permissions' => ['view dashboard', 'create posts', 'edit posts', 'publish posts']
            ],
            [
                'name' => 'User',
                'slug' => 'user',
                'description' => 'Usuario básico del tenant',
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

        $this->command->info('✓ Roles y permisos del tenant creados correctamente');
        $this->command->info('  • Tenant Admin: Todos los permisos del tenant');
        $this->command->info('  • Manager: Permisos de gestión básicos');
        $this->command->info('  • Editor: Permisos de contenido');
        $this->command->info('  • User: Permisos básicos');
    }
}
