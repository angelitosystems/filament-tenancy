<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Subscription;
use BackedEnum;
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
    protected static ?string $model = Subscription::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-banknotes';

    protected static ?string $navigationLabel = 'Suscripciones';

    protected static string|\UnitEnum|null $navigationGroup = 'Gestión de Facturación';

    protected static ?int $navigationSort = 2;

    protected static ?string $modelLabel = 'Suscripción';

    protected static ?string $pluralModelLabel = 'Suscripciones';

    protected static ?string $breadcrumb = 'Suscripciones';

    protected static ?string $recordTitleAttribute = 'id';

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make('Subscription Information')
                    ->schema([
                        Select::make('tenant_id')
                            ->label('Tenant')
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
                            ->label('Plan')
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

                        TextInput::make('price')
                            ->label('Price')
                            ->numeric()
                            ->prefix('$')
                            ->step(0.01)
                            ->required(),

                        Select::make('billing_cycle')
                            ->label('Billing Cycle')
                            ->options([
                                'monthly' => 'Monthly',
                                'yearly' => 'Yearly',
                                'quarterly' => 'Quarterly',
                                'lifetime' => 'Lifetime',
                            ])
                            ->required(),
                    ])
                    ->columns(2),

                Section::make('Subscription Period')
                    ->schema([
                        DatePicker::make('starts_at')
                            ->label('Start Date')
                            ->required()
                            ->default(now()),

                        DatePicker::make('ends_at')
                            ->label('End Date')
                            ->helperText('Leave empty for lifetime subscriptions'),

                        DatePicker::make('trial_ends_at')
                            ->label('Trial Ends At')
                            ->helperText('Leave empty for no trial'),

                        DatePicker::make('next_billing_at')
                            ->label('Next Billing Date')
                            ->helperText('Calculated automatically based on billing cycle'),
                    ])
                    ->columns(2),

                Section::make('Status & Settings')
                    ->schema([
                        Select::make('status')
                            ->label('Status')
                            ->options([
                                'active' => 'Active',
                                'inactive' => 'Inactive',
                                'cancelled' => 'Cancelled',
                                'expired' => 'Expired',
                                'suspended' => 'Suspended',
                                'pending' => 'Pending',
                            ])
                            ->required()
                            ->default('active'),

                        Toggle::make('auto_renew')
                            ->label('Auto Renew')
                            ->default(true)
                            ->helperText('Automatically renew subscription when it expires'),

                        TextInput::make('payment_method')
                            ->label('Payment Method')
                            ->placeholder('stripe, paypal, bank_transfer, etc.'),

                        TextInput::make('external_id')
                            ->label('External ID')
                            ->placeholder('ID from payment provider')
                            ->helperText('External subscription ID from payment gateway'),
                    ])
                    ->columns(2),

                Section::make('Additional Information')
                    ->schema([
                        Textarea::make('notes')
                            ->label('Notes')
                            ->rows(3)
                            ->placeholder('Internal notes about this subscription'),

                        KeyValue::make('metadata')
                            ->label('Metadata')
                            ->keyLabel('Key')
                            ->valueLabel('Value')
                            ->addActionLabel('Add Metadata')
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
                    ->label('Tenant')
                    ->searchable()
                    ->sortable()
                    ->description(fn($record): string => $record->tenant->slug ?? ''),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label('Plan')
                    ->searchable()
                    ->sortable()
                    ->badge()
                    ->color(fn($record) => $record->plan->color ?? 'gray'),

                Tables\Columns\TextColumn::make('price')
                    ->money('USD')
                    ->sortable(),

                Tables\Columns\BadgeColumn::make('billing_cycle')
                    ->colors([
                        'monthly' => 'blue',
                        'yearly' => 'green',
                        'quarterly' => 'yellow',
                        'lifetime' => 'purple',
                    ]),

                Tables\Columns\BadgeColumn::make('status')
                    ->colors([
                        'active' => 'success',
                        'inactive' => 'gray',
                        'cancelled' => 'danger',
                        'expired' => 'warning',
                        'suspended' => 'danger',
                        'pending' => 'info',
                    ]),

                Tables\Columns\IconColumn::make('auto_renew')
                    ->boolean()
                    ->label('Auto Renew'),

                Tables\Columns\TextColumn::make('starts_at')
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('ends_at')
                    ->date()
                    ->sortable()
                    ->placeholder('Lifetime'),

                Tables\Columns\TextColumn::make('trial_ends_at')
                    ->date()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('plan')
                    ->relationship('plan', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\SelectFilter::make('status')
                    ->options([
                        'active' => 'Active',
                        'inactive' => 'Inactive',
                        'cancelled' => 'Cancelled',
                        'expired' => 'Expired',
                        'suspended' => 'Suspended',
                        'pending' => 'Pending',
                    ]),

                Tables\Filters\SelectFilter::make('billing_cycle')
                    ->options([
                        'monthly' => 'Monthly',
                        'yearly' => 'Yearly',
                        'quarterly' => 'Quarterly',
                        'lifetime' => 'Lifetime',
                    ]),

                Tables\Filters\TernaryFilter::make('auto_renew')
                    ->label('Auto Renew')
                    ->placeholder('All subscriptions')
                    ->trueLabel('Auto renew enabled')
                    ->falseLabel('Auto renew disabled'),

                Tables\Filters\Filter::make('expiring_soon')
                    ->query(fn($query) => $query->where('ends_at', '<=', now()->addDays(30))->where('ends_at', '>', now()))
                    ->label('Expiring Soon (30 days)'),

                Tables\Filters\Filter::make('expired')
                    ->query(fn($query) => $query->where('ends_at', '<', now()))
                    ->label('Expired'),

                Tables\Filters\Filter::make('in_trial')
                    ->query(fn($query) => $query->where('trial_ends_at', '>', now()))
                    ->label('In Trial'),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
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
