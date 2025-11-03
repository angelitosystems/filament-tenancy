# Traducciones Simplificadas en Filament Tenancy

El paquete ahora soporta un sistema de traducciones simplificado que permite usar claves más cortas y legibles en lugar de las claves largas del paquete.

## Antes vs Después

### **Antes (Claves largas):**
```php
// En propiedades estáticas
protected static ?string $navigationLabel = 'filament-tenancy::tenancy.navigation.plans';
protected static ?string $modelLabel = 'filament-tenancy::tenancy.resources.plan.singular';

// En formularios y tablas
Section::make('filament-tenancy::tenancy.sections.plan_information')
TextInput::make('name')->label('filament-tenancy::tenancy.fields.name')
```

### **Después (Claves simplificadas):**
```php
// En propiedades estáticas
protected static ?string $navigationLabel = 'filament-tenancy::tenancy.navigation.plans';
protected static ?string $modelLabel = 'filament-tenancy::tenancy.resources.plan.singular';

// En formularios y tablas (con trait)
Section::make(__('tenancy.plan_information'))
TextInput::make('name')->label(__('tenancy.name'))
```

## Uso del Trait `HasSimpleTranslations`

### 1. Agregar el Trait al Resource:

```php
<?php

use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;

class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    // ...
}
```

### 2. Sobrescribir las claves de traducción:

```php
class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    // ... propiedades estáticas
    
    /**
     * Override translation keys (must be public)
     */
    public static function getNavigationKey(): string
    {
        return 'plans';
    }

    public static function getModelKey(): string
    {
        return 'plan';
    }

    public static function getPluralModelKey(): string
    {
        return 'plans';
    }

    public static function getBreadcrumbKey(): string
    {
        return 'plans';
    }
    
    // ...
}
```

### 3. Usar traducciones simplificadas en formularios y tablas:

```php
public static function form(Form $form): Form
{
    return $form
        ->schema([
            Section::make(__('tenancy.plan_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(__('tenancy.name'))
                        ->required(),
                        
                    Select::make('billing_cycle')
                        ->label(__('tenancy.billing_cycle'))
                        ->options([
                            'monthly' => __('tenancy.monthly'),
                            'yearly' => __('tenancy.yearly'),
                        ]),
                ]),
        ]);
}

public static function table(Table $table): Table
{
    return $table
        ->columns([
            TextColumn::make('name')
                ->label(__('tenancy.name')),
                
            TextColumn::make('billing_cycle')
                ->label(__('tenancy.billing_cycle')),
        ])
        ->filters([
            SelectFilter::make('billing_cycle')
                ->options([
                    'monthly' => __('tenancy.monthly'),
                    'yearly' => __('tenancy.yearly'),
                ]),
        ]);
}
```

## Archivos de Traducción

### **Estructura de archivos:**

```
lang/
├── en/
│   ├── tenancy.php           # Traducciones originales (completas)
│   └── tenancy_simple.php    # Traducciones simplificadas
└── es/
    ├── tenancy.php           # Traducciones originales (completas)
    └── tenancy_simple.php    # Traducciones simplificadas
```

### **Publicar traducciones simplificadas:**

```bash
# Publicar todas las traducciones
php artisan filament-tenancy:publish --lang

# Esto publica:
# - resources/lang/vendor/filament-tenancy/en/tenancy.php
# - resources/lang/vendor/filament-tenancy/es/tenancy.php
# - resources/lang/vendor/filament-tenancy/en/tenancy_simple.php
# - resources/lang/vendor/filament-tenancy/es/tenancy_simple.php
```

## Claves de Traducción Disponibles

### **Navegación:**
- `plans` - "Planes" / "Plans"
- `tenants` - "Inquilinos" / "Tenants"
- `roles` - "Roles" / "Roles"
- `permissions` - "Permisos" / "Permissions"
- `users` - "Usuarios" / "Users"
- `subscriptions` - "Suscripciones" / "Subscriptions"

### **Grupos de Navegación:**
- `billing_management` - "Gestión de Facturación" / "Billing Management"
- `user_management` - "Gestión de Usuarios" / "User Management"

### **Modelos:**
- `plan` - "Plan" / "Plan"
- `tenant` - "Inquilino" / "Tenant"
- `role` - "Rol" / "Role"
- `permission` - "Permiso" / "Permission"
- `subscription` - "Suscripción" / "Subscription"

