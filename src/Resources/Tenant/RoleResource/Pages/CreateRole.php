<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource;
use Filament\Resources\Pages\CreateRecord;

class CreateRole extends CreateRecord
{
    protected static string $resource = RoleResource::class;

    protected ?array $permissionsToSync = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Store permissions to sync after creation
        // Asegurar que siempre sea un array de IDs
        $this->permissionsToSync = isset($data['permissions']) && is_array($data['permissions']) 
            ? array_filter($data['permissions']) // Filtrar valores vacíos
            : [];
        unset($data['permissions']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync permissions with role after creation
        if (!empty($this->permissionsToSync)) {
            // Asegurar que syncPermissions reciba los IDs correctos
            if (method_exists($this->record, 'syncPermissions')) {
                $this->record->syncPermissions($this->permissionsToSync);
            } elseif (method_exists($this->record, 'permissions')) {
                // Fallback: usar la relación directamente
                $this->record->permissions()->sync($this->permissionsToSync);
            }
        }
        
        // Refrescar el modelo para cargar las relaciones actualizadas
        $this->record->refresh();
    }
}
