<?php

namespace AngelitoSystems\FilamentTenancy\Resources\UsersResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\UsersResource;
use Filament\Resources\Pages\CreateRecord;

class CreateUser extends CreateRecord
{
    protected static string $resource = UsersResource::class;

    protected ?array $rolesToSync = null;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Handle email verification
        if (isset($data['email_verified_at']) && $data['email_verified_at']) {
            $data['email_verified_at'] = now();
        } else {
            unset($data['email_verified_at']);
        }

        // Remove password_confirmation if present
        unset($data['password_confirmation']);

        // Store roles to sync after creation
        // Asegurar que siempre sea un array de IDs
        $this->rolesToSync = isset($data['roles']) && is_array($data['roles']) 
            ? array_filter($data['roles']) // Filtrar valores vacíos
            : [];
        unset($data['roles']);

        return $data;
    }

    protected function afterCreate(): void
    {
        // Sync roles with model_type after creation
        if (!empty($this->rolesToSync)) {
            // Asegurar que syncRoles reciba los IDs correctos
            if (method_exists($this->record, 'syncRoles')) {
                $this->record->syncRoles($this->rolesToSync);
            } elseif (method_exists($this->record, 'roles')) {
                // Fallback: usar la relación directamente con model_type
                $userClass = get_class($this->record);
                $syncData = [];
                foreach ($this->rolesToSync as $roleId) {
                    $syncData[$roleId] = ['model_type' => $userClass];
                }
                $this->record->roles()->sync($syncData);
            }
        }
        
        // Refrescar el modelo para cargar las relaciones actualizadas
        $this->record->refresh();
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}

