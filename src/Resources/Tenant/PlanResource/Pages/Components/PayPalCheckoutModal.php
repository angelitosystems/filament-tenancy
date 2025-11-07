<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages\Components;

use Filament\Widgets\Widget;

class PayPalCheckoutModal extends Widget
{
    protected string $view = 'filament-tenancy::components.paypal-checkout-modal';
    
    protected static bool $isLazy = false;
    
    protected int | string | array $columnSpan = 'full';
}

