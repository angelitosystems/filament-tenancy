<?php

namespace AngelitoSystems\FilamentTenancy\Widgets;

use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Models\Permission;
use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Resources\PlanResource;
use AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource;
use AngelitoSystems\FilamentTenancy\Resources\TenantResource;
use AngelitoSystems\FilamentTenancy\Resources\UsersResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class StatsOverviewWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        return [
            Stat::make(__('filament-tenancy::tenancy.widgets.total_tenants'), Tenant::count())
                ->description(__('filament-tenancy::tenancy.widgets.total_tenants_description'))
                ->descriptionIcon('heroicon-m-building-office')
                ->color('primary')
                ->chart([7, 3, 4, 5, 6, 3, 5])
                ->url(fn() => TenantResource::getUrl('index')),

            Stat::make(__('filament-tenancy::tenancy.widgets.active_tenants'), Tenant::where('is_active', true)->count())
                ->description(__('filament-tenancy::tenancy.widgets.active_tenants_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([5, 4, 3, 5, 6, 7, 8]),

            Stat::make(__('filament-tenancy::tenancy.widgets.total_subscriptions'), Subscription::count())
                ->description(__('filament-tenancy::tenancy.widgets.total_subscriptions_description'))
                ->descriptionIcon('heroicon-m-banknotes')
                ->color('warning')
                ->chart([3, 2, 4, 5, 3, 4, 6])
                ->url(fn() => SubscriptionResource::getUrl('index')),

            Stat::make(__('filament-tenancy::tenancy.widgets.active_subscriptions'), Subscription::where('status', 'active')->count())
                ->description(__('filament-tenancy::tenancy.widgets.active_subscriptions_description'))
                ->descriptionIcon('heroicon-m-check-circle')
                ->color('success')
                ->chart([2, 3, 4, 5, 4, 5, 6]),

            Stat::make(__('filament-tenancy::tenancy.widgets.total_plans'), Plan::count())
                ->description(__('filament-tenancy::tenancy.widgets.total_plans_description'))
                ->descriptionIcon('heroicon-m-credit-card')
                ->color('info')
                ->chart([1, 2, 3, 2, 3, 4, 3])
                ->url(fn() => PlanResource::getUrl('index')),

            Stat::make(__('filament-tenancy::tenancy.widgets.monthly_revenue'), $this->formatCurrency($this->getMonthlyRevenue()))
                ->description(__('filament-tenancy::tenancy.widgets.monthly_revenue_description'))
                ->descriptionIcon('heroicon-m-currency-dollar')
                ->color('success')
                ->chart($this->getRevenueChart()),

            Stat::make(__('filament-tenancy::tenancy.widgets.total_users'), $this->getTotalUsers())
                ->description(__('filament-tenancy::tenancy.widgets.total_users_description'))
                ->descriptionIcon('heroicon-m-users')
                ->color('info')
                ->chart([2, 3, 4, 5, 4, 5, 6])
                ->url(fn() => UsersResource::getUrl('index')),

            Stat::make(__('filament-tenancy::tenancy.widgets.total_roles'), Role::count())
                ->description(__('filament-tenancy::tenancy.widgets.total_roles_description'))
                ->descriptionIcon('heroicon-m-shield-check')
                ->color('warning')
                ->chart([1, 2, 2, 3, 2, 3, 2]),

            Stat::make(__('filament-tenancy::tenancy.widgets.total_permissions'), Permission::count())
                ->description(__('filament-tenancy::tenancy.widgets.total_permissions_description'))
                ->descriptionIcon('heroicon-m-key')
                ->color('danger')
                ->chart([5, 6, 7, 8, 7, 8, 9]),
        ];
    }

    protected function getTotalUsers(): int
    {
        try {
            $userModelClass = UsersResource::getModel();
            return $userModelClass::count();
        } catch (\Exception $e) {
            return 0;
        }
    }

    protected function getMonthlyRevenue(): float
    {
        return Subscription::where('status', 'active')
            ->where('billing_cycle', 'monthly')
            ->sum('price') ?? 0;
    }

    protected function getRevenueChart(): array
    {
        // Get last 7 days revenue
        $revenue = [];
        for ($i = 6; $i >= 0; $i--) {
            $date = now()->subDays($i)->startOfDay();
            $revenue[] = Subscription::where('status', 'active')
                ->where('billing_cycle', 'monthly')
                ->whereDate('created_at', '<=', $date)
                ->sum('price') ?? 0;
        }
        return $revenue;
    }

    protected function formatCurrency(float $amount, string $currency = 'USD'): string
    {
        if (function_exists('money')) {
            return money($amount, $currency);
        }

        // Fallback formatting
        $symbols = [
            'USD' => '$',
            'EUR' => '€',
            'GBP' => '£',
            'MXN' => '$',
        ];

        $symbol = $symbols[$currency] ?? $currency . ' ';
        return $symbol . number_format($amount, 2);
    }
}

