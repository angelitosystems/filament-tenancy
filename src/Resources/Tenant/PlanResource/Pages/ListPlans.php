<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource;
use AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Widgets\CurrentSubscriptionWidget;
use Filament\Resources\Pages\ListRecords;

class ListPlans extends ListRecords
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderWidgets(): array
    {
        return [
            CurrentSubscriptionWidget::class,
        ];
    }

    protected function getHeaderActions(): array
    {
        return [
            // No create action for tenants
        ];
    }

    public function getTitle(): string
    {
        return PlanResource::__('available_plans');
    }
}
