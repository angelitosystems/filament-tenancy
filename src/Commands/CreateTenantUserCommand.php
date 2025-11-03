<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Models\Permission;
use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Support\Str;

class CreateTenantUserCommand extends Command
{
    protected ?Tenant $currentTenant = null;

    /**
     * The name and signature of the console command.
     */
    protected $signature = 'tenant:user-create 
                            {--tenant= : The tenant ID or slug}
                            {--name= : The user name}
                            {--email= : The user email}
                            {--password= : The user password (auto-generated if not provided)}
                            {--role= : The role slug (default: user)}
                            {--permissions= : Comma-separated list of permission slugs}
                            {--list-tenants : List all available tenants}
                            {--list-roles : List all available roles in tenant}
                            {--list-permissions : List all available permissions in tenant}';

    /**
     * The console command description.
     */
    protected $description = 'Create a user for a specific tenant with roles and permissions';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $this->displayBranding();

        // Handle list options
        if ($this->option('list-tenants')) {
            $this->listTenants();
            return self::SUCCESS;
        }

        if ($this->option('list-roles')) {
            $this->listRoles();
            return self::SUCCESS;
        }

        if ($this->option('list-permissions')) {
            $this->listPermissions();
            return self::SUCCESS;
        }

        // Get tenant
        $tenant = $this->getTenant();
        if (!$tenant) {
            return self::FAILURE;
        }
        
        // Store tenant for use in other methods
        $this->currentTenant = $tenant;

        // Get user data
        $userData = $this->getUserData();
        if (!$userData) {
            return self::FAILURE;
        }

