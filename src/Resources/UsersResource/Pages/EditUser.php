<?php

namespace AngelitoSystems\FilamentTenancy\Resources\UsersResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\UsersResource;
use Filament\Resources\Pages\EditRecord;

class EditUser extends EditRecord
{
    protected static string $resource = UsersResource::class;

    protected ?array $rolesToSync = null;

    protected function mutateFormDataBeforeFill(array $data): array
    {
        // Cargar los roles del usuario antes de llenar el formulario
        if ($this->record && method_exists($this->record, 'roles')) {
            $this->record->load('roles');
            $data['roles'] = $this->record->roles->pluck('id')->toArray();
        }

        return $data;
    }

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Si el campo viene vacío, se establece como null
        if (empty($data['email_verified_at'])) {
            $data['email_verified_at'] = null;
        }

        // Eliminar contraseñas vacías o sin confirmar
        if (empty($data['password'])) {
            unset($data['password']);
        }
        unset($data['password_confirmation']);

        // Sincronizar roles (si se envían)
        // IMPORTANTE: Siempre eliminar 'roles' del array de datos para evitar que Filament lo sincronice automáticamente
        if (isset($data['roles']) && is_array($data['roles'])) {
            // Los roles pueden venir como array vacío si se desmarcaron todos
            $this->rolesToSync = array_filter(
                array_map('intval', $data['roles']),
                fn($id) => $id > 0
            );
        } else {
            // Si no se envía el campo (no debería pasar con dehydrated(true), pero por seguridad)
            // Mantener roles actuales
            if ($this->record && method_exists($this->record, 'roles')) {
                $this->record->load('roles');
                $this->rolesToSync = $this->record->roles->pluck('id')->toArray();
            } else {
                $this->rolesToSync = [];
            }
        }

        // Siempre eliminar 'roles' para que Filament no lo sincronice automáticamente
        unset($data['roles']);

        return $data;
    }

    protected function afterSave(): void
    {
        // Sincronizar roles
        if (method_exists($this->record, 'syncRoles')) {
            $this->record->syncRoles($this->rolesToSync ?? []);
        } elseif (method_exists($this->record, 'roles')) {
            // Fallback: usar la relación directamente con model_type
            $userClass = get_class($this->record);
            $syncData = [];
            foreach ($this->rolesToSync ?? [] as $roleId) {
                $syncData[$roleId] = ['model_type' => $userClass];
            }
            $this->record->roles()->sync($syncData);
        }

        $this->record->refresh()->load('roles');
    }

    protected function getRedirectUrl(): string
    {
        return $this->getResource()::getUrl('index');
    }
}
