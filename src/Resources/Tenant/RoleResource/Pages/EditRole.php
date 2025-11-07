<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource;
use Filament\Resources\Pages\EditRecord;

class EditRole extends EditRecord
{
    protected static string $resource = RoleResource::class;

    protected ?array $permissionsToSync = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Store permissions to sync after save
        // Asegurar que siempre sea un array de IDs
        $this->permissionsToSync = isset($data['permissions']) && is_array($data['permissions']) 
            ? array_filter($data['permissions']) // Filtrar valores vacíos
            : [];
        unset($data['permissions']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Sync permissions with role after save
        if (!empty($this->permissionsToSync)) {
            // Asegurar que syncPermissions reciba los IDs correctos
            if (method_exists($this->record, 'syncPermissions')) {
                $this->record->syncPermissions($this->permissionsToSync);
            } elseif (method_exists($this->record, 'permissions')) {
                // Fallback: usar la relación directamente
                $this->record->permissions()->sync($this->permissionsToSync);
            }
        } else {
            // Si no hay permisos seleccionados, limpiar todos los permisos
            if (method_exists($this->record, 'permissions')) {
                $this->record->permissions()->detach();
            }
        }
        
        // Refrescar el modelo para cargar las relaciones actualizadas
        $this->record->refresh();
    }
}
