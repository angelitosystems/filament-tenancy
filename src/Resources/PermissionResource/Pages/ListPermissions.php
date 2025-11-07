<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PermissionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PermissionResource;
use AngelitoSystems\FilamentTenancy\Traits\BlocksSubscriptionRestrictedAccess;
use Filament\Resources\Pages\ListRecords;

class ListPermissions extends ListRecords
{
    use BlocksSubscriptionRestrictedAccess;

    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
