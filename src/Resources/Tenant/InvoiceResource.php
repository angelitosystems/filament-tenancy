<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Invoice;
use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use BackedEnum;
use Filament\Actions\Action;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Tables;
use Filament\Tables\Table;

class InvoiceResource extends Resource
{
    use HasSimpleTranslations;

    protected static ?string $model = Invoice::class;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-document-text';

    protected static ?int $navigationSort = 2;

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
        return 'billing';
    }

    protected static function getNavigationGroupLabel(): ?string
    {
        return 'billing';
    }

    public static function canCreate(): bool
    {
        return false; // Tenants cannot create invoices, only view them
    }

    public static function table(Table $table): Table
    {
        return $table
            ->modifyQueryUsing(function ($query) {
                // Solo mostrar facturas del tenant actual
                $tenant = Tenancy::current();
                if ($tenant) {
                    $query->where('tenant_id', $tenant->id);
                } else {
                    $query->whereRaw('1 = 0'); // No mostrar nada si no hay tenant
                }
            })
            ->columns([
                Tables\Columns\TextColumn::make('invoice_number')
                    ->label(static::__('invoice_number'))
                    ->searchable()
                    ->sortable()
                    ->copyable(),

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
                    ->color(fn($state) => match($state) {
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

                Tables\Filters\Filter::make('overdue')
                    ->label(static::__('overdue'))
                    ->query(fn($query) => $query->where('status', Invoice::STATUS_PENDING)
                        ->where('due_date', '<', now())),
            ])
            ->actions([
                \Filament\Actions\ViewAction::make(),
                Action::make('pay')
                    ->label(static::__('pay'))
                    ->icon('heroicon-o-credit-card')
                    ->color('success')
                    ->visible(fn(Invoice $record) => $record->isPending() && !empty($record->payment_link))
                    ->url(fn(Invoice $record) => $record->payment_link)
                    ->openUrlInNewTab(),
                Action::make('cancel_subscription')
                    ->label(static::__('cancel_subscription'))
                    ->icon('heroicon-o-x-circle')
                    ->color('danger')
                    ->visible(fn(Invoice $record) => $record->subscription && $record->subscription->isActive())
                    ->requiresConfirmation()
                    ->form([
                        \Filament\Forms\Components\Textarea::make('cancel_reason')
                            ->label(static::__('cancel_reason'))
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        if ($record->subscription) {
                            $record->subscription->update([
                                'status' => Subscription::STATUS_CANCELED,
                                'canceled_at' => now(),
                                'canceled_reason' => $data['cancel_reason'],
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title(static::__('subscription_canceled'))
                                ->success()
                                ->send();
                        }
                    }),
                Action::make('migrate_plan')
                    ->label(static::__('migrate_plan'))
                    ->icon('heroicon-o-arrow-right-circle')
                    ->color('info')
                    ->visible(fn(Invoice $record) => $record->subscription && $record->subscription->isActive())
                    ->form([
                        \Filament\Forms\Components\Select::make('new_plan_id')
                            ->label(static::__('new_plan'))
                            ->options(function () {
                                return Plan::where('is_active', true)
                                    ->orderBy('sort_order')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->searchable()
                            ->required(),
                    ])
                    ->action(function (Invoice $record, array $data) {
                        if ($record->subscription) {
                            $newPlan = Plan::find($data['new_plan_id']);
                            if ($newPlan) {
                                $oldPlan = $record->subscription->plan;
                                $record->subscription->update([
                                    'plan_id' => $newPlan->id,
                                    'price' => $newPlan->price,
                                    'billing_cycle' => $newPlan->billing_cycle,
                                    'metadata' => array_merge($record->subscription->metadata ?? [], [
                                        'plan_migrated_at' => now()->toIso8601String(),
                                        'old_plan_id' => $oldPlan->id,
                                        'old_plan_name' => $oldPlan->name,
                                    ]),
                                ]);
                                \Filament\Notifications\Notification::make()
                                    ->title(static::__('plan_migrated'))
                                    ->body(str_replace(':plan', $newPlan->name, static::__('plan_migrated_message')))
                                    ->success()
                                    ->send();
                            }
                        }
                    }),
            ])
            ->defaultSort('created_at', 'desc');
    }

    public static function getPages(): array
    {
        return [
            'index' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource\Pages\ListInvoices::route('/'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource\Pages\ViewInvoice::route('/{record}'),
        ];
    }
}

