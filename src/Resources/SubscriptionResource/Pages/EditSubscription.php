<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages;

use AngelitoSystems\FilamentTenancy\Models\Invoice;
use AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource;
use Filament\Resources\Pages\EditRecord;

class EditSubscription extends EditRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected ?string $originalStatus = null;

    protected function mutateFormDataBeforeSave(array $data): array
    {
        // Guardar el estado original antes de guardar
        if ($this->record && $this->record->exists) {
            $this->originalStatus = $this->record->getOriginal('status');
        }

        return $data;
    }

    protected function afterSave(): void
    {
        $subscription = $this->record;
        $newStatus = $subscription->status;

        // Si el estado cambió a "active" manualmente (no desde PayPal)
        if ($newStatus === 'active' && $this->originalStatus !== 'active') {
            // Activar tenant si no está activo
            $tenant = $subscription->tenant;
            if ($tenant && !$tenant->is_active) {
                $tenant->update(['is_active' => true]);
            }

            // Crear o actualizar factura como pagada
            $this->createOrUpdateInvoiceAsPaid($subscription);

            // Limpiar restricciones de sesión para habilitar todos los recursos
            session()->forget('subscription_restricted');
            session()->forget('subscription_restriction_type');
            session()->forget('subscription_restriction_message');
        }
    }

    protected function createOrUpdateInvoiceAsPaid($subscription): void
    {
        $plan = $subscription->plan;
        $subscriptionAmount = $subscription->price ?? $plan->price;
        
        // Si el monto es 0, no crear factura
        if ($subscriptionAmount <= 0) {
            return;
        }

        // Buscar CUALQUIER factura existente para esta suscripción (la que se creó cuando se creó la suscripción)
        $existingInvoice = Invoice::where('subscription_id', $subscription->id)
            ->orderBy('created_at', 'asc') // Tomar la primera factura creada
            ->first();

        if ($existingInvoice) {
            // Actualizar la factura existente como pagada (no crear una nueva)
            $existingInvoice->markAsPaid(
                $subscription->payment_method ?? 'manual_admin',
                $subscription->external_id ?? 'manual_activation'
            );
            
            // Actualizar metadata para indicar que fue activada manualmente por admin
            $metadata = $existingInvoice->metadata ?? [];
            $metadata['activated_by_admin'] = true;
            $metadata['admin_activation_at'] = now()->toIso8601String();
            $existingInvoice->update(['metadata' => $metadata]);
        } else {
            // Solo crear nueva factura si NO existe ninguna (caso raro)
            $currency = $plan->currency ?? 'USD';
            $dueDate = $subscription->ends_at ? $subscription->ends_at->copy() : now()->addDays(30);

            Invoice::create([
                'subscription_id' => $subscription->id,
                'tenant_id' => $subscription->tenant_id,
                'plan_id' => $subscription->plan_id,
                'subtotal' => $subscriptionAmount,
                'tax' => 0,
                'discount' => 0,
                'total' => $subscriptionAmount,
                'currency' => $currency,
                'status' => Invoice::STATUS_PAID,
                'issued_at' => now(),
                'due_date' => $dueDate,
                'paid_at' => now(),
                'payment_method' => $subscription->payment_method ?? 'manual_admin',
                'payment_reference' => $subscription->external_id ?? 'manual_activation',
                'metadata' => [
                    'created_via' => 'manual_admin_activation',
                    'subscription_starts_at' => $subscription->starts_at?->toIso8601String(),
                    'subscription_ends_at' => $subscription->ends_at?->toIso8601String(),
                    'billing_cycle' => $plan->billing_cycle,
                    'activated_by_admin' => true,
                ],
            ]);
        }
    }
}
