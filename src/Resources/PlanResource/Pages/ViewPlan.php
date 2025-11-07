<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PlanResource;
use Filament\Actions;
use Filament\Infolists\Components\ColorEntry;
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
            Actions\EditAction::make(),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make('Plan Information')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('slug'),
                    TextEntry::make('description'),
                    ColorEntry::make('color'),
                ])
                ->columns(2),

            Section::make('Pricing')
                ->schema([
                    TextEntry::make('price')
                        ->money('USD'),
                    TextColumn::make('billing_cycle')
                        ->badge(),
                    TextEntry::make('trial_days')
                        ->suffix(' days'),
                    IconEntry::make('is_active')
                        ->boolean(),
                ])
                ->columns(2),

            Section::make('Features')
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('features')
                        ->columnSpanFull(),
                ]),

            Section::make('Limits')
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('limits')
                        ->columnSpanFull(),
                ]),

            Section::make('Display Settings')
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
