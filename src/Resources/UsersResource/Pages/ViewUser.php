<?php

namespace AngelitoSystems\FilamentTenancy\Resources\UsersResource\Pages;

use AngelitoSystems\FilamentTenancy\Resources\UsersResource;
use Filament\Actions;
use Filament\Infolists\Components\IconEntry;
use Filament\Infolists\Components\TextEntry;
use Filament\Resources\Pages\ViewRecord;
use Filament\Schemas\Components\Section;

class ViewUser extends ViewRecord
{
    protected static string $resource = UsersResource::class;

    protected function getHeaderActions(): array
    {
        return [
            Actions\EditAction::make(),
        ];
    }

    protected function getInfolistSchema(): array
    {
        return [
            Section::make(__('tenancy.user_information'))
                ->schema([
                    TextEntry::make('name')
                        ->label(__('tenancy.name')),
                    TextEntry::make('email')
                        ->label(__('tenancy.email'))
                        ->copyable()
                        ->copyMessage(__('tenancy.email_copied'))
                        ->copyMessageDuration(1500),
                    IconEntry::make('email_verified_at')
                        ->label(__('tenancy.email_verified'))
                        ->boolean()
                        ->trueIcon('heroicon-o-check-circle')
                        ->falseIcon('heroicon-o-x-circle')
                        ->trueColor('success')
                        ->falseColor('danger'),
                ])
                ->columns(2),

            Section::make(__('tenancy.roles'))
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('roles.name')
                        ->label(__('tenancy.roles'))
                        ->badge()
                        ->color('primary')
                        ->getStateUsing(fn ($record) => $record->roles->pluck('name'))
                        ->placeholder(__('tenancy.no_roles_assigned'))
                        ->columnSpanFull(),
                ])
                ->visible(fn ($record) => $record->roles()->count() > 0),

            Section::make(__('tenancy.statistics'))
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('roles_count')
                        ->label(__('tenancy.roles_count'))
                        ->getStateUsing(fn ($record) => $record->roles()->count()),
                    \Filament\Infolists\Components\TextEntry::make('permissions_count')
                        ->label(__('tenancy.permissions_count'))
                        ->getStateUsing(function ($record) {
                            $permissions = collect();
                            foreach ($record->roles as $role) {
                                $permissions = $permissions->merge($role->permissions);
                            }
                            return $permissions->unique('id')->count();
                        }),
                ])
                ->columns(2),

            Section::make(__('tenancy.timestamps'))
                ->schema([
                    \Filament\Infolists\Components\TextEntry::make('created_at')
                        ->label(__('tenancy.created_at'))
                        ->dateTime(),
                    \Filament\Infolists\Components\TextEntry::make('updated_at')
                        ->label(__('tenancy.updated_at'))
                        ->dateTime(),
                ])
                ->columns(2),
        ];
    }
}

