<?php

namespace AngelitoSystems\FilamentTenancy\Resources\RoleResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\RoleResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

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
            \Filament\Infolists\Components\Section::make('Role Information')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('name'),
                    \Filament\Infolists\Components\TextEntry::make('slug'),
                    \Filament\Infolists\Components\TextEntry::make('description'),
                    \Filament\Infolists\Components\ColorEntry::make('color'),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Statistics')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('permissions_count')
                        ->label('Permissions')
                        ->getStateUsing(fn ($record) => $record->permissions()->count()),
                    \Filament\Infolists\Components\TextEntry::make('users_count')
                        ->label('Users')
                        ->getStateUsing(fn ($record) => $record->users()->count()),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Permissions')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('permissions.name')
                        ->label('Permissions')
                        ->badge()
                        ->getStateUsing(fn ($record) => $record->permissions->pluck('name'))
                        ->columnSpanFull(),
                ]),

            \Filament\Infolists\Components\Section::make('Users with this Role')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('users.name')
                        ->label('Users')
                        ->getStateUsing(fn ($record) => $record->users->pluck('name'))
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record->users()->count() > 0),

            \Filament\Infolists\Components\Section::make('Timestamps')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('created_at')
                        ->dateTime(),
                    \Filament\Infolists\Components\TextEntry::make('updated_at')
                        ->dateTime(),
                ])
                ->columns(2),
        ];
    }
}
