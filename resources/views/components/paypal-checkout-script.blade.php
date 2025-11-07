<script>
    document.addEventListener('DOMContentLoaded', function() {
        window.dispatchEvent(new CustomEvent('open-paypal-checkout', {
            detail: {
                approvalUrl: '{{ $approvalUrl }}',
                subscriptionId: {{ $subscriptionId }}
            }
        }));
    });
</script>

