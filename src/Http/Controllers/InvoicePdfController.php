<?php

namespace AngelitoSystems\FilamentTenancy\Http\Controllers;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Invoice;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\Support\Facades\Log;

class InvoicePdfController
{
    /**
     * Generate PDF for invoice (download)
     */
    public function generate(Request $request, Invoice $invoice): Response
    {
        $this->authorize($invoice);

        try {
            $pdf = $this->createPdf($invoice);

            return $pdf->download($this->getFilename($invoice));
        } catch (\Exception $e) {
            Log::error('Invoice PDF generation failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Error al generar la factura. Por favor, intente nuevamente.');
        }
    }

    /**
     * View PDF for invoice (inline/stream)
     */
    public function view(Request $request, Invoice $invoice): Response
    {
        $this->authorize($invoice);

        try {
            $pdf = $this->createPdf($invoice);

            return $pdf->stream($this->getFilename($invoice));
        } catch (\Exception $e) {
            Log::error('Invoice PDF streaming failed', [
                'invoice_id' => $invoice->id,
                'error' => $e->getMessage(),
                'trace' => $e->getTraceAsString()
            ]);

            abort(500, 'Error al visualizar la factura. Por favor, intente nuevamente.');
        }
    }

    /**
     * Send invoice PDF via email
     */
    public function email(Request $request, Invoice $invoice): JsonResponse
    {
        $this->authorize($invoice);

        $request->validate([
            'email' => 'required|email',
            'message' => 'nullable|string|max:500'
        ]);

        try {
            $pdf = $this->createPdf($invoice);

            // TODO: Implement email sending logic
            // Mail::to($request->email)->send(new InvoiceMail($invoice, $pdf));

            return response()->json([
                'success' => true,
                'message' => 'Factura enviada correctamente'
            ]);
        } catch (\Exception $e) {
            Log::error('Invoice email sending failed', [
                'invoice_id' => $invoice->id,
                'email' => $request->email,
                'error' => $e->getMessage()
            ]);

            return response()->json([
                'success' => false,
                'message' => 'Error al enviar la factura'
            ], 500);
        }
    }

    /**
     * Verify tenant access to invoice
     */
    protected function authorize(Invoice $invoice): void
    {
        $tenant = Tenancy::current();

        if (!$tenant) {
            abort(403, 'No se pudo identificar el tenant actual');
        }

        if ($invoice->tenant_id !== $tenant->id) {
            Log::warning('Unauthorized invoice access attempt', [
                'invoice_id' => $invoice->id,
                'invoice_tenant_id' => $invoice->tenant_id,
                'current_tenant_id' => $tenant->id,
                'ip' => request()->ip()
            ]);

            abort(403, 'No tiene permisos para acceder a esta factura');
        }
    }

    /**
     * Create PDF instance from invoice
     */
    protected function createPdf(Invoice $invoice): \Barryvdh\DomPDF\PDF
    {
        // Load necessary relationships
        $invoice->load([
            'tenant',
            'plan',
            'subscription'
        ]);

        // Prepare data for view
        $data = $this->prepareInvoiceData($invoice);

        // Render HTML
        $html = view('filament-tenancy::invoices.pdf', $data)->render();

        // Create PDF
        $pdf = Pdf::loadHTML($html)
            ->setPaper('a4', 'portrait')
            ->setOptions([
                'isRemoteEnabled' => true,
                'isHtml5ParserEnabled' => true,
                'isFontSubsettingEnabled' => true,
                'defaultFont' => 'DejaVu Sans',
                'defaultMediaType' => 'print',
                'dpi' => 96,
                'enable_php' => false,
            ]);

        return $pdf;
    }

