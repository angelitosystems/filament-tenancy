<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\SellerResource;
use Filament\Resources\Pages\ListRecords;

class ListSellers extends ListRecords
{
    protected static string $resource = SellerResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make(),
        ];
    }
}

