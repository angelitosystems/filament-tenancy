<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Commission;
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
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class CommissionResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;

    protected static ?string $model = Commission::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?int $navigationSort = 4;

    protected static ?string $recordTitleAttribute = 'id';

    protected static function getTranslationPrefix(): ?string
    {
        return 'commissions';
    }

    public static function getModelKey(): string
    {
        return 'commission';
    }

    public static function getPluralModelKey(): string
    {
        return 'commissions';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'commissions';
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
        return ['manage commissions', 'view commissions', 'view own commissions'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin', 'seller'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create commissions', 'manage commissions'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit commissions', 'manage commissions'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete commissions', 'manage commissions'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function canCreate(): bool
    {
        if (!auth()->check()) {
            return false;
        }
        
        $user = auth()->user();
        if (!$user || !method_exists($user, 'hasRole')) {
            return false;
        }
        return $user->hasRole('admin');
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('section_commission_information'))
                    ->schema([
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
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $seller = \AngelitoSystems\FilamentTenancy\Models\Seller::find($state);
                                if ($seller) {
                                    $set('commission_rate', $seller->commission_rate);
                                }
                            }),

                        Select::make('subscription_id')
                            ->label(static::__('subscription'))
                            ->relationship('subscription', 'id')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $state) {
                                $subscription = \AngelitoSystems\FilamentTenancy\Models\Subscription::find($state);
                                if ($subscription) {
                                    $set('subscription_amount', $subscription->price);
                                }
                            }),

                        TextInput::make('subscription_amount')
                            ->label(static::__('subscription_amount'))
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $amount = $get('subscription_amount');
                                $rate = $get('commission_rate');
                                if ($amount && $rate) {
                                    $set('amount', ($amount * $rate) / 100);
                                }
                            }),

                        TextInput::make('commission_rate')
                            ->label(static::__('commission_rate'))
                            ->numeric()
                            ->default(0)
                            ->minValue(0)
                            ->maxValue(100)
                            ->step(0.01)
                            ->suffix('%')
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $get, callable $set) {
                                $amount = $get('subscription_amount');
                                $rate = $get('commission_rate');
                                if ($amount && $rate) {
                                    $set('amount', ($amount * $rate) / 100);
                                }
                            }),

                        TextInput::make('amount')
                            ->label(static::__('amount'))
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required()
                            ->disabled(),

                        Select::make('status')
                            ->label(static::__('status'))
                            ->options([
                                'pending' => static::__('status_pending'),
                                'paid' => static::__('status_paid'),
                                'cancelled' => static::__('status_cancelled'),
                            ])
                            ->required()
                            ->default('pending'),

                        DatePicker::make('paid_at')
                            ->label(static::__('paid_at'))
                            ->visible(fn($get) => $get('status') === 'paid'),
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
                Tables\Columns\TextColumn::make('seller.user.name')
                    ->label(static::__('seller'))
                    ->searchable()
                    ->sortable()
                    ->getStateUsing(fn($record) => $record->seller?->user?->name ?? '-'),

                Tables\Columns\TextColumn::make('subscription.id')
                    ->label(static::__('subscription'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('subscription_amount')
                    ->label(static::__('subscription_amount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('commission_rate')
                    ->label(static::__('commission_rate'))
                    ->suffix('%')
                    ->sortable(),

                Tables\Columns\TextColumn::make('amount')
                    ->label(static::__('amount'))
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\TextColumn::make('status')
                    ->label(static::__('status'))
                    ->formatStateUsing(fn($state) => static::__("status_{$state}"))
                    ->colors([
                        'pending' => 'warning',
                        'paid' => 'success',
                        'cancelled' => 'danger',
                    ])
                    ->badge(),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(static::__('paid_at'))
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(static::__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('seller')
                    ->label(static::__('seller'))
                    ->relationship('seller', 'code', fn($query) => $query->where('is_active', true))
                    ->searchable()
                    ->preload()
                    ->getOptionLabelFromRecordUsing(fn($record) => $record->user->name ?? $record->code),

                Tables\Filters\SelectFilter::make('status')
                    ->label(static::__('status'))
                    ->options([
                        'pending' => static::__('status_pending'),
                        'paid' => static::__('status_paid'),
                        'cancelled' => static::__('status_cancelled'),
                    ]),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_as_paid')
                    ->label(static::__('mark_as_paid'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn($record) => $record->status === 'pending')
                    ->requiresConfirmation()
                    ->action(function ($record) {
                        $record->markAsPaid();
                        \Filament\Notifications\Notification::make()
                            ->title(static::__('commission_marked_as_paid'))
                            ->success()
                            ->send();
                    }),
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
            'index' => \AngelitoSystems\FilamentTenancy\Resources\CommissionResource\Pages\ListCommissions::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\CommissionResource\Pages\CreateCommission::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\CommissionResource\Pages\ViewCommission::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\CommissionResource\Pages\EditCommission::route('/{record}/edit'),
        ];
    }
}