        // Create user in tenant context
        try {
            $user = $this->createUserInTenant($this->currentTenant, $userData);
            
            $this->newLine();
            $this->info("✓ Usuario '{$user->name}' creado exitosamente en el tenant '{$this->currentTenant->name}'!");
            
            $this->displayUserInfo($user, $this->currentTenant);
            
            return self::SUCCESS;
        } catch (\Exception $e) {
            $this->error('✗ Error al crear el usuario: ' . $e->getMessage());
            return self::FAILURE;
        }
    }

    /**
     * Display branding.
     */
    protected function displayBranding(): void
    {
        $this->newLine();
        $this->line('╔═══════════════════════════════════════════════════════════════╗');
        $this->line('║                                                               ║');
        $this->line('║           <fg=cyan>Filament Tenancy</fg=cyan> - User Creator        ║');
        $this->line('║                  <fg=yellow>Angelito Systems</fg=yellow>                      ║');
        $this->line('║                                                               ║');
        $this->line('╚═══════════════════════════════════════════════════════════════╝');
        $this->newLine();
    }

    /**
     * Get the tenant.
     */
    protected function getTenant(): ?Tenant
    {
        $tenantIdentifier = $this->option('tenant');
        
        if (!$tenantIdentifier) {
            $this->info('🔍 Selección de tenant');
            $this->newLine();
            
            $tenants = Tenancy::getAllTenants();
            
            if ($tenants->isEmpty()) {
                $this->error('No hay tenants disponibles. Crea un tenant primero con:');
                $this->line('  <fg=yellow>php artisan tenancy:create</fg=yellow>');
                return null;
            }
            
            $choices = [];
            $tenantMap = [];
            $index = 1;
            
            foreach ($tenants as $tenant) {
                $choiceKey = (string) $index;
                $choices[$choiceKey] = "{$tenant->name} ({$tenant->slug}) - " . ($tenant->domain ?: $tenant->subdomain);
                $tenantMap[$choiceKey] = $tenant;
                $index++;
            }
            
            $selectedKey = $this->choice('Selecciona un tenant', $choices);
            $tenant = $tenantMap[$selectedKey] ?? null;
        } else {
            // Try to find by ID first, then by slug
            $tenant = Tenancy::findTenant((int) $tenantIdentifier);
            if (!$tenant) {
                $tenant = Tenancy::findTenantBySlug($tenantIdentifier);
            }
            
            if (!$tenant) {
                $this->error("Tenant '{$tenantIdentifier}' no encontrado.");
                $this->line('Usa <fg=yellow>--list-tenants</fg=yellow> para ver los tenants disponibles.');
                return null;
            }
        }
        
        return $tenant;
    }

    /**
     * Get user data from options or interactively.
     */
    protected function getUserData(): ?array
    {
        $this->info('👤 Datos del usuario');
        $this->newLine();
        
        $name = $this->option('name');
        $email = $this->option('email');
        $password = $this->option('password');
        $roleSlug = $this->option('role');
        $permissions = $this->option('permissions');
        
        // Get name
        if (!$name) {
            $name = $this->ask('Nombre del usuario');
            if (empty($name)) {
                $this->error('El nombre es requerido.');
                return null;
            }
        }
        
        // Get email
        if (!$email) {
            $email = $this->ask('Email del usuario');
        }
        
        if (empty($email)) {
            $this->error('El email es requerido.');
            return null;
        }
        
        // Validate email
        $validator = Validator::make(['email' => $email], [
            'email' => 'required|email',
        ]);
        
        if ($validator->fails()) {
            $this->error('Email inválido: ' . $validator->errors()->first('email'));
            return null;
        }
        
        // Get password
        if (!$password) {
            $password = $this->secret('Contraseña (dejar en blanco para generar automática)');
        }
        
        if (empty($password)) {
            $password = Str::random(12);
            $this->line("  ✓ Contraseña generada: <fg=green>{$password}</fg=green>");
        }
        
        // Get role
        $role = null;
        if ($roleSlug) {
            $role = $this->getRoleBySlug($roleSlug);
            if (!$role) {
                $this->warn("Rol '{$roleSlug}' no encontrado. Se usará el rol por defecto.");
            }
        }
        
        if (!$role) {
            $role = $this->selectRoleInteractively();
            if (!$role) {
                return null;
            }
        }
        
        // Get permissions
        $selectedPermissions = [];
        if ($permissions) {
            $permissionSlugs = array_map('trim', explode(',', $permissions));
            foreach ($permissionSlugs as $slug) {
                $permission = $this->getPermissionBySlug($slug);
                if ($permission) {
                    $selectedPermissions[] = $permission;
                } else {
                    $this->warn("Permiso '{$slug}' no encontrado y será omitido.");
                }
            }
        } else {
            $selectedPermissions = $this->selectPermissionsInteractively();
        }
        
        return [
            'name' => $name,
            'email' => $email,
            'password' => $password,
            'role' => $role,
            'permissions' => $selectedPermissions,
        ];
    }

    /**
     * Select role interactively.
     */
    protected function selectRoleInteractively(): ?Role
    {
        $roles = Role::all();
        
        if ($roles->isEmpty()) {
            $this->error('No hay roles disponibles. Asegúrate de que el tenant tenga roles creados.');
            return null;
        }
        
        $choices = [];
        $roleMap = [];
        $index = 1;
        
        foreach ($roles as $role) {
            $choiceKey = (string) $index;
            $choices[$choiceKey] = "{$role->name} ({$role->slug})";
            $roleMap[$choiceKey] = $role;
            $index++;
        }
        
        $selectedKey = $this->choice('Selecciona un rol para el usuario', $choices, '1');
        return $roleMap[$selectedKey] ?? null;
    }

    /**
     * Select permissions interactively.
     */
    protected function selectPermissionsInteractively(): array
    {
        $permissions = Permission::all();
        
        if ($permissions->isEmpty()) {
            $this->warn('No hay permisos disponibles. El usuario tendrá solo los permisos del rol.');
            return [];
        }
        
        if (!$this->confirm('¿Deseas asignar permisos adicionales además del rol?', false)) {
            return [];
        }
        
        $choices = [];
        $permissionMap = [];
        $index = 1;
        
        foreach ($permissions as $permission) {
            $choiceKey = (string) $index;
            $choices[$choiceKey] = "{$permission->name} ({$permission->slug})";
            $permissionMap[$choiceKey] = $permission;
            $index++;
        }
        
        $selectedKeys = $this->choice(
            'Selecciona permisos adicionales (separa múltiples con comas)',
            $choices,
            null,
            null,
            true
        );
        
        $selectedPermissions = [];
        foreach ($selectedKeys as $key) {
            if (isset($permissionMap[$key])) {
                $selectedPermissions[] = $permissionMap[$key];
            }
        }
        
        return $selectedPermissions;
    }

    /**
     * Create user in tenant context.
     */
    protected function createUserInTenant(Tenant $tenant, array $userData): User
    {
        return Tenancy::runForTenant($tenant, function () use ($userData) {
            // Create user
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make($userData['password']),
            ]);
            
            // Assign role
            if ($userData['role']) {
                $user->assignRole($userData['role']);
            }
            
            // Assign additional permissions
            foreach ($userData['permissions'] as $permission) {
                $user->givePermissionTo($permission);
            }
            
            return $user;
        });
    }

    /**
     * Display user information.
     */
    protected function displayUserInfo($user, Tenant $tenant): void
    {
        $this->newLine();
        $this->table(
            ['Propiedad', 'Valor'],
            [
                ['Tenant', $tenant->name . ' (' . $tenant->slug . ')'],
                ['ID', $user->id],
                ['Nombre', $user->name],
                ['Email', $user->email],
                ['Rol', $user->roles->first()?->name ?? 'N/A'],
                ['Permisos adicionales', $user->permissions->count() . ' permisos'],
                ['Creado', $user->created_at->format('Y-m-d H:i:s')],
                ['URL del tenant', $tenant->getUrl()],
            ]
        );
        
        $this->newLine();
        $this->info('🔐 Información de acceso:');
        $this->line("  • URL del panel: <fg=cyan>{$tenant->getUrl()}/admin</fg=cyan>");
        $this->line("  • Email: <fg=cyan>{$user->email}</fg=cyan>");
        $this->line("  • Contraseña: <fg=yellow>La que proporcionaste o la generada automáticamente</fg=yellow>");
        $this->newLine();
    }

    /**
     * List all tenants.
     */
    protected function listTenants(): void
    {
        $this->info('📋 Lista de tenants disponibles:');
        $this->newLine();
        
        $tenants = Tenancy::getAllTenants();
        
        if ($tenants->isEmpty()) {
            $this->line('No hay tenants disponibles.');
            return;
        }
        
        $data = [];
        foreach ($tenants as $tenant) {
            $data[] = [
                'ID' => $tenant->id,
                'Nombre' => $tenant->name,
                'Slug' => $tenant->slug,
                'Dominio' => $tenant->domain ?: $tenant->subdomain,
                'Activo' => $tenant->is_active ? 'Sí' : 'No',
                'URL' => $tenant->getUrl(),
            ];
        }
        
        $this->table(['ID', 'Nombre', 'Slug', 'Dominio', 'Activo', 'URL'], $data);
    }

    /**
     * List roles in tenant context.
     */
    protected function listRoles(): void
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return;
        }
        
        $this->info("📋 Roles disponibles en el tenant '{$tenant->name}':");
        $this->newLine();
        
        Tenancy::runForTenant($tenant, function () {
            $roles = Role::with('permissions')->get();
            
            if ($roles->isEmpty()) {
                $this->line('No hay roles disponibles en este tenant.');
                return;
            }
            
            $data = [];
            foreach ($roles as $role) {
                $data[] = [
                    'Nombre' => $role->name,
                    'Slug' => $role->slug,
                    'Permisos' => $role->permissions->count(),
                    'Descripción' => $role->description ?? 'N/A',
                ];
            }
            
            $this->table(['Nombre', 'Slug', 'Permisos', 'Descripción'], $data);
        });
    }

    /**
     * List permissions in tenant context.
     */
    protected function listPermissions(): void
    {
        $tenant = $this->getTenant();
        if (!$tenant) {
            return;
        }
        
        $this->info("📋 Permisos disponibles en el tenant '{$tenant->name}':");
        $this->newLine();
        
        Tenancy::runForTenant($tenant, function () {
            $permissions = Permission::all();
            
            if ($permissions->isEmpty()) {
                $this->line('No hay permisos disponibles en este tenant.');
                return;
            }
            
            $data = [];
            foreach ($permissions as $permission) {
                $data[] = [
                    'Nombre' => $permission->name,
                    'Slug' => $permission->slug,
                    'Activo' => $permission->is_active ? 'Sí' : 'No',
                    'Descripción' => $permission->description ?? 'N/A',
                ];
            }
            
            $this->table(['Nombre', 'Slug', 'Activo', 'Descripción'], $data);
        });
    }

    /**
     * Get role by slug in tenant context.
     */
    protected function getRoleBySlug(string $slug): ?Role
    {
        return Tenancy::runForCentral(function () use ($slug) {
            return Role::where('slug', $slug)->first();
        });
    }

    /**
     * Get permission by slug in tenant context.
     */
    protected function getPermissionBySlug(string $slug): ?Permission
    {
        return Tenancy::runForCentral(function () use ($slug) {
            return Permission::where('slug', $slug)->first();
        });
    }
}