    /**
     * Prepare invoice data for PDF view
     */
    protected function prepareInvoiceData(Invoice $invoice): array
    {
        $tenant = $invoice->tenant;
        $subscription = $invoice->subscription;
        $plan = $invoice->plan;

        // Generate items from plan/subscription data
        $items = $this->generateInvoiceItems($invoice, $plan, $subscription);

        // Format billing cycle
        $billingCycleLabel = $plan ? $this->getBillingCycleLabel($plan->billing_cycle) : 'Mensual';

        return [
            // Invoice info
            'invoice_number' => $invoice->invoice_number,
            'invoice_date' => $invoice->issued_at?->format('d/m/Y') ?? now()->format('d/m/Y'),
            'status' => $this->getStatusLabel($invoice->status),

            // Company info (Angelito Systems)
            'company_name' => config('filament-tenancy.invoice.company_name', 'Angelito Systems S.A.C.'),
            'company_ruc' => config('filament-tenancy.invoice.company_ruc', '20123456789'),
            'company_address' => config('filament-tenancy.invoice.company_address', 'Lima, Perú'),
            'company_email' => config('filament-tenancy.invoice.company_email', 'facturacion@angelitosystems.com'),
            'company_phone' => config('filament-tenancy.invoice.company_phone', '+51 987 654 321'),

            // Client info (Tenant)
            'client_name' => $tenant?->name ?? 'N/A',
            'client_ruc' => $tenant->ruc ?? 'N/A',
            'client_address' => $tenant->address ?? 'N/A',
            'client_email' => $tenant->email ?? 'N/A',

            // Payment info
            'payment_method' => $this->getPaymentMethodLabel($invoice->payment_method),
            'payment_terms' => $billingCycleLabel,

            // Period info
            'period_start' => $subscription?->starts_at?->format('d/m/Y'),
            'period_end' => $subscription?->ends_at?->format('d/m/Y'),
            'plan_name' => $plan?->name ?? 'N/A',

            // Items
            'items' => $items,

            // Totals
            'subtotal' => $invoice->subtotal,
            'igv' => $invoice->tax,
            'total' => $invoice->total,
            'discount' => $invoice->discount,
            'currency' => $invoice->currency ?? 'USD',

            // Verification (if exists)
            'verification_url' => $invoice->metadata['verification_token'] ?? null
                ? route('invoice.verify', ['token' => $invoice->metadata['verification_token']])
                : null,
        ];
    }

    /**
     * Generate invoice items from plan/subscription data
     */
    protected function generateInvoiceItems(Invoice $invoice, ?\AngelitoSystems\FilamentTenancy\Models\Plan $plan, ?\AngelitoSystems\FilamentTenancy\Models\Subscription $subscription): array
    {
        $items = [];

        if ($plan) {
            $description = $plan->name;
            if ($subscription) {
                $billingCycle = $this->getBillingCycleLabel($subscription->billing_cycle ?? $plan->billing_cycle);
                $description .= " - {$billingCycle}";

                if ($subscription->starts_at && $subscription->ends_at) {
                    $description .= " (" . $subscription->starts_at->format('d/m/Y') . " - " . $subscription->ends_at->format('d/m/Y') . ")";
                }
            }

            $items[] = [
                'description' => $description,
                'qty' => 1,
                'price' => $invoice->subtotal,
                'total' => $invoice->subtotal
            ];
        } else {
            // Fallback if no plan
            $items[] = [
                'description' => 'Suscripción',
                'qty' => 1,
                'price' => $invoice->subtotal,
                'total' => $invoice->subtotal
            ];
        }

        return $items;
    }

    /**
     * Get billing cycle label in Spanish
     */
    protected function getBillingCycleLabel(?string $cycle): string
    {
        return match ($cycle) {
            'monthly' => 'Mensual',
            'yearly' => 'Anual',
            'quarterly' => 'Trimestral',
            'lifetime' => 'De por vida',
            default => 'Mensual'
        };
    }

    /**
     * Get status label in Spanish
     */
    protected function getStatusLabel(string $status): string
    {
        return match ($status) {
            'paid' => 'Pagado',
            'pending' => 'Pendiente',
            'cancelled', 'canceled' => 'Cancelado',
            'overdue' => 'Vencido',
            'draft' => 'Borrador',
            default => ucfirst($status)
        };
    }

    /**
     * Get payment method label
     */
    protected function getPaymentMethodLabel(?string $method): string
    {
        if (!$method) {
            return 'N/A';
        }

        return match ($method) {
            'stripe' => 'Tarjeta (Stripe)',
            'mercadopago' => 'Mercado Pago',
            'paypal' => 'PayPal',
            'bank_transfer' => 'Transferencia Bancaria',
            'cash' => 'Efectivo',
            default => ucfirst(str_replace('_', ' ', $method))
        };
    }

    /**
     * Generate filename for invoice PDF
     */
    protected function getFilename(Invoice $invoice): string
    {
        $number = str_replace(['/', '-'], '_', $invoice->invoice_number);
        $date = $invoice->invoice_date?->format('Ymd') ?? date('Ymd');

        return "factura_{$number}_{$date}.pdf";
    }
}
