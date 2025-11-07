<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\PayPalSettings;
use AngelitoSystems\FilamentTenancy\Traits\HasResourceAuthorization;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use BackedEnum;
use Filament\Forms\Components\Placeholder;
use Filament\Forms\Components\Select;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class PayPalSettingsResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;

    protected static ?string $model = PayPalSettings::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-credit-card';

    protected static ?int $navigationSort = 1;

    protected static function getTranslationPrefix(): ?string
    {
        return 'paypal_settings';
    }

    /**
     * Override translation keys
     */
    public static function getModelKey(): string
    {
        return 'paypal_settings';
    }

    public static function getPluralModelKey(): string
    {
        return 'paypal_settings';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'paypal_settings';
    }

    /**
     * Define el key del navigation group
     * Por defecto busca la traducción en: tenancy.navigation_groups.user_management
     */
    public static function getNavigationGroupKey(): ?string
    {
        return 'administration';
    }


    protected static function getNavigationGroupLabel(): ?string
    {
        return 'administration';
    }

    /**
     * Define permisos y roles para autorización
     */
    protected static function getAccessPermissions(): array
    {
        return ['manage paypal settings', 'view paypal settings'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit paypal settings', 'manage paypal settings'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('section_status'))
                    ->schema([
                        Toggle::make('is_enabled')
                            ->label(static::__('is_enabled'))
                            ->helperText(static::__('is_enabled_helper'))
                            ->default(false)
                            ->required(),
                    ])
                    ->columns(1)
                    ->columnSpanFull(),

                Section::make(static::__('section_credentials'))
                    ->schema([
                        Select::make('mode')
                            ->label(static::__('mode'))
                            ->options([
                                'sandbox' => static::__('mode_sandbox'),
                                'live' => static::__('mode_live'),
                            ])
                            ->default('sandbox')
                            ->required()
                            ->helperText(static::__('mode_helper'))
                            ->reactive(),

                        TextInput::make('client_id')
                            ->label(static::__('client_id'))
                            ->required(fn($get) => $get('is_enabled'))
                            ->helperText(static::__('client_id_helper'))
                            ->password(false)
                            ->visible(fn($get) => $get('is_enabled')),

                        TextInput::make('client_secret')
                            ->label(static::__('client_secret'))
                            ->required(fn($get) => $get('is_enabled'))
                            ->helperText(static::__('client_secret_helper'))
                            ->password()
                            ->visible(fn($get) => $get('is_enabled')),

                        TextInput::make('currency')
                            ->label(static::__('currency'))
                            ->default('USD')
                            ->required()
                            ->maxLength(3)
                            ->helperText(static::__('currency_helper'))
                            ->visible(fn($get) => $get('is_enabled')),
                    ])
                    ->columns(2)
                    ->visible(fn($get) => $get('is_enabled'))
                    ->columnSpanFull(),

                Section::make(static::__('section_webhooks'))
                    ->schema([
                        TextInput::make('webhook_secret')
                            ->label(static::__('webhook_secret'))
                            ->helperText(static::__('webhook_secret_helper'))
                            ->password()
                            ->visible(fn($get) => $get('is_enabled')),

                        TextInput::make('return_url')
                            ->label(static::__('return_url'))
                            ->default('/paypal/success')
                            ->required()
                            ->helperText(static::__('return_url_helper'))
                            ->visible(fn($get) => $get('is_enabled')),

                        TextInput::make('cancel_url')
                            ->label(static::__('cancel_url'))
                            ->default('/paypal/cancel')
                            ->required()
                            ->helperText(static::__('cancel_url_helper'))
                            ->visible(fn($get) => $get('is_enabled')),
                    ])
                    ->columns(1)
                    ->visible(fn($get) => $get('is_enabled'))
                    ->columnSpanFull(),

                Section::make(static::__('section_info'))
                    ->schema([
                        Placeholder::make('info')
                            ->label('')
                            ->content(static::__('info_content'))
                            ->visible(fn($get) => $get('is_enabled')),
                    ])
                    ->visible(fn($get) => $get('is_enabled'))
                    ->columnSpanFull(),
            ]);
    }

    public static function getPages(): array
    {
        return [
            'index' => PayPalSettingsResource\Pages\ManagePayPalSettings::route('/edit'),
        ];
    }

    public static function canCreate(): bool
    {
        return false;
    }

    public static function shouldRegisterNavigation(): bool
    {
        return true;
    }
}
