<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class PlanResource extends Resource
{
    use HasSimpleTranslations;

    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Override translation keys
     */
    public static function getNavigationKey(): string
    {
        return 'plans';
    }

    public static function getModelKey(): string
    {
        return 'plan';
    }

    public static function getPluralModelKey(): string
    {
        return 'plans';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'plans';
    }

    public static function getNavigationGroupKey(): ?string
    {
        return 'billing_management';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('tenancy.plan_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('tenancy.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, Set $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label(__('tenancy.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readonly(),

                        Textarea::make('description')
                            ->label(__('tenancy.description'))
                            ->rows(3)
                            ->placeholder(__('tenancy.describe_plan')),

                        ColorPicker::make('color')
                            ->label(__('tenancy.color'))
                            ->helperText(__('tenancy.color_helper')),
                    ])
                    ->columns(2),

                Section::make(__('tenancy.pricing'))
                    ->schema([
                        TextInput::make('price')
                            ->label(__('tenancy.price'))
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),

                        Select::make('billing_cycle')
                            ->label(__('tenancy.billing_cycle'))
                            ->options([
                                'monthly' => __('tenancy.monthly'),
                                'yearly' => __('tenancy.yearly'),
                                'quarterly' => __('tenancy.quarterly'),
                                'lifetime' => __('tenancy.lifetime'),
                            ])
                            ->required()
                            ->default('monthly'),

                        TextInput::make('trial_days')
                            ->label(__('tenancy.trial_days'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('tenancy.trial_days_helper')),

                        Toggle::make('is_active')
                            ->label(__('tenancy.is_active'))
                            ->default(true)
                            ->helperText(__('tenancy.active_helper')),
                    ])
                    ->columns(2),

                Section::make(__('tenancy.features_limits'))
                    ->schema([
                        KeyValue::make('features')
                            ->label(__('tenancy.features'))
                            ->keyLabel(__('tenancy.feature_name'))
                            ->valueLabel(__('tenancy.feature_description'))
                            ->addActionLabel(__('tenancy.add_feature'))
                            ->reorderable()
                            ->deleteAction(fn(Action $action) => $action->requiresConfirmation()),

                        KeyValue::make('limits')
                            ->label(__('tenancy.limits'))
                            ->keyLabel(__('tenancy.limit_name'))
                            ->valueLabel(__('tenancy.limit_value'))
                            ->addActionLabel(__('tenancy.add_limit'))
                            ->reorderable()
                            ->deleteAction(fn(Action $action) => $action->requiresConfirmation()),
                    ])
                    ->columns(2),

                Section::make(__('tenancy.display_settings'))
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(__('tenancy.sort_order'))
                            ->numeric()
                            ->default(0)
                            ->helperText(__('tenancy.sort_order_helper')),

                        Toggle::make('is_popular')
                            ->label(__('tenancy.is_popular'))
                            ->default(false)
                            ->helperText(__('tenancy.popular_helper')),

                        Toggle::make('is_featured')
                            ->label(__('tenancy.is_featured'))
                            ->default(false)
                            ->helperText(__('tenancy.featured_helper')),
                    ])
                    ->columns(3),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(__('tenancy.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('tenancy.slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('price')
                    ->label(__('tenancy.price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label(__('tenancy.billing_cycle'))
                    ->badge()
                    ->colors([
                        'monthly' => 'blue',
                        'yearly' => 'green',
                        'quarterly' => 'yellow',
                        'lifetime' => 'purple',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('tenancy.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label(__('tenancy.is_popular'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(__('tenancy.is_featured'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(__('tenancy.sort_order'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->options([
                        'monthly' => __('tenancy.monthly'),
                        'yearly' => __('tenancy.yearly'),
                        'quarterly' => __('tenancy.quarterly'),
                        'lifetime' => __('tenancy.lifetime'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(__('tenancy.is_active'))
                    ->placeholder(__('tenancy.all_plans'))
                    ->trueLabel(__('tenancy.active_plans'))
                    ->falseLabel(__('tenancy.inactive_plans')),

                Tables\Filters\TernaryFilter::make('is_popular')
                    ->label(__('tenancy.is_popular'))
                    ->placeholder(__('tenancy.all_plans'))
                    ->trueLabel(__('tenancy.popular_plans'))
                    ->falseLabel(__('tenancy.regular_plans')),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(__('tenancy.is_featured'))
                    ->placeholder(__('tenancy.all_plans'))
                    ->trueLabel(__('tenancy.featured_plans'))
                    ->falseLabel(__('tenancy.regular_plans')),
            ])
            ->actions([
                ViewAction::make()
                    ->label(__('tenancy.view')),
                EditAction::make()
                    ->label(__('tenancy.edit')),
                DeleteAction::make()
                    ->label(__('tenancy.delete')),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
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
            'index' => \AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages\ListPlans::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages\CreatePlan::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages\ViewPlan::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\PlanResource\Pages\EditPlan::route('/{record}/edit'),
        ];
    }
}
