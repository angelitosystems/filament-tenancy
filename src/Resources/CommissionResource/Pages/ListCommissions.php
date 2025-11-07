<?php

namespace AngelitoSystems\FilamentTenancy\Resources\CommissionResource\Pages;

use AngelitoSystems\FilamentTenancy\Models\Seller;
use AngelitoSystems\FilamentTenancy\Resources\CommissionResource;
use Filament\Resources\Pages\ListRecords;
use Illuminate\Contracts\Auth\Authenticatable;

class ListCommissions extends ListRecords
{
    protected static string $resource = CommissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            \Filament\Actions\CreateAction::make()
                ->visible(fn() => auth()->user()?->hasRole('admin') ?? false),
        ];
    }

    protected function getTableQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $query = parent::getTableQuery();

        // Si el usuario es seller, solo mostrar sus propias comisiones
        $user = auth()->user();
        if ($user && $user->hasRole('seller') && !$user->hasRole('admin')) {
            $seller = Seller::where('user_id', $user->id)->first();
            if ($seller) {
                $query->where('seller_id', $seller->id);
            } else {
                // Si el usuario tiene rol seller pero no tiene registro en sellers, no mostrar nada
                $query->whereRaw('1 = 0');
            }
        }

        return $query;
    }
}

