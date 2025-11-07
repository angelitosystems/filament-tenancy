@php
    $subscriptionId = $subscriptionId ?? session()->get('pending_paypal_subscription_id');
    $approvalUrl = $approvalUrl ?? session()->get('pending_paypal_approval_url');
@endphp

@if($approvalUrl)
    <div class="w-full" style="min-height: 600px;">
        <iframe 
            id="paypal-checkout-iframe"
            src="{{ $approvalUrl }}"
            style="width: 100%; height: 600px; border: none;"
            allow="payment"
            title="PayPal Checkout"
        ></iframe>
    </div>
    
    @script
    <script>
        // Listen for postMessage from PayPal iframe
        window.addEventListener('message', function(e) {
            if (e.origin.includes('paypal.com') || e.origin.includes('paypalobjects.com')) {
                if (e.data && typeof e.data === 'object') {
                    if (e.data.type === 'paypal-checkout-success' || e.data.action === 'checkout-success') {
                        const subscriptionId = @js($subscriptionId);
                        if (subscriptionId) {
                            window.location.href = '/paypal/success?subscription_id=' + subscriptionId;
                        }
                    }
                }
            }
        });
    </script>
    @endscript
@else
    <div class="text-center py-8">
        <p class="text-gray-500">{{ __('filament-tenancy::plans.initializing_payment') }}</p>
    </div>
@endif

