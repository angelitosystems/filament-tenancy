<?php

namespace AngelitoSystems\FilamentTenancy\Facades;

use Illuminate\Support\Facades\Facade;

/**
 * @see \AngelitoSystems\FilamentTenancy\Support\PermissionManager
 */
class TenancyPermissions extends Facade
{
    protected static function getFacadeAccessor(): string
    {
        return 'tenancy.permissions';
    }
}
