<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource;
use Filament\Resources\Pages\ListRecords;

class ListSubscriptions extends ListRecords
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
