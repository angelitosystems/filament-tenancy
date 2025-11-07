<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages;

use AngelitoSystems\FilamentTenancy\Models\Invoice;
use AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSubscription extends CreateRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function afterCreate(): void
    {
        // Si la suscripción está activa, crear factura automáticamente
        if ($this->record->status === 'active' && ($this->record->price > 0 || $this->record->plan->price > 0)) {
            // Activar tenant si no está activo
            $tenant = $this->record->tenant;
            if ($tenant && !$tenant->is_active) {
                $tenant->update(['is_active' => true]);
            }

            $this->createInvoiceForSubscription();

            // Limpiar restricciones de sesión para habilitar todos los recursos
            session()->forget('subscription_restricted');
            session()->forget('subscription_restriction_type');
            session()->forget('subscription_restriction_message');
        }
    }

    protected function createInvoiceForSubscription(): void
    {
        $plan = $this->record->plan;
        $subscriptionAmount = $this->record->price ?? $plan->price;
        $currency = $plan->currency ?? 'USD';

        // Calcular fecha de vencimiento
        $dueDate = $this->record->ends_at ? $this->record->ends_at->copy() : now()->addDays(30);

        // Si la suscripción está activa, marcar factura como pagada automáticamente
        // (ya que fue activada manualmente por el admin)
        $invoiceStatus = ($this->record->status === 'active')
            ? Invoice::STATUS_PAID 
            : Invoice::STATUS_PENDING;

        // Crear factura
        Invoice::create([
            'subscription_id' => $this->record->id,
            'tenant_id' => $this->record->tenant_id,
            'plan_id' => $this->record->plan_id,
            'subtotal' => $subscriptionAmount,
            'tax' => 0,
            'discount' => 0,
            'total' => $subscriptionAmount,
            'currency' => $currency,
            'status' => $invoiceStatus,
            'issued_at' => now(),
            'due_date' => $dueDate,
            'paid_at' => $invoiceStatus === Invoice::STATUS_PAID ? now() : null,
            'payment_method' => $this->record->payment_method ?? 'manual_admin',
            'payment_reference' => $this->record->external_id ?? 'manual_creation',
            'metadata' => [
                'created_via' => 'manual_admin',
                'subscription_starts_at' => $this->record->starts_at?->toIso8601String(),
                'subscription_ends_at' => $this->record->ends_at?->toIso8601String(),
                'billing_cycle' => $plan->billing_cycle,
                'activated_by_admin' => $this->record->status === 'active',
            ],
        ]);
    }
}
