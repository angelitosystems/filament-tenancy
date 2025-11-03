<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Models\Role;
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
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
use Filament\Forms\Form;
use Filament\Resources\Resource;
use Filament\Schemas\Components\Section;
use Filament\Schemas\Components\Utilities\Set;
use Filament\Schemas\Schema;
use Filament\Tables;
use Filament\Tables\Table;

class RoleResource extends Resource
{
    use HasSimpleTranslations;

    protected static ?string $model = Role::class;

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

    public static function getNavigationGroupKey(): ?string
    {
        return 'user_management';
    }

    public static function form(Schema $form): Schema
    {
        return $form
            ->schema([
                Section::make(__('tenancy.role_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('tenancy.name'))
                            ->required()
                            ->maxLength(255)
                            ->live(onBlur: true)
                            ->afterStateUpdated(function (string $state, Set $set) {
                                $set('slug', \Illuminate\Support\Str::slug($state));
                            }),

                        TextInput::make('slug')
                            ->label(__('tenancy.slug'))
                            ->required()
                            ->maxLength(255)
                            ->unique(ignoreRecord: true)
                            ->readonly(),

                        Textarea::make('description')
                            ->label(__('tenancy.description'))
                            ->rows(3)
                            ->placeholder(__('tenancy.describe_role')),

                        ColorPicker::make('color')
                            ->label(__('tenancy.color')),

                        CheckboxList::make('permissions')
                            ->label(__('tenancy.permissions'))
                            ->relationship('permissions', 'name')
                            ->helperText(__('tenancy.permissions_helper'))
                            ->columns(3),
                    ])
                    ->columns(2),
            ]);
    }

    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                Tables\Columns\TextColumn::make('name')
                    ->searchable()
                    ->sortable(),

                Tables\Columns\TextColumn::make('slug')
                    ->searchable()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('description')
                    ->searchable()
                    ->limit(50)
                    ->toggleable(),

                Tables\Columns\TextColumn::make('permissions_count')
                    ->label(__('tenancy.permissions'))
                    ->counts('permissions')
                    ->sortable(),

                Tables\Columns\TextColumn::make('users_count')
                    ->label('Users')
                    ->counts('users')
                    ->sortable(),

                Tables\Columns\ColorColumn::make('color')
                    ->label(__('tenancy.color')),

                Tables\Columns\TextColumn::make('created_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),

                Tables\Columns\TextColumn::make('updated_at')
                    ->dateTime()
                    ->sortable()
                    ->toggleable(isToggledHiddenByDefault: true),
            ])
            ->filters([
                Tables\Filters\SelectFilter::make('has_permissions')
                    ->label(__('tenancy.has_permissions'))
                    ->options([
                        'true' => __('tenancy.has_permissions'),
                        'false' => __('tenancy.no_permissions'),
                    ]),

                Tables\Filters\SelectFilter::make('has_users')
                    ->label(__('tenancy.has_users'))
                    ->options([
                        'true' => __('tenancy.has_users'),
                        'false' => __('tenancy.no_users'),
                    ]),
            ])
            ->actions([
                ViewAction::make(),
                EditAction::make(),
                DeleteAction::make()
                    ->requiresConfirmation()
                    ->before(function (Role $record) {
                        if ($record->users()->count() > 0) {
                            throw new \Exception('Cannot delete role with assigned users. Please reassign users first.');
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
                                    throw new \Exception("Cannot delete role '{$record->name}' with assigned users. Please reassign users first.");
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
            'index' => \AngelitoSystems\FilamentTenancy\Resources\RoleResource\Pages\ListRoles::route('/'),
            'create' => \AngelitoSystems\FilamentTenancy\Resources\RoleResource\Pages\CreateRole::route('/create'),
            'view' => \AngelitoSystems\FilamentTenancy\Resources\RoleResource\Pages\ViewRole::route('/{record}'),
            'edit' => \AngelitoSystems\FilamentTenancy\Resources\RoleResource\Pages\EditRole::route('/{record}/edit'),
        ];
    }
}
