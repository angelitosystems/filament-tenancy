<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PermissionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PermissionResource;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
