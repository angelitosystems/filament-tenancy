<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Support\DebugHelper;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Artisan;

class SeedCentralDatabaseCommand extends Command
{
    protected $signature = 'filament-tenancy:seed-central 
                            {--force : Force the operation to run when in production}';

    protected $description = 'Seed the central database with roles and permissions';

    public function handle(): int
    {
        try {
            $this->info('🌱 Seeding Central Database...');

            // Run the central seeder
            $result = Artisan::call('db:seed', [
                '--class' => 'Database\\Seeders\\CentralRolePermissionSeeder',
                '--force' => $this->option('force'),
            ]);

            if ($result === 0) {
                $this->info('✅ Central database seeded successfully!');
                $this->info('📋 Created:');
                $this->info('   • 10 central permissions');
                $this->info('   • 3 central roles (Super Admin, Landlord Admin, Support)');
                
                DebugHelper::info('Central database seeded successfully');
            } else {
                $this->error('❌ Failed to seed central database');
                DebugHelper::error('Central database seeding failed', [
                    'exit_code' => $result,
                    'output' => Artisan::output(),
                ]);
                return 1;
            }

            return $result;

        } catch (\Exception $e) {
            $this->error("❌ Failed to seed central database: {$e->getMessage()}");
            DebugHelper::error("Central database seeding failed", [
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString(),
            ]);
            return 1;
        }
    }
}
