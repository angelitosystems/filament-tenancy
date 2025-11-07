<?php

namespace AngelitoSystems\FilamentTenancy\Resources\PermissionResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\PermissionResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;
use Filament\Tables\Columns\BadgeColumn;
use Filament\Tables\Columns\TextColumn;

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
            Section::make('Permission Information')
                ->schema([
                    TextEntry::make('name'),
                    TextEntry::make('slug'),
                    TextEntry::make('description'),
                    TextColumn::make('group')
                        ->colors([
                            'users' => 'blue',
                            'roles' => 'green',
                            'tenants' => 'purple',
                            'subscriptions' => 'yellow',
                            'billing' => 'orange',
                            'reports' => 'pink',
                            'settings' => 'gray',
                            'system' => 'red',
                        ])
                        ->badge(),
                    \Filament\Infolists\Components\ColorEntry::make('color'),
                ])
                ->columns(2),

            Section::make('Statistics')
                ->schema([
                    TextEntry::make('roles_count')
                        ->label('Roles')
                        ->getStateUsing(fn($record) => $record->roles()->count()),
                    TextEntry::make('users_count')
                        ->label('Users')
                        ->getStateUsing(fn($record) => $record->users()->count()),
                    IconEntry::make('is_system')
                        ->label('System')
                        ->boolean(),
                    IconEntry::make('is_active')
                        ->label('Active')
                        ->boolean(),
                ])
                ->columns(2),

            Section::make('Roles with this Permission')
                ->schema([
                    TextEntry::make('roles.name')
                        ->label('Roles')
                        ->badge()
                        ->getStateUsing(fn($record) => $record->roles->pluck('name'))
                        ->columnSpanFull(),
                ])
                ->visible(fn($record) => $record->roles()->count() > 0),

            Section::make('Users with this Permission')
                ->schema([
                    TextEntry::make('users.name')
                        ->label('Users')
                        ->badge()
                        ->getStateUsing(fn($record) => $record->users->pluck('name'))
                        ->columnSpanFull(),
                ])
                ->visible(fn($record) => $record->users()->count() > 0),

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
