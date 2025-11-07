@php
    // Get invoice from passed data or fallback to record
    $invoice = $invoice ?? ($record ?? null);
@endphp

@if ($invoice)
    <div class="w-full -mx-6 -my-4" style="height: 100vh; max-height: 1200px;">
        <iframe 
            src="{{ route('tenant.invoices.pdf.view', $invoice) }}" 
            class="w-full h-full border-0"
            frameborder="0"
            title="{{ __('filament-tenancy::invoices.invoice') }} - {{ $invoice->invoice_number }}">
        </iframe>
    </div>
@endif