<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages;

use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Resources\SellerResource;
use Filament\Resources\Pages\CreateRecord;

class CreateSeller extends CreateRecord
{
    protected static string $resource = SellerResource::class;

    protected function mutateFormDataBeforeCreate(array $data): array
    {
        // Assign seller role to user if it exists
        if (isset($data['user_id'])) {
            $sellerRole = Role::where('slug', 'seller')->first();
            if ($sellerRole) {
                $userModel = config('filament-tenancy.user_model', config('auth.providers.users.model', 'App\\Models\\User'));
                $user = $userModel::find($data['user_id']);
                if ($user && method_exists($user, 'assignRole')) {
                    $user->assignRole($sellerRole);
                }
            }
        }

        return $data;
    }
}

