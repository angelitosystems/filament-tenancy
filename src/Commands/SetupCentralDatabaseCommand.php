<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SetupCentralDatabaseCommand extends Command
{
    protected $signature = 'filament-tenancy:setup-central 
                            {--create-admin : Create a central admin user after setup}
                            {--admin-name= : Name for the central admin}
                            {--admin-email= : Email for the central admin}
                            {--admin-password= : Password for the central admin}
                            {--force : Force the operation to run when in production}';

    protected $description = 'Complete setup of central database with migrations, seeders, and optional admin user';

    public function handle(): int
    {
        try {
            $this->info('🚀 Setting up Central Database for Filament Tenancy...');

            // Step 1: Run central migrations
            $this->info('\n📦 Step 1: Running central migrations...');
            $migrationResult = Artisan::call('migrate', [
                '--path' => 'packages/filament-tenancy/database/migrations',
                '--force' => $this->option('force'),
            ]);

            if ($migrationResult !== 0) {
                $this->error('❌ Central migrations failed');
                return 1;
            }
            $this->info('✅ Central migrations completed');

            // Step 2: Seed central database
            $this->info('\n🌱 Step 2: Seeding central database...');
            $seedResult = Artisan::call('filament-tenancy:seed-central', [
                '--force' => $this->option('force'),
            ]);

            if ($seedResult !== 0) {
                $this->error('❌ Central seeding failed');
                return 1;
            }
            $this->info('✅ Central database seeded');

            // Step 3: Create admin user (optional)
            if ($this->option('create-admin') || $this->confirm('\n👤 Do you want to create a central admin user?')) {
                $this->info('\n🔧 Step 3: Creating central admin user...');
                
                $adminCommand = 'filament-tenancy:create-central-admin';
                $commandOptions = [
                    '--force' => $this->option('force'),
                ];
                
                if ($this->option('admin-name')) {
                    $commandOptions['--name'] = $this->option('admin-name');
                }
                if ($this->option('admin-email')) {
                    $commandOptions['--email'] = $this->option('admin-email');
                }
                if ($this->option('admin-password')) {
                    $commandOptions['--password'] = $this->option('admin-password');
                }
                
                $adminResult = Artisan::call($adminCommand, $commandOptions);
                
                if ($adminResult !== 0) {
                    $this->error('❌ Admin user creation failed');
                    return 1;
                }
                $this->info('✅ Central admin user created');
            }

            $this->info('\n🎉 Central database setup completed successfully!');
            $this->info('\n📋 Summary:');
            $this->info('   • Central tables created: roles, permissions, model_has_*, role_has_permissions');
            $this->info('   • Central permissions: 10 management permissions');
            $this->info('   • Central roles: Super Admin, Landlord Admin, Support');
            if ($this->option('create-admin') || $this->hasOption('create-admin')) {
                $this->info('   • Central admin user: Created with Super Admin role');
            }
            
            $this->info('\n🔗 Next steps:');
            $this->info('   1. Access your landlord panel: /admin');
            $this->info('   2. Login with your central admin credentials');
            $this->info('   3. Start creating tenants and managing your multitenancy setup');

            DebugHelper::info('Central database setup completed successfully');

            return 0;

        } catch (\Exception $e) {
            $this->error("❌ Central database setup failed: {$e->getMessage()}");
            DebugHelper::error("Central database setup failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
