<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Seller;
use AngelitoSystems\FilamentTenancy\Traits\HasResourceAuthorization;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class SellerResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;

    protected static ?string $model = Seller::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-user-group';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'user.name';

    protected static function getTranslationPrefix(): ?string
    {
        return 'sellers';
    }

    public static function getModelKey(): string
    {
        return 'seller';
    }

    public static function getPluralModelKey(): string
    {
        return 'sellers';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'sellers';
    }

    public static function getNavigationGroupKey(): ?string
    {
        return 'billing_management';
    }

    protected static function getNavigationGroupLabel(): ?string
    {
        return 'billing_management';
    }

    protected static function getAccessPermissions(): array
    {
        return ['manage sellers', 'view sellers'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create sellers', 'manage sellers'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit sellers', 'manage sellers'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete sellers', 'manage sellers'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('section_seller_information'))
                    ->schema([
                        Select::make('user_id')
                            ->label(static::__('user'))
                            ->relationship(
                                'user',
                                'name',
                                function ($query) {
                                    // Solo usuarios que no tienen un registro en sellers
                                    $userIds = \AngelitoSystems\FilamentTenancy\Models\Seller::pluck('user_id')->filter();
                                    if ($userIds->isNotEmpty()) {
                                        $query->whereNotIn('id', $userIds);
                                    }
                                    return $query;
                                }
                            )
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->label(static::__('name'))
                                    ->required(),
                                TextInput::make('email')
                                    ->label(static::__('email'))
                                    ->email()
                                    ->required()
                                    ->unique(),
                                TextInput::make('password')
                                    ->label(static::__('password'))
                                    ->password()
                                    ->required()
                                    ->minLength(8),
                            ])
                            ->helperText(static::__('user_helper'))
                            ->disabled(fn ($record) => $record !== null), // No permitir cambiar usuario después de crear

                        TextInput::make('code')
                            ->label(static::__('code'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->helperText(static::__('code_helper'))
                            ->disabled(fn($record) => $record !== null),

                        TextInput::make('commission_rate')
                            ->label(static::__('commission_rate'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->required()
                            ->helperText(static::__('commission_rate_helper')),

                        Toggle::make('is_active')
                            ->label(static::__('is_active'))
                            ->default(true)
                            ->helperText(static::__('is_active_helper')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(static::__('section_additional_info'))
                    ->schema([
                        Textarea::make('notes')
                            ->label(static::__('notes'))
                            ->rows(3)
                            ->placeholder(static::__('notes_placeholder')),

                        KeyValue::make('metadata')
                            ->label(static::__('metadata'))
                            ->keyLabel(static::__('metadata_key'))
                            ->valueLabel(static::__('metadata_value'))
                            ->addActionLabel(static::__('metadata_add'))
                            ->reorderable(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('user.name')
                    ->label(static::__('name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('user.email')
                    ->label(static::__('email'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('code')
                    ->label(static::__('code'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color('info')
                    ->copyable(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(static::__('commission_rate'))
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscriptions_count')
                    ->label(static::__('subscriptions_count'))
                    ->counts('subscriptions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_commissions')
                    ->label(static::__('total_commissions'))
                    ->money('USD')
                    ->getStateUsing(fn($record) => $record->total_commissions)
                    ->sortable(),

                Tables\Columns\TextColumn::make('total_pending_commissions')
                    ->label(static::__('total_pending_commissions'))
                    ->money('USD')
                    ->getStateUsing(fn($record) => $record->total_pending_commissions)
                    ->color('warning'),

                Tables\Columns\IconColumn::make('is_active')
                    ->boolean()
                    ->label(static::__('is_active')),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(static::__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\TernaryFilter::make('is_active')
                    ->label(static::__('is_active'))
                    ->placeholder(static::__('filter_all_sellers'))
                    ->trueLabel(static::__('filter_active'))
                    ->falseLabel(static::__('filter_inactive')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages\ListSellers::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages\CreateSeller::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages\ViewSeller::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\SellerResource\Pages\EditSeller::route('/{record}/edit'),
        ];
    }
}

