<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Traits\HasResourceAuthorization;
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
    use HasResourceAuthorization;

    protected static ?string $model = Plan::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Override translation prefix to use 'plans' namespace
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'plans';
    }

    /**
     * Override translation keys
     */
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

    /**
     * Define el key del navigation group
     */
    public static function getNavigationGroupKey(): ?string
    {
        return 'billing_management';
    }

    protected static function getNavigationGroupLabel(): ?string
    {
        return 'billing_management';
    }

    /**
     * Define permisos y roles para autorización
     */
    protected static function getAccessPermissions(): array
    {
        return ['manage plans', 'view plans'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create plans', 'manage plans'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit plans', 'manage plans'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete plans', 'manage plans'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('section_plan_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(static::__('name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, Set $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label(static::__('slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readonly(),

                        Textarea::make('description')
                            ->label(static::__('description'))
                            ->rows(3)
                            ->placeholder(static::__('describe_plan')),

                        ColorPicker::make('color')
                            ->label(static::__('color'))
                            ->helperText(static::__('color_helper')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(static::__('section_pricing'))
                    ->schema([
                        TextInput::make('price')
                            ->label(static::__('price'))
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),

                        Select::make('billing_cycle')
                            ->label(static::__('billing_cycle'))
                            ->options([
                                'monthly' => static::__('monthly'),
                                'yearly' => static::__('yearly'),
                                'quarterly' => static::__('quarterly'),
                                'lifetime' => static::__('lifetime'),
                            ])
                            ->required()
                            ->default('monthly'),

                        TextInput::make('trial_days')
                            ->label(static::__('trial_days'))
                            ->numeric()
                            ->default(0)
                            ->helperText(static::__('trial_days_helper')),

                        Toggle::make('is_active')
                            ->label(static::__('is_active'))
                            ->default(true)
                            ->helperText(static::__('active_helper')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(static::__('section_features_limits'))
                    ->schema([
                        KeyValue::make('features')
                            ->label(static::__('features'))
                            ->keyLabel(static::__('feature_name'))
                            ->valueLabel(static::__('feature_description'))
                            ->addActionLabel(static::__('add_feature'))
                            ->reorderable()
                            ->deleteAction(fn(Action $action) => $action->requiresConfirmation()),

                        KeyValue::make('limits')
                            ->label(static::__('limits'))
                            ->keyLabel(static::__('limit_name'))
                            ->valueLabel(static::__('limit_value'))
                            ->addActionLabel(static::__('add_limit'))
                            ->reorderable()
                            ->deleteAction(fn(Action $action) => $action->requiresConfirmation()),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(static::__('section_display_settings'))
                    ->schema([
                        TextInput::make('sort_order')
                            ->label(static::__('sort_order'))
                            ->numeric()
                            ->default(0)
                            ->helperText(static::__('sort_order_helper')),

                        Toggle::make('is_popular')
                            ->label(static::__('is_popular'))
                            ->default(false)
                            ->helperText(static::__('popular_helper')),

                        Toggle::make('is_featured')
                            ->label(static::__('is_featured'))
                            ->default(false)
                            ->helperText(static::__('featured_helper')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->label(static::__('name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(static::__('slug'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('price')
                    ->label(static::__('price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label(static::__('billing_cycle'))
                    ->badge()
                    ->formatStateUsing(fn($state) => static::__($state))
                    ->colors([
                        'monthly' => 'blue',
                        'yearly' => 'green',
                        'quarterly' => 'yellow',
                        'lifetime' => 'purple',
                    ]),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(static::__('is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_popular')
                    ->label(static::__('is_popular'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\IconColumn::make('is_featured')
                    ->label(static::__('is_featured'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('sort_order')
                    ->label(static::__('sort_order'))
                    ->sortable()
                    ->toggleable(),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(static::__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->label(static::__('billing_cycle'))
                    ->options([
                        'monthly' => static::__('monthly'),
                        'yearly' => static::__('yearly'),
                        'quarterly' => static::__('quarterly'),
                        'lifetime' => static::__('lifetime'),
                    ]),

                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(static::__('is_active'))
                    ->placeholder(static::__('all_plans'))
                    ->trueLabel(static::__('active_plans'))
                    ->falseLabel(static::__('inactive_plans')),

                Tables\Filters\TernaryFilter::make('is_popular')
                    ->label(static::__('is_popular'))
                    ->placeholder(static::__('all_plans'))
                    ->trueLabel(static::__('popular_plans'))
                    ->falseLabel(static::__('regular_plans')),

                Tables\Filters\TernaryFilter::make('is_featured')
                    ->label(static::__('is_featured'))
                    ->placeholder(static::__('all_plans'))
                    ->trueLabel(static::__('featured_plans'))
                    ->falseLabel(static::__('regular_plans')),
            ])
            ->recordActions([
                ViewAction::make()
                    ->label(static::__('view')),
                EditAction::make()
                    ->label(static::__('edit')),
                DeleteAction::make()
                    ->label(static::__('delete')),
            ])
            ->toolbarActions([
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
