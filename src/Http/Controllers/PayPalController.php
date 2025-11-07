<?php

namespace AngelitoSystems\FilamentTenancy\Http\Controllers;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Models\Commission;
use AngelitoSystems\FilamentTenancy\Models\Invoice;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Carbon\Carbon;

class PayPalController
{
    protected PayPalService $paypalService;

    public function __construct(PayPalService $paypalService)
    {
        $this->paypalService = $paypalService;
    }

    /**
     * Handle PayPal webhook notifications.
     */
    public function webhook(Request $request)
    {
        $headers = $request->headers->all();
        $body = $request->getContent();

        // Verify webhook signature
        if (!$this->paypalService->verifyWebhook($headers, $body)) {
            Log::warning('PayPal: Webhook signature verification failed', [
                'headers' => $headers,
            ]);

            return response()->json(['error' => 'Invalid signature'], 400);
        }

        $event = json_decode($body, true);
        $eventType = $event['event_type'] ?? '';

        Log::info('PayPal: Webhook received', [
            'event_type' => $eventType,
            'resource' => $event['resource'] ?? null,
        ]);

        try {
            switch ($eventType) {
                case 'PAYMENT.CAPTURE.COMPLETED':
                    $this->handlePaymentCompleted($event);
                    break;

                case 'PAYMENT.CAPTURE.DENIED':
                case 'PAYMENT.CAPTURE.REFUNDED':
                    $this->handlePaymentFailed($event);
                    break;

                case 'BILLING.SUBSCRIPTION.CREATED':
                case 'BILLING.SUBSCRIPTION.ACTIVATED':
                    $this->handleSubscriptionActivated($event);
                    break;

                case 'BILLING.SUBSCRIPTION.CANCELLED':
                case 'BILLING.SUBSCRIPTION.EXPIRED':
                    $this->handleSubscriptionCancelled($event);
                    break;

                case 'BILLING.SUBSCRIPTION.PAYMENT.FAILED':
                    $this->handleSubscriptionPaymentFailed($event);
                    break;

                case 'BILLING.SUBSCRIPTION.UPDATED':
                    $this->handleSubscriptionUpdated($event);
                    break;

                default:
                    Log::info('PayPal: Unhandled webhook event', [
                        'event_type' => $eventType,
                    ]);
            }

            return response()->json(['status' => 'success'], 200);
        } catch (\Exception $e) {
            Log::error('PayPal: Error processing webhook', [
                'error' => $e->getMessage(),
                'event_type' => $eventType,
                'trace' => $e->getTraceAsString(),
            ]);

            return response()->json(['error' => 'Processing failed'], 500);
        }
    }

    /**
     * Handle successful payment completion.
     */
    protected function handlePaymentCompleted(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $customId = $resource['custom_id'] ?? null;

        if (!$customId) {
            return;
        }

        $subscription = Subscription::find($customId);
        
        if (!$subscription) {
            Log::warning('PayPal: Subscription not found', [
                'subscription_id' => $customId,
            ]);
            return;
        }

        // Activate subscription
        $plan = $subscription->plan;
        $endsAt = null;

        if ($plan->billing_cycle !== 'lifetime') {
            $endsAt = match($plan->billing_cycle) {
                'monthly' => now()->addMonth(),
                'yearly' => now()->addYear(),
                'quarterly' => now()->addMonths(3),
                default => now()->addMonth(),
            };
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => $endsAt,
            'next_billing_at' => $endsAt,
            'payment_method' => 'paypal',
            'metadata' => array_merge($subscription->metadata ?? [], [
                'paypal_capture_id' => $resource['id'] ?? null,
                'paypal_payment_status' => 'completed',
                'last_payment_at' => now()->toIso8601String(),
            ]),
        ]);

