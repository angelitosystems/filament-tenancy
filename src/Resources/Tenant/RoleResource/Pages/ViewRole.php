<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource;
use Filament\Actions;
use Filament\Infolists\Components\ColorEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewRole extends ViewRecord
{
    protected static string $resource = RoleResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make('Role Information')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('slug'),
                    TextEntry::make('description'),
                    ColorEntry::make('color'),
                ])
                ->columns(2),

            Section::make('Statistics')
                ->schema([
                    TextEntry::make('permissions_count')
                        ->label('Permissions')
                        ->getStateUsing(fn ($record) => $record->permissions()->count()),
                    TextEntry::make('users_count')
                        ->label('Users')
                        ->getStateUsing(fn ($record) => $record->users()->count()),
                ])
                ->columns(2),

            Section::make('Permissions')
                ->schema([
                    TextEntry::make('permissions.name')
                        ->label('Permissions')
                        ->badge()
                        ->getStateUsing(fn ($record) => $record->permissions->pluck('name'))
                        ->columnSpanFull(),
                ]),

            Section::make('Users with this Role')
                ->schema([
                    TextEntry::make('users.name')
                        ->label('Users')
                        ->getStateUsing(fn ($record) => $record->users->pluck('name'))
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record->users()->count() > 0),

            Section::make('Timestamps')
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
