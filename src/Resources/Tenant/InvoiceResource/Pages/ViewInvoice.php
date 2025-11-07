<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource\Pages;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Models\Plan;
use AngelitoSystems\FilamentTenancy\Models\Subscription;
use AngelitoSystems\FilamentTenancy\Resources\Tenant\InvoiceResource;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Infolists\Components\ViewEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\Action::make('download_pdf')
                ->label(InvoiceResource::__('download_pdf'))
                ->icon('heroicon-o-arrow-down-tray')
                ->color('gray')
                ->url(fn() => route('tenant.invoices.pdf.download', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('view_pdf')
                ->label(InvoiceResource::__('view_pdf'))
                ->icon('heroicon-o-document-text')
                ->color('info')
                ->url(fn() => route('tenant.invoices.pdf.view', $this->record))
                ->openUrlInNewTab(),
            Actions\Action::make('pay')
                ->label(InvoiceResource::__('pay'))
                ->icon('heroicon-o-credit-card')
                ->color('success')
                ->visible(fn() => $this->record->isPending() && !empty($this->record->payment_link))
                ->url(fn() => $this->record->payment_link)
                ->openUrlInNewTab(),
            Actions\Action::make('cancel_subscription')
                ->label(InvoiceResource::__('cancel_subscription'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => $this->record->subscription && $this->record->subscription->isActive())
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancel_reason')
                        ->label(InvoiceResource::__('cancel_reason'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    if ($this->record->subscription) {
                        $this->record->subscription->update([
                            'status' => Subscription::STATUS_CANCELED,
                            'canceled_at' => now(),
                            'canceled_reason' => $data['cancel_reason'],
                        ]);
                        \Filament\Notifications\Notification::make()
                            ->title(InvoiceResource::__('subscription_canceled'))
                            ->success()
                            ->send();
                        $this->record->refresh();
                    }
                }),
            Actions\Action::make('migrate_plan')
                ->label(InvoiceResource::__('migrate_plan'))
                ->icon('heroicon-o-arrow-right-circle')
                ->color('info')
                ->visible(fn() => $this->record->subscription && $this->record->subscription->isActive())
                ->form([
                    \Filament\Forms\Components\Select::make('new_plan_id')
                        ->label(InvoiceResource::__('new_plan'))
                        ->options(function () {
                            return Plan::where('is_active', true)
                                ->orderBy('sort_order')
                                ->pluck('name', 'id')
                                ->toArray();
                        })
                        ->searchable()
                        ->required(),
                ])
                ->action(function (array $data) {
                    if ($this->record->subscription) {
                        $newPlan = Plan::find($data['new_plan_id']);
                        if ($newPlan) {
                            $oldPlan = $this->record->subscription->plan;
                            $this->record->subscription->update([
                                'plan_id' => $newPlan->id,
                                'price' => $newPlan->price,
                                'billing_cycle' => $newPlan->billing_cycle,
                                'metadata' => array_merge($this->record->subscription->metadata ?? [], [
                                    'plan_migrated_at' => now()->toIso8601String(),
                                    'old_plan_id' => $oldPlan->id,
                                    'old_plan_name' => $oldPlan->name,
                                ]),
                            ]);
                            \Filament\Notifications\Notification::make()
                                ->title(InvoiceResource::__('plan_migrated'))
                                ->body(str_replace(':plan', $newPlan->name, InvoiceResource::__('plan_migrated_message')))
                                ->success()
                                ->send();
                            $this->record->refresh();
                        }
                    }
                }),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->components([
                Section::make(InvoiceResource::__('section_invoice_information'))
                    ->schema([
                        TextEntry::make('invoice_number')
                            ->label(InvoiceResource::__('invoice_number'))
                            ->badge()
                            ->color('info')
                            ->copyable(),
                        TextEntry::make('plan.name')
                            ->label(InvoiceResource::__('plan'))
                            ->badge(),
                        TextEntry::make('subscription.id')
                            ->label(InvoiceResource::__('subscription')),
                        TextEntry::make('status')
                            ->label(InvoiceResource::__('status'))
                            ->badge()
                            ->color(fn($state) => match ($state) {
                                'pending' => 'warning',
                                'paid' => 'success',
                                'canceled' => 'danger',
                                'refunded' => 'gray',
                                default => 'gray',
                            }),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(InvoiceResource::__('section_billing_details'))
                    ->schema([
                        TextEntry::make('subtotal')
                            ->label(InvoiceResource::__('subtotal'))
                            ->money(fn($record) => $record->currency ?? 'USD'),
                        TextEntry::make('tax')
                            ->label(InvoiceResource::__('tax'))
                            ->money(fn($record) => $record->currency ?? 'USD'),
                        TextEntry::make('discount')
                            ->label(InvoiceResource::__('discount'))
                            ->money(fn($record) => $record->currency ?? 'USD'),
                        TextEntry::make('total')
                            ->label(InvoiceResource::__('total'))
                            ->money(fn($record) => $record->currency ?? 'USD')
                            ->weight('bold')
                            ->size('lg'),
                        TextEntry::make('currency')
                            ->label(InvoiceResource::__('currency')),
                        TextEntry::make('issued_at')
                            ->label(InvoiceResource::__('issued_at'))
                            ->dateTime(),
                        TextEntry::make('due_date')
                            ->label(InvoiceResource::__('due_date'))
                            ->dateTime()
                            ->color(fn($record) => $record->isOverdue() ? 'danger' : 'gray'),
                        TextEntry::make('paid_at')
                            ->label(InvoiceResource::__('paid_at'))
                            ->dateTime()
                            ->placeholder(InvoiceResource::__('not_paid')),
                        TextEntry::make('payment_method')
                            ->label(InvoiceResource::__('payment_method'))
                            ->placeholder(InvoiceResource::__('not_specified')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(InvoiceResource::__('section_additional_info'))
                    ->schema([
                        TextEntry::make('notes')
                            ->label(InvoiceResource::__('notes'))
                            ->placeholder(InvoiceResource::__('no_notes'))
                            ->columnSpanFull(),
                        KeyValueEntry::make('metadata')
                            ->columnSpanFull(),
                    ])
                    ->columnSpanFull(),

                Section::make(InvoiceResource::__('section_timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->dateTime(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                // PDF Viewer Section
                Section::make(InvoiceResource::__('view_pdf'))
                    ->schema([
                        ViewEntry::make('pdf_viewer')
                            ->view('filament-tenancy::invoices.pdf-viewer', [
                                'invoice' => $this->record,
                            ])
                            ->columnSpanFull(),
                    ])
                    ->collapsible()
                    ->collapsed(false)
                    ->columnSpanFull()
                    ->extraAttributes([
                        'class' => 'overflow-hidden',
                    ]),
            ]);
    }
}
