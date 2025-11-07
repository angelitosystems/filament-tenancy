<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Widgets;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource;
use Filament\Widgets\StatsOverviewWidget as BaseWidget;
use Filament\Widgets\StatsOverviewWidget\Stat;

class CurrentSubscriptionWidget extends BaseWidget
{
    protected static ?int $sort = 1;

    protected function getStats(): array
    {
        $tenant = Tenancy::current();
        
        if (!$tenant) {
            return [
                Stat::make(static::__('current_plan'), static::__('no_tenant'))
                    ->description(static::__('no_active_tenant_found'))
                    ->color('danger'),
            ];
        }

        $subscription = $tenant->subscriptions()->where('status', 'active')->first();

        if (!$subscription) {
            return [
                Stat::make(static::__('current_plan'), static::__('no_active_subscription'))
                    ->description(static::__('subscribe_to_get_started'))
                    ->color('warning')
                    ->url(fn() => PlanResource::getUrl('index')),
            ];
        }

        $plan = $subscription->plan;
        $billingCycleLabel = static::__($subscription->billing_cycle) ?? ucfirst($subscription->billing_cycle);

        return [
            Stat::make(static::__('current_plan'), $plan->name ?? static::__('no_plan'))
                ->description($billingCycleLabel)
                ->color('success')
                ->icon('heroicon-o-credit-card')
                ->url(fn() => PlanResource::getUrl('view', ['record' => $plan->id])),

            Stat::make(static::__('status'), ucfirst(static::__("status_{$subscription->status}") ?? $subscription->status))
                ->description($subscription->auto_renew ? static::__('auto_renew_enabled') : static::__('auto_renew_disabled'))
                ->color($subscription->status === 'active' ? 'success' : 'warning'),

            Stat::make(static::__('next_billing'), $subscription->next_billing_at?->format('M d, Y') ?? static::__('lifetime'))
                ->description($subscription->ends_at ? static::__('expires_on') . ': ' . $subscription->ends_at->format('M d, Y') : static::__('no_expiration'))
                ->color($subscription->ends_at && $subscription->ends_at->isPast() ? 'danger' : 'primary'),
        ];
    }

    /**
     * Get translation for widget
     */
    protected static function __(string $key, array $replace = [], ?string $locale = null): string
    {
        return PlanResource::__($key, $replace, $locale);
    }
}
