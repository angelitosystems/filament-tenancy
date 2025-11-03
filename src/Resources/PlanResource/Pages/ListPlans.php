<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PlanResource;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}
