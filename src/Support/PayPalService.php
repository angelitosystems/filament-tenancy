<?php

namespace AngelitoSystems\FilamentTenancy\Support;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Models\PayPalSettings;
use Illuminate\Support\Facades\Http;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Cache;

class PayPalService
{
    protected string $mode;
    protected string $clientId;
    protected string $clientSecret;
    protected string $baseUrl;
    protected string $currency;
    protected bool $isEnabled;

    public function __construct()
    {
        $this->loadSettings();
    }

    /**
     * Load PayPal settings from database or config fallback.
     */
    protected function loadSettings(): void
    {
        try {
            $settings = PayPalSettings::current();
            
            if ($settings->is_enabled) {
                $this->mode = $settings->mode ?? 'sandbox';
                $this->clientId = $settings->client_id ?? '';
                $this->clientSecret = $settings->client_secret ?? '';
                $this->currency = $settings->currency ?? 'USD';
                $this->isEnabled = true;
            } else {
                // Fallback to config if not enabled in DB
                $config = config('filament-tenancy.paypal', []);
                $this->mode = $config['mode'] ?? 'sandbox';
                $this->clientId = $config['client_id'] ?? env('PAYPAL_CLIENT_ID', '');
                $this->clientSecret = $config['client_secret'] ?? env('PAYPAL_CLIENT_SECRET', '');
                $this->currency = $config['currency'] ?? env('PAYPAL_CURRENCY', 'USD');
                $this->isEnabled = false;
            }
        } catch (\Exception $e) {
            // Fallback to config/env if DB not available
            $config = config('filament-tenancy.paypal', []);
            $this->mode = $config['mode'] ?? env('PAYPAL_MODE', 'sandbox');
            $this->clientId = $config['client_id'] ?? env('PAYPAL_CLIENT_ID', '');
            $this->clientSecret = $config['client_secret'] ?? env('PAYPAL_CLIENT_SECRET', '');
            $this->currency = $config['currency'] ?? env('PAYPAL_CURRENCY', 'USD');
            $this->isEnabled = false;
        }

        $apiUrls = config('filament-tenancy.paypal.api_urls', [
            'sandbox' => 'https://api-m.sandbox.paypal.com',
            'live' => 'https://api-m.paypal.com',
        ]);
        
        $this->baseUrl = $apiUrls[$this->mode] ?? $apiUrls['sandbox'];
    }

    /**
     * Check if PayPal is enabled.
     */
    public function isEnabled(): bool
    {
        return $this->isEnabled && !empty($this->clientId) && !empty($this->clientSecret);
    }

    /**
     * Get PayPal access token.
     */
    public function getAccessToken(): ?string
    {
        $cacheKey = "paypal_access_token_{$this->mode}";
        
        return Cache::remember($cacheKey, 3300, function () {
            try {
                $response = Http::asForm()
                    ->withBasicAuth($this->clientId, $this->clientSecret)
                    ->post("{$this->baseUrl}/v1/oauth2/token", [
                        'grant_type' => 'client_credentials',
                    ]);

                if ($response->successful()) {
                    return $response->json('access_token');
                }

                Log::error('PayPal: Failed to get access token', [
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);

                return null;
            } catch (\Exception $e) {
                Log::error('PayPal: Exception getting access token', [
                    'error' => $e->getMessage(),
                ]);

                return null;
            }
        });
    }

    /**
     * Create a PayPal order for a subscription.
     */
    public function createOrder(Subscription $subscription, ?string $returnUrl = null, ?string $cancelUrl = null): ?array
    {
        if (!$this->isEnabled()) {
            Log::error('PayPal: Service is not enabled or credentials are missing');
            return null;
        }

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        $settings = PayPalSettings::current();
        $returnUrl = $returnUrl ?? $settings->return_url ?? '/paypal/success';
        $cancelUrl = $cancelUrl ?? $settings->cancel_url ?? '/paypal/cancel';

        $plan = $subscription->plan;
        $tenant = $subscription->tenant;

        // Add subscription ID to return URL
        $returnUrl = $returnUrl . '?subscription_id=' . $subscription->id;
        $cancelUrl = $cancelUrl . '?subscription_id=' . $subscription->id;

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => "subscription_{$subscription->id}",
                    'description' => "Subscription: {$plan->name}",
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => number_format($plan->price, 2, '.', ''),
                    ],
                    'custom_id' => (string) $subscription->id,
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => url($returnUrl),
                'cancel_url' => url($cancelUrl),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders", $orderData);

            if ($response->successful()) {
                $order = $response->json();
                
                // Update subscription with PayPal order ID
                $subscription->update([
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'paypal_order_id' => $order['id'],
                        'paypal_status' => $order['status'],
                    ]),
                ]);

