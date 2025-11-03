<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPlan extends ViewRecord
{
    protected static string $resource = PlanResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('subscribe')
                ->label('Subscribe Now')
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->visible(fn ($record) => $record->is_active)
                ->action(function () {
                    // Handle subscription logic
                    \Filament\Notifications\Notification::make()
                        ->title('Subscription Initiated')
                        ->body('Redirecting to payment...')
                        ->success()
                        ->send();
                }),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            \Filament\Infolists\Components\Section::make('Plan Details')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('name')
                        ->size('text-lg')
                        ->weight('bold'),
                    \Filament\Infolists\Components\TextEntry::make('description')
                        ->columnSpanFull(),
                ])
                ->columns(1),

            \Filament\Infolists\Components\Section::make('Pricing Information')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('price')
                        ->money('USD')
                        ->size('text-xl')
                        ->weight('bold')
                        ->color('primary'),
                    \Filament\Infolists\Components\BadgeEntry::make('billing_cycle'),
                    \Filament\Infolists\Components\TextEntry::make('trial_days')
                        ->label('Trial Period')
                        ->formatStateUsing(fn ($state) => $state ? "{$state} days" : 'No trial'),
                    \Filament\Infolists\Components\IconEntry::make('is_active')
                        ->label('Available')
                        ->boolean(),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Features')
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('features')
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => !empty($record->features)),

            \Filament\Infolists\Components\Section::make('Limits')
                ->schema([
                    \Filament\Infolists\Components\KeyValueEntry::make('limits')
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => !empty($record->limits)),

            \Filament\Infolists\Components\Section::make('Plan Highlights')
                ->schema([
                    \Filament\Infolists\Components\IconEntry::make('is_popular')
                        ->label('Popular Plan')
                        ->boolean()
                        ->trueIcon('heroicon-o-star')
                        ->trueColor('warning'),
                    \Filament\Infolists\Components\IconEntry::make('is_featured')
                        ->label('Featured')
                        ->boolean()
                        ->trueIcon('heroicon-o-sparkles')
                        ->trueColor('success'),
                ])
                ->columns(2)
                ->visible(fn ($record) => $record->is_popular || $record->is_featured),
        ];
    }
}
