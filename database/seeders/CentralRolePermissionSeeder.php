<?php

namespace Database\Seeders;

use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;

class CentralRolePermissionSeeder extends Seeder
{
    /**
     * Run the database seeds for central database.
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

        // Create permissions for central management
        $permissions = [
            ['name' => 'manage tenants', 'slug' => 'manage-tenants', 'description' => 'Gestionar tenants'],
            ['name' => 'manage plans', 'slug' => 'manage-plans', 'description' => 'Gestionar planes de suscripción'],
            ['name' => 'manage subscriptions', 'slug' => 'manage-subscriptions', 'description' => 'Gestionar suscripciones'],
            ['name' => 'manage central users', 'slug' => 'manage-central-users', 'description' => 'Gestionar usuarios centrales'],
            ['name' => 'manage central roles', 'slug' => 'manage-central-roles', 'description' => 'Gestionar roles centrales'],
            ['name' => 'manage central permissions', 'slug' => 'manage-central-permissions', 'description' => 'Gestionar permisos centrales'],
            ['name' => 'view central dashboard', 'slug' => 'view-central-dashboard', 'description' => 'Ver dashboard central'],
            ['name' => 'manage system settings', 'slug' => 'manage-system-settings', 'description' => 'Gestionar configuración del sistema'],
            ['name' => 'access landlord panel', 'slug' => 'access-landlord-panel', 'description' => 'Acceder al panel landlord'],
            ['name' => 'manage tenant databases', 'slug' => 'manage-tenant-databases', 'description' => 'Gestionar bases de datos de tenants'],
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

        // Create central roles
        $roles = [
            [
                'name' => 'Super Admin',
                'slug' => 'super-admin',
                'description' => 'Super administrador central con acceso total',
                'permissions' => [
                    'manage tenants', 'manage plans', 'manage subscriptions', 'manage central users', 
                    'manage central roles', 'manage central permissions', 'view central dashboard', 
                    'manage system settings', 'access landlord panel', 'manage tenant databases'
                ]
            ],
            [
                'name' => 'Landlord Admin',
                'slug' => 'landlord-admin',
                'description' => 'Administrador landlord con permisos de gestión',
                'permissions' => [
                    'manage tenants', 'manage plans', 'manage subscriptions', 'view central dashboard', 
                    'access landlord panel'
                ]
            ],
            [
                'name' => 'Support',
                'slug' => 'support',
                'description' => 'Usuario de soporte con permisos limitados',
                'permissions' => ['view central dashboard', 'access landlord panel']
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

        $this->command->info('✓ Roles y permisos centrales creados correctamente');
        $this->command->info('  • Super Admin: Todos los permisos centrales');
        $this->command->info('  • Landlord Admin: Permisos de gestión de tenants');
        $this->command->info('  • Support: Permisos de solo lectura');
    }
}
