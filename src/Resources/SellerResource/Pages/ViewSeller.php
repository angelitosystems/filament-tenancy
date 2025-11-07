<?php

namespace AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\SellerResource;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewSeller extends ViewRecord
{
    protected static string $resource = SellerResource::class;

    protected function getInfolistSchema(): array
    {
        return [
            Section::make(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('section_seller_information'))
                ->schema([
                    TextEntry::make('user.name')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('name')),
                    TextEntry::make('user.email')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('email')),
                    TextEntry::make('code')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('code'))
                        ->badge()
                        ->color('info')
                        ->copyable(),
                    TextEntry::make('commission_rate')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('commission_rate'))
                        ->suffix('%'),
                    IconEntry::make('is_active')
                        ->boolean()
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('is_active')),
                ])
                ->columns(2),

            Section::make(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('section_statistics'))
                ->schema([
                    TextEntry::make('subscriptions_count')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('subscriptions_count'))
                        ->getStateUsing(fn($record) => $record->subscriptions()->count()),
                    TextEntry::make('total_commissions')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('total_commissions'))
                        ->money('USD')
                        ->getStateUsing(fn($record) => $record->total_commissions),
                    TextEntry::make('total_pending_commissions')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('total_pending_commissions'))
                        ->money('USD')
                        ->getStateUsing(fn($record) => $record->total_pending_commissions),
                    TextEntry::make('total_paid_commissions')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('total_paid_commissions'))
                        ->money('USD')
                        ->getStateUsing(fn($record) => $record->total_paid_commissions),
                ])
                ->columns(2),

            Section::make(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('section_additional_info'))
                ->schema([
                    TextEntry::make('notes')
                        ->label(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('notes'))
                        ->placeholder(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('no_notes')),
                    KeyValueEntry::make('metadata')
                        ->columnSpanFull(),
                ]),

            Section::make(\AngelitoSystems\FilamentTenancy\Resources\SellerResource::__('section_timestamps'))
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

