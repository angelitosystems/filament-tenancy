<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Hash;
use Illuminate\Support\Facades\Validator;
use Illuminate\Validation\ValidationException;

class CreateCentralAdminCommand extends Command
{
    protected $signature = 'filament-tenancy:create-central-admin 
                            {--name= : Name of the admin user}
                            {--email= : Email of the admin user}
                            {--password= : Password for the admin user}
                            {--force : Force creation even if user exists}';

    protected $description = 'Create a central admin user with super admin role';

    public function handle(): int
    {
        try {
            $this->info('🔧 Creating Central Admin User...');

            // Get user input
            $name = $this->option('name') ?: $this->ask('Enter admin name');
            $email = $this->option('email') ?: $this->ask('Enter admin email');
            $password = $this->option('password') ?: $this->secret('Enter admin password');
            $force = $this->option('force');

            // Validate input
            $validator = Validator::make([
                'name' => $name,
                'email' => $email,
                'password' => $password,
            ], [
                'name' => ['required', 'string', 'max:255'],
                'email' => ['required', 'email', 'max:255'],
                'password' => ['required', 'string', 'min:8'],
            ]);

            if ($validator->fails()) {
                throw new ValidationException($validator);
            }

            // Check if user already exists
            $userModel = config('filament-tenancy.user_model', config('auth.providers.users.model', 'App\\Models\\User'));
            
            if (!class_exists($userModel)) {
                $this->error("User model {$userModel} not found!");
                return 1;
            }

            $existingUser = $userModel::where('email', $email)->first();
            
            if ($existingUser && !$force) {
                $this->warn("A user with email {$email} already exists!");
                if (!$this->confirm('Do you want to update this user to super admin?')) {
                    $this->info('Operation cancelled.');
                    return 0;
                }
                $user = $existingUser;
            } else {
                $user = $existingUser ?: new $userModel();
            }

            // Create or update user
            $user->name = $name;
            $user->email = $email;
            $user->password = Hash::make($password);
            $user->email_verified_at = now();
            
            // Save user with additional fields if they exist
            if (isset($user->is_active)) {
                $user->is_active = true;
            }
            
            $user->save();

            // Get or create Super Admin role
            $superAdminRole = Role::where('slug', 'super-admin')->first();
            
            if (!$superAdminRole) {
                $this->warn('Super Admin role not found. Creating it...');
                $superAdminRole = Role::create([
                    'name' => 'Super Admin',
                    'slug' => 'super-admin',
                    'description' => 'Super administrador central con acceso total',
                    'guard_name' => config('auth.defaults.guard', 'web'),
                    'is_active' => true,
                ]);
                $this->info('✓ Super Admin role created');
            }

            // Assign role to user
            if (method_exists($user, 'assignRole')) {
                $user->syncRoles([$superAdminRole]);
            } else {
                // Fallback: Direct database assignment
                $userClass = get_class($user);
                DB::table('model_has_roles')->updateOrInsert(
                    [
                        'role_id' => $superAdminRole->id,
                        'model_type' => $userClass,
                        'model_id' => $user->id,
                    ],
                    [
                        'role_id' => $superAdminRole->id,
                        'model_type' => $userClass,
                        'model_id' => $user->id,
                    ]
                );
            }

            $this->info('✅ Central Admin User created successfully!');
            $this->info('📋 User Details:');
            $this->info("   Name: {$user->name}");
            $this->info("   Email: {$user->email}");
            $this->info("   Role: {$superAdminRole->name}");
            $this->info("   Status: Active");

            DebugHelper::info('Central admin user created', [
                'user_id' => $user->id,
                'email' => $user->email,
                'role' => $superAdminRole->slug,
            ]);

            return 0;

        } catch (ValidationException $e) {
            $this->error('❌ Validation failed:');
            foreach ($e->errors() as $field => $errors) {
                foreach ($errors as $error) {
                    $this->error("   - {$field}: {$error}");
                }
            }
            return 1;
        } catch (\Exception $e) {
            $this->error("❌ Failed to create central admin: {$e->getMessage()}");
            DebugHelper::error("Central admin creation failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
