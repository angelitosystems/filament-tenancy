# 🎯 Traducciones Personalizadas con Prefijo

El trait `HasSimpleTranslations` ahora es más robusto y permite personalizar el prefijo de traducción y buscar en múltiples ubicaciones con fallback automático.

## ✨ Características

- **Prefijo personalizable**: Define tu propio namespace de traducciones
- **Búsqueda con fallback**: Busca automáticamente en múltiples ubicaciones
- **Compatibilidad total**: Mantiene compatibilidad con el código existente

## 🔍 Orden de Búsqueda

El trait busca traducciones en el siguiente orden:

1. **Prefijo personalizado** (si está configurado): `{prefix}.{key}`
   - Ejemplo: `tenant.fields.name`
   
2. **Namespace del paquete**: `filament-tenancy::tenancy.{key}`
   - Ejemplo: `filament-tenancy::tenancy.name`
   
3. **Namespace por defecto**: `tenancy.{key}`
   - Ejemplo: `tenancy.name`

## 📝 Uso Básico

### Sin personalización (comportamiento por defecto)

```php
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;

class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    // Busca en: tenancy.{key}
    // Fallback: filament-tenancy::tenancy.{key}
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(static::__('name')), // Busca 'tenancy.name'
        ]);
    }
}
```

## 🎨 Personalización del Prefijo

### Ejemplo 1: Prefijo simple

```php
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;

class TenantResource extends Resource
{
    use HasSimpleTranslations;
    
    /**
     * Personaliza el prefijo de traducción
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'tenant'; // Busca en 'tenant.{key}'
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(static::__('name')), 
                // Busca en orden:
                // 1. tenant.name
                // 2. filament-tenancy::tenancy.name
                // 3. tenancy.name
        ]);
    }
}
```

### Ejemplo 2: Prefijo anidado

```php
use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;

class TenantResource extends Resource
{
    use HasSimpleTranslations;
    
    /**
     * Prefijo anidado para organizar mejor las traducciones
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'tenant.fields'; // Busca en 'tenant.fields.{key}'
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(static::__('name')), 
                // Busca en orden:
                // 1. tenant.fields.name
                // 2. filament-tenancy::tenancy.name
                // 3. tenancy.name
        ]);
    }
}
```

## 📁 Estructura de Archivos de Traducción

### Archivo: `lang/es/tenant.php`

```php
<?php

return [
    'fields' => [
        'name' => 'Nombre del Inquilino',
        'domain' => 'Dominio',
        'slug' => 'Slug',
    ],
    
    'sections' => [
        'basic_information' => 'Información Básica',
        'domain_configuration' => 'Configuración de Dominio',
    ],
];
```

### Archivo: `lang/en/tenant.php`

```php
<?php

return [
    'fields' => [
        'name' => 'Tenant Name',
        'domain' => 'Domain',
        'slug' => 'Slug',
    ],
    
    'sections' => [
        'basic_information' => 'Basic Information',
        'domain_configuration' => 'Domain Configuration',
    ],
];
```

## 🔄 Migración desde Código Existente

El trait es **100% compatible** con el código existente. No necesitas cambiar nada si ya estás usando `HasSimpleTranslations`.

### Antes (sigue funcionando)

```php
class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(__('tenancy.name')), // ✅ Sigue funcionando
        ]);
    }
}
```

### Después (con el trait mejorado)

```php
class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            TextInput::make('name')
                ->label(static::__('name')), // ✅ Más limpio y con fallback
        ]);
    }
}
```

## 💡 Ventajas

1. **Organización**: Separa tus traducciones personalizadas del paquete
2. **Fallback automático**: Si no encuentra la traducción personalizada, busca en el paquete
3. **Flexibilidad**: Puedes usar prefijos simples o anidados
4. **Compatibilidad**: No rompe código existente
5. **Mantenibilidad**: Fácil de mantener y extender

## 🎯 Casos de Uso

### Caso 1: Traducciones específicas por recurso

```php
class UserResource extends Resource
{
    use HasSimpleTranslations;
    
    protected static function getTranslationPrefix(): ?string
    {
        return 'users.fields';
    }
    
    // Todas las traducciones buscarán primero en 'users.fields.{key}'
}
```

### Caso 2: Múltiples prefijos según contexto

```php
class TenantResource extends Resource
{
    use HasSimpleTranslations;
    
    protected static function getTranslationPrefix(): ?string
    {
        // Puedes usar lógica condicional
        if (request()->routeIs('admin.*')) {
            return 'admin.tenant';
        }
        
        return 'tenant';
    }
}
```

### Caso 3: Sobrescribir traducciones del paquete

```php
class PlanResource extends Resource
{
    use HasSimpleTranslations;
    
    protected static function getTranslationPrefix(): ?string
    {
        return 'plans'; // Traducciones personalizadas
    }
    
    // Si 'plans.name' existe, lo usa
    // Si no, busca en 'filament-tenancy::tenancy.name'
    // Si no, busca en 'tenancy.name'
}
```

## 📚 API del Trait

### Métodos Protegidos

- `getTranslationPrefix(): ?string` - Define el prefijo personalizado
- `trans(string $key): string` - Genera la clave de traducción con prefijo
- `__(string $key, array $replace = [], ?string $locale = null): string` - Obtiene traducción con fallback

### Métodos Públicos

- `getNavigationLabel(): string`
- `getNavigationGroup(): ?string`
- `getModelLabel(): string`
- `getPluralModelLabel(): string`
- `getBreadcrumb(): string`

## 🔧 Ejemplo Completo

```php
<?php

namespace App\Filament\Resources;

use AngelitoSystems\FilamentTenancy\Traits\HasSimpleTranslations;
use Filament\Resources\Resource;
use Filament\Schemas\Schema;
use Filament\Forms\Components\TextInput;
use Filament\Schemas\Components\Section;

class CustomTenantResource extends Resource
{
    use HasSimpleTranslations;
    
    /**
     * Personaliza el prefijo de traducción
     */
    protected static function getTranslationPrefix(): ?string
    {
        return 'tenant.fields';
    }
    
    /**
     * Override translation keys
     */
    public static function getNavigationKey(): string
    {
        return 'tenants';
    }
    
    public static function getModelKey(): string
    {
        return 'tenant';
    }
    
    public static function form(Schema $schema): Schema
    {
        return $schema->components([
            Section::make(static::__('basic_information'))
                ->schema([
                    TextInput::make('name')
                        ->label(static::__('name')),
                    
                    TextInput::make('domain')
                        ->label(static::__('domain')),
                ]),
        ]);
    }
}
```

## ✅ Resumen

- ✅ Prefijo personalizable con `getTranslationPrefix()`
- ✅ Búsqueda automática con fallback en múltiples ubicaciones
- ✅ Compatible con código existente
- ✅ Soporta prefijos simples y anidados
- ✅ Organización mejorada de traducciones






