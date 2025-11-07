<?php

namespace AngelitoSystems\FilamentTenancy\Resources\CommissionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\CommissionResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewCommission extends ViewRecord
{
    protected static string $resource = CommissionResource::class;

    protected function getInfolistSchema(): array
    {
        return [
            Section::make(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('section_commission_information'))
                ->schema([
                    TextEntry::make('seller.name')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('seller')),
                    TextEntry::make('subscription.id')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('subscription')),
                    TextEntry::make('subscription_amount')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('subscription_amount'))
                        ->money('USD'),
                    TextEntry::make('commission_rate')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('commission_rate'))
                        ->suffix('%'),
                    TextEntry::make('amount')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('amount'))
                        ->money('USD'),
                    TextEntry::make('status')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('status'))
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'pending' => 'warning',
                            'paid' => 'success',
                            'cancelled' => 'danger',
                            default => 'gray',
                        }),
                    TextEntry::make('paid_at')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('paid_at'))
                        ->date()
                        ->placeholder(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('not_paid')),
                ])
                ->columns(2),

            Section::make(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('section_additional_info'))
                ->schema([
                    TextEntry::make('notes')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('notes'))
                        ->placeholder(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('no_notes')),
                    KeyValueEntry::make('metadata')
                        ->columnSpanFull(),
                ]),

            Section::make(\AngelitoSystems\FilamentTenancy\Resources\CommissionResource::__('section_timestamps'))
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

