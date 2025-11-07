<?php

namespace AngelitoSystems\FilamentTenancy\Resources\Tenant;

use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Traits\HasResourceAuthorization;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use AngelitoSystems\FilamentTenancy\Traits\ChecksSubscriptionRestrictions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\ColorPicker;
use Filament\Forms\Components\Textarea;
use Filament\Forms\Components\TextInput;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Tables;
use Filament\Tables\Table;
use Filament\Schemas\Schema;

class RoleResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;
    use ChecksSubscriptionRestrictions;

    protected static ?string $model = Role::class;

    protected static ?string $slug = 'roles';

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-shield-check';

    protected static ?int $navigationSort = 3;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Override translation keys
     */
    public static function getNavigationKey(): string
    {
        return 'roles';
    }

    public static function getModelKey(): string
    {
        return 'role';
    }

    public static function getPluralModelKey(): string
    {
        return 'roles';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'roles';
    }

    /**
     * Override translation prefix to use 'roles' namespace
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'roles';
    }

    /**
     * Define el grupo de navegación para agrupar con usuarios
     * Por defecto busca la traducción en: tenancy.navigation_groups.administration
     */
    public static function getNavigationGroupKey(): ?string
    {
        return 'administration';
    }

    protected static function getNavigationGroupLabel(): ?string
    {
        return 'administration';
    }


    /**
     * Define permisos y roles para autorización
     */
    protected static function getAccessPermissions(): array
    {
        return ['manage roles', 'view roles'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create roles', 'manage roles'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit roles', 'manage roles'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete roles', 'manage roles'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(static::__('role_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(static::__('name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, Set $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label(static::__('slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readonly(),

                        Textarea::make('description')
                            ->label(static::__('description'))
                            ->rows(3)
                            ->placeholder(static::__('describe_role')),

                        ColorPicker::make('color')
                            ->label(static::__('color'))
                            ->helperText(static::__('color_helper')),
                    ])
                    ->columns(2),

                Section::make(static::__('permissions'))
                    ->schema([
                        CheckboxList::make('permissions')
                            ->label(static::__('permissions'))
                            ->relationship('permissions', 'name')
                            ->searchable()
                            ->bulkToggleable()
                            ->columns(3)
                            ->helperText(static::__('select_permissions')),
                    ]),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label(static::__('permissions'))
                    ->counts('permissions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label(static::__('users'))
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label(static::__('color')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\Filter::make('has_users')
                    ->query(fn($query) => $query->has('users'))
                    ->label(static::__('has_users')),

                Tables\Filters\Filter::make('no_users')
                    ->query(fn($query) => $query->doesntHave('users'))
                    ->label(static::__('no_users')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Role $record) {
                        if ($record->users()->count() > 0) {
                            throw new \Exception(static::__('cannot_delete_with_users'));
                        }
                    }),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation()
                        ->before(function ($records) {
                            foreach ($records as $record) {
                                if ($record->users()->count() > 0) {
                                    throw new \Exception(str_replace(':role', $record->name, static::__('cannot_delete_with_users_specific')));
                                }
                            }
                        }),
                ]),
            ])
            ->defaultSort('name', 'asc');
    }

    public static function getRelations(): array
    {
        return [
            //
        ];
    }

    public static function getPages(): array
    {
        return [
            'index' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages\ListRoles::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages\CreateRole::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages\ViewRole::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\Tenant\RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
