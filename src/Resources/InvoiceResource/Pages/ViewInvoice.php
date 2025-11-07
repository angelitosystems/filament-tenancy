<?php

namespace AngelitoSystems\FilamentTenancy\Resources\InvoiceResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\InvoiceResource;
use Filament\Actions;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewInvoice extends ViewRecord
{
    protected static string $resource = InvoiceResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('mark_as_paid')
                ->label(InvoiceResource::__('mark_as_paid'))
                ->icon('heroicon-o-check-circle')
                ->color('success')
                ->visible(fn() => $this->record->isPending())
                ->requiresConfirmation()
                ->action(function () {
                    $this->record->markAsPaid();
                    \Filament\Notifications\Notification::make()
                        ->title(InvoiceResource::__('invoice_marked_as_paid'))
                        ->success()
                        ->send();
                    $this->record->refresh();
                }),
            Actions\Action::make('cancel')
                ->label(InvoiceResource::__('cancel'))
                ->icon('heroicon-o-x-circle')
                ->color('danger')
                ->visible(fn() => !$this->record->isCanceled() && !$this->record->isPaid())
                ->requiresConfirmation()
                ->form([
                    \Filament\Forms\Components\Textarea::make('cancel_reason')
                        ->label(InvoiceResource::__('cancel_reason'))
                        ->required(),
                ])
                ->action(function (array $data) {
                    $this->record->cancel($data['cancel_reason']);
                    \Filament\Notifications\Notification::make()
                        ->title(InvoiceResource::__('invoice_canceled'))
                        ->success()
                        ->send();
                    $this->record->refresh();
                }),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make(InvoiceResource::__('section_invoice_information'))
                ->schema([
                    TextEntry::make('invoice_number')
                        ->label(InvoiceResource::__('invoice_number'))
                        ->badge()
                        ->color('info')
                        ->copyable(),
                    TextEntry::make('tenant.name')
                        ->label(InvoiceResource::__('tenant')),
                    TextEntry::make('plan.name')
                        ->label(InvoiceResource::__('plan'))
                        ->badge(),
                    TextEntry::make('subscription.id')
                        ->label(InvoiceResource::__('subscription')),
                    TextEntry::make('status')
                        ->label(InvoiceResource::__('status'))
                        ->badge()
                        ->color(fn($state) => match($state) {
                            'pending' => 'warning',
                            'paid' => 'success',
                            'canceled' => 'danger',
                            'refunded' => 'gray',
                            default => 'gray',
                        }),
                ])
                ->columns(2),

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
                        ->color(fn($record) => $record->isOverdue() ? 'danger' : null),
                    TextEntry::make('paid_at')
                        ->label(InvoiceResource::__('paid_at'))
                        ->dateTime()
                        ->placeholder(InvoiceResource::__('not_paid')),
                    TextEntry::make('payment_method')
                        ->label(InvoiceResource::__('payment_method'))
                        ->placeholder(InvoiceResource::__('not_specified')),
                    TextEntry::make('payment_reference')
                        ->label(InvoiceResource::__('payment_reference'))
                        ->placeholder(InvoiceResource::__('not_specified')),
                ])
                ->columns(3),

            Section::make(InvoiceResource::__('section_additional_info'))
                ->schema([
                    TextEntry::make('notes')
                        ->label(InvoiceResource::__('notes'))
                        ->placeholder(InvoiceResource::__('no_notes')),
                    TextEntry::make('cancel_reason')
                        ->label(InvoiceResource::__('cancel_reason'))
                        ->visible(fn($record) => $record->isCanceled())
                        ->placeholder(InvoiceResource::__('not_specified')),
                    KeyValueEntry::make('metadata')
                        ->columnSpanFull(),
                ]),

            Section::make(InvoiceResource::__('section_timestamps'))
                ->schema([
                    TextEntry::make('created_at')
                        ->dateTime(),
                    TextEntry::make('updated_at')
                        ->dateTime(),
                ])
                ->columns(2),
        ];
    }
}




