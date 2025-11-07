<x-filament-widgets::widget>
    <div x-data="{ 
        isOpen: false, 
        approvalUrl: null, 
        subscriptionId: null 
    }" 
    x-init="
        // Listen for custom event to open modal
        window.addEventListener('open-paypal-checkout', function(event) {
            isOpen = true;
            approvalUrl = event.detail.approvalUrl;
            subscriptionId = event.detail.subscriptionId;
        });
        
        // Check session for pending checkout on page load
        @if(session()->has('pending_paypal_checkout'))
            isOpen = true;
            approvalUrl = @js(session()->get('pending_paypal_checkout')['approval_url']);
            subscriptionId = @js(session()->get('pending_paypal_checkout')['subscription_id']);
            @php session()->forget('pending_paypal_checkout'); @endphp
        @endif
        
        // Listen for postMessage from PayPal iframe
        window.addEventListener('message', function(e) {
            if (e.origin.includes('paypal.com') || e.origin.includes('paypalobjects.com')) {
                if (e.data && typeof e.data === 'object') {
                    if (e.data.type === 'paypal-checkout-success' || e.data.action === 'checkout-success') {
                        window.location.href = '/paypal/success?subscription_id=' + subscriptionId;
                    } else if (e.data.type === 'paypal-checkout-cancel' || e.data.action === 'checkout-cancel') {
                        isOpen = false;
                    }
                }
            }
        });
    ">
        <!-- PayPal Checkout Modal -->
        <div x-show="isOpen" 
             x-cloak
             class="fixed inset-0 z-50 overflow-y-auto"
             style="display: none;">
            <div class="flex items-center justify-center min-h-screen px-4 pt-4 pb-20 text-center sm:block sm:p-0">
                <!-- Background overlay -->
                <div x-show="isOpen" 
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0"
                     x-transition:enter-end="opacity-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100"
                     x-transition:leave-end="opacity-0"
                     class="fixed inset-0 transition-opacity bg-gray-500 bg-opacity-75"
                     @click="isOpen = false">
                </div>

                <!-- Modal panel -->
                <div x-show="isOpen"
                     x-transition:enter="ease-out duration-300"
                     x-transition:enter-start="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     x-transition:enter-end="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave="ease-in duration-200"
                     x-transition:leave-start="opacity-100 translate-y-0 sm:scale-100"
                     x-transition:leave-end="opacity-0 translate-y-4 sm:translate-y-0 sm:scale-95"
                     class="inline-block align-bottom bg-white rounded-lg text-left overflow-hidden shadow-xl transform transition-all sm:my-8 sm:align-middle sm:max-w-4xl sm:w-full">
                    <div class="bg-white px-4 pt-5 pb-4 sm:p-6 sm:pb-4">
                        <div class="sm:flex sm:items-start">
                            <div class="mt-3 text-center sm:mt-0 sm:text-left w-full">
                                <h3 class="text-lg leading-6 font-medium text-gray-900 mb-4">
                                    {{ __('filament-tenancy::plans.completing_payment') }}
                                </h3>
                                <p class="text-sm text-gray-500 mb-4">
                                    {{ __('filament-tenancy::plans.please_complete_payment') }}
                                </p>
                                <div class="w-full" style="min-height: 600px;" x-show="approvalUrl">
                                    <iframe 
                                        x-bind:src="approvalUrl"
                                        style="width: 100%; height: 600px; border: none;"
                                        allow="payment"
                                        title="PayPal Checkout"
                                    ></iframe>
                                </div>
                            </div>
                        </div>
                    </div>
                    <div class="bg-gray-50 px-4 py-3 sm:px-6 sm:flex sm:flex-row-reverse">
                        <button 
                            type="button"
                            @click="isOpen = false"
                            class="w-full inline-flex justify-center rounded-md border border-transparent shadow-sm px-4 py-2 bg-gray-600 text-base font-medium text-white hover:bg-gray-700 focus:outline-none focus:ring-2 focus:ring-offset-2 focus:ring-gray-500 sm:ml-3 sm:w-auto sm:text-sm">
                            {{ __('filament-tenancy::plans.cancel') }}
                        </button>
                    </div>
                </div>
            </div>
        </div>
    </div>
</x-filament-widgets::widget>
