<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Tenant;
use AngelitoSystems\FilamentTenancy\Resources\TenantResource\Pages;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\KeyValue;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Database\Eloquent\SoftDeletingScope;

class TenantResource extends Resource
{

    protected static ?string $model = Tenant::class;

    protected static string | BackedEnum | null $navigationIcon = 'heroicon-o-building-office';

    protected static ?int $navigationSort = 1;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Override translation keys
     */
    public static function getNavigationKey(): string
    {
        return 'tenants';
    }

    /**
     * Example: Customize translation prefix
     * 
     * If you want to use custom translation namespace like 'tenant.fields',
     * uncomment this method:
     * 
     * protected static function getTranslationPrefix(): ?string
     * {
     *     return 'tenant.fields'; // Will search in 'tenant.fields.{key}'
     * }
     * 
     * The trait will search translations in this order:
     * 1. Custom prefix: 'tenant.fields.{key}'
     * 2. Package namespace: 'filament-tenancy::tenancy.{key}'
     * 3. Default namespace: 'tenancy.{key}'
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'tenant.fields'; // Will search in 'tenant.fields.{key}'
    }

    public static function getModelKey(): string
    {
        return 'tenant';
    }

    public static function getPluralModelKey(): string
    {
        return 'tenants';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'tenants';
    }

    public static function getNavigationGroupKey(): ?string
    {
        return 'user_management';
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('tenancy.basic_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('tenancy.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $context, $state, Set $set) {
                                if ($context === 'create') {
                                    $set('slug', \Illuminate\Support\Str::slug($state));
                                }
                            }),

                        TextInput::make('slug')
                            ->label(__('tenancy.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->rules(['alpha_dash']),

                        Toggle::make('is_active')
                            ->label(__('tenancy.is_active'))
                            ->default(true),

                        Select::make('plan')
                            ->label(__('tenancy.plan'))
                            ->options([
                                'basic' => __('tenancy.basic'),
                                'premium' => __('tenancy.premium'),
                                'enterprise' => __('tenancy.enterprise'),
                            ])
                            ->placeholder(__('tenancy.select_plan')),

                        DatePicker::make('expires_at')
                            ->label(__('tenancy.expires_at'))
                            ->placeholder(__('tenancy.never_expires')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('tenancy.domain_configuration'))
                    ->schema([
                        TextInput::make('domain')
                            ->label(__('tenancy.domain'))
                            ->placeholder(__('tenancy.example_domain'))
                            ->helperText(__('tenancy.full_domain'))
                            ->unique(ignoreRecord: true),

                        TextInput::make('subdomain')
                            ->label(__('tenancy.subdomain'))
                            ->placeholder(__('tenancy.subdomain_prefix'))
                            ->helperText(__('tenancy.subdomain_helper'))
                            ->unique(ignoreRecord: true),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('tenancy.additional_data'))
                    ->schema([
                        KeyValue::make('data')
                            ->label(__('tenancy.data'))
                            ->keyLabel(__('tenancy.name'))
                            ->valueLabel(__('tenancy.value'))
                            ->helperText(__('tenancy.custom_data')),
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable(),

                Tables\Columns\TextColumn::make('name')
                    ->label(__('tenancy.name'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->label(__('tenancy.slug'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('domain')
                    ->label(__('tenancy.domain'))
                    ->searchable()
                    ->placeholder(__('tenancy.na')),

                Tables\Columns\TextColumn::make('subdomain')
                    ->label(__('tenancy.subdomain'))
                    ->searchable()
                    ->placeholder(__('tenancy.na')),

                Tables\Columns\IconColumn::make('is_active')
                    ->label(__('tenancy.is_active'))
                    ->boolean()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan')
                    ->label(__('tenancy.plan'))
                    ->badge()
                    ->color(fn(string $state): string => match ($state) {
                        'basic' => 'gray',
                        'premium' => 'warning',
                        'enterprise' => 'success',
                        default => 'gray',
                    })
                    ->placeholder(__('tenancy.no_plan')),

                Tables\Columns\TextColumn::make('expires_at')
                    ->label(__('tenancy.expires_at'))
                    ->dateTime()
                    ->placeholder(__('tenancy.never'))
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label('Created at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('is_active')
                    ->label(__('tenancy.is_active'))
                    ->options([
                        'true' => __('tenancy.active_status'),
                        'false' => __('tenancy.expired'),
                    ]),

                Tables\Filters\SelectFilter::make('plan')
                    ->label(__('tenancy.plan'))
                    ->options([
                        'basic' => __('tenancy.basic'),
                        'premium' => __('tenancy.premium'),
                        'enterprise' => __('tenancy.enterprise'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListTenants::route('/'),
            'create' => Pages\CreateTenant::route('/create'),
            'view' => Pages\ViewTenant::route('/{record}'),
            'edit' => Pages\EditTenant::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): Builder
    {
        return parent::getEloquentQuery()
            ->withoutGlobalScopes([
                SoftDeletingScope::class,
            ]);
    }
}
