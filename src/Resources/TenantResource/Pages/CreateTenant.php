<?php

namespace AngelitoSystems\FilamentTenancy\Resources\TenantResource\Pages;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Resources\TenantResource;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\CreateRecord;

class CreateTenant extends CreateRecord
{
    protected static string $resource = TenantResource::class;

    protected function handleRecordCreation(array $data): \Illuminate\Database\Eloquent\Model
    {
        try {
            // Use the Tenancy facade to create the tenant with all the hooks
            $tenant = Tenancy::createTenant($data);

            // Build success message with details
            $details = [];
            
            if (config('filament-tenancy.database.auto_create', true)) {
                $details[] = "✅ Database '{$tenant->database_name}' created";
            }
            
            if (config('filament-tenancy.migrations.auto_run', true)) {
                $details[] = "✅ Migrations executed";
            }
            
            if (config('filament-tenancy.seeders.auto_run', true)) {
                $seederCount = count(config('filament-tenancy.seeders.classes', []));
                if ($seederCount > 0) {
                    $details[] = "✅ {$seederCount} seeders executed";
                }
            }

            $bodyMessage = "Tenant '{$tenant->name}' has been created successfully.";
            if (!empty($details)) {
                $bodyMessage .= "\n\n" . implode("\n", $details);
            }

            Notification::make()
                ->title('🎉 Tenant created successfully!')
                ->body($bodyMessage)
                ->success()
                ->duration(8000) // Show longer to read details
                ->send();

            return $tenant;
        } catch (\Exception $e) {
            Notification::make()
                ->title('❌ Failed to create tenant')
                ->body("Error: {$e->getMessage()}")
                ->danger()
                ->duration(10000)
                ->send();

            throw $e;
        }
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}