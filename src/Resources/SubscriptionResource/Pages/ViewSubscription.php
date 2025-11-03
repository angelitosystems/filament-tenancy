<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;

class ViewSubscription extends ViewRecord
{
    protected static string $resource = SubscriptionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('cancel')
                ->label('Cancel Subscription')
                ->color('danger')
                ->icon('heroicon-o-x-mark')
                ->requiresConfirmation()
                ->visible(fn ($record) => $record->status === 'active')
                ->action(function () {
                    $this->record->update(['status' => 'cancelled']);
                    $this->notify('success', 'Subscription cancelled successfully');
                }),
            Actions\Action::make('reactivate')
                ->label('Reactivate')
                ->color('success')
                ->icon('heroicon-o-arrow-path')
                ->visible(fn ($record) => in_array($record->status, ['cancelled', 'expired']))
                ->action(function () {
                    $this->record->update(['status' => 'active']);
                    $this->notify('success', 'Subscription reactivated successfully');
                }),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make('Subscription Information')
                ->schema([
                    TextEntry::make('tenant.name')
                        ->label('Tenant'),
                    TextEntry::make('plan.name')
                        ->label('Plan')
                        ->badge()
                        ->color(fn ($record) => $record->plan->color ?? 'gray'),
                    TextEntry::make('price')
                        ->money('USD'),
                    BadgeColumn::make('billing_cycle'),
                ])
                ->columns(2),

            Section::make('Subscription Period')
                ->schema([
                    TextEntry::make('starts_at')
                        ->date(),
                    TextEntry::make('ends_at')
                        ->date()
                        ->placeholder('Lifetime'),
                    TextEntry::make('trial_ends_at')
                        ->date()
                        ->placeholder('No trial'),
                    TextEntry::make('next_billing_at')
                        ->date()
                        ->placeholder('N/A'),
                ])
                ->columns(2),

            Section::make('Status & Settings')
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
                        ->placeholder('Not specified'),
                    TextEntry::make('external_id')
                        ->placeholder('Not specified'),
                ])
                ->columns(2),

            Section::make('Additional Information')
                ->schema([
                    TextEntry::make('notes')
                        ->placeholder('No notes'),
                    KeyValueEntry::make('metadata')
                        ->columnSpanFull(),
                ]),

            Section::make('Timestamps')
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