        // Activate tenant if not already active
        $tenant = $subscription->tenant;
        if ($tenant && !$tenant->is_active) {
            $tenant->update(['is_active' => true]);
            Log::info('PayPal: Tenant activated after payment', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
            ]);
        }

        // Calculate and create commission if seller exists
        $this->createCommission($subscription);

        // Create invoice for the payment
        $this->createInvoice($subscription, $resource);

        Log::info('PayPal: Subscription activated', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle payment failure.
     */
    protected function handlePaymentFailed(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $customId = $resource['custom_id'] ?? null;

        if (!$customId) {
            return;
        }

        $subscription = Subscription::find($customId);
        
        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => Subscription::STATUS_PENDING,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'paypal_payment_status' => 'failed',
                'payment_failure_reason' => $resource['status_details']['reason'] ?? 'unknown',
            ]),
        ]);

        Log::warning('PayPal: Payment failed', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription activation.
     */
    protected function handleSubscriptionActivated(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('external_id', $subscriptionId)
            ->orWhereJsonContains('metadata->paypal_subscription_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            Log::warning('PayPal: Subscription not found for activation', [
                'paypal_subscription_id' => $subscriptionId,
            ]);
            return;
        }

        $plan = $subscription->plan;
        $endsAt = null;

        if ($plan->billing_cycle !== 'lifetime') {
            $endsAt = match($plan->billing_cycle) {
                'monthly' => now()->addMonth(),
                'yearly' => now()->addYear(),
                'quarterly' => now()->addMonths(3),
                default => now()->addMonth(),
            };
        }

        $subscription->update([
            'status' => Subscription::STATUS_ACTIVE,
            'starts_at' => now(),
            'ends_at' => $endsAt,
            'next_billing_at' => $endsAt,
            'payment_method' => 'paypal',
            'external_id' => $subscriptionId,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'paypal_subscription_id' => $subscriptionId,
                'paypal_status' => $resource['status'] ?? 'ACTIVE',
            ]),
        ]);

        // Activate tenant if not already active
        $tenant = $subscription->tenant;
        if ($tenant && !$tenant->is_active) {
            $tenant->update(['is_active' => true]);
            Log::info('PayPal: Tenant activated after subscription activation', [
                'tenant_id' => $tenant->id,
                'subscription_id' => $subscription->id,
            ]);
        }

        // Calculate and create commission if seller exists
        $this->createCommission($subscription);

        // Create invoice for the subscription activation
        $this->createInvoice($subscription, $resource);

        Log::info('PayPal: Subscription activated', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Create invoice for subscription payment.
     */
    protected function createInvoice(Subscription $subscription, array $resource = []): void
    {
        // Check if invoice already exists for this subscription payment
        $existingInvoice = Invoice::where('subscription_id', $subscription->id)
            ->where('status', Invoice::STATUS_PAID)
            ->whereDate('created_at', today())
            ->first();

        if ($existingInvoice) {
            Log::info('PayPal: Invoice already exists for subscription payment', [
                'subscription_id' => $subscription->id,
                'invoice_id' => $existingInvoice->id,
            ]);
            return;
        }

        $plan = $subscription->plan;
        $subscriptionAmount = $subscription->price ?? $plan->price;
        $currency = $plan->currency ?? 'USD';

        // Calculate due date (30 days from now by default, or match subscription end date)
        $dueDate = $subscription->ends_at ? $subscription->ends_at->copy() : now()->addDays(30);

        // Get payment reference from resource
        $paymentReference = $resource['id'] ?? $resource['paypal_capture_id'] ?? null;
        $paymentMethod = 'paypal';

        // Create invoice
        $invoice = Invoice::create([
            'subscription_id' => $subscription->id,
            'tenant_id' => $subscription->tenant_id,
            'plan_id' => $subscription->plan_id,
            'subtotal' => $subscriptionAmount,
            'tax' => 0, // Can be configured later
            'discount' => 0, // Can be configured later
            'total' => $subscriptionAmount,
            'currency' => $currency,
            'status' => Invoice::STATUS_PAID, // Mark as paid since payment was successful
            'issued_at' => now(),
            'due_date' => $dueDate,
            'paid_at' => now(),
            'payment_method' => $paymentMethod,
            'payment_reference' => $paymentReference,
            'metadata' => [
                'created_via' => 'paypal_payment',
                'paypal_resource' => $resource,
                'subscription_starts_at' => $subscription->starts_at?->toIso8601String(),
                'subscription_ends_at' => $subscription->ends_at?->toIso8601String(),
                'billing_cycle' => $plan->billing_cycle,
            ],
        ]);

        Log::info('PayPal: Invoice created', [
            'subscription_id' => $subscription->id,
            'invoice_id' => $invoice->id,
            'invoice_number' => $invoice->invoice_number,
            'amount' => $subscriptionAmount,
        ]);
    }

    /**
     * Create commission for subscription if seller exists.
     */
    protected function createCommission(Subscription $subscription): void
    {
        // Check if subscription has a seller
        if (!$subscription->seller_id || !$subscription->seller) {
            return;
        }

        // Check if commission already exists
        if ($subscription->commission) {
            Log::info('PayPal: Commission already exists for subscription', [
                'subscription_id' => $subscription->id,
                'commission_id' => $subscription->commission->id,
            ]);
            return;
        }

        $seller = $subscription->seller;
        $subscriptionAmount = $subscription->price ?? $subscription->plan->price;
        $commissionRate = $seller->commission_rate;

        // Calculate commission amount
        $commissionAmount = ($subscriptionAmount * $commissionRate) / 100;

        if ($commissionAmount <= 0) {
            return;
        }

        // Create commission
        Commission::create([
            'seller_id' => $seller->id,
            'subscription_id' => $subscription->id,
            'amount' => $commissionAmount,
            'commission_rate' => $commissionRate,
            'subscription_amount' => $subscriptionAmount,
            'status' => Commission::STATUS_PENDING,
            'metadata' => [
                'created_via' => 'paypal_payment',
                'created_at' => now()->toIso8601String(),
            ],
        ]);

        Log::info('PayPal: Commission created', [
            'subscription_id' => $subscription->id,
            'seller_id' => $seller->id,
            'commission_amount' => $commissionAmount,
        ]);
    }

    /**
     * Handle subscription cancellation.
     */
    protected function handleSubscriptionCancelled(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('external_id', $subscriptionId)
            ->orWhereJsonContains('metadata->paypal_subscription_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        $subscription->cancel($resource['status_change_note'] ?? 'Cancelled via PayPal');

        Log::info('PayPal: Subscription cancelled', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription payment failure.
     */
    protected function handleSubscriptionPaymentFailed(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('external_id', $subscriptionId)
            ->orWhereJsonContains('metadata->paypal_subscription_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'status' => Subscription::STATUS_PENDING,
            'metadata' => array_merge($subscription->metadata ?? [], [
                'payment_failure_reason' => $resource['outstanding_balance']['value'] ?? 'unknown',
                'last_payment_failure_at' => now()->toIso8601String(),
            ]),
        ]);

        Log::warning('PayPal: Subscription payment failed', [
            'subscription_id' => $subscription->id,
        ]);
    }

    /**
     * Handle subscription update.
     */
    protected function handleSubscriptionUpdated(array $event): void
    {
        $resource = $event['resource'] ?? [];
        $subscriptionId = $resource['id'] ?? null;

        if (!$subscriptionId) {
            return;
        }

        $subscription = Subscription::where('external_id', $subscriptionId)
            ->orWhereJsonContains('metadata->paypal_subscription_id', $subscriptionId)
            ->first();

        if (!$subscription) {
            return;
        }

        $subscription->update([
            'metadata' => array_merge($subscription->metadata ?? [], [
                'paypal_status' => $resource['status'] ?? null,
                'last_updated_at' => now()->toIso8601String(),
            ]),
        ]);
    }

    /**
     * Handle PayPal return URL (success).
     */
    public function success(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');
        $token = $request->query('token');
        $payerId = $request->query('PayerID');

        if (!$subscriptionId) {
            return redirect()->route('filament.admin.pages.subscriptions')
                ->with('error', 'Invalid subscription ID');
        }

        $subscription = Subscription::find($subscriptionId);

        if (!$subscription) {
            return redirect()->route('filament.admin.pages.subscriptions')
                ->with('error', 'Subscription not found');
        }

        // If token is present, it's a one-time payment
        if ($token) {
            $order = $this->paypalService->captureOrder($token);
            
            if ($order && $order['status'] === 'COMPLETED') {
                $plan = $subscription->plan;
                $endsAt = null;

                if ($plan->billing_cycle !== 'lifetime') {
                    $endsAt = match($plan->billing_cycle) {
                        'monthly' => now()->addMonth(),
                        'yearly' => now()->addYear(),
                        'quarterly' => now()->addMonths(3),
                        default => now()->addMonth(),
                    };
                }

                $subscription->update([
                    'status' => Subscription::STATUS_ACTIVE,
                    'starts_at' => now(),
                    'ends_at' => $endsAt,
                    'next_billing_at' => $endsAt,
                    'payment_method' => 'paypal',
                ]);

                // Activate tenant if not already active
                $tenant = $subscription->tenant;
                if ($tenant && !$tenant->is_active) {
                    $tenant->update(['is_active' => true]);
                }

                // Calculate and create commission if seller exists
                $this->createCommission($subscription);

                // Create invoice for the payment
                $this->createInvoice($subscription, $order ?? []);

                return redirect()->route('filament.admin.resources.subscriptions.view', $subscription)
                    ->with('success', 'Payment completed successfully');
            }
        }

        // For subscriptions, PayPal redirects here after approval
        // Check subscription status in PayPal
        if ($subscription->external_id) {
            $paypalSubscription = $this->paypalService->getSubscription($subscription->external_id);
            
            if ($paypalSubscription && isset($paypalSubscription['status'])) {
                $status = $paypalSubscription['status'];
                
                if ($status === 'ACTIVE' || $status === 'APPROVAL_PENDING') {
                    // Subscription is active or pending approval
                    // The webhook will handle the final activation
                    // But we can update the status here
                    if ($status === 'ACTIVE') {
                        $plan = $subscription->plan;
                        $endsAt = null;

                        if ($plan->billing_cycle !== 'lifetime') {
                            $endsAt = match($plan->billing_cycle) {
                                'monthly' => now()->addMonth(),
                                'yearly' => now()->addYear(),
                                'quarterly' => now()->addMonths(3),
                                default => now()->addMonth(),
                            };
                        }

                        $subscription->update([
                            'status' => Subscription::STATUS_ACTIVE,
                            'starts_at' => now(),
                            'ends_at' => $endsAt,
                            'next_billing_at' => $endsAt,
                            'payment_method' => 'paypal',
                        ]);

                        // Activate tenant if not already active
                        $tenant = $subscription->tenant;
                        if ($tenant && !$tenant->is_active) {
                            $tenant->update(['is_active' => true]);
                        }

                        // Create invoice
                        $this->createInvoice($subscription, $paypalSubscription);
                    }
                    
                    // Redirect to tenant panel if tenant context
                    $tenant = $subscription->tenant;
                    if ($tenant) {
                        return redirect()
                            ->to(config('filament-tenancy.filament.tenant_panel_path', '/admin') . '/plans')
                            ->with('success', 'Subscription activated successfully');
                    }
                }
            }
        }
        
        return redirect()->route('filament.admin.resources.subscriptions.view', $subscription)
            ->with('success', 'Subscription approved. Processing payment...');
    }

    /**
     * Handle PayPal cancel URL.
     */
    public function cancel(Request $request)
    {
        $subscriptionId = $request->query('subscription_id');

        if ($subscriptionId) {
            $subscription = Subscription::find($subscriptionId);
            
            if ($subscription) {
                $tenant = $subscription->tenant;
                
                // Redirect to tenant panel if tenant context
                if ($tenant) {
                    return redirect()
                        ->to(config('filament-tenancy.filament.tenant_panel_path', '/admin') . '/plans')
                        ->with('warning', 'Payment was cancelled');
                }
                
                return redirect()->route('filament.admin.resources.subscriptions.view', $subscription)
                    ->with('warning', 'Payment was cancelled');
            }
        }

        return redirect()->route('filament.admin.pages.subscriptions')
            ->with('warning', 'Payment was cancelled');
    }
}

