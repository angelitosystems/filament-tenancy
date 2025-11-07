<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\TextColumn;

class ViewPlan extends ViewRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('subscribe')
                ->label(PlanResource::__('subscribe_now'))
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->visible(function () {
                    // No mostrar si el plan no está activo
                    if (!$this->record->is_active) {
                        return false;
                    }
                    
                    // No mostrar si es el plan actual del tenant
                    $tenant = Tenancy::current();
                    if (!$tenant) {
                        return true;
                    }
                    
                    $activeSubscription = $tenant->subscriptions()
                        ->where('status', Subscription::STATUS_ACTIVE)
                        ->where('plan_id', $this->record->id)
                        ->where(function ($query) {
                            $query->whereNull('ends_at')
                                ->orWhere('ends_at', '>', now());
                        })
                        ->first();
                    
                    // Si tiene suscripción activa para este plan, no mostrar el botón
                    return !$activeSubscription;
                })
                ->modalHeading(PlanResource::__('completing_payment'))
                ->modalDescription(PlanResource::__('please_complete_payment'))
                ->modalWidth('5xl')
                ->modalSubmitAction(false)
                ->modalCancelActionLabel(PlanResource::__('cancel'))
                ->mountUsing(function () {
                    // Create subscription before opening modal
                    PlanResource::initializeSubscription($this->record, $this);
                })
                ->modalContent(function () {
                    return view('filament-tenancy::components.paypal-checkout-iframe', [
                        'plan' => $this->record,
                        'subscriptionId' => session()->get('pending_paypal_subscription_id'),
                        'approvalUrl' => session()->get('pending_paypal_approval_url'),
                    ]);
                }),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make(PlanResource::__('plan_details'))
                ->schema([
                    TextEntry::make('name')
                        ->label(PlanResource::__('name'))
                        ->size('text-lg')
                        ->weight('bold'),
                    TextEntry::make('description')
                        ->label(PlanResource::__('description'))
                        ->columnSpanFull(),
                ])
                ->columns(1),

            Section::make(PlanResource::__('pricing_information'))
                ->schema([
                    TextEntry::make('price')
                        ->label(PlanResource::__('price'))
                        ->money('USD')
                        ->size('text-xl')
                        ->weight('bold')
                        ->color('primary'),
                    TextEntry::make('billing_cycle')
                        ->label(PlanResource::__('billing_cycle'))
                        ->formatStateUsing(fn ($state) => PlanResource::__($state) ?? ucfirst($state))
                        ->badge(),
                    TextEntry::make('trial_days')
                        ->label(PlanResource::__('trial_period'))
                        ->formatStateUsing(fn ($state) => $state 
                            ? "{$state} " . PlanResource::__('days') 
                            : PlanResource::__('no_trial')),
                    IconEntry::make('is_active')
                        ->label(PlanResource::__('available'))
                        ->boolean(),
                ])
                ->columns(2),

            Section::make(PlanResource::__('features'))
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('features')
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => !empty($record->features)),

            Section::make(PlanResource::__('limits'))
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('limits')
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => !empty($record->limits)),

            Section::make(PlanResource::__('plan_highlights'))
                ->schema([
                    \Filament\Infolists\Components\IconEntry::make('is_popular')
                        ->label(PlanResource::__('popular_plan'))
                        ->boolean()
                        ->trueIcon('heroicon-o-star')
                        ->trueColor('warning'),
                    \Filament\Infolists\Components\IconEntry::make('is_featured')
                        ->label(PlanResource::__('featured'))
                        ->boolean()
                        ->trueIcon('heroicon-o-sparkles')
                        ->trueColor('success'),
                ])
                ->columns(2)
                ->visible(fn ($record) => $record->is_popular || $record->is_featured),
        ];
    }
}
