<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant;

use AngelitoSystems\FilamentTenancy\Models\Plan;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\ViewAction;
use Filament\Resources\Resource;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Support\Icons\Heroicon;

class PlanResource extends Resource
{
    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = Heroicon::OutlinedCurrencyDollar;

    protected static ?string $navigationLabel = 'Planes';

    protected static ?string $modelLabel = 'Plan';

    protected static ?string $pluralModelLabel = 'Planes';

    protected static ?string $breadcrumb = 'Planes';

    protected static string|\UnitEnum|null $navigationGroup = 'Facturación';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    public static function canCreate(): bool
    {
        return false; // Tenants cannot create plans, only view available plans
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\BadgeColumn::make('billing_cycle')
                    ->colors([
                        'monthly' => 'blue',
                        'yearly' => 'green',
                        'quarterly' => 'yellow',
                        'lifetime' => 'purple',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label('Available'),

                Tables\Columns\IconColumn::make('is_popular')
                    ->boolean()
                    ->label('Popular')
                    ->trueIcon('heroicon-o-star')
                    ->falseIcon('heroicon-o-star'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->options([
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                        'quarterly' => 'Quarterly',
                        'lifetime' => 'Lifetime',
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label('Available')
                    ->placeholder('All plans')
                    ->trueLabel('Available plans')
                    ->falseLabel('Unavailable plans'),

                Tables\Filters\TernaryFilter::make('is_popular')
                    ->label('Popular')
                    ->placeholder('All plans')
                    ->trueLabel('Popular plans')
                    ->falseLabel('Regular plans'),
            ])
            ->actions([
                ViewAction::make()
                    ->label('View Details'),
                Action::make('subscribe')
                    ->label('Subscribe')
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn($record) => $record->is_active)
                    ->action(function (Plan $record) {
                        // Redirect to subscription page or open modal
                        // This would integrate with your payment system
                        \Filament\Notifications\Notification::make()
                            ->title('Subscribe to ' . $record->name)
                            ->body('Redirecting to payment...')
                            ->success()
                            ->send();
                    }),
            ])
            ->defaultSort('sort_order', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages\ListPlans::route('/'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\PlanResource\Pages\ViewPlan::route('/{record}'),
        ];
    }
}