                return $order;
            }

            Log::error('PayPal: Failed to create order', [
                'response' => $response->json(),
                'status' => $response->status(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception creating order', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        }
    }

    /**
     * Capture a PayPal order payment.
     */
    public function captureOrder(string $orderId): ?array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders/{$orderId}/capture");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayPal: Failed to capture order', [
                'response' => $response->json(),
                'status' => $response->status(),
                'order_id' => $orderId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception capturing order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return null;
        }
    }

    /**
     * Get PayPal order details.
     */
    public function getOrder(string $orderId): ?array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/v2/checkout/orders/{$orderId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayPal: Failed to get order', [
                'response' => $response->json(),
                'status' => $response->status(),
                'order_id' => $orderId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception getting order', [
                'error' => $e->getMessage(),
                'order_id' => $orderId,
            ]);

            return null;
        }
    }

    /**
     * Create a PayPal subscription (recurring billing).
     */
    public function createSubscription(Subscription $subscription): ?array
    {
        if (!$this->isEnabled()) {
            Log::error('PayPal: Service is not enabled or credentials are missing');
            return null;
        }

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        $plan = $subscription->plan;
        $settings = PayPalSettings::current();

        // Create billing plan first
        $billingPlan = $this->createBillingPlan($plan);
        
        if (!$billingPlan) {
            return null;
        }

        // Create subscription
        $subscriptionData = [
            'plan_id' => $billingPlan['id'],
            'start_time' => now()->addMinutes(5)->toIso8601String(),
            'subscriber' => [
                'name' => [
                    'given_name' => $subscription->tenant->name ?? 'Tenant',
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'locale' => app()->getLocale(),
                'shipping_preference' => 'NO_SHIPPING',
                'user_action' => 'SUBSCRIBE_NOW',
                'payment_method' => [
                    'payer_selected' => 'PAYPAL',
                    'payee_preferred' => 'IMMEDIATE_PAYMENT_REQUIRED',
                ],
                'return_url' => url(($settings->return_url ?? '/paypal/success') . '?subscription_id=' . $subscription->id),
                'cancel_url' => url(($settings->cancel_url ?? '/paypal/cancel') . '?subscription_id=' . $subscription->id),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v1/billing/subscriptions", $subscriptionData);

            if ($response->successful()) {
                $paypalSubscription = $response->json();
                
                // Update subscription with PayPal subscription ID
                $subscription->update([
                    'external_id' => $paypalSubscription['id'],
                    'payment_method' => 'paypal',
                    'metadata' => array_merge($subscription->metadata ?? [], [
                        'paypal_subscription_id' => $paypalSubscription['id'],
                        'paypal_plan_id' => $billingPlan['id'],
                        'paypal_status' => $paypalSubscription['status'],
                    ]),
                ]);

                return $paypalSubscription;
            }

            Log::error('PayPal: Failed to create subscription', [
                'response' => $response->json(),
                'status' => $response->status(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception creating subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        }
    }

    /**
     * Create a PayPal billing plan.
     */
    protected function createBillingPlan($plan): ?array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        // Check if plan already exists in metadata
        if (isset($plan->metadata['paypal_plan_id'])) {
            return ['id' => $plan->metadata['paypal_plan_id']];
        }

        $frequency = match($plan->billing_cycle) {
            'monthly' => 'MONTH',
            'yearly' => 'YEAR',
            'quarterly' => 'MONTH', // PayPal doesn't support quarterly, use 3 months
            default => 'MONTH',
        };

        $frequencyInterval = $plan->billing_cycle === 'quarterly' ? 3 : 1;

        $planData = [
            'product_id' => $this->getOrCreateProduct(),
            'name' => $plan->name,
            'description' => $plan->description ?? "Subscription plan: {$plan->name}",
            'status' => 'ACTIVE',
            'billing_cycles' => [
                [
                    'frequency' => [
                        'interval_unit' => $frequency,
                        'interval_count' => $frequencyInterval,
                    ],
                    'tenure_type' => 'REGULAR',
                    'sequence' => 1,
                    'total_cycles' => 0, // 0 = infinite
                    'pricing_scheme' => [
                        'fixed_price' => [
                            'value' => number_format($plan->price, 2, '.', ''),
                            'currency_code' => $this->currency,
                        ],
                    ],
                ],
            ],
            'payment_preferences' => [
                'auto_bill_outstanding' => true,
                'setup_fee' => [
                    'value' => '0',
                    'currency_code' => $this->currency,
                ],
                'setup_fee_failure_action' => 'CONTINUE',
                'payment_failure_threshold' => 3,
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v1/billing/plans", $planData);

            if ($response->successful()) {
                $paypalPlan = $response->json();
                
                // Store PayPal plan ID in plan metadata
                $metadata = $plan->metadata ?? [];
                if (!is_array($metadata)) {
                    $metadata = [];
                }
                $plan->update([
                    'metadata' => array_merge($metadata, [
                        'paypal_plan_id' => $paypalPlan['id'],
                    ]),
                ]);

                return $paypalPlan;
            }

            Log::error('PayPal: Failed to create billing plan', [
                'response' => $response->json(),
                'status' => $response->status(),
                'plan_id' => $plan->id,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception creating billing plan', [
                'error' => $e->getMessage(),
                'plan_id' => $plan->id,
            ]);

            return null;
        }
    }

    /**
     * Get or create PayPal product.
     */
    protected function getOrCreateProduct(): string
    {
        $cacheKey = 'paypal_product_id';
        
        return Cache::rememberForever($cacheKey, function () {
            $accessToken = $this->getAccessToken();
            
            if (!$accessToken) {
                return '';
            }

            // Try to get existing product
            try {
                $response = Http::withToken($accessToken)
                    ->get("{$this->baseUrl}/v1/catalogs/products", [
                        'page_size' => 1,
                    ]);

                if ($response->successful()) {
                    $products = $response->json('products', []);
                    if (!empty($products)) {
                        return $products[0]['id'];
                    }
                }
            } catch (\Exception $e) {
                Log::warning('PayPal: Could not fetch existing products', [
                    'error' => $e->getMessage(),
                ]);
            }

            // Create new product
            try {
                $response = Http::withToken($accessToken)
                    ->post("{$this->baseUrl}/v1/catalogs/products", [
                        'name' => config('app.name') . ' Subscriptions',
                        'description' => 'Subscription plans for ' . config('app.name'),
                        'type' => 'SERVICE',
                        'category' => 'SOFTWARE',
                    ]);

                if ($response->successful()) {
                    return $response->json('id');
                }

                Log::error('PayPal: Failed to create product', [
                    'response' => $response->json(),
                    'status' => $response->status(),
                ]);

                return '';
            } catch (\Exception $e) {
                Log::error('PayPal: Exception creating product', [
                    'error' => $e->getMessage(),
                ]);

                return '';
            }
        });
    }

    /**
     * Cancel a PayPal subscription.
     */
    public function cancelSubscription(Subscription $subscription, ?string $reason = null): bool
    {
        $paypalSubscriptionId = $subscription->external_id ?? $subscription->metadata['paypal_subscription_id'] ?? null;
        
        if (!$paypalSubscriptionId) {
            return false;
        }

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v1/billing/subscriptions/{$paypalSubscriptionId}/cancel", [
                    'reason' => $reason ?? 'Cancelled by admin',
                ]);

            if ($response->successful() || $response->status() === 204) {
                $subscription->cancel($reason);
                
                return true;
            }

            Log::error('PayPal: Failed to cancel subscription', [
                'response' => $response->json(),
                'status' => $response->status(),
                'subscription_id' => $subscription->id,
            ]);

            return false;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception canceling subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            return false;
        }
    }

    /**
     * Get PayPal subscription details.
     */
    public function getSubscription(string $subscriptionId): ?array
    {
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        try {
            $response = Http::withToken($accessToken)
                ->get("{$this->baseUrl}/v1/billing/subscriptions/{$subscriptionId}");

            if ($response->successful()) {
                return $response->json();
            }

            Log::error('PayPal: Failed to get subscription', [
                'response' => $response->json(),
                'status' => $response->status(),
                'subscription_id' => $subscriptionId,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception getting subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscriptionId,
            ]);

            return null;
        }
    }

    /**
     * Verify PayPal webhook signature.
     */
    public function verifyWebhook(array $headers, string $body): bool
    {
        $settings = PayPalSettings::current();
        $webhookSecret = $settings->webhook_secret ?? config('filament-tenancy.paypal.webhook_secret');
        
        if (!$webhookSecret) {
            Log::warning('PayPal: Webhook secret not configured');
            return false;
        }

        $signature = $headers['paypal-transmission-id'][0] ?? '';
        $certUrl = $headers['paypal-cert-url'][0] ?? '';
        $authAlgo = $headers['paypal-auth-algo'][0] ?? '';

        // Verify webhook signature using PayPal API
        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return false;
        }

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v1/notifications/verify-webhook-signature", [
                    'auth_algo' => $authAlgo,
                    'cert_url' => $certUrl,
                    'transmission_id' => $signature,
                    'transmission_sig' => $headers['paypal-transmission-sig'][0] ?? '',
                    'transmission_time' => $headers['paypal-transmission-time'][0] ?? '',
                    'webhook_id' => $webhookSecret,
                    'webhook_event' => json_decode($body, true),
                ]);

            return $response->successful() && $response->json('verification_status') === 'SUCCESS';
        } catch (\Exception $e) {
            Log::error('PayPal: Exception verifying webhook', [
                'error' => $e->getMessage(),
            ]);

            return false;
        }
    }

    /**
     * Generate a payment link for an expired or expiring subscription.
     * This creates a PayPal order and returns the approval URL.
     */
    public function generatePaymentLink(Subscription $subscription, ?int $expiresInHours = 24): ?string
    {
        if (!$this->isEnabled()) {
            Log::error('PayPal: Service is not enabled or credentials are missing');
            return null;
        }

        // Check if subscription needs payment (expired, canceled, or pending)
        if (!$subscription->isExpired() && !$subscription->isCanceled() && $subscription->status !== Subscription::STATUS_PENDING) {
            Log::warning('PayPal: Subscription does not need payment', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ]);
            return null;
        }

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        $settings = PayPalSettings::current();
        $returnUrl = $settings->return_url ?? '/paypal/success';
        $cancelUrl = $settings->cancel_url ?? '/paypal/cancel';

        $plan = $subscription->plan;
        $tenant = $subscription->tenant;

        // Add subscription ID and seller code to return URL if seller exists
        $returnUrlQuery = ['subscription_id' => $subscription->id];
        if ($subscription->seller_id && $subscription->seller) {
            $returnUrlQuery['seller_code'] = $subscription->seller->code;
        }
        $returnUrl = $returnUrl . '?' . http_build_query($returnUrlQuery);
        $cancelUrl = $cancelUrl . '?subscription_id=' . $subscription->id;

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => "subscription_renewal_{$subscription->id}",
                    'description' => "Renewal: {$plan->name}",
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => number_format($plan->price, 2, '.', ''),
                    ],
                    'custom_id' => (string) $subscription->id,
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => url($returnUrl),
                'cancel_url' => url($cancelUrl),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders", $orderData);

            if ($response->successful()) {
                $order = $response->json();
                
                // Find approval URL
                $approveUrl = null;
                if (isset($order['links'])) {
                    $approveLink = collect($order['links'])->firstWhere('rel', 'approve');
                    $approveUrl = $approveLink['href'] ?? null;
                }

                if ($approveUrl) {
                    // Update subscription with payment link
                    $expiresAt = now()->addHours($expiresInHours);
                    $subscription->update([
                        'payment_link' => $approveUrl,
                        'payment_link_expires_at' => $expiresAt,
                        'metadata' => array_merge($subscription->metadata ?? [], [
                            'paypal_order_id' => $order['id'],
                            'paypal_status' => $order['status'],
                            'payment_link_generated_at' => now()->toIso8601String(),
                        ]),
                    ]);

                    return $approveUrl;
                }
            }

            Log::error('PayPal: Failed to generate payment link', [
                'response' => $response->json(),
                'status' => $response->status(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception generating payment link', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        }
    }

    /**
     * Generate a payment link for a pending subscription.
     * Similar to generatePaymentLink but specifically for pending subscriptions.
     */
    public function generatePaymentLinkForPending(Subscription $subscription, ?int $expiresInHours = 24): ?string
    {
        if (!$this->isEnabled()) {
            Log::error('PayPal: Service is not enabled or credentials are missing');
            return null;
        }

        // Only generate for pending subscriptions
        if ($subscription->status !== Subscription::STATUS_PENDING) {
            Log::warning('PayPal: Subscription is not pending', [
                'subscription_id' => $subscription->id,
                'status' => $subscription->status,
            ]);
            return null;
        }

        $accessToken = $this->getAccessToken();
        
        if (!$accessToken) {
            return null;
        }

        $settings = PayPalSettings::current();
        $returnUrl = $settings->return_url ?? '/paypal/success';
        $cancelUrl = $settings->cancel_url ?? '/paypal/cancel';

        $plan = $subscription->plan;
        $tenant = $subscription->tenant;

        // Add subscription ID and seller code to return URL if seller exists
        $returnUrlQuery = ['subscription_id' => $subscription->id];
        if ($subscription->seller_id && $subscription->seller) {
            $returnUrlQuery['seller_code'] = $subscription->seller->code;
        }
        $returnUrl = $returnUrl . '?' . http_build_query($returnUrlQuery);
        $cancelUrl = $cancelUrl . '?subscription_id=' . $subscription->id;

        $orderData = [
            'intent' => 'CAPTURE',
            'purchase_units' => [
                [
                    'reference_id' => "subscription_pending_{$subscription->id}",
                    'description' => "Subscription: {$plan->name}",
                    'amount' => [
                        'currency_code' => $this->currency,
                        'value' => number_format($plan->price, 2, '.', ''),
                    ],
                    'custom_id' => (string) $subscription->id,
                ],
            ],
            'application_context' => [
                'brand_name' => config('app.name'),
                'landing_page' => 'BILLING',
                'user_action' => 'PAY_NOW',
                'return_url' => url($returnUrl),
                'cancel_url' => url($cancelUrl),
            ],
        ];

        try {
            $response = Http::withToken($accessToken)
                ->post("{$this->baseUrl}/v2/checkout/orders", $orderData);

            if ($response->successful()) {
                $order = $response->json();
                
                // Find approval URL
                $approveUrl = null;
                if (isset($order['links'])) {
                    $approveLink = collect($order['links'])->firstWhere('rel', 'approve');
                    $approveUrl = $approveLink['href'] ?? null;
                }

                if ($approveUrl) {
                    // Update subscription with payment link
                    $expiresAt = now()->addHours($expiresInHours);
                    $subscription->update([
                        'payment_link' => $approveUrl,
                        'payment_link_expires_at' => $expiresAt,
                        'metadata' => array_merge($subscription->metadata ?? [], [
                            'paypal_order_id' => $order['id'],
                            'paypal_status' => $order['status'],
                            'payment_link_generated_at' => now()->toIso8601String(),
                        ]),
                    ]);

                    return $approveUrl;
                }
            }

            Log::error('PayPal: Failed to generate payment link for pending subscription', [
                'response' => $response->json(),
                'status' => $response->status(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        } catch (\Exception $e) {
            Log::error('PayPal: Exception generating payment link for pending subscription', [
                'error' => $e->getMessage(),
                'subscription_id' => $subscription->id,
            ]);

            return null;
        }
    }
}

