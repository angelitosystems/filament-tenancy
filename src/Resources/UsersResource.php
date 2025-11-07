<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Resources\UsersResource\Pages;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use AngelitoSystems\FilamentTenancy\Traits\HasResourceAuthorization;
use AngelitoSystems\FilamentTenancy\Traits\ChecksSubscriptionRestrictions;
use BackedEnum;
use Filament\Actions\BulkActionGroup;
use Filament\Actions\DeleteAction;
use Filament\Actions\DeleteBulkAction;
use Filament\Actions\EditAction;
use Filament\Actions\ViewAction;
use Filament\Forms\Components\CheckboxList;
use Filament\Forms\Components\DatePicker;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Toggle;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;
use Illuminate\Support\Facades\Hash;

class UsersResource extends Resource
{
    use HasSimpleTranslations;
    use HasResourceAuthorization;
    use ChecksSubscriptionRestrictions;

    protected static ?string $model = null;

    protected static string|BackedEnum|null $navigationIcon = 'heroicon-o-users';

    protected static ?int $navigationSort = 2;

    protected static ?string $recordTitleAttribute = 'name';

    /**
     * Get the User model class from config
     */
    public static function getModel(): string
    {
        if (static::$model) {
            return static::$model;
        }

        $userModelClass = config('filament-tenancy.user_model', config('auth.providers.users.model', 'App\\Models\\User'));

        if (!class_exists($userModelClass)) {
            throw new \Exception("User model class '{$userModelClass}' not found. Please check your configuration.");
        }

        return $userModelClass;
    }

    /**
     * Override translation prefix to use 'users' namespace
     * Esto permite buscar primero en users.php antes que en tenancy.php
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'users';
    }

    /**
     * Define el key del navigation group
     * Por defecto busca la traducción en: tenancy.navigation_groups.user_management
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
        return ['manage users', 'view users'];
    }

    protected static function getAccessRoles(): array
    {
        return ['admin'];
    }

    protected static function getCreatePermissions(): array
    {
        return ['create users', 'manage users'];
    }

    protected static function getCreateRoles(): array
    {
        return ['admin'];
    }

    protected static function getEditPermissions(): array
    {
        return ['edit users', 'manage users'];
    }

    protected static function getEditRoles(): array
    {
        return ['admin'];
    }

    protected static function getDeletePermissions(): array
    {
        return ['delete users', 'manage users'];
    }

    protected static function getDeleteRoles(): array
    {
        return ['admin'];
    }

    public static function form(Schema $schema): Schema
    {
        return $schema
            ->columns(2)
            ->components([
                Section::make(static::__('user_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(static::__('name'))
                            ->required()
                            ->maxLength(255)
                            ->columnSpanFull(),

                        TextInput::make('email')
                            ->label(static::__('email'))
                            ->email()
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->columnSpanFull(),

                        TextInput::make('password')
                            ->label(static::__('password'))
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->dehydrated(fn($state) => filled($state))
                            ->dehydrateStateUsing(fn($state) => filled($state) ? Hash::make($state) : null)
                            ->minLength(8)
                            ->columnSpanFull(),

                        TextInput::make('password_confirmation')
                            ->label(static::__('password_confirmation'))
                            ->password()
                            ->required(fn(string $context): bool => $context === 'create')
                            ->same('password')
                            ->dehydrated(false)
                            ->columnSpanFull(),

                        DatePicker::make('email_verified_at')
                            ->label(static::__('email_verified'))
                            ->native(false)
                            ->displayFormat('Y-m-d H:i')
                            ->placeholder('Not verified')
                            ->suffixIcon('heroicon-o-check-circle')
                            ->closeOnDateSelection()
                            ->default(null)
                            ->columnSpanFull(),
                    ])
                    ->columns(2)
                    ->columnSpanFull(),

                Section::make(static::__('roles'))
                    ->schema([
                        CheckboxList::make('roles')
                            ->label(static::__('roles'))
                            ->options(function ($record) {
                                return Role::query()
                                    ->where('is_active', true)
                                    ->orderBy('name')
                                    ->pluck('name', 'id')
                                    ->toArray();
                            })
                            ->default(function ($get, $record) {
                                // Si hay un record (edición), cargar los roles
                                if ($record && method_exists($record, 'roles')) {
                                    $record->load('roles');
                                    return $record->roles->pluck('id')->toArray();
                                }
                                return [];
                            })
                            ->helperText(static::__('select_roles'))
                            ->columns(2)
                            ->searchable()
                            ->dehydrated(true), // Guardar en el array de datos para procesarlo en mutateFormDataBeforeSave
                    ])
                    ->columnSpanFull(),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('id')
                    ->label('ID')
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('name')
                    ->label(static::__('name'))
                    ->searchable()
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('email')
                    ->label(static::__('email'))
                    ->searchable()
                    ->sortable()
                    ->copyable()
                    ->copyMessage(static::__('email_copied'))
                    ->copyMessageDuration(1500)
                    ->alignment('center'),

                Tables\Columns\IconColumn::make('email_verified_at')
                    ->label(static::__('email_verified'))
                    ->boolean()
                    ->trueIcon('heroicon-o-check-circle')
                    ->falseIcon('heroicon-o-x-circle')
                    ->trueColor('success')
                    ->falseColor('danger')
                    ->sortable()
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('roles.name')
                    ->label(static::__('roles'))
                    ->badge()
                    ->color('primary')
                    ->separator(',')
                    ->limit(10)
                    ->tooltip(fn($record) => $record->roles->pluck('name')->join(', '))
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('roles_count')
                    ->label(static::__('roles_count'))
                    ->counts('roles')
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('created_at')
                    ->label(static::__('created_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignment('center'),

                Tables\Columns\TextColumn::make('updated_at')
                    ->label(static::__('updated_at'))
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true)
                    ->alignment('center'),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('roles')
                    ->label(static::__('roles'))
                    ->relationship('roles', 'name')
                    ->multiple()
                    ->preload(),

                Tables\Filters\Filter::make('email_verified')
                    ->label(static::__('email_verified'))
                    ->query(fn($query) => $query->whereNotNull('email_verified_at')),

                Tables\Filters\Filter::make('email_not_verified')
                    ->label(static::__('email_not_verified'))
                    ->query(fn($query) => $query->whereNull('email_verified_at')),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation(),
            ])
            ->bulkActions([
                BulkActionGroup::make([
                    DeleteBulkAction::make()
                        ->requiresConfirmation(),
                ]),
            ])
            ->defaultSort('created_at', 'desc');
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
            'index' => Pages\ListUsers::route('/'),
            'create' => Pages\CreateUser::route('/create'),
            'view' => Pages\ViewUser::route('/{record}'),
            'edit' => Pages\EditUser::route('/{record}/edit'),
        ];
    }

    public static function getEloquentQuery(): \Illuminate\Database\Eloquent\Builder
    {
        $modelClass = static::getModel();
        return $modelClass::query();
    }
}
