<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Invoice;
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

class InvoiceResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;

    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 5;

    protected static ?string $recordTitleAttribute = 'invoice_number';

    protected static function getTranslationPrefix(): ?string
    {
        return 'invoices';
    }

    public static function getModelKey(): string
    {
        return 'invoice';
    }

    public static function getPluralModelKey(): string
    {
        return 'invoices';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'invoices';
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
        return ['manage invoices', 'view invoices'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create invoices', 'manage invoices'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit invoices', 'manage invoices'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete invoices', 'manage invoices'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('section_invoice_information'))
                    ->schema([
                        TextInput::make('invoice_number')
                            ->label(static::__('invoice_number'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->disabled(fn($record) => $record !== null),

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
                                    $set('tenant_id', $subscription->tenant_id);
                                    $set('plan_id', $subscription->plan_id);
                                    $set('subtotal', $subscription->price ?? $subscription->plan->price);
                                    $set('currency', $subscription->plan->currency ?? 'USD');
                                    $set('total', $subscription->price ?? $subscription->plan->price);
                                }
                            }),

                        Select::make('tenant_id')
                            ->label(static::__('tenant'))
                            ->relationship('tenant', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),

                        Select::make('plan_id')
                            ->label(static::__('plan'))
                            ->relationship('plan', 'name')
                            ->searchable()
                            ->preload()
                            ->required()
                            ->disabled(),

                        Select::make('status')
                            ->label(static::__('status'))
                            ->options([
                                Invoice::STATUS_PENDING => static::__('status_pending'),
                                Invoice::STATUS_PAID => static::__('status_paid'),
                                Invoice::STATUS_CANCELED => static::__('status_canceled'),
                                Invoice::STATUS_REFUNDED => static::__('status_refunded'),
                            ])
                            ->default(Invoice::STATUS_PENDING)
                            ->required(),
                    ])
                    ->columns(2),

                Section::make(static::__('section_billing_details'))
                    ->schema([
                        TextInput::make('subtotal')
                            ->label(static::__('subtotal'))
                            ->numeric()
                            ->prefix(fn($get) => $get('currency') ?? 'USD')
                            ->step(0.01)
                            ->required()
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $get) {
                                $subtotal = $get('subtotal') ?? 0;
                                $tax = $get('tax') ?? 0;
                                $discount = $get('discount') ?? 0;
                                $set('total', max(0, $subtotal + $tax - $discount));
                            }),

                        TextInput::make('tax')
                            ->label(static::__('tax'))
                            ->numeric()
                            ->prefix(fn($get) => $get('currency') ?? 'USD')
                            ->step(0.01)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $get) {
                                $subtotal = $get('subtotal') ?? 0;
                                $tax = $get('tax') ?? 0;
                                $discount = $get('discount') ?? 0;
                                $set('total', max(0, $subtotal + $tax - $discount));
                            }),

                        TextInput::make('discount')
                            ->label(static::__('discount'))
                            ->numeric()
                            ->prefix(fn($get) => $get('currency') ?? 'USD')
                            ->step(0.01)
                            ->default(0)
                            ->reactive()
                            ->afterStateUpdated(function (callable $set, $get) {
                                $subtotal = $get('subtotal') ?? 0;
                                $tax = $get('tax') ?? 0;
                                $discount = $get('discount') ?? 0;
                                $set('total', max(0, $subtotal + $tax - $discount));
                            }),

                        TextInput::make('total')
                            ->label(static::__('total'))
                            ->numeric()
                            ->prefix(fn($get) => $get('currency') ?? 'USD')
                            ->step(0.01)
                            ->required()
                            ->disabled(),

                        TextInput::make('currency')
                            ->label(static::__('currency'))
                            ->required()
                            ->maxLength(3)
                            ->default('USD')
                            ->live(onBlur: true) // update when input loses focus
                            ->afterStateUpdated(function ($state, callable $set) {
                                $set('currency', strtoupper($state));
                            }),

                        DatePicker::make('issued_at')
                            ->label(static::__('issued_at'))
                            ->default(now())
                            ->required(),

                        DatePicker::make('due_date')
                            ->label(static::__('due_date'))
                            ->required(),

                        Select::make('payment_method')
                            ->label(static::__('payment_method'))
                            ->options([
                                'paypal' => 'PayPal',
                                'stripe' => 'Stripe',
                                'bank_transfer' => static::__('bank_transfer'),
                                'cash' => static::__('cash'),
                                'other' => static::__('other'),
                            ])
                            ->searchable(),

                        TextInput::make('payment_reference')
                            ->label(static::__('payment_reference'))
                            ->maxLength(255),

                        TextInput::make('payment_link')
                            ->label(static::__('payment_link'))
                            ->url()
                            ->maxLength(500),
                    ])
                    ->columns(3),

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
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label(static::__('invoice_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

                Tables\Columns\TextColumn::make('tenant.name')
                    ->label(static::__('tenant'))
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('plan.name')
                    ->label(static::__('plan'))
                    ->searchable()
                    ->sortable()
                    ->badge(),

                Tables\Columns\TextColumn::make('total')
                    ->label(static::__('total'))
                    ->money(fn($record) => $record->currency ?? 'USD')
                    ->sortable()
                    ->weight('bold'),

                Tables\Columns\TextColumn::make('status')
                    ->label(static::__('status'))
                    ->badge()
                    ->formatStateUsing(fn($state) => static::__("status_{$state}"))
                    ->color(fn($state) => match ($state) {
                        'pending' => 'warning',
                        'paid' => 'success',
                        'canceled' => 'danger',
                        'refunded' => 'gray',
                        default => 'gray',
                    }),

                Tables\Columns\TextColumn::make('issued_at')
                    ->label(static::__('issued_at'))
                    ->date()
                    ->sortable(),

                Tables\Columns\TextColumn::make('due_date')
                    ->label(static::__('due_date'))
                    ->date()
                    ->sortable()
                    ->color(fn($record) => $record->isOverdue() ? 'danger' : null),

                Tables\Columns\TextColumn::make('paid_at')
                    ->label(static::__('paid_at'))
                    ->date()
                    ->sortable()
                    ->placeholder(static::__('not_paid')),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('status')
                    ->label(static::__('status'))
                    ->options([
                        Invoice::STATUS_PENDING => static::__('status_pending'),
                        Invoice::STATUS_PAID => static::__('status_paid'),
                        Invoice::STATUS_CANCELED => static::__('status_canceled'),
                        Invoice::STATUS_REFUNDED => static::__('status_refunded'),
                    ]),

                Tables\Filters\SelectFilter::make('tenant')
                    ->label(static::__('tenant'))
                    ->relationship('tenant', 'name')
                    ->searchable()
                    ->preload(),

                Tables\Filters\Filter::make('overdue')
                    ->label(static::__('overdue'))
                    ->query(fn($query) => $query->where('status', Invoice::STATUS_PENDING)
                        ->where('due_date', '<', now())),
            ])
            ->recordActions([
                ViewAction::make(),
                EditAction::make(),
                Action::make('mark_as_paid')
                    ->label(static::__('mark_as_paid'))
                    ->icon('heroicon-o-check-circle')
                    ->color('success')
                    ->visible(fn(Invoice $record) => $record->isPending())
                    ->requiresConfirmation()
                    ->action(function (Invoice $record) {
                        $record->markAsPaid();
                        \Filament\Notifications\Notification::make()
                            ->title(static::__('invoice_marked_as_paid'))
                            ->success()
                            ->send();
                    }),
                Action::make('cancel')
                    ->label(static::__('cancel'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Invoice $record) => !$record->isCanceled() && !$record->isPaid())
                    ->requiresConfirmation()
                    ->schema([
                        Textarea::make('cancel_reason')
                            ->label(static::__('cancel_reason'))
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        $record->cancel($data['cancel_reason']);
                        \Filament\Notifications\Notification::make()
                            ->title(static::__('invoice_canceled'))
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

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages\ListInvoices::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages\CreateInvoice::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages\ViewInvoice::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages\EditInvoice::route('/{record}/edit'),
        ];
    }
}
