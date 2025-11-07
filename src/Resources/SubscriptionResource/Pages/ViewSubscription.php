<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource;
use AngelitoSystems\FilamentTenancy\Support\PayPalService;
use AngelitoSystems\FilamentTenancy\Models\PayPalSettings;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Notifications\Notification;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        $paypalService = app(PayPalService::class);
        $paypalEnabled = PayPalSettings::current()->is_enabled ?? false;
        
        return [
            Actions\EditAction::make(),
            
            // PayPal payment actions
            Actions\Action::make('pay_with_paypal')
                ->label(SubscriptionResource::__('pay_with_paypal'))
                ->color('success')
                ->icon('heroicon-o-credit-card')
                ->visible(fn ($record) => $paypalEnabled && $paypalService->isEnabled() && $record->status === 'pending' && !$record->external_id)
                ->action(function () use ($paypalService) {
                    $order = $paypalService->createOrder($this->record);
                    
                    if ($order && isset($order['links'])) {
                        $approveUrl = collect($order['links'])->firstWhere('rel', 'approve')['href'] ?? null;
                        
                        if ($approveUrl) {
                            return redirect($approveUrl);
                        }
                    }
                    
                    Notification::make()
                        ->title(SubscriptionResource::__('paypal_payment_failed'))
                        ->danger()
                        ->send();
                }),
            
            Actions\Action::make('create_paypal_subscription')
                ->label(SubscriptionResource::__('create_paypal_subscription'))
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn ($record) => $paypalEnabled && $paypalService->isEnabled() && $record->status === 'pending' && !$record->external_id && $record->plan->billing_cycle !== 'lifetime')
                ->action(function () use ($paypalService) {
                    $subscription = $paypalService->createSubscription($this->record);
                    
                    if ($subscription && isset($subscription['links'])) {
                        $approveUrl = collect($subscription['links'])->firstWhere('rel', 'approve')['href'] ?? null;
                        
                        if ($approveUrl) {
                            return redirect($approveUrl);
                        }
                    }
                    
                    Notification::make()
                        ->title(SubscriptionResource::__('paypal_subscription_failed'))
                        ->danger()
                        ->send();
                }),
            
            Actions\Action::make('cancel_paypal')
                ->label(SubscriptionResource::__('cancel_paypal_subscription'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->visible(fn ($record) => $paypalEnabled && $paypalService->isEnabled() && $record->status === 'active' && $record->payment_method === 'paypal' && $record->external_id)
                ->action(function () use ($paypalService) {
                    if ($paypalService->cancelSubscription($this->record, 'Cancelled by admin')) {
                        Notification::make()
                            ->title(SubscriptionResource::__('paypal_subscription_cancelled'))
                            ->success()
                            ->send();
                        $this->record->refresh();
                    } else {
                        Notification::make()
                            ->title(SubscriptionResource::__('payment_link_error'))
                            ->danger()
                            ->send();
                    }
                }),
            
            Actions\Action::make('cancel')
                ->label(SubscriptionResource::__('cancel_subscription'))
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'active' && $record->payment_method !== 'paypal')
                ->action(function () {
                    $this->record->cancel('Cancelled by admin');
                    Notification::make()
                        ->title(SubscriptionResource::__('subscription_cancelled'))
                        ->success()
                        ->send();
                    $this->record->refresh();
                }),
            
            Actions\Action::make('reactivate')
                ->label(SubscriptionResource::__('reactivate'))
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn ($record) => in_array($record->status, ['cancelled', 'expired']))
                ->action(function () {
                    $plan = $this->record->plan;
                    $endsAt = null;

                    if ($plan->billing_cycle !== 'lifetime') {
                        $endsAt = match($plan->billing_cycle) {
                            'monthly' => now()->addMonth(),
                            'yearly' => now()->addYear(),
                            'quarterly' => now()->addMonths(3),
                            default => now()->addMonth(),
                        };
                    }

                    $this->record->activate($endsAt);
                    Notification::make()
                        ->title(SubscriptionResource::__('subscription_reactivated'))
                        ->success()
                        ->send();
                    $this->record->refresh();
                }),

            // Generate payment link for expired/cancelled subscriptions
            Actions\Action::make('generate_payment_link')
                ->label(SubscriptionResource::__('generate_payment_link'))
                ->color('info')
                ->icon('heroicon-o-link')
                ->visible(fn ($record) => $paypalEnabled && $paypalService->isEnabled() && ($record->isExpired() || $record->isCanceled()))
                ->action(function () use ($paypalService) {
                    $paymentLink = $paypalService->generatePaymentLink($this->record);
                    
                    if ($paymentLink) {
                        \Filament\Notifications\Notification::make()
                            ->title(SubscriptionResource::__('payment_link_generated'))
                            ->body(SubscriptionResource::__('payment_link_generated_message'))
                            ->success()
                            ->persistent()
                            ->send();
                        
                        $this->record->refresh();
                    } else {
                        \Filament\Notifications\Notification::make()
                            ->title(SubscriptionResource::__('error'))
                            ->body(SubscriptionResource::__('payment_link_error'))
                            ->danger()
                            ->send();
                    }
                }),

            // Copy payment link
            Actions\Action::make('copy_payment_link')
                ->label(SubscriptionResource::__('copy_payment_link'))
                ->color('success')
                ->icon('heroicon-o-clipboard')
                ->visible(fn ($record) => !empty($record->payment_link) && (!$record->payment_link_expires_at || $record->payment_link_expires_at->isFuture()))
                ->action(function () {
                    $this->dispatch('copy-to-clipboard', text: $this->record->payment_link);
                    
                    \Filament\Notifications\Notification::make()
                        ->title(SubscriptionResource::__('payment_link_copied'))
                        ->body(SubscriptionResource::__('payment_link_copied_message'))
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make(SubscriptionResource::__('subscription_information'))
                ->schema([
                    TextEntry::make('tenant.name')
                        ->label(SubscriptionResource::__('tenant_label')),
                    TextEntry::make('plan.name')
                        ->label(SubscriptionResource::__('plan_label'))
                        ->badge()
                        ->color(fn ($record) => $record->plan->color ?? 'gray'),
                    TextEntry::make('price')
                        ->money('USD'),
                    BadgeColumn::make('billing_cycle'),
                ])
                ->columns(2),

            Section::make(SubscriptionResource::__('subscription_period'))
                ->schema([
                    TextEntry::make('starts_at')
                        ->date(),
                    TextEntry::make('ends_at')
                        ->date()
                        ->placeholder(SubscriptionResource::__('lifetime')),
                    TextEntry::make('trial_ends_at')
                        ->date()
                        ->placeholder(SubscriptionResource::__('no_trial')),
                    TextEntry::make('next_billing_at')
                        ->date()
                        ->placeholder(SubscriptionResource::__('n_a')),
                ])
                ->columns(2),

            Section::make(SubscriptionResource::__('status_settings'))
                ->schema([
                    BadgeColumn::make('status')
                        ->colors([
                            'active' => 'success',
                            'inactive' => 'gray',
                            'cancelled' => 'danger',
                            'expired' => 'warning',
                            'suspended' => 'danger',
                            'pending' => 'info',
                        ]),
                    IconEntry::make('auto_renew')
                        ->boolean(),
                    TextEntry::make('payment_method')
                        ->placeholder(SubscriptionResource::__('not_specified')),
                    TextEntry::make('external_id')
                        ->placeholder(SubscriptionResource::__('not_specified')),
                ])
                ->columns(2),

            Section::make(SubscriptionResource::__('additional_information'))
                ->schema([
                    TextEntry::make('notes')
                        ->placeholder(SubscriptionResource::__('no_notes_label')),
                    KeyValueEntry::make('metadata')
                        ->columnSpanFull(),
                ]),

            Section::make(SubscriptionResource::__('timestamps'))
                ->schema([
                    TextEntry::make('created_at')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->dateTime(),
                ])
                ->columns(2),
        ];
    }
}
