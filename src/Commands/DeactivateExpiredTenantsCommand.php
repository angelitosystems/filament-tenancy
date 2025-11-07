<?php

namespace AngelitoSystems\FilamentTenancy\Commands;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Models\Tenant;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Log;

class DeactivateExpiredTenantsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'tenancy:deactivate-expired-tenants';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Deactivate tenants whose subscriptions have expired beyond the grace period';

    /**
     * Execute the console command.
     */
    public function handle(): int
    {
        $gracePeriodDays = config('filament-tenancy.paypal.grace_period_days', 7);
        $cutoffDate = now()->subDays($gracePeriodDays);

        $this->info("Checking for tenants with subscriptions expired before {$cutoffDate->toDateString()}...");

        // Find subscriptions that expired beyond grace period
        $expiredSubscriptions = Subscription::where('status', Subscription::STATUS_ACTIVE)
            ->where('ends_at', '<=', $cutoffDate)
            ->whereNotNull('ends_at')
            ->with('tenant')
            ->get();

        $deactivatedCount = 0;

        foreach ($expiredSubscriptions as $subscription) {
            $tenant = $subscription->tenant;

            if (!$tenant) {
                continue;
            }

            // Check if tenant has any other active subscription
            $hasActiveSubscription = $tenant->subscriptions()
                ->where('status', Subscription::STATUS_ACTIVE)
                ->where(function ($query) {
                    $query->whereNull('ends_at')
                        ->orWhere('ends_at', '>', now());
                })
                ->where('id', '!=', $subscription->id)
                ->exists();

            // Only deactivate if no other active subscription exists
            if (!$hasActiveSubscription && $tenant->is_active) {
                $tenant->update(['is_active' => false]);
                
                // Update subscription status to expired
                $subscription->update(['status' => Subscription::STATUS_EXPIRED]);

                $deactivatedCount++;

                $this->info("Deactivated tenant: {$tenant->name} (ID: {$tenant->id}) - Subscription expired on {$subscription->ends_at->toDateString()}");

                Log::info('Tenant deactivated due to expired subscription', [
                    'tenant_id' => $tenant->id,
                    'tenant_name' => $tenant->name,
                    'subscription_id' => $subscription->id,
                    'expired_at' => $subscription->ends_at->toIso8601String(),
                    'grace_period_days' => $gracePeriodDays,
                ]);
            }
        }

        if ($deactivatedCount === 0) {
            $this->info('No tenants needed to be deactivated.');
        } else {
            $this->info("Successfully deactivated {$deactivatedCount} tenant(s).");
        }

        return Command::SUCCESS;
    }
}

