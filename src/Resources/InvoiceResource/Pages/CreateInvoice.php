<?php

namespace AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\InvoiceResource;
use Filament\Resources\Pages\CreateRecord;

class CreateInvoice extends CreateRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Calcular el total si no está establecido
        if (!isset($data['total']) || $data['total'] == 0) {
            $subtotal = $data['subtotal'] ?? 0;
            $tax = $data['tax'] ?? 0;
            $discount = $data['discount'] ?? 0;
            $data['total'] = max(0, $subtotal + $tax - $discount);
        }

        return $data;
    }
}




