<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use Illuminate\Console\Command;

class TestTranslationsCommand extends Command
{
    protected $signature = 'filament-tenancy:test-translations';
    
    protected $description = 'Test translations based on current APP_LOCALE configuration';

    public function handle()
    {
        $this->info('🌐 Testing Filament Tenancy Translations');
        $this->info('Current APP_LOCALE: ' . app()->getLocale());
        $this->info('Current APP_FALLBACK_LOCALE: ' . config('app.fallback_locale'));
        $this->newLine();

        $tests = [
            'tenancy.plans' => 'Navigation: Plans',
            'tenancy.tenants' => 'Navigation: Tenants', 
            'tenancy.roles' => 'Navigation: Roles',
            'tenancy.permissions' => 'Navigation: Permissions',
            'tenancy.billing_management' => 'Group: Billing Management',
            'tenancy.user_management' => 'Group: User Management',
            'tenancy.admin_management' => 'Group: Admin Management',
            'tenancy.plan' => 'Model: Plan',
            'tenancy.tenant' => 'Model: Tenant',
            'tenancy.role' => 'Model: Role',
            'tenancy.permission' => 'Model: Permission',
            'tenancy.plan_information' => 'Section: Plan Information',
            'tenancy.basic_information' => 'Section: Basic Information',
            'tenancy.role_information' => 'Section: Role Information',
            'tenancy.permission_information' => 'Section: Permission Information',
            'tenancy.name' => 'Field: Name',
            'tenancy.description' => 'Field: Description',
            'tenancy.price' => 'Field: Price',
            'tenancy.color' => 'Field: Color',
            'tenancy.is_active' => 'Field: Active',
            'tenancy.monthly' => 'Cycle: Monthly',
            'tenancy.yearly' => 'Cycle: Yearly',
            'tenancy.quarterly' => 'Cycle: Quarterly',
            'tenancy.lifetime' => 'Cycle: Lifetime',
            'tenancy.view' => 'Action: View',
            'tenancy.edit' => 'Action: Edit',
            'tenancy.create' => 'Action: Create',
            'tenancy.delete' => 'Action: Delete',
            'tenancy.save' => 'Action: Save',
            'tenancy.cancel' => 'Action: Cancel',
        ];

        $this->info('📋 Translation Tests:');
        $this->newLine();

        foreach ($tests as $key => $description) {
            $translation = __($key);
            $status = ($translation !== $key) ? '✅' : '❌';
            
            $this->line("{$status} {$description}");
            $this->line("   Key: {$key}");
            $this->line("   Result: \"{$translation}\"");
            $this->newLine();
        }

        $this->info('🎯 Test completed!');
        $this->info('If you see ❌ marks, translations are missing for the current locale.');
        
        return 0;
    }
}
