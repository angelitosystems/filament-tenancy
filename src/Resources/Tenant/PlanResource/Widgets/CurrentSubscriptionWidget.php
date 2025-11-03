<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Widgets;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentSubscriptionWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenant = Tenancy::currentTenant();
        
        if (!$tenant) {
            return [
                Stat::make('Current Plan', 'No Tenant')
                    ->description('No active tenant found')
                    ->color('danger'),
            ];
        }

        $subscription = $tenant->subscriptions()->where('status', 'active')->first();

        if (!$subscription) {
            return [
                Stat::make('Current Plan', 'No Active Subscription')
                    ->description('Subscribe to a plan to get started')
                    ->color('warning')
                    ->action('View Available Plans'),
            ];
        }

        return [
            Stat::make('Current Plan', $subscription->plan->name)
                ->description($subscription->plan->billing_cycle)
                ->color('success')
                ->icon('heroicon-o-credit-card'),

            Stat::make('Status', ucfirst($subscription->status))
                ->description($subscription->auto_renew ? 'Auto-renew enabled' : 'Auto-renew disabled')
                ->color($subscription->status === 'active' ? 'success' : 'warning'),

            Stat::make('Next Billing', $subscription->next_billing_at?->format('M d, Y') ?? 'Lifetime')
                ->description($subscription->ends_at ? 'Expires: ' . $subscription->ends_at->format('M d, Y') : 'No expiration')
                ->color($subscription->ends_at && $subscription->ends_at->isPast() ? 'danger' : 'primary'),
        ];
    }
}
