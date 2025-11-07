<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Traits\HasResourceAuthorization;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\DatePicker;
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

class SubscriptionResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;

    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'id';

    /**
     * Override translation prefix to use 'subscriptions' namespace
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'subscriptions';
    }

    /**
     * Public wrapper for the protected __ method from trait
     * This allows the method to be called from any context
     */
    public static function __(string $key, array $replace = [], ?string $locale = null): string
    {
        $prefix = static::getTranslationPrefix();
        $isPublished = static::areTranslationsPublished($prefix, $locale);

        if ($isPublished) {
            // Translations published in project - load from lang/ folder
            if ($prefix) {
                $customKey = "{$prefix}.{$key}";
                $translation = __($customKey, $replace, $locale);
                if ($translation !== $customKey) {
                    return $translation;
                }
            }

            // Try default tenancy namespace
            $tenancyKey = "tenancy.{$key}";
            return __($tenancyKey, $replace, $locale);
        } else {
            // Translations NOT published - load from package ./lang/ folder
            if ($prefix) {
                $packageCustomKey = "filament-tenancy::{$prefix}.{$key}";
                $translation = __($packageCustomKey, $replace, $locale);
                if ($translation !== $packageCustomKey) {
                    return $translation;
                }
            }

            // Use package tenancy namespace
            return __("filament-tenancy::tenancy.{$key}", $replace, $locale);
        }
    }

    /**
     * Override translation keys
     */
    public static function getModelKey(): string
    {
        return 'subscription';
    }

    public static function getPluralModelKey(): string
    {
        return 'subscriptions';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'subscriptions';
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
        return ['manage subscriptions', 'view subscriptions'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create subscriptions', 'manage subscriptions'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit subscriptions', 'manage subscriptions'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete subscriptions', 'manage subscriptions'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('section_subscription_info'))
                    ->schema([
                        Select::make('tenant_id')
                            ->label(static::__('tenant'))
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->createOptionForm([
                                TextInput::make('name')
                                    ->required(),
                                TextInput::make('slug')
                                    ->required(),
                            ]),

                        Select::make('plan_id')
                            ->label(static::__('plan'))
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $plan = \AngelitoSystems\FilamentTenancy\Models\Plan::find($state);
                                if ($plan) {
                                    $set('price', $plan->price);
                                    $set('billing_cycle', $plan->billing_cycle);
                                }
                            }),

                        Select::make('seller_id')
                            ->label(static::__('seller'))
                            ->options(function () {
                                return \AngelitoSystems\FilamentTenancy\Models\Seller::with('user')
                                    ->where('is_active', true)
                                    ->get()
                                    ->mapWithKeys(function ($seller) {
                                        return [$seller->id => $seller->user->name ?? $seller->code];
                                    })
                                    ->toArray();
                            })
                            ->searchable()
                            ->preload()
                            ->helperText(static::__('seller_helper')),

                        TextInput::make('price')
                            ->label(static::__('price'))
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),

                        Select::make('billing_cycle')
                            ->label(static::__('billing_cycle'))
                            ->options([
                                'monthly' => static::__('billing_cycle_monthly'),
                                'yearly' => static::__('billing_cycle_yearly'),
                                'quarterly' => static::__('billing_cycle_quarterly'),
                                'lifetime' => static::__('billing_cycle_lifetime'),
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(static::__('section_subscription_period'))
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label(static::__('starts_at'))
                            ->required()
                            ->default(now()),

                        DatePicker::make('ends_at')
                            ->label(static::__('ends_at'))
                            ->helperText(static::__('ends_at_helper')),

                        DatePicker::make('trial_ends_at')
                            ->label(static::__('trial_ends_at'))
                            ->helperText(static::__('trial_ends_at_helper')),

                        DatePicker::make('next_billing_at')
                            ->label(static::__('next_billing_at'))
                            ->helperText(static::__('next_billing_at_helper')),
                    ])
                    ->columns(2),

                Section::make(static::__('section_status_settings'))
                    ->schema([
                        Select::make('status')
                            ->label(static::__('status'))
                            ->options([
                                'active' => static::__('status_active'),
                                'inactive' => static::__('status_inactive'),
                                'cancelled' => static::__('status_cancelled'),
                                'expired' => static::__('status_expired'),
                                'suspended' => static::__('status_suspended'),
                                'pending' => static::__('status_pending'),
                            ])
                            ->required()
                            ->default('pending'),

                        Toggle::make('auto_renew')
                            ->label(static::__('auto_renew'))
                            ->default(true)
                            ->helperText(static::__('auto_renew_helper')),

                        TextInput::make('payment_method')
                            ->label(static::__('payment_method'))
                            ->placeholder(static::__('payment_method_placeholder')),

                        TextInput::make('external_id')
                            ->label(static::__('external_id'))
                            ->placeholder(static::__('external_id_placeholder'))
                            ->helperText(static::__('external_id_helper')),
                    ])
                    ->columns(2),

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
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('tenant.name')
                    ->label(static::__('tenant'))
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => $record->tenant->slug ?? ''),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label(static::__('plan'))
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->plan->color ?? 'gray'),

                Tables\Columns\TextColumn::make('price')
                    ->label(static::__('price'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('billing_cycle')
                    ->label(static::__('billing_cycle'))
                    ->formatStateUsing(fn($state) => static::__("billing_cycle_{$state}"))
                    ->colors([
                        'monthly' => 'blue',
                        'yearly' => 'green',
                        'quarterly' => 'yellow',
                        'lifetime' => 'purple',
                    ])
                    ->badge(),

                Tables\Columns\TextColumn::make('status')
                    ->label(static::__('status'))
                    ->formatStateUsing(fn($state) => static::__("status_{$state}"))
                    ->colors([
                        'active' => 'success',
                        'inactive' => 'gray',
                        'cancelled' => 'danger',
                        'expired' => 'warning',
                        'suspended' => 'danger',
                        'pending' => 'info',
                    ])
                    ->badge(),

                Tables\Columns\IconColumn::make('auto_renew')
                    ->boolean()
                    ->label(static::__('auto_renew')),

                Tables\Columns\TextColumn::make('starts_at')
                    ->label(static::__('starts_at'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->label(static::__('ends_at'))
                    ->date()
                    ->sortable()
                    ->placeholder(static::__('lifetime')),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->label(static::__('trial_ends_at'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('seller.name')
                    ->label(static::__('seller'))
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('payment_link')
                    ->label(static::__('payment_link'))
                    ->limit(30)
                    ->tooltip(fn($record) => $record->payment_link)
                    ->icon('heroicon-o-link')
                    ->color('success')
                    ->copyable()
                    ->copyMessage('Link copiado')
                    ->visible(fn($record) => !empty($record->payment_link))
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(static::__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->label(static::__('plan'))
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->label(static::__('status'))
                    ->options([
                        'active' => static::__('status_active'),
                        'inactive' => static::__('status_inactive'),
                        'cancelled' => static::__('status_cancelled'),
                        'expired' => static::__('status_expired'),
                        'suspended' => static::__('status_suspended'),
                        'pending' => static::__('status_pending'),
                    ]),

                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->label(static::__('billing_cycle'))
                    ->options([
                        'monthly' => static::__('billing_cycle_monthly'),
                        'yearly' => static::__('billing_cycle_yearly'),
                        'quarterly' => static::__('billing_cycle_quarterly'),
                        'lifetime' => static::__('billing_cycle_lifetime'),
                    ]),

                Tables\Filters\TernaryFilter::make('auto_renew')
                    ->label(static::__('auto_renew'))
                    ->placeholder(static::__('filter_all_subscriptions'))
                    ->trueLabel(static::__('filter_auto_renew_enabled'))
                    ->falseLabel(static::__('filter_auto_renew_disabled')),

                Tables\Filters\Filter::make('expiring_soon')
                    ->query(fn($query) => $query->where('ends_at', '<=', now()->addDays(30))->where('ends_at', '>', now()))
                    ->label(static::__('filter_expiring_soon')),

                Tables\Filters\Filter::make('expired')
                    ->query(fn($query) => $query->where('ends_at', '<', now()))
                    ->label(static::__('filter_expired')),

                Tables\Filters\Filter::make('in_trial')
                    ->query(fn($query) => $query->where('trial_ends_at', '>', now()))
                    ->label(static::__('filter_in_trial')),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('generate_payment_link')
                    ->label(static::__('generate_payment_link'))
                    ->icon('heroicon-o-link')
                    ->color('info')
                    ->visible(fn($record) => ($record->isExpired() || $record->isCanceled() || $record->isPending()) && empty($record->payment_link))
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $paypalService = app(\AngelitoSystems\FilamentTenancy\Support\PayPalService::class);
                        
                        // Use appropriate method based on subscription status
                        if ($record->isPending()) {
                            $paymentLink = $paypalService->generatePaymentLinkForPending($record);
                        } else {
                        $paymentLink = $paypalService->generatePaymentLink($record);
                        }

                        if ($paymentLink) {
                            \Filament\Notifications\Notification::make()
                                ->title(static::__('payment_link_generated'))
                                ->body(static::__('payment_link_generated_message'))
                                ->success()
                                ->send();
                        } else {
                            \Filament\Notifications\Notification::make()
                                ->title(static::__('error'))
                                ->body(static::__('payment_link_error'))
                                ->danger()
                                ->send();
                        }
                    }),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        if ($record) {
                            $record->delete();
                        }
                    }),
            ])
            ->toolbarActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
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
            'index' => \AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages\ListSubscriptions::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages\CreateSubscription::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages\ViewSubscription::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\SubscriptionResource\Pages\EditSubscription::route('/{record}/edit'),
        ];
    }
}
