<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PermissionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PermissionResource;
use Filament\Actions;
use Filament\Resources\Pages\ViewRecord;

class ViewPermission extends ViewRecord
{
    protected static string $resource = PermissionResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            \Filament\Infolists\Components\Section::make('Permission Information')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('name'),
                    \Filament\Infolists\Components\TextEntry::make('slug'),
                    \Filament\Infolists\Components\TextEntry::make('description'),
                    \Filament\Infolists\Components\BadgeEntry::make('group')
                        ->colors([
                            'users' => 'blue',
                            'roles' => 'green',
                            'tenants' => 'purple',
                            'subscriptions' => 'yellow',
                            'billing' => 'orange',
                            'reports' => 'pink',
                            'settings' => 'gray',
                            'system' => 'red',
                        ]),
                    \Filament\Infolists\Components\ColorEntry::make('color'),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Statistics')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('roles_count')
                        ->label('Roles')
                        ->getStateUsing(fn ($record) => $record->roles()->count()),
                    \Filament\Infolists\Components\TextEntry::make('users_count')
                        ->label('Users')
                        ->getStateUsing(fn ($record) => $record->users()->count()),
                    \Filament\Infolists\Components\IconEntry::make('is_system')
                        ->label('System')
                        ->boolean(),
                    \Filament\Infolists\Components\IconEntry::make('is_active')
                        ->label('Active')
                        ->boolean(),
                ])
                ->columns(2),

            \Filament\Infolists\Components\Section::make('Roles with this Permission')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('roles.name')
                        ->label('Roles')
                        ->badge()
                        ->getStateUsing(fn ($record) => $record->roles->pluck('name'))
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record->roles()->count() > 0),

            \Filament\Infolists\Components\Section::make('Users with this Permission')
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('users.name')
                        ->label('Users')
                        ->badge()
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
