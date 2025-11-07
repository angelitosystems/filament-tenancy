<?php

namespace AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\InvoiceResource;
use Filament\Resources\Pages\ListRecords;

class ListInvoices extends ListRecords
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}