### **Secciones:**
- `plan_information` - "Información del Plan" / "Plan Information"
- `pricing` - "Precios" / "Pricing"
- `features_limits` - "Características y Límites" / "Features & Limits"
- `basic_information` - "Información Básica" / "Basic Information"

### **Campos:**
- `name` - "Nombre" / "Name"
- `slug` - "Slug" / "Slug"
- `description` - "Descripción" / "Description"
- `price` - "Precio" / "Price"
- `billing_cycle` - "Ciclo de Facturación" / "Billing Cycle"
- `is_active` - "Activo" / "Active"
- `is_popular` - "Popular" / "Popular"
- `is_featured` - "Destacado" / "Featured"

### **Ciclos de Facturación:**
- `monthly` - "Mensual" / "Monthly"
- `yearly` - "Anual" / "Yearly"
- `quarterly` - "Trimestral" / "Quarterly"
- `lifetime` - "De por vida" / "Lifetime"

### **Acciones:**
- `view` - "Ver" / "View"
- `edit` - "Editar" / "Edit"
- `create` - "Crear" / "Create"
- `delete` - "Eliminar" / "Delete"
- `save` - "Guardar" / "Save"
- `cancel` - "Cancelar" / "Cancel"

### **Filtros:**
- `all_plans` - "Todos los planes" / "All plans"
- `active_plans` - "Planes activos" / "Active plans"
- `inactive_plans` - "Planes inactivos" / "Inactive plans"
- `popular_plans` - "Planes populares" / "Popular plans"
- `featured_plans` - "Planes destacados" / "Featured plans"

## Ejemplo Completo

### **Resource con traducciones simplificadas:**

```php
<?php

namespace AngelitoSystems\FilamentTenancy\Resources;

use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use Filament\Resources\Resource;
use Filament\Forms\Form;
use Filament\Tables\Table;
use Filament\Forms\Components\Section;
use Filament\Forms\Components\TextInput;
use Filament\Forms\Components\Select;
use Filament\Tables\Columns\TextColumn;
use Filament\Tables\Filters\SelectFilter;

class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    protected static ?string $model = Plan::class;
    protected static string $navigationIcon = 'heroicon-o-credit-card';
    protected static ?int $navigationSort = 1;
    
    // Claves de traducción
    protected static function getNavigationKey(): string
    {
        return 'plans';
    }
    
    protected static function getModelKey(): string
    {
        return 'plan';
    }
    
    public static function form(Form $form): Form
    {
        return $form
            ->schema([
                Section::make(__('tenancy.plan_information'))
                    ->schema([
                        TextInput::make('name')
                            ->label(__('tenancy.name'))
                            ->required(),
                            
                        Select::make('billing_cycle')
                            ->label(__('tenancy.billing_cycle'))
                            ->options([
                                'monthly' => __('tenancy.monthly'),
                                'yearly' => __('tenancy.yearly'),
                            ]),
                    ]),
            ]);
    }
    
    public static function table(Table $table): Table
    {
        return $table
            ->columns([
                TextColumn::make('name')
                    ->label(__('tenancy.name')),
                    
                TextColumn::make('billing_cycle')
                    ->label(__('tenancy.billing_cycle')),
            ])
            ->filters([
                SelectFilter::make('billing_cycle')
                    ->options([
                        'monthly' => __('tenancy.monthly'),
                        'yearly' => __('tenancy.yearly'),
                    ]),
            ]);
    }
}
```

## Beneficios

1. **Código más limpio** - Claves más cortas y legibles
2. **Mantenimiento fácil** - Estructura simple de traducciones
3. **Compatibilidad** - Funciona con el sistema existente
4. **Flexibilidad** - Puedes usar claves largas o simplificadas
5. **Rendimiento** - Las traducciones se cargan igual de rápido

## Notas

- Las propiedades estáticas todavía deben usar las claves largas (`filament-tenancy::tenancy.*`)
- Las traducciones simplificadas solo funcionan en formularios, tablas y métodos dinámicos
- Los archivos de traducción `tenancy_simple.php` son opcionales pero recomendados
- Puedes mezclar ambos sistemas según tus necesidades
- **Importante**: Los métodos que sobrescriben las claves de traducción deben ser `public static` para ser compatibles con la clase base de Filament Resource
