<?php

namespace AngelitoSystems\FilamentTenancy\Resources\TenantResource\Pages;

use AngelitoSystems\FilamentTenancy\Facades\Tenancy;
use AngelitoSystems\FilamentTenancy\Resources\TenantResource;
use Filament\Actions;
use Filament\Infolists;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\KeyValueEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Schema;

class ViewTenant extends ViewRecord
{
    protected static string $resource = TenantResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
            Actions\Action::make('visit_tenant')
                ->label(__('tenant.visit_tenant'))
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn() => $this->record->getUrl())
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->isActive()),
            Actions\Action::make('run_migrations')
                ->label(__('tenant.run_migrations'))
                ->icon('heroicon-o-cog-6-tooth')
                ->action(function () {
                    try {
                        Tenancy::database()->runTenantMigrations($this->record);

                        \Filament\Notifications\Notification::make()
                            ->title(__('tenant.migrations_completed'))
                            ->body(__('tenant.migrations_completed_message'))
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title(__('tenant.migration_failed'))
                            ->body(str_replace(':message', $e->getMessage(), __('tenant.error')))
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalDescription(__('tenant.run_migrations_description')),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(__('tenant.basic_information'))
                    ->schema([
                        TextEntry::make('id')
                            ->label(__('tenant.id')),
                        TextEntry::make('name')
                            ->label(__('tenancy.name')),
                        TextEntry::make('slug')
                            ->label(__('tenancy.slug')),
                        IconEntry::make('is_active')
                            ->label(__('tenant.active'))
                            ->boolean(),
                        TextEntry::make('plan')
                            ->label(__('tenancy.plan'))
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'basic' => 'gray',
                                'premium' => 'warning',
                                'enterprise' => 'success',
                                default => 'gray',
                            })
                            ->placeholder(__('tenancy.no_plan')),
                        TextEntry::make('expires_at')
                            ->label(__('tenant.expires'))
                            ->date()
                            ->placeholder(__('tenancy.never')),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('tenant.domain_configuration'))
                    ->schema([
                        TextEntry::make('domain')
                            ->label(__('tenant.custom_domain'))
                            ->placeholder(__('tenant.not_set')),
                        TextEntry::make('subdomain')
                            ->label(__('tenancy.subdomain'))
                            ->placeholder(__('tenant.not_set')),
                        TextEntry::make('full_domain')
                            ->label(__('tenant.full_url'))
                            ->state(fn() => $this->record->getUrl())
                            ->url(fn() => $this->record->getUrl())
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(__('tenant.timestamps'))
                    ->schema([
                        TextEntry::make('created_at')
                            ->label(__('tenant.created'))
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label(__('tenant.updated'))
                            ->dateTime(),
                        TextEntry::make('deleted_at')
                            ->label(__('tenant.deleted'))
                            ->dateTime()
                            ->placeholder(__('tenant.not_deleted')),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make(__('tenant.additional_data'))
                    ->schema([
                        KeyValueEntry::make('data')
                            ->label(__('tenant.custom_data'))
                            ->placeholder(__('tenant.no_additional_data')),
                    ])
                    ->visible(fn() => !empty($this->record->data))
                    ->columnSpanFull(),
            ]);
    }
}
