<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPlan extends ViewRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            \Filament\Infolists\Components\Section::make('Plan Information')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('name'),
                    \Filament\Infolists\Components\TextEntry::make('slug'),
                    \Filament\Infolists\Components\TextEntry::make('description'),
                    \Filament\Infolists\Components\ColorEntry::make('color'),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Pricing')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('price')
                        ->money('USD'),
                    \Filament\Infolists\Components\BadgeEntry::make('billing_cycle'),
                    \Filament\Infolists\Components\TextEntry::make('trial_days')
                        ->suffix(' days'),
                    \Filament\Infolists\Components\IconEntry::make('is_active')
                        ->boolean(),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Features')
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('features')
                        ->columnSpanFull(),
                ]),

            \Filament\Infolists\Components\Section::make('Limits')
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('limits')
                        ->columnSpanFull(),
                ]),

            \Filament\Infolists\Components\Section::make('Display Settings')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('sort_order'),
                    \Filament\Infolists\Components\IconEntry::make('is_popular')
                        ->label('Popular')
                        ->boolean(),
                    \Filament\Infolists\Components\IconEntry::make('is_featured')
                        ->label('Featured')
                        ->boolean(),
                ])
                ->columns(3),

            \Filament\Infolists\Components\Section::make('Timestamps')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('created_at')
                        ->dateTime(),
                    \Filament\Infolists\Components\TextEntry::make('updated_at')
                        ->dateTime(),
                ])
                ->columns(2),
        ];
    }
}
