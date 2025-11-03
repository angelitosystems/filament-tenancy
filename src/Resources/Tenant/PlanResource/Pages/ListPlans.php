<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            // No create action for tenants
        ];
    }

    public function getTitle(): string
    {
        return 'Available Plans';
    }

    protected function getHeaderWidgets(): array
    {
        return [
            \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Widgets\CurrentSubscriptionWidget::class,
        ];
    }
}
