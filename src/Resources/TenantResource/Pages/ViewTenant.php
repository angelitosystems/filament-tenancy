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
                ->label('Visit Tenant')
                ->icon('heroicon-o-arrow-top-right-on-square')
                ->url(fn() => $this->record->getUrl())
                ->openUrlInNewTab()
                ->visible(fn() => $this->record->isActive()),
            Actions\Action::make('run_migrations')
                ->label('Run Migrations')
                ->icon('heroicon-o-cog-6-tooth')
                ->action(function () {
                    try {
                        Tenancy::database()->runTenantMigrations($this->record);

                        \Filament\Notifications\Notification::make()
                            ->title('Migrations completed')
                            ->body('Tenant migrations have been run successfully.')
                            ->success()
                            ->send();
                    } catch (\Exception $e) {
                        \Filament\Notifications\Notification::make()
                            ->title('Migration failed')
                            ->body($e->getMessage())
                            ->danger()
                            ->send();
                    }
                })
                ->requiresConfirmation()
                ->modalDescription('This will run all pending migrations for this tenant.'),
        ];
    }

    public function infolist(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make('Basic Information')
                    ->schema([
                        TextEntry::make('id')
                            ->label('ID'),
                        TextEntry::make('name'),
                        TextEntry::make('slug'),
                        IconEntry::make('is_active')
                            ->label('Active')
                            ->boolean(),
                        TextEntry::make('plan')
                            ->badge()
                            ->color(fn(string $state): string => match ($state) {
                                'basic' => 'gray',
                                'premium' => 'warning',
                                'enterprise' => 'success',
                                default => 'gray',
                            })
                            ->placeholder('No plan'),
                        TextEntry::make('expires_at')
                            ->label('Expires')
                            ->date()
                            ->placeholder('Never'),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Domain Configuration')
                    ->schema([
                        TextEntry::make('domain')
                            ->label('Custom Domain')
                            ->placeholder('Not set'),
                        TextEntry::make('subdomain')
                            ->label('Subdomain')
                            ->placeholder('Not set'),
                        TextEntry::make('full_domain')
                            ->label('Full URL')
                            ->state(fn() => $this->record->getUrl())
                            ->url(fn() => $this->record->getUrl())
                            ->openUrlInNewTab(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make('Timestamps')
                    ->schema([
                        TextEntry::make('created_at')
                            ->label('Created')
                            ->dateTime(),
                        TextEntry::make('updated_at')
                            ->label('Updated')
                            ->dateTime(),
                        TextEntry::make('deleted_at')
                            ->label('Deleted')
                            ->dateTime()
                            ->placeholder('Not deleted'),
                    ])
                    ->columns(3)
                    ->columnSpanFull(),

                Section::make('Additional Data')
                    ->schema([
                        KeyValueEntry::make('data')
                            ->label('Custom Data')
                            ->placeholder('No additional data'),
                    ])
                    ->visible(fn() => !empty($this->record->data))
                    ->columnSpanFull(),
            ]);
    }
}
