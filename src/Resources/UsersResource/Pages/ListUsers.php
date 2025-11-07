<?php

namespace AngelitoSystems\FilamentTenancy\Resources\UsersResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\UsersResource;
use AngelitoSystems\FilamentTenancy\Traits\BlocksSubscriptionRestrictedAccess;
use Filament\Actions;
use Filament\Resources\Pages\ListRecords;

class ListUsers extends ListRecords
{
    use BlocksSubscriptionRestrictedAccess;

    protected static string $resource = UsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\CreateAction::make(),
        ];
    }
}

