<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource;
use Filament\Resources\Pages\ListRecords;

class ListRoles extends ListRecords
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
